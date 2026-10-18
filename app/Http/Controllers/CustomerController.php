<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Customer;
use App\Models\MikrotikAccount;
use App\Models\NasServer;
use App\Models\Package;
use App\Models\Tenant;
use App\Services\MikrotikService;
use App\Services\PlanLimitChecker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with('package');

        if ($request->has('search') && $request->search) {
            $s = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('area', 'like', "%{$s}%");
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('area') && $request->area) {
            $query->where('area', $request->area);
        }

        $customers = $query->paginate(15);
        $areas = Area::where('tenant_id', tenantId())->pluck('name');

        return view('customers.index', compact('customers', 'areas'));
    }

    public function create()
    {
        $packages = Package::all();
        $areas = Area::where('tenant_id', tenantId())->pluck('name');

        return view('customers.create', compact('packages', 'areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'area' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'ktp_id' => 'nullable|string|max:50',
            'ktp_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'package_id' => 'required|exists:packages,id',
            'status' => 'required|in:active,isolated,suspended',
            'join_date' => 'required|date',
            'billing_cycle_day' => 'nullable|integer|min:1|max:28',
            'pppoe_username' => 'nullable|string|max:255',
            'pppoe_password' => 'nullable|required_with:pppoe_username|string|max:255',
            'pppoe_service' => 'nullable|string|max:50',
        ]);

        $tenant = Tenant::find(tenantId());
        if ($tenant && ! (new PlanLimitChecker($tenant))->canAddCustomer()) {
            return back()
                ->withInput()
                ->with('error', 'Batas jumlah pelanggan aktif pada paket langganan Anda sudah tercapai. Silakan upgrade paket.');
        }

        if ($request->hasFile('ktp_image')) {
            $validated['ktp_image'] = $request->file('ktp_image')->store('ktp', 'public');
        }

        $customer = Customer::create($validated);

        $message = 'Pelanggan berhasil ditambahkan.';

        if ($request->filled('pppoe_username')) {
            $account = MikrotikAccount::create([
                'tenant_id' => $customer->tenant_id,
                'customer_id' => $customer->id,
                'pppoe_secret' => $request->pppoe_username,
                'pppoe_password' => $request->pppoe_password,
                'pppoe_service' => $request->pppoe_service ?: 'pppoe',
                'status' => 'active',
            ]);

            $nas = NasServer::where('status', 'online')->orderBy('id')->first();

            if (! $nas) {
                return redirect()->route('customers.index')
                    ->with('warning', 'Pelanggan tersimpan, tetapi PPPoE belum diproses karena tidak ada router NAS online.');
            }

            $service = new MikrotikService($nas);

            if ($service->createPppoeSecret($account->pppoe_secret, $account->pppoe_password, $account->pppoe_service)) {
                $account->update(['nas_id' => $nas->id, 'provisioned_at' => now()]);

                $speed = $customer->package?->parseSpeed();
                if ($speed) {
                    $service->createSimpleQueue(
                        $account->pppoe_secret,
                        $account->pppoe_secret,
                        MikrotikService::formatMaxLimit($speed)
                    );
                }
            } else {
                $message .= ' Perhatian: provisioning PPPoE ke router gagal.';
            }
        }

        return redirect()->route('customers.index')
            ->with('success', $message);
    }

    public function show(Customer $customer)
    {
        $customer->load(['package', 'invoices.payments', 'mikrotikAccount.nasServer']);

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $packages = Package::all();
        $areas = Area::where('tenant_id', tenantId())->pluck('name');
        $customer->load('mikrotikAccount');

        return view('customers.edit', compact('customer', 'packages', 'areas'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,'.$customer->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'area' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'ktp_id' => 'nullable|string|max:50',
            'ktp_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'package_id' => 'required|exists:packages,id',
            'status' => 'required|in:active,isolated,suspended',
            'join_date' => 'required|date',
            'billing_cycle_day' => 'nullable|integer|min:1|max:28',
            'pppoe_username' => 'nullable|string|max:255',
            'pppoe_password' => 'nullable|string|max:255',
            'pppoe_service' => 'nullable|string|max:50',
        ]);

        if ($request->hasFile('ktp_image')) {
            $validated['ktp_image'] = $request->file('ktp_image')->store('ktp', 'public');
        }

        $customer->update($validated);

        $message = 'Pelanggan berhasil diperbarui.';

        if ($request->filled('pppoe_username')) {
            $account = $customer->mikrotikAccount;
            $password = $request->pppoe_password ?: $account?->pppoe_password;

            if (blank($password)) {
                return back()->withInput()->withErrors(['pppoe_password' => 'Password PPPoE wajib diisi untuk akun baru.']);
            }

            $nas = NasServer::where('status', 'online')->orderBy('id')->first();
            $service = $nas ? new MikrotikService($nas) : null;

            $oldSecret = $account?->pppoe_secret;
            if ($service && $oldSecret && $oldSecret !== $request->pppoe_username) {
                $service->deletePppoeSecret($oldSecret);
            }

            $account = $account ?: new MikrotikAccount(['tenant_id' => $customer->tenant_id, 'customer_id' => $customer->id]);
            $account->fill([
                'pppoe_secret' => $request->pppoe_username,
                'pppoe_password' => $password,
                'pppoe_service' => $request->pppoe_service ?: 'pppoe',
            ]);
            $account->save();

            if ($service) {
                if ($service->createPppoeSecret($account->pppoe_secret, $account->pppoe_password, $account->pppoe_service)) {
                    $account->update(['nas_id' => $nas->id, 'provisioned_at' => $account->provisioned_at ?? now()]);

                    $speed = $customer->package?->parseSpeed();
                    if ($speed) {
                        $service->createSimpleQueue(
                            $account->pppoe_secret,
                            $account->pppoe_secret,
                            MikrotikService::formatMaxLimit($speed)
                        );
                    }
                } else {
                    $message .= ' Perhatian: provisioning PPPoE ke router gagal.';
                }
            } else {
                $message .= ' Perhatian: PPPoE belum diproses karena tidak ada router NAS online.';
            }
        }

        return redirect()->route('customers.index')
            ->with('success', $message);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }

    public function sendPortalLink(Customer $customer)
    {
        if (empty($customer->phone)) {
            return back()->with('error', 'Nomor telepon pelanggan belum diisi.');
        }

        $pin = $customer->issuePortalPin();
        $portalUrl = $customer->portalUrl();
        $magicUrl = $customer->magicLoginUrl();

        $message = "Halo {$customer->name}, berikut link portal pelanggan Anda: {$portalUrl}\n"
            . "Login cepat: {$magicUrl}\n"
            . "PIN: *{$pin}*";

        $apiUrl = config('services.wa_notification.api_url', 'https://api.callmebot.com/whatsapp.php');

        Http::get($apiUrl, [
            'phone' => $customer->phone,
            'text' => $message,
            'apikey' => config('services.wa_notification.api_key'),
        ]);

        return back()->with('success', 'Link portal berhasil dikirim ke WhatsApp.');
    }

    public function sendWelcome(Customer $customer)
    {
        if (empty($customer->phone)) {
            return back()->with('error', 'Nomor telepon pelanggan belum diisi.');
        }

        $pin = $customer->issuePortalPin();
        $portalUrl = $customer->portalUrl();
        $magicUrl = $customer->magicLoginUrl();

        $message = "Selamat datang {$customer->name}! Layanan internet Anda telah aktif.\n"
            . "Akses portal pelanggan Anda: {$portalUrl}\n"
            . "Login cepat: {$magicUrl}\n"
            . "PIN: *{$pin}*";

        $apiUrl = config('services.wa_notification.api_url', 'https://api.callmebot.com/whatsapp.php');

        Http::get($apiUrl, [
            'phone' => $customer->phone,
            'text' => $message,
            'apikey' => config('services.wa_notification.api_key'),
        ]);

        return back()->with('success', 'Pesan selamat datang berhasil dikirim ke WhatsApp.');
    }

    public function enablePppoe(Customer $customer)
    {
        $account = $customer->mikrotikAccount;

        if (! $account) {
            return back()->with('error', 'Akun PPPoE tidak ditemukan untuk pelanggan ini.');
        }

        $nas = $this->resolveNas($account);
        if (! $nas) {
            return back()->with('error', 'Router NAS tidak ditemukan atau sedang offline.');
        }

        if (! (new MikrotikService($nas))->enablePppoeSecret($account->pppoe_secret)) {
            return back()->with('error', 'Gagal mengaktifkan PPPoE di router.');
        }

        $account->update(['status' => 'active', 'nas_id' => $nas->id]);

        return back()->with('success', 'Akun PPPoE diaktifkan kembali di router.');
    }

    public function disablePppoe(Customer $customer)
    {
        $account = $customer->mikrotikAccount;

        if (! $account) {
            return back()->with('error', 'Akun PPPoE tidak ditemukan untuk pelanggan ini.');
        }

        $nas = $this->resolveNas($account);
        if (! $nas) {
            return back()->with('error', 'Router NAS tidak ditemukan atau sedang offline.');
        }

        if (! (new MikrotikService($nas))->disablePppoeSecret($account->pppoe_secret)) {
            return back()->with('error', 'Gagal menonaktifkan PPPoE di router.');
        }

        $account->update(['status' => 'disabled', 'nas_id' => $nas->id]);

        return back()->with('success', 'Akun PPPoE dinonaktifkan di router.');
    }

    public function resetPppoePassword(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'pppoe_password' => 'required|string|max:255',
        ]);

        $account = $customer->mikrotikAccount;

        if (! $account) {
            return back()->with('error', 'Akun PPPoE tidak ditemukan untuk pelanggan ini.');
        }

        $nas = $this->resolveNas($account);
        if (! $nas) {
            return back()->with('error', 'Router NAS tidak ditemukan atau sedang offline.');
        }

        if (! (new MikrotikService($nas))->resetPppoePassword($account->pppoe_secret, $validated['pppoe_password'])) {
            return back()->with('error', 'Gagal mereset password PPPoE di router.');
        }

        $account->update(['pppoe_password' => $validated['pppoe_password'], 'nas_id' => $nas->id]);

        return back()->with('success', 'Password PPPoE berhasil direset.');
    }

    public function deletePppoe(Customer $customer)
    {
        $account = $customer->mikrotikAccount;

        if (! $account) {
            return back()->with('error', 'Akun PPPoE tidak ditemukan untuk pelanggan ini.');
        }

        $nas = $this->resolveNas($account);

        if ($nas) {
            (new MikrotikService($nas))->deletePppoeSecret($account->pppoe_secret);
        }

        $account->delete();

        return back()->with('success', 'Akun PPPoE dihapus.');
    }

    protected function resolveNas(MikrotikAccount $account): ?NasServer
    {
        if ($account->nas_id && $account->nasServer && $account->nasServer->status === 'online') {
            return $account->nasServer;
        }

        return NasServer::where('status', 'online')->orderBy('id')->first();
    }
}
