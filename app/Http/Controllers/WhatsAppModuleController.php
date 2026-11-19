<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Tenant;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppModuleController extends Controller
{
    /**
     * WhatsApp Notification Templates
     */
    public function templates()
    {
        $tid = tenantId();
        $tenant = Tenant::findOrFail($tid);
        $settings = $tenant->settings ?? [];

        $defaultTemplates = [
            'wa_template_invoice' => "Halo {nama},\n\nTagihan internet NetSaz Anda untuk periode {periode} telah terbit:\nNo Invoice: {invoice_no}\nTotal: Rp {nominal}\nJatuh Tempo: {jatuh_tempo}\n\nSilakan bayar melalui portal berikut:\n{link_portal}\n\nTerima kasih.",
            'wa_template_reminder' => "Halo {nama},\n\nPengingat: Tagihan internet Anda sebesar Rp {nominal} akan jatuh tempo pada {jatuh_tempo}.\n\nSegera lakukan pembayaran untuk menghindari pemutusan otomatis:\n{link_portal}\n\nTerima kasih.",
            'wa_template_paid' => "Halo {nama},\n\nPembayaran tagihan {invoice_no} sebesar Rp {nominal} telah kami terima. Layanan internet Anda aktif.\n\nUnduh kuitansi:\n{link_portal}\n\nTerima kasih atas kepercayaan Anda.",
            'wa_template_isolir' => "Pemberitahuan: Layanan internet atas nama {nama} saat ini di-isolir sementara karena tagihan melewati jatuh tempo ({jatuh_tempo}).\n\nUntuk mengaktifkan kembali seketika, silakan lakukan pembayaran di:\n{link_portal}\n\nTerima kasih.",
            'wa_template_ticket' => "Halo {nama},\n\nUpdate Tiket #{ticket_no} ({judul}):\nStatus: {status}\nCatatan: {catatan}\n\nTim teknisi kami siap membantu Anda.",
        ];

        return view('whatsapp.templates', compact('tenant', 'settings', 'defaultTemplates'));
    }

    public function templatesUpdate(Request $request)
    {
        $tid = tenantId();
        $tenant = Tenant::findOrFail($tid);

        $validated = $request->validate([
            'wa_template_invoice' => 'required|string',
            'wa_template_reminder' => 'required|string',
            'wa_template_paid' => 'required|string',
            'wa_template_isolir' => 'required|string',
            'wa_template_ticket' => 'required|string',
        ]);

        $settings = $tenant->settings ?? [];
        foreach ($validated as $key => $val) {
            $settings[$key] = $val;
        }

        $tenant->settings = $settings;
        $tenant->save();

        return back()->with('success', 'Template pesan WhatsApp berhasil diperbarui.');
    }

    /**
     * WhatsApp Broadcast Blast
     */
    public function broadcast()
    {
        $tid = tenantId();
        $areas = Area::where('tenant_id', $tid)->get();
        $packages = Package::where('tenant_id', $tid)->get();
        $totalCustomers = Customer::where('tenant_id', $tid)->count();

        return view('whatsapp.broadcast', compact('areas', 'packages', 'totalCustomers'));
    }

    public function broadcastSend(Request $request, WhatsAppService $waService)
    {
        $tid = tenantId();
        $validated = $request->validate([
            'message' => 'required|string|min:5',
            'target_filter' => 'required|in:all,area,package,isolated',
            'area_id' => 'nullable|exists:areas,id',
            'package_id' => 'nullable|exists:packages,id',
        ]);

        $query = Customer::where('tenant_id', $tid)->whereNotNull('phone');

        if ($validated['target_filter'] === 'area' && !empty($validated['area_id'])) {
            $query->where('area_id', $validated['area_id']);
        } elseif ($validated['target_filter'] === 'package' && !empty($validated['package_id'])) {
            $query->where('package_id', $validated['package_id']);
        } elseif ($validated['target_filter'] === 'isolated') {
            $query->where('status', 'isolated');
        }

        $recipients = $query->get(['id', 'name', 'phone']);
        $count = $recipients->count();

        if ($count === 0) {
            return back()->with('warning', 'Tidak ditemukan pelanggan yang cocok dengan kriteria filter.');
        }

        // Send messages
        $successCount = 0;
        foreach ($recipients as $recipient) {
            $personalized = str_replace(
                ['{nama}', '{name}'],
                [$recipient->name, $recipient->name],
                $validated['message']
            );

            try {
                $sent = $waService->sendTextMessage($recipient->phone, $personalized);
                if ($sent) {
                    $successCount++;
                }
            } catch (\Throwable $e) {
                // Continue broadcast
            }
        }

        return back()->with('success', "Broadcast WhatsApp berhasil dikirim ke {$successCount} dari {$count} pelanggan.");
    }
}
