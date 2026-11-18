<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class TenantSettingController extends Controller
{
    public function edit()
    {
        $tid = tenantId();
        $tenant = Tenant::findOrFail($tid);
        $settings = $tenant->settings ?? [];

        return view('tenant.settings', compact('tenant', 'settings'));
    }

    public function update(Request $request)
    {
        $tid = tenantId();
        $tenant = Tenant::findOrFail($tid);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'whatsapp_number' => 'nullable|string|max:25',
            'brand_color' => 'nullable|string|max:20',
            'slogan' => 'nullable|string|max:255',
            // Midtrans
            'midtrans_server_key' => 'nullable|string|max:255',
            'midtrans_client_key' => 'nullable|string|max:255',
            'midtrans_is_production' => 'nullable|boolean',
            // WhatsApp Gateway
            'wa_gateway_provider' => 'nullable|string|max:50',
            'wa_api_url' => 'nullable|string|max:255',
            'wa_api_token' => 'nullable|string|max:255',
            'wa_session_id' => 'nullable|string|max:100',
            'wa_sender_number' => 'nullable|string|max:25',
            // Billing preferences
            'invoice_due_days' => 'nullable|integer|min:1|max:30',
            'auto_isolate_enabled' => 'nullable|boolean',
        ]);

        $tenant->name = $validated['name'];
        $tenant->whatsapp_number = $validated['whatsapp_number'] ?? null;
        if (! empty($validated['brand_color'])) {
            $tenant->brand_color = $validated['brand_color'];
        }

        // Encrypt secret settings
        if (isset($validated['midtrans_server_key'])) {
            $tenant->setSecretSetting('midtrans_server_key', $validated['midtrans_server_key']);
        }
        if (isset($validated['midtrans_client_key'])) {
            $tenant->setSecretSetting('midtrans_client_key', $validated['midtrans_client_key']);
        }
        if (isset($validated['wa_api_token'])) {
            $tenant->setSecretSetting('wa_api_token', $validated['wa_api_token']);
        }

        // Save public settings
        $settings = $tenant->settings ?? [];
        $settings['midtrans_is_production'] = $request->boolean('midtrans_is_production', false);
        $settings['slogan'] = $validated['slogan'] ?? ($settings['slogan'] ?? null);
        $settings['wa_gateway_provider'] = $validated['wa_gateway_provider'] ?? 'open_wa';
        $settings['wa_api_url'] = $validated['wa_api_url'] ?? ($settings['wa_api_url'] ?? 'http://localhost:2785');
        $settings['wa_session_id'] = $validated['wa_session_id'] ?? ($settings['wa_session_id'] ?? 'default');
        $settings['wa_sender_number'] = $validated['wa_sender_number'] ?? ($settings['wa_sender_number'] ?? null);
        $settings['invoice_due_days'] = (int) ($validated['invoice_due_days'] ?? 7);
        $settings['auto_isolate_enabled'] = $request->boolean('auto_isolate_enabled', true);

        $tenant->settings = $settings;
        $tenant->save();

        return redirect()->route('tenant.settings.edit', request()->route('tenant_slug') ? ['tenant_slug' => request()->route('tenant_slug')] : [])
            ->with('success', 'Pengaturan ISP berhasil diperbarui.');
    }

    public function testWhatsapp(Request $request)
    {
        $request->validate([
            'test_phone' => 'required|string|max:25',
            'test_message' => 'nullable|string|max:500',
            'test_provider' => 'nullable|string|in:internal,open_wa,fonnte,waha,callmebot',
        ]);

        $tid = tenantId();
        $tenant = Tenant::findOrFail($tid);

        // If provider is supplied from the UI, persist it to tenant settings
        if ($request->filled('test_provider')) {
            $settings = $tenant->settings ?? [];
            $settings['wa_gateway_provider'] = $request->input('test_provider');
            $tenant->settings = $settings;
            $tenant->save();
        }

        $waService = new WhatsAppService($tenant);
        $phone = $request->input('test_phone');
        $message = $request->input('test_message') ?: "Halo! Ini adalah pesan uji coba WhatsApp Gateway dari {$tenant->name} via NetSaz.";

        $result = $waService->sendMessage($phone, $message);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function checkWhatsappStatus()
    {
        $tid = tenantId();
        $tenant = Tenant::findOrFail($tid);
        $waService = new WhatsAppService($tenant);

        $provider = $tenant->settings['wa_gateway_provider'] ?? 'internal';
        if ($provider === 'internal') {
            $status = $waService->getInternalStatus();
            return response()->json($status);
        }

        if ($provider === 'open_wa') {
            $status = $waService->checkOpenWaStatus();
            return response()->json($status);
        }

        return response()->json(['connected' => true, 'status' => 'CONNECTED', 'provider' => $provider]);
    }

    public function logoutWhatsapp()
    {
        $tid = tenantId();
        $tenant = Tenant::findOrFail($tid);
        $waService = new WhatsAppService($tenant);
        $res = $waService->logoutInternal();

        return response()->json($res);
    }
}
