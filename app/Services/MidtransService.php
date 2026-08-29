<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    private string $serverKey;
    private string $clientKey;
    private bool $isProduction;
    private string $baseUrl;

    public function __construct(?array $tenantSettings = null)
    {
        if ($tenantSettings) {
            $this->serverKey = $tenantSettings['midtrans_server_key'] ?? '';
            $this->clientKey = $tenantSettings['midtrans_client_key'] ?? '';
            $this->isProduction = !empty($tenantSettings['midtrans_is_production']);
        } else {
            $this->serverKey = config('services.midtrans.server_key');
            $this->clientKey = config('services.midtrans.client_key');
            $this->isProduction = config('services.midtrans.is_production', false);
        }
        $this->baseUrl = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1'
            : 'https://app.sandbox.midtrans.com/snap/v1';
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
            'gateway_provider' => 'midtrans',
            'status' => 'pending',
        ]);

        $payload = [
            'transaction_details' => [
                'order_id' => $payment->payment_number,
                'gross_amount' => (int) $remainingAmount,
            ],
            'customer_details' => [
                'first_name' => $invoice->customer->name,
                'email' => $invoice->customer->email ?? 'noemail@example.com',
                'phone' => $invoice->customer->phone ?? '',
                'address' => $invoice->customer->address ?? '',
            ],
            'item_details' => [
                [
                    'id' => $invoice->invoice_number,
                    'price' => (int) $remainingAmount,
                    'quantity' => 1,
                    'name' => "Invoice {$invoice->invoice_number}",
                ],
            ],
        ];

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->post("{$this->baseUrl}/transactions", $payload);

            if (!$response->successful()) {
                Log::error('Midtrans API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Gagal membuat transaksi di Midtrans.');
            }

            $data = $response->json();

            $payment->update([
                'gateway_transaction_id' => $data['transaction_id'] ?? null,
                'gateway_reference' => $data['order_id'] ?? null,
            ]);

            return [
                'success' => true,
                'token' => $data['token'] ?? null,
                'redirect_url' => $data['redirect_url'] ?? null,
                'payment_id' => $payment->id,
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans Exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    public function verifyWebhook(array $payload, ?string $signature): bool
    {
        if ($signature === null || $signature === '' || $this->serverKey === '') {
            return false;
        }

        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';

        $calculatedSignature = hash('sha512',
            $orderId.$statusCode.$grossAmount.$this->serverKey
        );

        return hash_equals($calculatedSignature, $signature);
    }

    public function processWebhookPayload(array $payload): void
    {
        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        if (! $orderId || ! $transactionStatus) {
            throw new \Exception('Invalid Midtrans webhook payload.');
        }

        $payment = Payment::withoutGlobalScopes()
            ->where(function ($q) use ($orderId) {
                $q->where('gateway_transaction_id', $orderId)
                    ->orWhere('payment_number', $orderId);
            })
            ->first();

        if (! $payment) {
            Log::warning('Payment not found for order_id: '.$orderId);

            return;
        }

        if (isset($payload['gross_amount'])) {
            $grossAmount = (float) $payload['gross_amount'];
            if (abs($grossAmount - (float) $payment->amount) > 0.01) {
                Log::warning('Midtrans webhook amount mismatch', [
                    'order_id' => $orderId,
                    'expected' => $payment->amount,
                    'got' => $grossAmount,
                ]);

                return;
            }
        }

        $currentStatus = $payment->status;
        $newStatus = $this->mapMidtransStatus($transactionStatus, $fraudStatus);

        // Idempotent - jangan update kalau sudah success/done
        if (in_array($currentStatus, ['success', 'expired', 'refunded', 'failed'], true)) {
            return;
        }

        $payment->update([
            'status' => $newStatus,
            'paid_at' => $newStatus === 'success' ? now() : null,
        ]);

        if ($newStatus === 'success') {
            $payment->invoice->recalculateStatus();
        }
    }

    private function mapMidtransStatus(string $transactionStatus, ?string $fraudStatus): string
    {
        if ($fraudStatus === 'accept') {
            return $transactionStatus === 'settlement' ? 'success' : 'pending';
        }

        return match ($transactionStatus) {
            'settlement' => 'success',
            'pending' => 'pending',
            'expire' => 'expired',
            'cancel' => 'failed',
            'deny' => 'failed',
            'refund' => 'refunded',
            default => 'pending',
        };
    }

    private function generatePaymentNumber(int $tenantId): string
    {
        return 'PAY/' . date('YmdHis') . '/' . $tenantId . '/' . random_int(1000, 9999);
    }
}
