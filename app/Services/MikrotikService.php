<?php

namespace App\Services;

use App\Models\MikrotikAccount;
use App\Models\NasServer;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MikrotikService
{
    protected string $baseUrl;

    public function __construct(protected NasServer $nas)
    {
        $port = $nas->api_port ?: 8728;
        $this->baseUrl = "http://{$nas->host}:{$port}";
    }

    protected function request()
    {
        return Http::withBasicAuth($this->nas->api_username ?? '', $this->nas->api_password ?? '')
            ->timeout(10);
    }

    public function findPppoeSecret(string $name): ?array
    {
        $response = $this->request()->get("{$this->baseUrl}/rest/ppp/secret", ['name' => $name]);

        if (! $response->successful()) {
            return null;
        }

        foreach ($response->json() ?? [] as $secret) {
            if (($secret['name'] ?? null) === $name) {
                return $secret;
            }
        }

        return null;
    }

    public function createPppoeSecret(string $name, string $password, string $service = 'pppoe'): bool
    {
        if ($this->findPppoeSecret($name)) {
            return $this->updatePppoeSecret($name, ['password' => $password, 'service' => $service]);
        }

        $response = $this->request()->post("{$this->baseUrl}/rest/ppp/secret", [
            'name' => $name,
            'password' => $password,
            'service' => $service,
        ]);

        if (! $response->successful()) {
            Log::warning('Mikrotik: gagal membuat PPPoE secret', [
                'nas' => $this->nas->host,
                'name' => $name,
                'status' => $response->status(),
            ]);
        }

        return $response->successful();
    }

    public function updatePppoeSecret(string $name, array $attributes): bool
    {
        return $this->request()
            ->patch("{$this->baseUrl}/rest/ppp/secret/{$name}", $attributes)
            ->successful();
    }

    public function enablePppoeSecret(string $name): bool
    {
        return $this->updatePppoeSecret($name, ['disabled' => 'no']);
    }

    public function disablePppoeSecret(string $name): bool
    {
        return $this->updatePppoeSecret($name, ['disabled' => 'yes']);
    }

    public function resetPppoePassword(string $name, string $password): bool
    {
        return $this->updatePppoeSecret($name, ['password' => $password]);
    }

    public function deletePppoeSecret(string $name): bool
    {
        return $this->request()
            ->delete("{$this->baseUrl}/rest/ppp/secret/{$name}")
            ->successful();
    }

    public function createSimpleQueue(string $name, string $target, string $maxLimit): bool
    {
        $response = $this->request()->post("{$this->baseUrl}/rest/queue/simple", [
            'name' => $name,
            'target' => $target,
            'max-limit' => $maxLimit,
        ]);

        if (! $response->successful()) {
            Log::warning('Mikrotik: gagal membuat simple queue', [
                'nas' => $this->nas->host,
                'name' => $name,
                'max-limit' => $maxLimit,
            ]);
        }

        return $response->successful();
    }

    public static function formatRate(float $mbps): string
    {
        $formatted = rtrim(rtrim(number_format($mbps, 2, '.', ''), '0'), '.');

        return "{$formatted}M";
    }

    public static function formatMaxLimit(array $speed): string
    {
        return self::formatRate($speed['down']).'/'.self::formatRate($speed['up']);
    }

    /**
     * Payment succeeded: re-enable the customer's PPPoE account on the router
     * (e.g. after isolation for an overdue invoice).
     */
    public static function reactivateAfterPayment(Payment $payment): void
    {
        if (! $payment->customer_id) {
            return;
        }

        try {
            $account = MikrotikAccount::where('tenant_id', $payment->tenant_id)
                ->where('customer_id', $payment->customer_id)
                ->where('status', 'disabled')
                ->first();

            if (! $account) {
                return;
            }

            $nas = NasServer::where('tenant_id', $payment->tenant_id)
                ->where('status', 'online')
                ->orderBy('id')
                ->first();

            if ($nas) {
                (new self($nas))->enablePppoeSecret($account->pppoe_secret);
            }

            $account->update(['status' => 'active']);
        } catch (\Throwable $e) {
            Log::warning('Gagal mengaktifkan kembali PPPoE setelah pembayaran', [
                'payment_id' => $payment->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
