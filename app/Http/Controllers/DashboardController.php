<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $isDashboard = request()->path() === 'dashboard';

        if (!Auth::check()) {
            return $isDashboard ? redirect('/login') : view('landing');
        }

        if (!$isDashboard) {
            return view('landing');
        }

        $user = Auth::user();
        $role = $user->role;
        $tid = tenantId();

        $host = request()->getHost();
        $mainDomain = config('app.main_domain');
        $isMainDomain = ($host === $mainDomain || !str_contains($host, '.'));
        $isPlatformSuperAdmin = $user->isTenantSuperAdmin() && is_null($user->tenant_id);

        if ($isMainDomain) {
            if ($isPlatformSuperAdmin) {
                return $this->platformDashboard();
            }

            if ($user->tenant) {
                $scheme = request()->getScheme();
                $port = request()->getPort();
                $portSuffix = in_array($port, [80, 443]) ? '' : ":{$port}";

                return redirect("{$scheme}://{$user->tenant->slug}." . config('app.tenant_domain') . "{$portSuffix}/dashboard?email=" . urlencode($user->email));
            }

            return view('landing');
        }

        if ($isPlatformSuperAdmin && !$tid) {
            return $this->platformDashboard();
        }

        $totalCustomers = Customer::when($tid, fn($q) => $q->where('tenant_id', $tid))->count();
        $activeCustomers = Customer::when($tid, fn($q) => $q->where('tenant_id', $tid))->where('status', 'active')->count();
        $isolatedCustomers = Customer::when($tid, fn($q) => $q->where('tenant_id', $tid))->where('status', 'isolated')->count();
        $suspendedCustomers = Customer::when($tid, fn($q) => $q->where('tenant_id', $tid))->where('status', 'suspended')->count();

        $thisMonth = Carbon::now()->startOfMonth();
        $today = Carbon::now()->startOfDay();

        $paidInvoicesThisMonth = Invoice::when($tid, fn($q) => $q->where('tenant_id', $tid))
            ->where('status', 'paid')
            ->where('updated_at', '>=', $thisMonth)
            ->count();
        $paidRevenueThisMonth = Invoice::when($tid, fn($q) => $q->where('tenant_id', $tid))
            ->where('status', 'paid')
            ->where('updated_at', '>=', $thisMonth)
            ->sum(DB::raw('amount - COALESCE(discount, 0) + COALESCE(tax, 0)'));

        $todayPayments = Payment::when($tid, fn($q) => $q->where('tenant_id', $tid))
            ->whereDate('created_at', $today)->sum('amount');

        $unpaidInvoices = Invoice::when($tid, fn($q) => $q->where('tenant_id', $tid))
            ->where('status', 'unpaid')->count();
        $overdueInvoices = Invoice::when($tid, fn($q) => $q->where('tenant_id', $tid))
            ->where('status', 'unpaid')
            ->where('due_date', '<', Carbon::now())
            ->count();
        $totalUnpaidAmount = Invoice::when($tid, fn($q) => $q->where('tenant_id', $tid))
            ->where('status', 'unpaid')
            ->sum(DB::raw('amount - COALESCE(discount, 0) + COALESCE(tax, 0)'));
        $totalOverdueAmount = Invoice::when($tid, fn($q) => $q->where('tenant_id', $tid))
            ->where('status', 'unpaid')
            ->where('due_date', '<', Carbon::now())
            ->sum(DB::raw('amount - COALESCE(discount, 0) + COALESCE(tax, 0)'));

        $invoicedCustomerIds = Invoice::when($tid, fn($q) => $q->where('tenant_id', $tid))
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->pluck('customer_id');
        $unbilledCustomers = Customer::when($tid, fn($q) => $q->where('tenant_id', $tid))
            ->where('status', 'active')
            ->whereNotIn('id', $invoicedCustomerIds)
            ->count();

        $recentPayments = Payment::with(['customer', 'invoice'])
            ->when($tid, fn($q) => $q->where('tenant_id', $tid))
            ->where('status', 'success')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentInvoices = Invoice::with('customer')
            ->when($tid, fn($q) => $q->where('tenant_id', $tid))
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $topCustomers = Payment::with('customer')
            ->when($tid, fn($q) => $q->where('tenant_id', $tid))
            ->select('customer_id', DB::raw('SUM(amount) as total_paid'))
            ->where('status', 'success')
            ->groupBy('customer_id')
            ->orderByDesc('total_paid')
            ->limit(5)
            ->get();

        $revenueChart = $this->getRevenueChart($tid);
        $customerChart = $this->getCustomerChart($tid);
        $paymentMethodChart = $this->getPaymentMethodChart($tid);
        $collectionRate = $this->getCollectionRate($tid);

        return view('dashboard', compact(
            'role',
            'totalCustomers', 'activeCustomers', 'isolatedCustomers', 'suspendedCustomers',
            'paidInvoicesThisMonth', 'paidRevenueThisMonth', 'todayPayments',
            'unpaidInvoices', 'overdueInvoices', 'totalUnpaidAmount', 'totalOverdueAmount',
            'unbilledCustomers', 'recentPayments', 'recentInvoices', 'topCustomers',
            'revenueChart', 'customerChart', 'paymentMethodChart', 'collectionRate'
        ));
    }

    protected function platformDashboard()
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('status', 'active')->count();
        $pendingTenants = Tenant::where('status', 'pending')->count();
        $suspendedTenants = Tenant::where('status', 'suspended')->count();

        $totalUsers = User::whereNotNull('tenant_id')->count();
        $totalCustomers = Customer::count();
        $totalRevenue = Payment::where('status', 'success')->sum('amount');

        return view('dashboard-platform', compact(
            'totalTenants', 'activeTenants', 'pendingTenants', 'suspendedTenants',
            'totalUsers', 'totalCustomers', 'totalRevenue'
        ));
    }

    protected function getRevenueChart($tid): array
    {
        $days = 30;
        $chart = [];
        $start = today()->subDays($days - 1);

        $payments = Payment::when($tid, fn($q) => $q->where('tenant_id', $tid))
            ->where('status', 'success')
            ->where('paid_at', '>=', $start)
            ->selectRaw('DATE(paid_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $key = $date->format('Y-m-d');
            $chart[] = [
                'date' => $date->format('d M'),
                'total' => (int) ($payments[$key] ?? 0),
            ];
        }

        return $chart;
    }

    protected function getCustomerChart($tid): array
    {
        $months = 12;
        $chart = [];
        $start = now()->subMonths($months - 1)->startOfMonth();

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $endOfMonth = $month->copy()->endOfMonth();

            $count = Customer::when($tid, fn($q) => $q->where('tenant_id', $tid))
                ->where('created_at', '<=', $endOfMonth)
                ->where(function ($q) use ($endOfMonth) {
                    $q->whereNull('deleted_at')
                        ->orWhere('deleted_at', '>', $endOfMonth);
                })
                ->count();

            $chart[] = [
                'label' => $month->format('M Y'),
                'total' => $count,
            ];
        }

        return $chart;
    }

    protected function getPaymentMethodChart($tid): array
    {
        $methods = Payment::when($tid, fn($q) => $q->where('tenant_id', $tid))
            ->where('status', 'success')
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        return $methods->toArray();
    }

    protected function getCollectionRate($tid): array
    {
        $total = Invoice::when($tid, fn($q) => $q->where('tenant_id', $tid))
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $paid = Invoice::when($tid, fn($q) => $q->where('tenant_id', $tid))
            ->where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $rate = $total > 0 ? round($paid / $total * 100) : 0;

        return [
            'total' => $total,
            'paid' => $paid,
            'rate' => $rate,
        ];
    }
}
