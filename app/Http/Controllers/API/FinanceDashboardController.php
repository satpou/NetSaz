<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinanceDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('tenant');
    }

    public function dashboard(Request $request)
    {
        $tenantId = session('active_tenant_id');

        $dateRange = [
            'start' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'end' => Carbon::now()->endOfMonth()->format('Y-m-d'),
        ];

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $dateRange['start'] = $request->input('start_date');
            $dateRange['end'] = $request->input('end_date');
        }

        $response = [
            'total_billed_this_month' => $this->getTotalBilled($tenantId, $dateRange),
            'total_paid_this_month' => $this->getTotalPaid($tenantId, $dateRange),
            'total_outstanding' => $this->getTotalOutstanding($tenantId),
            'daily_cashflow' => $this->getDailyCashflow($tenantId),
            'top_overdue_customers' => $this->getTopOverdueCustomers($tenantId),
            'payment_method_breakdown' => $this->getPaymentMethodBreakdown($tenantId, $dateRange),
        ];

        return response()->json($response);
    }

    private function getTotalBilled(int $tenantId, array $dateRange): float
    {
        return (float) Invoice::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->sum('total_amount');
    }

    private function getTotalPaid(int $tenantId, array $dateRange): float
    {
        return (float) Payment::where('tenant_id', $tenantId)
            ->where('status', 'success')
            ->whereBetween('paid_at', [$dateRange['start'], $dateRange['end']])
            ->sum('amount');
    }

    private function getTotalOutstanding(int $tenantId): float
    {
        return (float) Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->sum('total_amount');
    }

    private function getDailyCashflow(int $tenantId): array
    {
        $startDate = Carbon::now()->subDays(29)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $dailyPayments = Payment::selectRaw('DATE(paid_at) as date, SUM(amount) as total')
            ->where('tenant_id', $tenantId)
            ->where('status', 'success')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $cashflow = [];
        $current = Carbon::parse($startDate);

        while ($current <= Carbon::parse($endDate)) {
            $date = $current->format('Y-m-d');
            $cashflow[] = [
                'date' => $date,
                'amount' => (float) ($dailyPayments[$date]->total ?? 0),
            ];
            $current->addDay();
        }

        return $cashflow;
    }

    private function getTopOverdueCustomers(int $tenantId): array
    {
        $customers = Customer::with(['invoices' => function ($query) {
            $query->whereIn('status', ['unpaid', 'overdue', 'partial']);
        }])
            ->where('tenant_id', $tenantId)
            ->whereHas('invoices', function ($query) {
                $query->whereIn('status', ['unpaid', 'overdue', 'partial']);
            })
            ->get()
            ->map(function ($customer) {
                $totalOverdue = $customer->invoices
                    ->whereIn('status', ['unpaid', 'overdue', 'partial'])
                    ->sum('total_amount');

                $latestInvoice = $customer->invoices
                    ->whereIn('status', ['unpaid', 'overdue', 'partial'])
                    ->sortByDesc('due_date')
                    ->first();

                return [
                    'customer_id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'total_overdue' => (float) $totalOverdue,
                    'latest_invoice_id' => $latestInvoice?->id,
                    'latest_invoice_number' => $latestInvoice?->invoice_number,
                    'latest_due_date' => $latestInvoice?->due_date,
                ];
            })
            ->sortByDesc('total_overdue')
            ->take(5)
            ->values()
            ->toArray();

        return $customers;
    }

    private function getPaymentMethodBreakdown(int $tenantId, array $dateRange): array
    {
        $payments = Payment::selectRaw('payment_method, SUM(amount) as total')
            ->where('tenant_id', $tenantId)
            ->where('status', 'success')
            ->whereBetween('paid_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy('payment_method')
            ->get();

        $totalAmount = $payments->sum('total');

        if ($totalAmount === 0) {
            return [];
        }

        $breakdown = $payments->map(function ($payment) use ($totalAmount) {
            $percentage = ($payment->total / $totalAmount) * 100;

            return [
                'method' => $payment->payment_method,
                'total' => (float) $payment->total,
                'percentage' => round($percentage, 2),
            ];
        })->toArray();

        return $breakdown;
    }
}
