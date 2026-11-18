<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;

class PlatformSettingController extends Controller
{
    public function index()
    {
        $bankAccounts = PlatformSetting::get('platform_bank_accounts', [
            [
                'bank' => 'BCA (Bank Central Asia)',
                'account_number' => '8728208410',
                'account_name' => 'Satria Rahmaddhani',
            ],
        ]);

        $midtransServerKey = PlatformSetting::get('midtrans_server_key', '');
        $midtransClientKey = PlatformSetting::get('midtrans_client_key', '');
        $midtransIsProduction = (bool) PlatformSetting::get('midtrans_is_production', false);
        $salesWhatsapp = PlatformSetting::get('sales_whatsapp', '083162238930');
        $salesName = PlatformSetting::get('sales_name', 'Ahmad Zidnan');
        $paymentInstructions = PlatformSetting::get('payment_instructions', 'Silakan transfer sesuai nominal ke salah satu rekening di atas, lalu unggah bukti transfer. Akun akan segera diverifikasi dan diaktifkan oleh Super Admin.');

        return view('admin.settings.index', compact(
            'bankAccounts',
            'midtransServerKey',
            'midtransClientKey',
            'midtransIsProduction',
            'salesWhatsapp',
            'salesName',
            'paymentInstructions'
        ));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'sales_whatsapp' => 'nullable|string|max:30',
            'sales_name' => 'nullable|string|max:100',
            'midtrans_server_key' => 'nullable|string|max:255',
            'midtrans_client_key' => 'nullable|string|max:255',
            'midtrans_is_production' => 'nullable|boolean',
            'payment_instructions' => 'nullable|string|max:2000',
            'banks' => 'nullable|array',
            'banks.*.bank' => 'required|string|max:100',
            'banks.*.account_number' => 'required|string|max:50',
            'banks.*.account_name' => 'required|string|max:100',
        ]);

        // Clean & format bank accounts
        $banks = [];
        if (! empty($validated['banks'])) {
            foreach ($validated['banks'] as $b) {
                if (! empty($b['bank']) && ! empty($b['account_number'])) {
                    $banks[] = [
                        'bank' => trim($b['bank']),
                        'account_number' => trim($b['account_number']),
                        'account_name' => trim($b['account_name']),
                    ];
                }
            }
        }

        PlatformSetting::set('platform_bank_accounts', $banks);
        PlatformSetting::set('sales_whatsapp', $validated['sales_whatsapp'] ?? '083162238930');
        PlatformSetting::set('sales_name', $validated['sales_name'] ?? 'Ahmad Zidnan');
        PlatformSetting::set('midtrans_server_key', trim($validated['midtrans_server_key'] ?? ''));
        PlatformSetting::set('midtrans_client_key', trim($validated['midtrans_client_key'] ?? ''));
        PlatformSetting::set('midtrans_is_production', (bool) ($request->has('midtrans_is_production')));
        PlatformSetting::set('payment_instructions', $validated['payment_instructions'] ?? '');

        return back()->with('success', 'Pengaturan rekening bank & pembayaran platform berhasil disimpan!');
    }
}
