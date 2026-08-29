<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\MidtransService;
use App\Services\XenditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerPortalController extends Controller
{
    public function profile()
    {
        $customer = Auth::guard('customer')->user();
        $customer->load('package', 'tenant');

        $activeInvoiceCount = $customer->invoices()
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->count();

        $totalDue = $customer->invoices()
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->sum('total_amount');

        $subscribePackage = null;
        $subscribePackageId = session('subscribe_package_id');
        if ($subscribePackageId) {
            session()->forget('subscribe_package_id');
            $subscribePackage = \App\Models\Package::where('id', $subscribePackageId)
                ->where('tenant_id', $customer->tenant_id)
                ->first();
        }

        return view('customer.portal.profile', compact('customer', 'activeInvoiceCount', 'totalDue', 'subscribePackage'));
    }

    public function updateProfile(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                \Illuminate\Validation\Rule::unique('customers', 'email')
                    ->ignore($customer->id)
                    ->where(fn ($q) => $q->where('tenant_id', $customer->tenant_id)),
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                \Illuminate\Validation\Rule::unique('customers', 'phone')
                    ->ignore($customer->id)
                    ->where(fn ($q) => $q->where('tenant_id', $customer->tenant_id)),
            ],
            'address' => 'required|string|max:500',
        ]);

        $customer->update($validated);

        return back()->with('success', 'Data profil berhasil diperbarui.');
    }

    public function invoices(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $query = $customer->invoices();

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('sort')) {
            match ($request->sort) {
                'newest' => $query->orderByDesc('created_at'),
                'oldest' => $query->orderBy('created_at'),
                'due_soon' => $query->orderBy('due_date'),
                default => $query->orderByDesc('created_at'),
            };
        } else {
            $query->orderByDesc('created_at');
        }

        $invoices = $query->paginate(10);

        return view('customer.portal.invoices', compact('invoices'));
    }

    public function showInvoice($id)
    {
        $customer = Auth::guard('customer')->user();
        $invoice = $customer->invoices()->find($id);

        if (! $invoice) {
            abort(403, 'Anda tidak memiliki akses ke invoice ini.');
        }

        $invoice->load('customer', 'package', 'invoiceItems', 'payments');

        $tenant = $invoice->tenant;
        $tenantSettings = $tenant ? $tenant->getDecryptedSettings() : [];

        $bankAccounts = collect($tenantSettings['payment_bank_accounts'] ?? [])
            ->filter(fn ($acc) => ! empty($acc['bank']) && ! empty($acc['account_number']))
            ->values();

        $hasGateway = ! empty($tenantSettings['midtrans_server_key'] ?? '')
            || ! empty($tenantSettings['xendit_api_key'] ?? '');

        return view('customer.portal.invoice-detail', compact('invoice', 'bankAccounts', 'hasGateway'));
    }

    public function downloadInvoice($id)
    {
        $customer = Auth::guard('customer')->user();
        $invoice = $customer->invoices()->with('customer', 'package', 'invoiceItems')->find($id);

        if (! $invoice) {
            abort(403, 'Anda tidak memiliki akses ke invoice ini.');
        }

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    public function payInvoice(Request $request, $id)
    {
        $customer = Auth::guard('customer')->user();
        $invoice = $customer->invoices()->find($id);

        if (! $invoice) {
            abort(403, 'Anda tidak memiliki akses ke invoice ini.');
        }

        if ($invoice->status === 'paid') {
            return back()->with('info', 'Invoice ini sudah lunas.');
        }

        try {
            $tenant = $invoice->tenant;
        $tenantSettings = $tenant ? $tenant->getDecryptedSettings() : [];

            $useXendit = ! empty($tenantSettings['xendit_api_key'] ?? '')
                && empty($tenantSettings['midtrans_server_key'] ?? '');

            $result = $useXendit
                ? (new XenditService($tenantSettings))->createTransaction($invoice)
                : (new MidtransService($tenantSettings))->createTransaction($invoice);

            if ($result['success'] && ! empty($result['redirect_url'])) {
                return redirect()->away($result['redirect_url']);
            }

            return back()->with('error', 'Gagal mendapatkan URL pembayaran.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat memproses pembayaran.');
        }
    }

    public function submitManualPayment(Request $request, $id)
    {
        $customer = Auth::guard('customer')->user();
        $invoice = $customer->invoices()->find($id);

        if (! $invoice) {
            abort(403, 'Anda tidak memiliki akses ke invoice ini.');
        }

        if ($invoice->status === 'paid') {
            return back()->with('info', 'Invoice ini sudah lunas.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:transfer',
            'reference_number' => 'required_if:payment_method,transfer|nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'proof_of_payment' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $payment = Payment::create([
            'tenant_id' => $invoice->tenant_id,
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'payment_number' => 'MAN/'.date('YmdHis').'/'.$invoice->tenant_id.'/'.random_int(1000, 9999),
            'amount' => $invoice->remaining_amount,
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        $proofPath = $request->file('proof_of_payment')
            ->store('payments/'.$invoice->tenant_id, 'public');

        $payment->update([
            'proof_of_payment' => $proofPath,
        ]);

        return redirect()
            ->route('customer.portal.payments', ['invoice_id' => $invoice->id])
            ->with('success', 'Bukti pembayaran terkirim. Menunggu verifikasi admin.');
    }

    public function payQris($id)
    {
        $customer = Auth::guard('customer')->user();
        $invoice = $customer->invoices()->find($id);

        if (! $invoice) {
            abort(403, 'Anda tidak memiliki akses ke invoice ini.');
        }

        if ($invoice->status === 'paid') {
            return back()->with('info', 'Invoice ini sudah lunas.');
        }

        $tenant = $invoice->tenant;
        $tenantSettings = $tenant ? $tenant->getDecryptedSettings() : [];

        if (empty($tenantSettings['xendit_api_key'] ?? '')) {
            return back()->with('error', 'QRIS belum diaktifkan oleh penyedia layanan.');
        }

        try {
            $result = (new XenditService($tenantSettings))->createQris($invoice);

            if (! $result['success']) {
                return back()->with('error', 'Gagal membuat kode QRIS.');
            }

            return redirect()->route('customer.portal.invoices.pay.qris.show', $invoice->id);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat membuat QRIS.');
        }
    }

    public function showQris($id)
    {
        $customer = Auth::guard('customer')->user();
        $invoice = $customer->invoices()->find($id);

        if (! $invoice) {
            abort(403, 'Anda tidak memiliki akses ke invoice ini.');
        }

        $payment = $invoice->payments()
            ->where('payment_method', 'qris')
            ->whereIn('status', ['pending'])
            ->latest('id')
            ->first();

        if (! $payment || empty($payment->qr_string) || empty($payment->gateway_transaction_id)) {
            return redirect()->route('customer.portal.invoices.show', $invoice->id)
                ->with('info', 'Belum ada QRIS aktif untuk invoice ini.');
        }

        $qrDataUri = $this->renderQrDataUri($payment->qr_string);

        return view('customer.portal.qris-payment', compact('invoice', 'payment', 'qrDataUri'));
    }

    public function checkQris($id)
    {
        $customer = Auth::guard('customer')->user();
        $invoice = Invoice::find($id);

        if (! $invoice || $invoice->customer_id !== $customer->id) {
            abort(403, 'Anda tidak memiliki akses ke invoice ini.');
        }

        $payment = $invoice->payments()
            ->where('payment_method', 'qris')
            ->whereIn('status', ['pending'])
            ->latest('id')
            ->first();

        if (! $payment || empty($payment->gateway_transaction_id)) {
            return redirect()->route('customer.portal.invoices.show', $invoice->id)
                ->with('info', 'Belum ada QRIS aktif untuk invoice ini.');
        }

        $tenantSettings = $invoice->tenant?->getDecryptedSettings() ?? [];
        $status = (new XenditService($tenantSettings))->getQrisStatus($payment->gateway_transaction_id);

if ($status === 'COMPLETED') {
            $payment->update([
                'status' => 'success',
                'paid_at' => now(),
            ]);
            $invoice->recalculateStatus();
            return redirect()->route('customer.portal.invoices.show', $invoice->id)
                ->with('success', 'Pembayaran QRIS terkonfirmasi. Terima kasih!');
        }

        if ($status === 'EXPIRED' || $status === 'FAILED') {
            $payment->update(['status' => 'expired']);

            return redirect()->route('customer.portal.invoices.show', $invoice->id)
                ->with('info', 'QRIS sudah tidak berlaku. Silakan buat ulang.');
        }

        return back()->with('info', 'Pembayaran belum terdeteksi. Pastikan kamu sudah memindai dan menyelesaikan pembayaran.');
    }

    public function payments(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $query = Payment::whereIn(
            'invoice_id',
            $customer->invoices()->pluck('id')
        )->orderByDesc('created_at');

        if ($request->has('invoice_id') && $request->invoice_id) {
            $query->where('invoice_id', $request->invoice_id);
        }

        $payments = $query->paginate(10);

        return view('customer.portal.payments', compact('payments'));
    }

    protected function renderQrDataUri(string $qrString): string
    {
        $qrCode = new QrCode($qrString, size: 280, margin: 10);
        $writer = new PngWriter;

        return $writer->write($qrCode)->getDataUri();
    }
}
