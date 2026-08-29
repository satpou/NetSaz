<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['invoice', 'invoice.customer'])
            ->where('tenant_id', session('active_tenant_id'));

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }

        $payments = $query->orderByDesc('created_at')->paginate(20);

        return view('payments.index', compact('payments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string|max:100',
            'proof_of_payment' => 'required_if:payment_method,transfer|nullable|image|mimes:jpeg,png,jpg|max:2048',
            'notes' => 'nullable|string|max:65535',
        ], [
            'proof_of_payment.required_if' => 'Bukti pembayaran wajib diunggah untuk metode transfer.',
        ]);

        $invoice = Invoice::with('customer')
            ->where('tenant_id', session('active_tenant_id'))
            ->findOrFail($request->invoice_id);

        if (!$this->isStaffOrAdmin()) {
            abort(403, 'Anda tidak memiliki izin.');
        }

        if ($request->amount > $invoice->remaining_amount) {
            return redirect()->back()
                ->withErrors(['amount' => 'Jumlah pembayaran melebihi sisa tagihan invoice.'])
                ->withInput();
        }

        $proofPath = null;
        if ($request->hasFile('proof_of_payment')) {
            $proofPath = $request->file('proof_of_payment')
                ->store('payments/' . session('active_tenant_id'), 'public');
        }

        $payment = Payment::create([
            'tenant_id' => session('active_tenant_id'),
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'reference_number' => $request->reference_number,
            'proof_of_payment' => $proofPath,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Pembayaran berhasil dikirim dan menunggu verifikasi.');
    }

    public function show(Payment $payment)
    {
        abort_unless($payment->tenant_id === session('active_tenant_id'), 403);

        $payment->load(['invoice.customer', 'customer', 'verifiedBy']);

        return view('payments.show', compact('payment'));
    }

    public function receipt(Payment $payment)
    {
        abort_unless($payment->tenant_id === session('active_tenant_id'), 403);

        $payment->load(['invoice.customer', 'invoice.package', 'customer']);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.receipt', compact('payment'))
            ->download("kuitansi-{$payment->payment_number}.pdf");
    }

    public function verify(Payment $payment)
    {
        abort_unless($payment->tenant_id === session('active_tenant_id'), 403);

        $payment->update([
            'status' => 'success',
            'paid_at' => now(),
            'verified_by' => Auth::id(),
        ]);

        $payment->invoice->recalculateStatus();

        return redirect()->back()
            ->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function reject(Request $request, Payment $payment)
    {
        abort_unless($payment->tenant_id === session('active_tenant_id'), 403);

        $request->validate([
            'reason' => 'required|string|max:65535',
        ]);

        $payment->update([
            'status' => 'failed',
            'notes' => $payment->notes 
                ? $payment->notes . "\n[Ditolak] " . $request->reason
                : "[Ditolak] " . $request->reason,
        ]);

        return redirect()->back()
            ->with('error', 'Pembayaran ditolak: ' . $request->reason);
    }

    private function isStaffOrAdmin(): bool
    {
        $user = Auth::user();
        return $user && ($user->isStaff() || $user->isTenantSuperAdmin() || $user->isTenantAdmin());
    }
}
