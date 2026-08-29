<?php

use App\Http\Controllers\API\BillingController;
use App\Http\Controllers\WebhookController;
use App\Models\Payment;
use App\Models\PaymentGatewayLog;
use App\Models\Tenant;
use App\Services\MidtransService;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::post('/webhook/midtrans', function (Request $request) {
    $payload = $request->all();
    $orderId = $payload['order_id'] ?? null;

    if (! $orderId) {
        return response()->json(['error' => 'Missing order_id'], 400);
    }

    $payment = Payment::withoutGlobalScopes()
        ->where(function ($q) use ($orderId) {
            $q->where('payment_number', $orderId)
                ->orWhere('gateway_transaction_id', $orderId);
        })
        ->first();

    if (! $payment) {
        return response()->json(['error' => 'Payment not found'], 404);
    }

    $tenant = Tenant::find($payment->tenant_id);
    if (! $tenant) {
        return response()->json(['error' => 'Tenant not found'], 404);
    }

    $signature = $request->header('X-Callback-Token') ?? $request->input('signature_key');
    $midtransService = new MidtransService($tenant->getDecryptedSettings());
    $isValid = $midtransService->verifyWebhook($payload, $signature);

    PaymentGatewayLog::create([
        'tenant_id' => $tenant->id,
        'provider' => 'midtrans',
        'payload' => $payload,
        'signature_valid' => $isValid,
        'processed' => false,
    ]);

    if (! $isValid) {
        Log::warning('Invalid Midtrans signature on API webhook', [
            'tenant_id' => $tenant->id,
            'order_id' => $orderId,
        ]);

        return response()->json(['error' => 'Invalid signature'], 401);
    }

    $controller = app(WebhookController::class);

    return $controller->midtrans($request, $tenant->slug);
})->middleware('throttle:60,1');

Route::post('/webhook/xendit', function (Request $request) {
    $payload = $request->all();
    $externalId = $payload['external_id'] ?? null;

    if (! $externalId || ! isset($payload['status'])) {
        return response()->json(['error' => 'Missing external_id or status'], 400);
    }

    $payment = Payment::withoutGlobalScopes()
        ->where(function ($q) use ($externalId) {
            $q->where('payment_number', $externalId)
                ->orWhere('gateway_reference', $externalId);
        })
        ->first();

    if (! $payment) {
        return response()->json(['error' => 'Payment not found'], 404);
    }

    $tenant = Tenant::find($payment->tenant_id);
    $settings = $tenant ? $tenant->getDecryptedSettings() : [];

    $token = $request->header('x-callback-token');
    $xenditService = new XenditService($settings);
    $signatureValid = $xenditService->verifyWebhook($payload, $token);

    PaymentGatewayLog::create([
        'tenant_id' => $payment->tenant_id,
        'provider' => 'xendit',
        'payload' => $payload,
        'signature_valid' => $signatureValid,
        'processed' => false,
    ]);

    if (! $signatureValid) {
        Log::warning('Invalid Xendit signature', [
            'tenant_id' => $payment->tenant_id,
            'external_id' => $externalId,
        ]);

        return response()->json(['error' => 'Invalid token'], 401);
    }

    $xenditService->processWebhookPayload($payload);

    PaymentGatewayLog::where('tenant_id', $payment->tenant_id)
        ->where('provider', 'xendit')
        ->where('payload->external_id', $externalId)
        ->where('processed', false)
        ->update(['processed' => true]);

    return response()->json(['status' => 'ok']);
})->middleware('throttle:60,1');

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/customers/{customer}/invoices/unpaid', [BillingController::class, 'unpaidInvoices']);
    Route::get('/customers/{customer}/payments', [BillingController::class, 'customerPaymentHistory']);
});
