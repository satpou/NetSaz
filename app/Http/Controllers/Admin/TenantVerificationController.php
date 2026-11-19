<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantVerificationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending_payment');

        $query = Tenant::with(['subscriptions.plan', 'users'])
            ->latest();

        if ($status === 'pending_payment') {
            $query->where('status', 'pending_payment');
        } elseif ($status === 'active') {
            $query->where('status', 'active');
        } elseif ($status === 'rejected') {
            $query->where('status', 'rejected');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('whatsapp_number', 'like', "%{$search}%");
            });
        }

        $tenants = $query->paginate(15)->withQueryString();

        $counts = [
            'pending' => Tenant::where('status', 'pending_payment')->count(),
            'active' => Tenant::where('status', 'active')->count(),
            'rejected' => Tenant::where('status', 'rejected')->count(),
            'total' => Tenant::count(),
        ];

        return view('admin.verifications.index', compact('tenants', 'counts', 'status'));
    }

    public function accept(Request $request, Tenant $tenant)
    {
        DB::transaction(function () use ($tenant) {
            $settings = $tenant->settings ?? [];
            $settings['payment_status'] = 'paid';
            $settings['verified_at'] = now()->toDateTimeString();
            $settings['verified_by'] = auth()->id();

            $tenant->update([
                'status' => 'active',
                'settings' => $settings,
            ]);

            // Activate super admin user for this tenant
            User::where('tenant_id', $tenant->id)->update([
                'is_active' => true,
            ]);

            // Activate subscription for 1 Year (12 months)
            $sub = TenantSubscription::where('tenant_id', $tenant->id)->latest()->first();
            $now = Carbon::now();
            $oneYearLater = (clone $now)->addYear();

            if ($sub) {
                $sub->update([
                    'status' => 'active',
                    'started_at' => $now,
                    'current_period_start' => $now,
                    'current_period_end' => $oneYearLater,
                ]);
            } else {
                $planId = $tenant->settings['selected_package_id'] ?? null;
                TenantSubscription::create([
                    'tenant_id' => $tenant->id,
                    'subscription_plan_id' => $planId ?? 1,
                    'status' => 'active',
                    'started_at' => $now,
                    'current_period_start' => $now,
                    'current_period_end' => $oneYearLater,
                ]);
            }
        });

        // Send WhatsApp notification to tenant PIC if number is present
        try {
            if (! empty($tenant->whatsapp_number)) {
                $wa = app(WhatsAppService::class);
                $picName = $tenant->settings['pic_name'] ?? $tenant->name;
                $loginUrl = "https://{$tenant->slug}.netsaz.web.id/login";
                $isEnt = ($tenant->settings['selected_package'] ?? '') === 'enterprise';
                $planDesc = $isEnt ? 'layanan Enterprise Kustom' : 'langganan tahunan NetSaz';
                $message = "Halo *{$picName}*,\n\nAkun ISP *{$tenant->name}* untuk {$planDesc} telah BERHASIL diaktifkan oleh Tim NetSaz.\n\nAkun ISP Anda kini sudah AKTIF. Silakan login ke dashboard Anda melalui tautan berikut:\n{$loginUrl}\n\nEmail Login: {$tenant->email}\n\nTerima kasih telah mempercayai NetSaz.";
                $wa->sendMessage($tenant->whatsapp_number, $message);
            }
        } catch (\Throwable $e) {
            Log::warning("Gagal mengirim WA notifikasi aktivasi tenant {$tenant->id}: " . $e->getMessage());
        }

        return back()->with('success', "Pendaftaran tenant '{$tenant->name}' berhasil diterima! Akun dan langganan tahunan telah aktif.");
    }

    public function reject(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($tenant, $validated) {
            $settings = $tenant->settings ?? [];
            $settings['payment_status'] = 'rejected';
            $settings['rejection_reason'] = $validated['reason'];
            $settings['rejected_at'] = now()->toDateTimeString();
            $settings['rejected_by'] = auth()->id();

            $tenant->update([
                'status' => 'rejected',
                'settings' => $settings,
            ]);

            User::where('tenant_id', $tenant->id)->update([
                'is_active' => false,
            ]);

            TenantSubscription::where('tenant_id', $tenant->id)->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
        });

        // Send WhatsApp notification of rejection
        try {
            if (! empty($tenant->whatsapp_number)) {
                $wa = app(WhatsAppService::class);
                $picName = $tenant->settings['pic_name'] ?? $tenant->name;
                $message = "Halo *{$picName}*,\n\nMohon maaf, pendaftaran ISP *{$tenant->name}* di NetSaz belum dapat disetujui.\n\n*Alasan:* {$validated['reason']}\n\nJika ada pertanyaan atau ingin melakukan konfirmasi ulang pembayaran, silakan hubungi tim sales kami di WhatsApp 083162238930.\n\nTerima kasih.";
                $wa->sendMessage($tenant->whatsapp_number, $message);
            }
        } catch (\Throwable $e) {
            Log::warning("Gagal mengirim WA notifikasi penolakan tenant {$tenant->id}: " . $e->getMessage());
        }

        return back()->with('warning', "Pendaftaran tenant '{$tenant->name}' telah ditolak.");
    }
}
