<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $thisMonth = now()->startOfMonth();

        $monthlyRevenue = Payment::where('status', 'success')
            ->where('paid_at', '>=', $thisMonth)
            ->sum('amount');

        $totalOutstanding = Invoice::whereIn('status', ['unpaid', 'overdue'])
            ->sum('total_amount');

        $customerCount = Customer::count();
        $invoiceCount = Invoice::whereMonth('created_at', now()->month)->count();

        return view('reports.index', compact(
            'monthlyRevenue', 'totalOutstanding', 'customerCount', 'invoiceCount'
        ));
    }

    public function exportCustomers(Request $request)
    {
        $format = $request->query('format', 'csv');

        $customers = Customer::with('package')
            ->orderBy('name')
            ->get();

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="pelanggan-' . now()->format('Y-m-d') . '.csv"',
            ];

            $callback = function () use ($customers) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF");
                fputcsv($handle, ['Nama', 'Email', 'No HP', 'Alamat', 'Paket', 'Status', 'Tanggal Gabung']);

                foreach ($customers as $c) {
                    fputcsv($handle, [
                        $c->name,
                        $c->email,
                        $c->phone,
                        $c->address,
                        $c->package->name ?? '-',
                        $c->status,
                        $c->join_date ? $c->join_date->format('d/m/Y') : '-',
                    ]);
                }

                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        }

        return back()->with('error', 'Format tidak didukung.');
    }

    public function exportInvoices(Request $request)
    {
        $format = $request->query('format', 'csv');
        $status = $request->query('status');

        $query = Invoice::with('customer');
        if ($status) {
            $query->where('status', $status);
        }
        $invoices = $query->orderByDesc('created_at')->get();

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="invoice-' . now()->format('Y-m-d') . '.csv"',
            ];

            $callback = function () use ($invoices) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF");
                fputcsv($handle, ['No Invoice', 'Pelanggan', 'Periode Awal', 'Periode Akhir', 'Total', 'Diskon', 'Pajak', 'Total Bayar', 'Status', 'Jatuh Tempo']);

                foreach ($invoices as $inv) {
                    fputcsv($handle, [
                        $inv->invoice_number,
                        $inv->customer->name ?? '-',
                        $inv->period_start ? $inv->period_start->format('d/m/Y') : '-',
                        $inv->period_end ? $inv->period_end->format('d/m/Y') : '-',
                        $inv->amount,
                        $inv->discount,
                        $inv->tax,
                        $inv->total_amount,
                        $inv->status,
                        $inv->due_date->format('d/m/Y'),
                    ]);
                }

                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        }

        return back()->with('error', 'Format tidak didukung.');
    }

    public function exportPayments(Request $request)
    {
        $format = $request->query('format', 'csv');
        $startDate = $request->query('start', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end', now()->format('Y-m-d'));

        $payments = Payment::with(['customer', 'invoice'])
            ->where('status', 'success')
            ->whereDate('paid_at', '>=', $startDate)
            ->whereDate('paid_at', '<=', $endDate)
            ->orderByDesc('paid_at')
            ->get();

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="pembayaran-' . now()->format('Y-m-d') . '.csv"',
            ];

            $callback = function () use ($payments) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF");
                fputcsv($handle, ['No Pembayaran', 'Invoice', 'Pelanggan', 'Tanggal', 'Metode', 'Jumlah', 'Status']);

                foreach ($payments as $pmt) {
                    fputcsv($handle, [
                        $pmt->payment_number,
                        $pmt->invoice->invoice_number ?? '-',
                        $pmt->customer->name ?? $pmt->invoice->customer->name ?? '-',
                        $pmt->paid_at ? $pmt->paid_at->format('d/m/Y') : $pmt->created_at->format('d/m/Y'),
                        $pmt->payment_method,
                        $pmt->amount,
                        $pmt->status,
                    ]);
                }

                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        }

        return back()->with('error', 'Format tidak didukung.');
    }

    public function handleExport(Request $request, $type)
    {
        return match ($type) {
            'customers' => $this->exportCustomers($request),
            'invoices' => $this->exportInvoices($request),
            'payments' => $this->exportPayments($request),
            'revenue' => $this->exportRevenue($request),
            default => abort(404),
        };
    }

    public function exportRevenue(Request $request)
    {
        $year = $request->query('year', now()->year);

        $monthly = Payment::where('status', 'success')
            ->whereYear('paid_at', $year)
            ->selectRaw("strftime('%m', paid_at) as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $months = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="pendapatan-tahunan-' . $year . '.csv"',
        ];

        $callback = function () use ($monthly, $months, $year) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Bulan', 'Pendapatan']);

            $grandTotal = 0;
            foreach ($months as $num => $name) {
                $total = (int) ($monthly[$num] ?? 0);
                $grandTotal += $total;
                fputcsv($handle, [$name, $total]);
            }

            fputcsv($handle, ['TOTAL', $grandTotal]);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
