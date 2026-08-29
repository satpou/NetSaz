<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $contact = ContactMessage::create($validated);

        // ---------- WhatsApp notification (free via CallMeBot) ----------
        // Environment variables (add to .env):
        //   WA_PHONE – nomor tujuan (contoh: 6281234567890)
        //   WA_API_URL – endpoint CallMeBot (default https://api.callmebot.com/whatsapp.php)
        $phone = config('services.wa_notification.phone');
        $apiUrl = config('services.wa_notification.api_url') ?? 'https://api.callmebot.com/whatsapp.php';
        $text = "*Pesan Baru dari NetSaz*\n\n".
            "Nama   : {$contact->name}\n".
            "Email  : {$contact->email}\n".
            "Pesan  : {$contact->message}";

        $url = $apiUrl . '?phone=' . $phone . '&text=' . urlencode($text);

        try {
            $response = Http::get($url);
            Log::info('WhatsApp notification sent via CallMeBot', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp notification failed', ['exception' => $e]);
        }
        // -----------------------------------------------------------------

        return redirect()->back()
            ->with('success', 'Pesan Anda berhasil terkirim. Tim kami akan merespon via WhatsApp.');
    }
}

