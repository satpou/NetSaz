<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlatformReportController extends Controller
{
    public function index(Request $request)
    {
        $tenantsCount = Tenant::count();
        $activeTenantsCount = Tenant::where('status', 'active')->count();

        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', 'active')->count();
        $isolatedCustomers = Customer::where('status', 'isolated')->count();
        $suspendedCustomers = Customer::where('status', 'suspended')->count();

        $totalUsers = User::whereNotNull('tenant_id')->count();
        $adminUsers = User::whereNotNull('tenant_id')->whereIn('role', ['admin', 'tenant_admin', 'super_admin'])->count();
        $staffUsers = User::whereNotNull('tenant_id')->where('role', 'staff')->count();

        $totalRevenue = Payment::where('status', 'success')->sum('amount');
        $thisMonthRevenue = Payment::where('status', 'success')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        $totalInvoices = Invoice::count();
        $paidInvoices = Invoice::where('status', 'paid')->count();
        $unpaidInvoices = Invoice::where('status', 'unpaid')->count();

        // Breakdown per ISP
        $tenantQuery = Tenant::with(['activeSubscription.plan'])
            ->withCount(['customers', 'users', 'invoices'])
            ->withSum(['payments as total_revenue' => function ($q) {
                $q->where('status', 'success');
            }], 'amount')
            ->withSum(['payments as this_month_revenue' => function ($q) {
                $q->where('status', 'success')
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
            }], 'amount');

        if ($request->filled('search')) {
            $search = $request->search;
            $tenantQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $tenantBreakdown = $tenantQuery->orderByDesc('total_revenue')->paginate(15)->withQueryString();

        return view('admin.reports.index', compact(
            'tenantsCount',
            'activeTenantsCount',
            'totalCustomers',
            'activeCustomers',
            'isolatedCustomers',
            'suspendedCustomers',
            'totalUsers',
            'adminUsers',
            'staffUsers',
            'totalRevenue',
            'thisMonthRevenue',
            'totalInvoices',
            'paidInvoices',
            'unpaidInvoices',
            'tenantBreakdown'
        ));
    }

    public function export()
    {
        $tenants = Tenant::with(['activeSubscription.plan'])
            ->withCount(['customers', 'users', 'invoices'])
            ->withSum(['payments as total_revenue' => function ($q) {
                $q->where('status', 'success');
            }], 'amount')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="laporan-platform-netsaz-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($tenants) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Nama ISP', 'Subdomain', 'Email', 'Status', 'Paket Aktif', 'Total Pelanggan', 'Total Pengguna', 'Total Invoice', 'Total Revenue (Rp)']);

            $no = 1;
            foreach ($tenants as $t) {
                fputcsv($file, [
                    $no++,
                    $t->name,
                    $t->slug,
                    $t->email,
                    strtoupper($t->status),
                    $t->activeSubscription?->plan?->name ?? 'None',
                    $t->customers_count,
                    $t->users_count,
                    $t->invoices_count,
                    $t->total_revenue ?? 0,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
