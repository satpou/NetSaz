<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DuitkuService
{
    private string $merchantCode;
    private string $apiKey;
    private bool $isProduction;
    private string $baseUrl;

    public function __construct(?array $tenantSettings = null)
    {
        if ($tenantSettings) {
            $this->merchantCode = $tenantSettings['duitku_merchant_code'] ?? '';
            $this->apiKey = $tenantSettings['duitku_api_key'] ?? '';
            $this->isProduction = !empty($tenantSettings['duitku_is_production']);
        } else {
            $this->merchantCode = (string) (\App\Models\PlatformSetting::get('duitku_merchant_code') ?: config('services.duitku.merchant_code', ''));
            $this->apiKey = (string) (\App\Models\PlatformSetting::get('duitku_api_key') ?: config('services.duitku.api_key', ''));
            $isProd = \App\Models\PlatformSetting::get('duitku_is_production');
            $this->isProduction = $isProd !== null ? (bool) $isProd : (bool) config('services.duitku.is_production', false);
        }

        $this->baseUrl = $this->isProduction
            ? 'https://passport.duitku.com/webapi/api/merchant/v2/inquiry'
            : 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry';
    }

    public function isConfigured(): bool
    {
        return !empty($this->merchantCode) && !empty($this->apiKey);
    }

    public function createSubscriptionTransaction(\App\Models\Tenant $tenant, \App\Models\SubscriptionPlan $plan, float $amount): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Payment gateway Duitku platform belum dikonfigurasi.');
        }

        $orderId = 'SUB-' . $tenant->id . '-' . date('YmdHis');
        $intAmount = (int) round($amount);

        // Duitku signature formula: md5(merchantCode + merchantOrderId + paymentAmount + apiKey)
        $signature = md5($this->merchantCode . $orderId . $intAmount . $this->apiKey);

        $customerEmail = !empty($tenant->email) ? $tenant->email : 'admin@netsaz.web.id';
        $customerPhone = $tenant->whatsapp_number ?: ($tenant->phone ?: '081234567890');
        $customerName = substr($tenant->name, 0, 30);

        $payload = [
            'merchantCode' => $this->merchantCode,
            'paymentAmount' => $intAmount,
            'merchantOrderId' => $orderId,
            'productDetails' => 'Langganan NetSaz ' . $plan->name . ' (1 Tahun)',
            'email' => $customerEmail,
            'phoneNumber' => $customerPhone,
            'additionalParam' => (string) $tenant->id,
            'customerVaName' => $customerName,
            'callbackUrl' => url('/webhook/duitku-subscription'),
            'returnUrl' => url('/duitku-callback'),
            'signature' => $signature,
            'expiryPeriod' => 1440, // 24 hours
        ];

        try {
            $response = Http::asJson()
                ->timeout(15)
                ->post($this->baseUrl, $payload);

            if (!$response->successful()) {
                Log::error('Duitku Subscription API Error', [
                    'tenant_id' => $tenant->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Gagal membuat transaksi di Duitku: ' . $response->body());
            }

            $data = $response->json();

            if (isset($data['statusCode']) && $data['statusCode'] !== '00') {
                $err = $data['statusMessage'] ?? 'Terjadi kesalahan pada gateway Duitku';
                Log::error('Duitku rejected subscription transaction', ['response' => $data]);
                throw new \Exception('Duitku Error: ' . $err);
            }

            $paymentUrl = $data['paymentUrl'] ?? null;
            $reference = $data['reference'] ?? null;

            return [
                'redirect_url' => $paymentUrl,
                'order_id' => $orderId,
                'reference' => $reference,
            ];
        } catch (\Exception $e) {
            Log::error('Duitku Subscription Exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    public function createTransaction(Invoice $invoice, ?float $amount = null): array
    {
        $remainingAmount = $amount ?? $invoice->remaining_amount;

        if ($remainingAmount <= 0) {
            throw new \Exception('Invoice sudah lunas atau sisa pembayaran invalid.');
        }

        $payment = Payment::create([
            'tenant_id' => $invoice->tenant_id,
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'payment_number' => $this->generatePaymentNumber($invoice->tenant_id),
            'amount' => $remainingAmount,
            'payment_method' => 'gateway',
            'gateway_provider' => 'duitku',
            'status' => 'pending',
        ]);

        $intAmount = (int) round($remainingAmount);
        // Duitku signature formula: md5(merchantCode + merchantOrderId + paymentAmount + apiKey)
        $signature = md5($this->merchantCode . $payment->payment_number . $intAmount . $this->apiKey);

        $customer = $invoice->customer;
        $customerName = $customer->name ?? 'Pelanggan NetSaz';
        $customerEmail = !empty($customer->email) ? $customer->email : 'customer@netsaz.web.id';
        $customerPhone = $customer->phone ?? '081234567890';

        $payload = [
            'merchantCode' => $this->merchantCode,
            'paymentAmount' => $intAmount,
            'merchantOrderId' => $payment->payment_number,
            'productDetails' => "Tagihan Internet #{$invoice->invoice_number}",
            'email' => $customerEmail,
            'phoneNumber' => $customerPhone,
            'additionalParam' => (string) $invoice->tenant_id,
            'customerVaName' => substr($customerName, 0, 30),
            'callbackUrl' => url('/api/webhook/duitku'),
            'returnUrl' => url('/duitku-callback'),
            'signature' => $signature,
            'expiryPeriod' => 1440, // 24 hours
        ];

        try {
            $response = Http::asJson()
                ->timeout(15)
                ->post($this->baseUrl, $payload);

            if (!$response->successful()) {
                Log::error('Duitku API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Gagal membuat transaksi di Duitku: ' . $response->body());
            }

            $data = $response->json();

            if (isset($data['statusCode']) && $data['statusCode'] !== '00') {
                $err = $data['statusMessage'] ?? 'Terjadi kesalahan pada gateway Duitku';
                Log::error('Duitku rejected transaction', ['response' => $data]);
                throw new \Exception('Duitku Error: ' . $err);
            }

            $paymentUrl = $data['paymentUrl'] ?? null;
            $reference = $data['reference'] ?? null;

            $payment->update([
                'gateway_transaction_id' => $reference,
                'gateway_reference' => $reference,
            ]);

            return [
                'success' => true,
                'redirect_url' => $paymentUrl,
                'reference' => $reference,
                'payment_id' => $payment->id,
            ];
        } catch (\Exception $e) {
            Log::error('Duitku Exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    public function verifyWebhook(array $payload, ?string $signature): bool
    {
        if (empty($signature) || empty($this->apiKey) || empty($this->merchantCode)) {
            return false;
        }

        $merchantCode = $payload['merchantCode'] ?? '';
        $amount = $payload['amount'] ?? '';
        $merchantOrderId = $payload['merchantOrderId'] ?? '';

        // Duitku callback signature formula: md5(merchantCode + amount + merchantOrderId + apiKey)
        $expectedSignature = md5($merchantCode . $amount . $merchantOrderId . $this->apiKey);

        return hash_equals($expectedSignature, $signature);
    }

    public function processWebhookPayload(array $payload): void
    {
        $merchantOrderId = $payload['merchantOrderId'] ?? null;
        $resultCode = $payload['resultCode'] ?? null;
        $reference = $payload['reference'] ?? null;

        if (!$merchantOrderId) {
            throw new \Exception('Invalid Duitku callback payload: Missing merchantOrderId');
        }

        $payment = Payment::withoutGlobalScopes()
            ->where(function ($q) use ($merchantOrderId, $reference) {
                $q->where('payment_number', $merchantOrderId);
                if ($reference) {
                    $q->orWhere('gateway_transaction_id', $reference)
                      ->orWhere('gateway_reference', $reference);
                }
            })
            ->first();

        if (!$payment) {
            Log::warning('Duitku payment not found for merchantOrderId: ' . $merchantOrderId);
            return;
        }

        if (isset($payload['amount'])) {
            $amount = (float) $payload['amount'];
            if (abs($amount - (float) $payment->amount) > 0.01) {
                Log::warning('Duitku webhook amount mismatch', [
                    'merchantOrderId' => $merchantOrderId,
                    'expected' => $payment->amount,
                    'got' => $amount,
                ]);
                return;
            }
        }

        $currentStatus = $payment->status;
        if (in_array($currentStatus, ['success', 'refunded'], true)) {
            return;
        }

        // Duitku resultCode: '00' = Success, '01' = Failed
        $isSuccess = ($resultCode === '00');
        $newStatus = $isSuccess ? 'success' : 'failed';

        $payment->update([
            'status' => $newStatus,
            'paid_at' => $isSuccess ? now() : null,
            'gateway_transaction_id' => $reference ?: $payment->gateway_transaction_id,
        ]);

        if ($isSuccess) {
            $payment->invoice->recalculateStatus();
            MikrotikService::reactivateAfterPayment($payment);
        }
    }

    private function generatePaymentNumber(int $tenantId): string
    {
        return 'DUITKU-' . date('YmdHis') . '-' . $tenantId . '-' . random_int(100, 999);
    }
}
