<?php

namespace App\Services;

use App\Models\GenieacsConfig;
use Illuminate\Support\Facades\Http;

class GenieacsService
{
    public function __construct(protected ?GenieacsConfig $config = null)
    {
    }

    public function isConfigured(): bool
    {
        return $this->config !== null
            && $this->config->enabled
            && filled($this->config->host);
    }

    public function checkHealth(): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::timeout(5)->get($this->config->baseUrl().'/health');

            return [
                'reachable' => $response->successful(),
                'status' => $response->successful() ? ($response->json('status') ?? 'up') : 'down',
            ];
        } catch (\Throwable) {
            return ['reachable' => false, 'status' => 'down'];
        }
    }

    public function getDevices(): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        try {
            $response = Http::timeout(15)->get($this->config->baseUrl().'/devices', [
                'fields' => '_id,_lastInform,InternetGatewayDevice.DeviceInfo.SoftwareVersion',
            ]);

            if (! $response->successful()) {
                return [];
            }

            return collect($response->json() ?? [])
                ->map(function ($device) {
                    $lastInform = $device['_lastInform'] ?? null;
                    $timestamp = null;

                    if (filled($lastInform)) {
                        $timestamp = is_numeric($lastInform) ? (int) $lastInform : strtotime($lastInform);
                    }

                    return [
                        'serial' => $device['_id'] ?? null,
                        'online' => $timestamp !== null && $timestamp > 0 && (time() - $timestamp) < 300,
                        'software_version' => $device['InternetGatewayDevice.DeviceInfo.SoftwareVersion'] ?? null,
                        'last_inform' => $lastInform,
                    ];
                })
                ->filter(fn ($device) => filled($device['serial']))
                ->values()
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    public function reboot(string $serial): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            return Http::timeout(10)
                ->post("{$this->config->baseUrl()}/devices/{$serial}/tasks", ['name' => 'reboot'])
                ->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    public function setPreseed(string $serial, array $parameters): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $url = "{$this->config->baseUrl()}/preseed/{$serial}";

        try {
            $exists = Http::timeout(10)->get($url)->successful();

            $response = $exists
                ? Http::timeout(10)->put($url, $parameters)
                : Http::timeout(10)->post($url, $parameters);

            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    public function provision(string $serial, array $parameters): array
    {
        $preseedWritten = $this->setPreseed($serial, $parameters);
        $rebooted = $this->reboot($serial);

        return [
            'success' => $preseedWritten && $rebooted,
            'preseed' => $preseedWritten,
            'reboot' => $rebooted,
        ];
    }
}
