<?php

namespace App\Http\Controllers;

use App\Models\PlatformSetting;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RegistrationPaymentController extends Controller
{
    public function show(string $slug)
    {
        $tenant = Tenant::with(['subscriptions.plan'])->where('slug', $slug)->firstOrFail();

        // If already active, redirect to tenant login
        if ($tenant->status === 'active') {
            $tenantDomain = config('app.tenant_domain');
            return redirect("https://{$tenant->slug}.{$tenantDomain}/login")
                ->with('success', 'Akun ISP Anda sudah aktif! Silakan login.');
        }

        $settings = $tenant->settings ?? [];
        $selectedCode = $settings['selected_package'] ?? 'launch';
        $plan = SubscriptionPlan::where('code', $selectedCode)->first()
            ?? SubscriptionPlan::orderBy('sort_order')->first();

        $amount = (float) ($settings['payment_amount'] ?? ($plan ? $plan->price : 399000));

        // Bank Accounts from Superadmin Platform Settings
        $bankAccounts = PlatformSetting::get('platform_bank_accounts', [
            [
                'bank' => 'BCA (Bank Central Asia)',
                'account_number' => '8728208410',
                'account_name' => 'Satria Rahmaddhani',
            ],
        ]);

        $salesWhatsapp = PlatformSetting::get('sales_whatsapp', '083162238930');
        $salesName = PlatformSetting::get('sales_name', 'Ahmad Zidnan');
        $paymentInstructions = PlatformSetting::get('payment_instructions', '');

        // Midtrans Snap Token if configured
        $snapToken = null;
        $midtransService = new MidtransService();
        $isMidtransAvailable = $midtransService->isConfigured();

        if ($isMidtransAvailable && $plan && ($settings['payment_status'] ?? '') !== 'waiting_verification') {
            try {
                $snapResult = $midtransService->createSubscriptionTransaction($tenant, $plan, $amount);
                $snapToken = $snapResult['snap_token'] ?? null;
            } catch (\Throwable $e) {
                Log::warning("Gagal membuat Snap token registrasi tenant {$tenant->slug}: " . $e->getMessage());
            }
        }

        $snapScriptUrl = $midtransService->getSnapUrl();
        $midtransClientKey = $midtransService->getClientKey();

        return view('register-payment', compact(
            'tenant',
            'plan',
            'amount',
            'bankAccounts',
            'salesWhatsapp',
            'salesName',
            'paymentInstructions',
            'isMidtransAvailable',
            'snapToken',
            'snapScriptUrl',
            'midtransClientKey',
            'settings'
        ));
    }

    public function submitManual(Request $request, string $slug)
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'sender_bank' => 'required|string|max:100',
            'sender_account_name' => 'required|string|max:100',
            'sender_account_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'proof_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $path = $request->file('proof_image')->store('payment-proofs', 'public');

        $settings = $tenant->settings ?? [];
        $settings['payment_method'] = 'manual';
        $settings['payment_status'] = 'waiting_verification';
        $settings['sender_bank'] = $validated['sender_bank'];
        $settings['sender_account_name'] = $validated['sender_account_name'];
        $settings['sender_account_number'] = $validated['sender_account_number'] ?? null;
        $settings['proof_image_path'] = $path;
        $settings['submitted_at'] = now()->toDateTimeString();
        $settings['notes'] = $validated['notes'] ?? null;

        $tenant->update([
            'settings' => $settings,
        ]);

        return back()->with('success', 'Bukti pembayaran berhasil diunggah! Super Admin kami akan segera memverifikasi dan mengaktifkan akun Anda.');
    }
}
