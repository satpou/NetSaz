<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendInvoiceReminders extends Command
{
    protected $signature = 'invoices:send-reminders';
    protected $description = 'Kirim pengingat tagihan otomatis (H-3 dan H-1 jatuh tempo) ke WhatsApp pelanggan';

    public function handle()
    {
        $this->info('Memulai pengecekan invoice untuk reminder WhatsApp...');

        $now = Carbon::today();
        $targetDates = [
            $now->copy()->addDays(3)->format('Y-m-d'), // H-3
            $now->copy()->addDays(1)->format('Y-m-d'), // H-1
            $now->format('Y-m-d'),                     // Hari H
        ];

        $invoices = Invoice::with(['customer', 'tenant'])
            ->where('status', 'unpaid')
            ->whereIn('due_date', $targetDates)
            ->get();

        $this->info("Ditemukan {$invoices->count()} invoice yang perlu dikirimkan pengingat.");

        $sentCount = 0;
        $failCount = 0;

        foreach ($invoices as $invoice) {
            $customer = $invoice->customer;
            if (! $customer || empty($customer->phone)) {
                continue;
            }

            $waService = new WhatsAppService($invoice->tenant);
            $formattedTotal = 'Rp ' . number_format($invoice->total_amount, 0, ',', '.');
            $dueDate = $invoice->due_date ? $invoice->due_date->format('d M Y') : '-';
            $portalUrl = $customer->portalUrl() . '/invoices/' . $invoice->id;
            $tenantName = $customer->tenant?->name ?? 'NetSaz';

            $daysLeft = $now->diffInDays($invoice->due_date, false);
            $urgency = match ($daysLeft) {
                0 => 'HARI INI adalah batas akhir pembayaran',
                1 => 'besok adalah batas waktu pembayaran',
                default => "sisa {$daysLeft} hari sebelum jatuh tempo",
            };

            $message = "*PENGINGAT TAGIHAN INTERNET - {$tenantName}*\n\n"
                . "Yth. *{$customer->name}*,\n"
                . "Mengingatkan bahwa {$urgency} untuk tagihan internet Anda:\n\n"
                . "• No. Invoice: *{$invoice->invoice_number}*\n"
                . "• Total Tagihan: *{$formattedTotal}*\n"
                . "• Batas Jatuh Tempo: *{$dueDate}*\n\n"
                . "Mohon lakukan pembayaran tepat waktu agar layanan tidak terisolir otomatis.\n"
                . "Link pembayaran: {$portalUrl}\n\n"
                . "Abaikan pesan ini jika Anda telah melakukan pembayaran.";

            $result = $waService->sendMessage($customer->phone, $message);

            if ($result['success']) {
                $sentCount++;
                $this->line(" [✓] Reminder terkirim ke {$customer->name} ({$customer->phone}) - {$invoice->invoice_number}");
            } else {
                $failCount++;
                $this->warn(" [✗] Gagal kirim ke {$customer->name} ({$customer->phone}): {$result['message']}");
            }
        }

        $this->info("Selesai. Sukses: {$sentCount}, Gagal/Lewat: {$failCount}.");
        Log::info("Invoice WhatsApp reminders executed. Success: {$sentCount}, Failed: {$failCount}");

        return Command::SUCCESS;
    }
}
