<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use App\Services\InvoiceNumberGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'package', 'payments'])
            ->where('tenant_id', tenantId());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('date_from')) {
            $query->where('due_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('due_date', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('due_date')->paginate(20);

        return view('invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $tenantId = tenantId();
        $customers = Customer::with('package')->where('tenant_id', $tenantId)->orderBy('name')->get();

        return view('invoices.create', compact('customers'));
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $tenantId = tenantId();
        $customers = Customer::with('package')->where('tenant_id', $tenantId)->orderBy('name')->get();

        return view('invoices.edit', compact('invoice', 'customers'));
    }

    public function show(Invoice $invoice)
    {
        abort_unless($invoice->tenant_id === tenantId(), 403);

        $chargeMethodMapper = [
            'invoice_items' => 'Per Item',
            'flat' => 'Flat Rate',
            'percentage' => 'Percentage',
        ];

        $invoice->load(['package', 'customer', 'invoiceItems']);

        return view('invoices.show', compact('invoice', 'chargeMethodMapper'));
    }

    public function pdf(Invoice $invoice)
    {
        abort_unless($invoice->tenant_id === tenantId(), 403);

        $invoice->load(['package', 'customer', 'invoiceItems']);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact('invoice'))
            ->download("invoice-{$invoice->invoice_number}.pdf");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('customers', 'id')->where(fn ($q) => $q->where('tenant_id', tenantId())),
            ],
            'due_date' => 'nullable|date',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:65535',
        ]);

        $customer = Customer::with('package')
            ->where('tenant_id', tenantId())
            ->findOrFail($validated['customer_id']);
        $package = $customer->package;

        if (! $package) {
            return back()->withErrors(['customer_id' => 'Pelanggan ini belum memiliki paket.'])->withInput();
        }

        $billingDate = Carbon::now();
        $periodStart = $billingDate->copy()->startOfMonth();
        $periodEnd = $billingDate->copy()->endOfMonth();
        $ppnRate = (float) config('services.tax.ppn_rate', 0.11);

        $prorated = false;
        $amount = $package->price;

        if ($customer->join_date && $customer->join_date->isSameMonth($billingDate)) {
            $daysInMonth = $billingDate->daysInMonth;
            $dailyRate = $package->price / $daysInMonth;
            $days = $daysInMonth - $customer->join_date->day + 1;
            $amount = round($dailyRate * $days, 2);
            $prorated = true;
        }

        $tax = $package->is_taxable ? round($amount * $ppnRate, 2) : 0;
        $discount = (float) ($validated['discount'] ?? 0);
        $totalAmount = $amount + $tax - $discount;

        $dueDate = ! empty($validated['due_date'])
            ? $validated['due_date']
            : $billingDate->copy()->addDays(5)->format('Y-m-d');

        $invoice = Invoice::create([
            'tenant_id' => tenantId(),
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'invoice_number' => InvoiceNumberGenerator::generate(),
            'period_start' => $periodStart->format('Y-m-d'),
            'period_end' => $periodEnd->format('Y-m-d'),
            'amount' => $amount,
            'discount' => $discount,
            'tax' => $tax,
            'total_amount' => $totalAmount,
            'due_date' => $dueDate,
            'status' => 'unpaid',
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($prorated) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'tenant_id' => tenantId(),
                'description' => "Paket {$package->name} (Prorata {$days} hari)",
                'days' => $days,
                'unit_price' => round($amount / $days, 2),
                'subtotal' => $amount,
            ]);
        } else {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'tenant_id' => tenantId(),
                'description' => "Paket {$package->name} ({$billingDate->daysInMonth} hari)",
                'days' => $billingDate->daysInMonth,
                'unit_price' => $package->price / $billingDate->daysInMonth,
                'subtotal' => $amount,
            ]);
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice berhasil dibuat secara otomatis.');
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'package_id' => 'nullable|exists:packages,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'nullable|in:unpaid,paid,overdue,partial,cancelled',
            'notes' => 'nullable|string|max:65535',
        ]);

        $validated['total_amount'] = ($validated['amount'] ?? 0) + ($validated['tax'] ?? 0) - ($validated['discount'] ?? 0);
        $invoice->update($validated);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice berhasil diperbarui.');
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        $invoice->update(['status' => 'cancelled']);

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice berhasil dibatalkan.');
    }

    public function pay(Invoice $invoice, Request $request)
    {
        $this->authorize('pay', $invoice);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,transfer,va,bank_transfer',
            'payment_number' => 'nullable|required_if:payment_method,transfer|regex:/^[0-9]+$/|max:50',
            'notes' => 'nullable|string|max:65535',
        ]);

        $remaining = $invoice->remaining_amount;
        if ($validated['amount'] > $remaining) {
            return back()->withErrors(['amount' => "Jumlah pembayaran (Rp" . number_format($validated['amount'], 0, ',', '.') . ") melebihi sisa tagihan (Rp" . number_format($remaining, 0, ',', '.') . ")."])->withInput();
        }

        $payment = Payment::create([
            'tenant_id' => tenantId(),
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['payment_number'],
            'status' => 'success',
            'paid_at' => now(),
            'verified_by' => Auth::id(),
        ]);

        $invoice->recalculateStatus();

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function addManualPayment(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->tenant_id === tenantId(), 403);
        $this->authorize('manage_payments');

        $validated = $request->validate([
            'payment_method' => 'required|string|in:cash,transfer,va,bank_transfer,e-wallet,qris',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:65535',
        ]);

        $payment = Payment::create([
            'tenant_id' => $invoice->tenant_id,
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'amount' => $request->input('amount', $invoice->remaining_amount),
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'],
            'notes' => $validated['notes'],
            'status' => 'pending',
        ]);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Pembayaran manual berhasil dicatat dan menunggu verifikasi.');
    }
}