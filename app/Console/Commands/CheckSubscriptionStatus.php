<?php

namespace App\Console\Commands;

use App\Models\TenantSubscription;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckSubscriptionStatus extends Command
{
    protected $signature = 'subscriptions:check-status';
    protected $description = 'Periksa masa berlaku langganan tenant ISP dan kirim notifikasi WhatsApp pengingat perpanjangan tahunan';

    public function handle(): int
    {
        $this->info('Memeriksa masa berlaku langganan tenant ISP...');

        $subscriptions = TenantSubscription::with(['tenant', 'plan'])
            ->whereIn('status', ['active', 'trial'])
            ->whereNotNull('current_period_end')
            ->get();

        $today = Carbon::today();
        $processed = 0;
        $remindersSent = 0;
        $expiredCount = 0;

        $wa = app(WhatsAppService::class);

        foreach ($subscriptions as $sub) {
            $tenant = $sub->tenant;
            if (! $tenant) {
                continue;
            }

            $expiry = $sub->current_period_end->startOfDay();
            $daysLeft = (int) $today->diffInDays($expiry, false);

            $processed++;

            // KASUS 1: LANGGANAN TELAH EXPIRED
            if ($daysLeft < 0) {
                $sub->update(['status' => 'expired']);

                if ($tenant->status === 'active' || $tenant->status === 'trial') {
                    $tenant->update(['status' => 'suspended']);
                }

                $expiredCount++;
                $this->warn("Subscription #{$sub->id} ({$tenant->name}) telah EXPIRED.");

                $cacheKey = "sub_notify_expired_{$sub->id}_{$today->toDateString()}";
                if (! Cache::has($cacheKey) && ! empty($tenant->whatsapp_number)) {
                    Cache::put($cacheKey, true, 86400);
                    $pic = $tenant->settings['pic_name'] ?? $tenant->name;
                    $msg = "Halo *{$pic}*,\n\nPemberitahuan Resmi NetSaz:\nMasa aktif langganan tahunan untuk ISP *{$tenant->name}* telah BERAKHIR pada *{$expiry->format('d M Y')}*.\n\nAkses dashboard ISP Anda saat ini ditangguhkan sementara. Silakan lakukan perpanjangan langganan atau hubungi tim NetSaz untuk mengaktifkan kembali akun Anda.\n\nTerima kasih atas kerja samanya.";
                    try {
                        $wa->sendMessage($tenant->whatsapp_number, $msg);
                        $remindersSent++;
                    } catch (\Throwable $e) {
                        Log::warning("Gagal mengirim WA expired sub {$sub->id}: " . $e->getMessage());
                    }
                }
                continue;
            }

            // KASUS 2: PENGINGAT H-30, H-7, H-1
            if (in_array($daysLeft, [30, 7, 1], true)) {
                $cacheKey = "sub_notify_{$sub->id}_{$daysLeft}_{$today->toDateString()}";
                if (Cache::has($cacheKey)) {
                    continue;
                }

                Cache::put($cacheKey, true, 86400);

                if (! empty($tenant->whatsapp_number)) {
                    $pic = $tenant->settings['pic_name'] ?? $tenant->name;
                    $planName = $sub->plan?->name ?? 'Langganan';

                    if ($daysLeft === 30) {
                        $msg = "Halo *{$pic}*,\n\nPengingat Perpanjangan NetSaz:\nMasa langganan paket *{$planName}* untuk ISP *{$tenant->name}* akan berakhir dalam *30 hari* lagi (pada *{$expiry->format('d M Y')}*).\n\nUntuk menjaga operasional dan otomatisasi billing ISP Anda tetap berjalan tanpa gangguan, silakan siapkan perpanjangan langganan tahunan melalui dashboard atau hubungi tim NetSaz.\n\nTerima kasih!";
                    } elseif ($daysLeft === 7) {
                        $msg = "Halo *{$pic}*,\n\nPeringatan Perpanjangan NetSaz:\nMasa langganan paket *{$planName}* untuk ISP *{$tenant->name}* tersisa *7 hari* lagi (berakhir pada *{$expiry->format('d M Y')}*).\n\nSegera selesaikan perpanjangan langganan tahunan untuk menghindari pembatasan akses dashboard dan isolir otomatis.\n\nTerima kasih!";
                    } else { // 1 day
                        $msg = "Halo *{$pic}*,\n\nPERHATIAN MENDESAK:\nMasa langganan paket *{$planName}* untuk ISP *{$tenant->name}* akan BERAKHIR BESOK (pada *{$expiry->format('d M Y')}*).\n\nMohon segera selesaikan perpanjangan langganan Anda hari ini agar layanan sistem tetap aktif normal.\n\nTerima kasih!";
                    }

                    try {
                        $wa->sendMessage($tenant->whatsapp_number, $msg);
                        $remindersSent++;
                        $this->info("Pengingat H-{$daysLeft} terkirim ke {$tenant->name} ({$tenant->whatsapp_number})");
                    } catch (\Throwable $e) {
                        Log::warning("Gagal mengirim WA reminder H-{$daysLeft} sub {$sub->id}: " . $e->getMessage());
                    }
                }
            }
        }

        $this->info("Selesai. Diproses: {$processed}, Reminder terkirim: {$remindersSent}, Expired: {$expiredCount}.");
        return Command::SUCCESS;
    }
}
