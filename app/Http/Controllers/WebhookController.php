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

    public function githubDeploy(Request $request)
    {
        $secret = config('services.github.webhook_secret') ?? env('GITHUB_WEBHOOK_SECRET');

        if (!empty($secret)) {
            $signature = $request->header('X-Hub-Signature-256');
            if (!$signature) {
                return response()->json(['error' => 'Missing signature header'], 401);
            }
            $expectedSignature = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);
            if (!hash_equals($expectedSignature, $signature)) {
                return response()->json(['error' => 'Invalid webhook signature'], 403);
            }
        }

        \Illuminate\Support\Facades\Artisan::call('git:sync', ['--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();

        return response()->json([
            'status' => 'success',
            'message' => 'VM synchronized with GitHub successfully',
            'output' => trim($output),
        ]);
    }

    public function subscriptionMidtrans(Request $request)
    {
        $payload = $request->all();
        $orderId = $payload['order_id'] ?? '';
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';

        Log::info('Platform Subscription Midtrans webhook received', [
            'order_id' => $orderId,
            'status' => $transactionStatus,
            'fraud' => $fraudStatus,
        ]);

        if (preg_match('/^SUB-(\d+)-/', $orderId, $matches)) {
            $tenantId = (int) $matches[1];
            $tenant = \App\Models\Tenant::find($tenantId);

            if ($tenant) {
                if (in_array($transactionStatus, ['capture', 'settlement']) && ($fraudStatus === 'accept' || empty($fraudStatus))) {
                    \Illuminate\Support\Facades\DB::transaction(function () use ($tenant, $payload) {
                        $settings = $tenant->settings ?? [];
                        $settings['payment_method'] = 'midtrans';
                        $settings['payment_status'] = 'paid';
                        $settings['paid_at'] = now()->toDateTimeString();
                        $settings['midtrans_transaction_id'] = $payload['transaction_id'] ?? null;
                        $settings['midtrans_payment_type'] = $payload['payment_type'] ?? null;

                        $tenant->update([
                            'status' => 'active',
                            'settings' => $settings,
                        ]);

                        \App\Models\User::where('tenant_id', $tenant->id)->update([
                            'is_active' => true,
                        ]);

                        $sub = \App\Models\TenantSubscription::where('tenant_id', $tenant->id)->latest()->first();
                        $now = \Carbon\Carbon::now();
                        $end = (clone $now)->addYear();

                        if ($sub) {
                            $sub->update([
                                'status' => 'active',
                                'started_at' => $now,
                                'current_period_start' => $now,
                                'current_period_end' => $end,
                            ]);
                        }
                    });

                    Log::info("Tenant {$tenant->id} ({$tenant->slug}) activated via Midtrans subscription payment.");
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    public function duitku(Request $request)
    {
        $payload = $request->all();
        $merchantOrderId = $payload['merchantOrderId'] ?? null;
        $signature = $payload['signature'] ?? null;

        Log::info('Duitku callback received', $payload);

        if (!$merchantOrderId) {
            return response('Bad Request: Missing merchantOrderId', 400);
        }

        $payment = Payment::withoutGlobalScopes()
            ->where('payment_number', $merchantOrderId)
            ->first();

        if (!$payment) {
            Log::warning('Duitku payment not found for order', ['order_id' => $merchantOrderId]);
            return response('Payment not found', 404);
        }

        $tenant = \App\Models\Tenant::find($payment->tenant_id);
        if (!$tenant) {
            return response('Tenant not found', 404);
        }

        $duitkuService = new \App\Services\DuitkuService($tenant->getDecryptedSettings());
        $isValid = $duitkuService->verifyWebhook($payload, $signature);

        PaymentGatewayLog::create([
            'tenant_id' => $tenant->id,
            'provider' => 'duitku',
            'payload' => $payload,
            'signature_valid' => $isValid,
            'processed' => $isValid,
        ]);

        if (!$isValid) {
            Log::warning('Invalid Duitku signature', [
                'tenant_id' => $tenant->id,
                'merchantOrderId' => $merchantOrderId,
            ]);
            return response('Bad Signature', 400);
        }

        $duitkuService->processWebhookPayload($payload);

        return response('SUCCESS', 200);
    }

    public function duitkuCallback(Request $request)
    {
        // If Duitku sends the POST webhook to this callback URL
        if ($request->isMethod('post')) {
            return $this->duitku($request);
        }

        $merchantOrderId = $request->input('merchantOrderId');
        $resultCode = $request->input('resultCode');

        if (!$merchantOrderId) {
            return redirect('/')->with('error', 'Order ID tidak ditemukan.');
        }

        $payment = Payment::withoutGlobalScopes()
            ->where('payment_number', $merchantOrderId)
            ->first();

        if (!$payment) {
            return redirect('/')->with('error', 'Pembayaran tidak ditemukan.');
        }

        $tenant = \App\Models\Tenant::find($payment->tenant_id);

        if ($tenant?->slug) {
            $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https';
            $tenantDomain = config('app.tenant_domain', config('app.main_domain'));
            $url = "{$scheme}://{$tenant->slug}.{$tenantDomain}/portal/invoices/{$payment->invoice_id}";

            if ($resultCode === '00' || $payment->status === 'success') {
                return redirect()->away($url)->with('success', 'Pembayaran via Duitku berhasil! Terima kasih.');
            }

            return redirect()->away($url)->with('error', 'Pembayaran belum diselesaikan atau dibatalkan.');
        }

        return redirect('/')->with('error', 'Pembayaran tidak dapat ditampilkan.');
    }
}
