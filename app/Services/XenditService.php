<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditService
{
    private string $apiKey;

    private string $webhookToken;

    private bool $isProduction;

    private string $baseUrl;

    public function __construct(?array $tenantSettings = null)
    {
        if ($tenantSettings) {
            $this->apiKey = $tenantSettings['xendit_api_key'] ?? '';
            $this->webhookToken = $tenantSettings['xendit_webhook_token'] ?? '';
            $this->isProduction = ! empty($tenantSettings['xendit_is_production']);
        } else {
            $this->apiKey = config('services.xendit.api_key');
            $this->webhookToken = config('services.xendit.webhook_token');
            $this->isProduction = config('services.xendit.is_production', false);
        }
        $this->baseUrl = $this->isProduction
            ? 'https://api.xendit.co'
            : 'https://api.xendit.co';
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
            'gateway_provider' => 'xendit',
            'status' => 'pending',
        ]);

        $payload = [
            'external_id' => $payment->payment_number,
            'amount' => (int) $remainingAmount,
            'payer_email' => $invoice->customer->email ?? 'noemail@example.com',
            'description' => "Invoice {$invoice->invoice_number} - {$invoice->customer->name}",
            'customer' => [
                'given_names' => $invoice->customer->name,
                'email' => $invoice->customer->email ?? 'noemail@example.com',
                'mobile_number' => $invoice->customer->phone ?? '',
            ],
            'items' => [
                [
                    'name' => "Invoice {$invoice->invoice_number}",
                    'quantity' => 1,
                    'price' => (int) $remainingAmount,
                ],
            ],
            'currency' => 'IDR',
            'invoice_duration' => 86400,
        ];

        try {
            $response = Http::withBasicAuth($this->apiKey, '')
                ->post("{$this->baseUrl}/v2/invoices", $payload);

            if (! $response->successful()) {
                Log::error('Xendit API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Gagal membuat invoice di Xendit.');
            }

            $data = $response->json();

            $payment->update([
                'gateway_transaction_id' => $data['id'] ?? null,
                'gateway_reference' => $data['external_id'] ?? null,
            ]);

            return [
                'success' => true,
                'token' => null,
                'redirect_url' => $data['invoice_url'] ?? null,
                'payment_id' => $payment->id,
            ];
        } catch (\Exception $e) {
            Log::error('Xendit Exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    public function createQris(Invoice $invoice, ?float $amount = null): array
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
            'payment_method' => 'qris',
            'gateway_provider' => 'xendit',
            'status' => 'pending',
        ]);

        $payload = [
            'reference_id' => $payment->payment_number,
            'type' => 'DYNAMIC',
            'currency' => 'IDR',
            'amount' => (int) $remainingAmount,
            'is_single_use' => true,
        ];

        try {
            $response = Http::withBasicAuth($this->apiKey, '')
                ->post("{$this->baseUrl}/qr_codes", $payload);

            if (! $response->successful()) {
                Log::error('Xendit QRIS API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Gagal membuat QRIS di Xendit.');
            }

            $data = $response->json();

            $payment->update([
                'gateway_transaction_id' => $data['id'] ?? null,
                'gateway_reference' => $data['reference_id'] ?? null,
                'qr_string' => $data['qr_string'] ?? null,
            ]);

            if (empty($data['qr_string'])) {
                throw new \Exception('QRIS tidak mengembalikan QR string.');
            }

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'qr_string' => $data['qr_string'],
                'external_id' => $data['id'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Xendit QRIS Exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getQrisStatus(string $externalId): ?string
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, '')
                ->get("{$this->baseUrl}/qr_codes/{$externalId}");

            if (! $response->successful()) {
                Log::error('Xendit QRIS status check failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            return $response->json('status');
        } catch (\Exception $e) {
            Log::error('Xendit QRIS status exception', ['message' => $e->getMessage()]);

            return null;
        }
    }

    public function verifyWebhook(array $payload, ?string $signature): bool
    {
        if ($signature === null || $signature === '' || $this->webhookToken === '') {
            return false;
        }

        return hash_equals($this->webhookToken, $signature);
    }

    public function processWebhookPayload(array $payload): void
    {
        $externalId = $payload['external_id'] ?? null;
        $status = $payload['status'] ?? null;

        if (! $externalId || ! $status) {
            throw new \Exception('Invalid Xendit webhook payload.');
        }

        $payment = Payment::withoutGlobalScopes()
            ->where(function ($q) use ($externalId) {
                $q->where('gateway_reference', $externalId)
                    ->orWhere('payment_number', $externalId);
            })
            ->first();

        if (! $payment) {
            Log::warning('Payment not found for external_id: '.$externalId);

            return;
        }

        if (isset($payload['amount']) && abs((float) $payload['amount'] - (float) $payment->amount) > 0.01) {
            Log::warning('Xendit webhook amount mismatch', [
                'external_id' => $externalId,
                'expected' => $payment->amount,
                'got' => $payload['amount'],
            ]);

            return;
        }

        if (in_array($payment->status, ['success', 'expired', 'refunded', 'failed'], true)) {
            return;
        }

        $newStatus = match ($status) {
            'PAID', 'COMPLETED' => 'success',
            'EXPIRED' => 'expired',
            'FAILED' => 'failed',
            default => 'pending',
        };

        $payment->update([
            'status' => $newStatus,
            'paid_at' => $newStatus === 'success' ? now() : null,
        ]);

        if ($newStatus === 'success') {
            $payment->invoice->recalculateStatus();
        }
    }

    private function generatePaymentNumber(int $tenantId): string
    {
        return 'XDT/'.date('YmdHis').'/'.$tenantId.'/'.random_int(1000, 9999);
    }
}
