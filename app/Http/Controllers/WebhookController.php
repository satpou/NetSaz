<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentGatewayLog;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function midtrans(Request $request, string $tenantCode)
    {
        $payload = $request->all();
        $signature = $request->header('X-Callback-Token') ?? $request->input('signature_key');

        try {
            $tenant = \App\Models\Tenant::where('slug', $tenantCode)->firstOrFail();

            $log = PaymentGatewayLog::create([
                'tenant_id' => $tenant->id,
                'provider' => 'midtrans',
                'payload' => $payload,
            ]);

            $midtransService = new MidtransService($tenant->getDecryptedSettings());
            $isValid = $midtransService->verifyWebhook($payload, $signature);

            $log->update(['signature_valid' => $isValid]);

            if (!$isValid) {
                Log::warning('Invalid Midtrans signature', [
                    'tenant_id' => $tenant->id,
                    'order_id' => $payload['order_id'] ?? null,
                    'payload' => $payload,
                ]);
                $log->update(['processed' => false]); // Mark log as not processed due to invalid signature
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            // Check if this webhook payload was already processed
            $existingProcessedLog = PaymentGatewayLog::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('provider', 'midtrans')
                ->where('processed', true)
                ->whereJsonContains('payload->order_id', $payload['order_id'] ?? null)
                ->first();

            if ($existingProcessedLog) {
                $log->update(['processed' => true]);
                Log::info("Midtrans webhook for order_id {$payload['order_id']} already processed. Skipping.");
                return response()->json(['status' => 'ok', 'message' => 'Already processed']);
            }

            // Check if payment already processed (idempotent by payment status)
            $paymentCheck = Payment::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where(function($q) use ($payload) {
                    $q->where('payment_number', $payload['order_id'] ?? null)
                      ->orWhere('gateway_transaction_id', $payload['order_id'] ?? null);
                })
                ->first();

            if ($paymentCheck && in_array($paymentCheck->status, ['success', 'expired', 'failed', 'refunded'])) {
                $log->update(['processed' => true]);
                Log::info("Midtrans webhook for order_id {$payload['order_id']} received, payment already in status: {$paymentCheck->status}.");
                return response()->json(['status' => 'ok', 'message' => 'Payment already in final state.']);
            }

            $midtransService->processWebhookPayload($payload);

            $log->update(['processed' => true]); // Mark this log as processed after successful processing of payment

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('Midtrans webhook error', [
                'message' => $e->getMessage(),
                'tenant_code' => $tenantCode,
                'payload' => $payload,
            ]);

            return response()->json(['error' => 'Internal error'], 400);
        }
    }

    public function midtransCallback(Request $request)
    {
        $orderId = $request->input('order_id');

        if (! $orderId) {
            return redirect('/')->with('error', 'Order ID tidak ditemukan.');
        }

        $payment = Payment::withoutGlobalScopes()
            ->where(function ($q) use ($orderId) {
                $q->where('payment_number', $orderId)
                    ->orWhere('gateway_transaction_id', $orderId);
            })
            ->first();

        if (! $payment) {
            return redirect('/')->with('error', 'Pembayaran tidak ditemukan.');
        }

        $tenant = \App\Models\Tenant::find($payment->tenant_id);

        // Browser return URL from Midtrans — send customers to their portal, not admin invoices
        if ($tenant?->slug) {
            $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https';
            $tenantDomain = config('app.tenant_domain', config('app.main_domain'));
            $url = "{$scheme}://{$tenant->slug}.{$tenantDomain}/portal/invoices/{$payment->invoice_id}";

            if ($payment->status === 'success') {
                return redirect()->away($url)->with('success', 'Pembayaran berhasil! Terima kasih.');
            }

            return redirect()->away($url)->with('error', 'Status pembayaran: '.$payment->status);
        }

        return redirect('/')->with('error', 'Pembayaran tidak dapat ditampilkan.');
    }
}
