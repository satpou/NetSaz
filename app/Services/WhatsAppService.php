<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected ?Tenant $tenant;
    protected array $settings;

    public function __construct(?Tenant $tenant = null)
    {
        if (!$tenant && function_exists('tenantId')) {
            $tid = tenantId();
            if ($tid) {
                $tenant = Tenant::find($tid);
            }
        }

        $this->tenant = $tenant;
        $this->settings = $tenant?->settings ?? [];
    }

    /**
     * Format phone number to international standard without leading zero or plus sign (e.g. 628123456789).
     */
    public function formatPhone(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        }

        return $clean;
    }

    /**
     * Send a WhatsApp message to a phone number.
     *
     * @param string $phone Destination phone number
     * @param string $message Text message content
     * @return array ['success' => bool, 'message' => string, 'response' => mixed]
     */
    public function sendMessage(string $phone, string $message): array
    {
        $phone = $this->formatPhone($phone);
        if (empty($phone)) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp tujuan tidak valid.',
            ];
        }

        $provider = $this->settings['wa_gateway_provider'] ?? config('services.wa.default_provider', 'internal');

        try {
            return match ($provider) {
                'internal' => $this->sendViaInternal($phone, $message),
                'open_wa' => $this->sendViaOpenWa($phone, $message),
                'fonnte' => $this->sendViaFonnte($phone, $message),
                'waha' => $this->sendViaWaha($phone, $message),
                default => $this->sendViaInternal($phone, $message),
            };
        } catch (\Throwable $e) {
            Log::error('WhatsApp send error: ' . $e->getMessage(), [
                'provider' => $provider,
                'phone' => $phone,
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengirim pesan WhatsApp: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get isolated session ID for this tenant.
     * Prevents different ISPs from sharing the same WhatsApp session.
     */
    public function getSessionId(): string
    {
        if ($this->tenant && $this->tenant->id) {
            return 'tenant_' . $this->tenant->id;
        }

        if (function_exists('tenantId') && tenantId()) {
            return 'tenant_' . tenantId();
        }

        return 'default';
    }

    /**
     * Send message using NetSaz Built-in Internal WhatsApp Gateway (Auto QR Code on port 3001)
     */
    public function sendViaInternal(string $phone, string $message): array
    {
        $baseUrl = config('app.whatsapp_bridge_url', 'http://127.0.0.1:3001');

        try {
            $response = Http::timeout(12)->post("{$baseUrl}/send", [
                'sessionId' => $this->getSessionId(),
                'to' => $phone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Pesan WhatsApp berhasil dikirim via Gateway Internal NetSaz.',
                    'response' => $response->json(),
                ];
            }

            $body = $response->json();
            return [
                'success' => false,
                'message' => $body['message'] ?? 'Gateway Internal mengembalikan status ' . $response->status(),
                'response' => $body,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke WhatsApp Gateway Internal: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get real-time status and live QR code from Internal Gateway for this specific tenant
     */
    public function getInternalStatus(): array
    {
        $baseUrl = config('app.whatsapp_bridge_url', 'http://127.0.0.1:3001');

        try {
            $response = Http::timeout(4)->get("{$baseUrl}/status", [
                'sessionId' => $this->getSessionId(),
            ]);
            if ($response->successful()) {
                return $response->json();
            }
            return [
                'success' => false,
                'status' => 'DISCONNECTED',
                'error' => 'Gateway mengembalikan status HTTP ' . $response->status(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 'DISCONNECTED',
                'error' => 'Service Gateway belum berjalan (' . $e->getMessage() . ')',
            ];
        }
    }

    /**
     * Logout and reset internal WhatsApp session for this tenant to generate new QR
     */
    public function logoutInternal(): array
    {
        $baseUrl = config('app.whatsapp_bridge_url', 'http://127.0.0.1:3001');

        try {
            $response = Http::timeout(5)->post("{$baseUrl}/logout", [
                'sessionId' => $this->getSessionId(),
            ]);
            return $response->json() ?? ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send message using Open-WA (wa-automate)
     * Reference: https://www.open-wa.org/
     */
    protected function sendViaOpenWa(string $phone, string $message): array
    {
        $baseUrl = rtrim($this->settings['wa_api_url'] ?? config('services.open_wa.api_url', 'http://localhost:2785'), '/');
        $apiKey = $this->tenant?->getSecretSetting('wa_api_token') ?? ($this->settings['wa_api_token'] ?? config('services.open_wa.api_key', ''));
        $sessionId = $this->settings['wa_session_id'] ?? $this->getSessionId();

        $chatId = str_ends_with($phone, '@c.us') ? $phone : "{$phone}@c.us";

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
        if (!empty($apiKey)) {
            $headers['X-API-Key'] = $apiKey;
            $headers['api_key'] = $apiKey;
        }

        $endpoint = "{$baseUrl}/api/sessions/{$sessionId}/messages/send-text";
        $payload = [
            'chatId' => $chatId,
            'text' => $message,
        ];

        try {
            $response = Http::withHeaders($headers)
                ->timeout(12)
                ->post($endpoint, $payload);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke Open-WA (' . $baseUrl . '): ' . $e->getMessage(),
            ];
        }

        if ($response->status() === 404) {
            $fallbackEndpoint = "{$baseUrl}/sendText";
            $fallbackPayload = [
                'args' => [
                    'to' => $chatId,
                    'content' => $message,
                ],
            ];
            try {
                $response = Http::withHeaders($headers)
                    ->timeout(12)
                    ->post($fallbackEndpoint, $fallbackPayload);
            } catch (\Throwable $e) {
                return [
                    'success' => false,
                    'message' => 'Gagal terhubung ke Open-WA endpoint /sendText: ' . $e->getMessage(),
                ];
            }
        }

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => 'Pesan WhatsApp berhasil dikirim via Open-WA.',
                'response' => $response->json(),
            ];
        }

        return [
            'success' => false,
            'message' => 'Open-WA mengembalikan respons HTTP ' . $response->status() . ': ' . $response->body(),
            'response' => $response->json() ?? $response->body(),
        ];
    }

    /**
     * Check Open-WA connection status and session health.
     */
    public function checkOpenWaStatus(): array
    {
        $baseUrl = rtrim($this->settings['wa_api_url'] ?? config('services.open_wa.api_url', 'http://localhost:2785'), '/');
        $apiKey = $this->tenant?->getSecretSetting('wa_api_token') ?? ($this->settings['wa_api_token'] ?? config('services.open_wa.api_key', ''));
        $sessionId = $this->settings['wa_session_id'] ?? $this->getSessionId();

        $headers = [
            'Accept' => 'application/json',
        ];
        if (!empty($apiKey)) {
            $headers['X-API-Key'] = $apiKey;
            $headers['api_key'] = $apiKey;
        }

        try {
            $response = Http::withHeaders($headers)->timeout(5)->get("{$baseUrl}/api/sessions/{$sessionId}/status");
            if ($response->successful()) {
                return ['connected' => true, 'data' => $response->json()];
            }

            $response = Http::withHeaders($headers)->timeout(5)->get("{$baseUrl}/isConnected");
            if ($response->successful()) {
                return ['connected' => true, 'data' => $response->json()];
            }

            return ['connected' => false, 'error' => 'HTTP status ' . $response->status()];
        } catch (\Throwable $e) {
            return ['connected' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send message using Fonnte API
     */
    protected function sendViaFonnte(string $phone, string $message): array
    {
        $apiKey = $this->tenant?->getSecretSetting('wa_api_token') ?? ($this->settings['wa_api_token'] ?? '');
        if (empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'Token API Fonnte belum diatur di Pengaturan ISP.',
            ];
        }

        $response = Http::withHeaders([
            'Authorization' => $apiKey,
        ])->timeout(12)->post('https://api.fonnte.com/send', [
            'target' => $phone,
            'message' => $message,
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => 'Pesan WhatsApp berhasil dikirim via Fonnte.',
                'response' => $response->json(),
            ];
        }

        return [
            'success' => false,
            'message' => 'Fonnte mengembalikan respons HTTP ' . $response->status() . ': ' . $response->body(),
            'response' => $response->json() ?? $response->body(),
        ];
    }

    /**
     * Send message using WAHA (WhatsApp HTTP API)
     */
    protected function sendViaWaha(string $phone, string $message): array
    {
        $baseUrl = rtrim($this->settings['wa_api_url'] ?? 'http://localhost:3000', '/');
        $apiKey = $this->tenant?->getSecretSetting('wa_api_token') ?? ($this->settings['wa_api_token'] ?? '');
        $sessionId = $this->settings['wa_session_id'] ?? $this->getSessionId();

        $chatId = str_ends_with($phone, '@c.us') ? $phone : "{$phone}@c.us";

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
        if (!empty($apiKey)) {
            $headers['X-Api-Key'] = $apiKey;
        }

        $response = Http::withHeaders($headers)->timeout(12)->post("{$baseUrl}/api/sendText", [
            'session' => $sessionId,
            'chatId' => $chatId,
            'text' => $message,
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => 'Pesan WhatsApp berhasil dikirim via WAHA.',
                'response' => $response->json(),
            ];
        }

        return [
            'success' => false,
            'message' => 'WAHA mengembalikan respons HTTP ' . $response->status() . ': ' . $response->body(),
            'response' => $response->json() ?? $response->body(),
        ];
    }

    /**
     * Send message using CallMeBot or generic webhook fallback
     */
    protected function sendViaCallmebotOrGeneric(string $phone, string $message): array
    {
        $apiUrl = $this->settings['wa_api_url'] ?? config('services.wa_notification.api_url', 'https://api.callmebot.com/whatsapp.php');
        $apiKey = $this->tenant?->getSecretSetting('wa_api_token') ?? ($this->settings['wa_api_token'] ?? config('services.wa_notification.api_key', ''));

        $response = Http::timeout(12)->get($apiUrl, [
            'phone' => $phone,
            'text' => $message,
            'apikey' => $apiKey,
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => 'Pesan WhatsApp berhasil dikirim via CallMeBot / Webhook.',
                'response' => $response->body(),
            ];
        }

        return [
            'success' => false,
            'message' => 'Webhook mengembalikan status ' . $response->status(),
        ];
    }
}
