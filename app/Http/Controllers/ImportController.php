<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\MikrotikAccount;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Tenant;
use App\Services\PlanLimitChecker;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportController extends Controller
{
    public function index()
    {
        $tid = tenantId();
        $tenant = Tenant::find($tid);

        $counts = [
            'total_customers' => Customer::where('tenant_id', $tid)->count(),
            'active_customers' => Customer::where('tenant_id', $tid)->where('status', 'active')->count(),
            'isolated_customers' => Customer::where('tenant_id', $tid)->where('status', 'isolated')->count(),
            'suspended_customers' => Customer::where('tenant_id', $tid)->where('status', 'suspended')->count(),
            'unpaid_invoices' => Invoice::where('tenant_id', $tid)->where('status', 'unpaid')->count(),
            'paid_invoices' => Invoice::where('tenant_id', $tid)->where('status', 'paid')->count(),
            'payments' => Payment::where('tenant_id', $tid)->count(),
            'packages' => Package::where('tenant_id', $tid)->count(),
            'areas' => Area::where('tenant_id', $tid)->count(),
        ];

        return view('import.index', compact('tenant', 'counts'));
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_pelanggan_netsaz.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens it with proper encoding
            fputs($file, "\xEF\xBB\xBF");

            // Header columns
            fputcsv($file, [
                'nama',
                'email',
                'telepon',
                'alamat',
                'area',
                'paket',
                'status',
                'user_pppoe',
                'password_pppoe',
                'tgl_gabung',
                'tgl_tagihan',
            ]);

            // Sample rows
            fputcsv($file, [
                'Budi Santoso',
                'budi.santoso@example.com',
                '081234567890',
                'Jl. Merdeka No. 10 RT 01/RW 02',
                'Area Pusat',
                'Paket 20 Mbps',
                'active',
                'budi_pppoe',
                'rahasia123',
                '2026-01-15',
                '10',
            ]);

            fputcsv($file, [
                'Siti Nurhaliza',
                'siti.nur@example.com',
                '082198765432',
                'Perum Graha Indah Blok C-5',
                'Area Barat',
                'Paket 50 Mbps',
                'active',
                'siti_pppoe',
                'rahasia456',
                '2026-02-01',
                '1',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB
        ]);

        $tid = tenantId();
        $tenant = Tenant::findOrFail($tid);
        $limitChecker = new PlanLimitChecker($tenant);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        if (! $handle) {
            return back()->with('error', 'Gagal membaca file yang diunggah.');
        }

        // Auto-detect delimiter
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = str_contains($firstLine, ';') ? ';' : (str_contains($firstLine, "\t") ? "\t" : ',');

        $header = null;
        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $limitReached = false;

        // Default package
        $defaultPackage = Package::where('tenant_id', $tid)->first();
        if (! $defaultPackage) {
            $defaultPackage = Package::create([
                'tenant_id' => $tid,
                'name' => 'Paket Standar',
                'price' => 150000,
                'speed' => '20 Mbps',
                'is_taxable' => false,
            ]);
        }

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            // Remove UTF-8 BOM if present on first column
            if (! $header) {
                $row[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $row[0]);
                $header = array_map(fn ($h) => strtolower(trim($h)), $row);
                continue;
            }

            if (empty(array_filter($row))) {
                continue;
            }

            $data = array_combine($header, array_pad($row, count($header), null));

            // Extract fields
            $name = trim($data['nama'] ?? $data['name'] ?? '');
            if (empty($name)) {
                $skipped++;
                continue;
            }

            $email = trim($data['email'] ?? '');
            $phone = trim($data['telepon'] ?? $data['phone'] ?? $data['no_hp'] ?? $data['no_wa'] ?? '');
            $address = trim($data['alamat'] ?? $data['address'] ?? 'Alamat belum diatur');
            $areaName = trim($data['area'] ?? '');
            $packageName = trim($data['paket'] ?? $data['package'] ?? '');
            $statusRaw = strtolower(trim($data['status'] ?? 'active'));
            $userPppoe = trim($data['user_pppoe'] ?? $data['pppoe_user'] ?? $data['secret'] ?? '');
            $passPppoe = trim($data['password_pppoe'] ?? $data['pppoe_password'] ?? '');
            $joinDateRaw = trim($data['tgl_gabung'] ?? $data['join_date'] ?? '');
            $billingDayRaw = (int) ($data['tgl_tagihan'] ?? $data['billing_day'] ?? $data['billing_cycle_day'] ?? 1);

            // Normalize status
            $status = match ($statusRaw) {
                'isolir', 'isolated' => 'isolated',
                'berhenti', 'suspend', 'suspended', 'putus' => 'suspended',
                default => 'active',
            };

            // Normalize dates
            $joinDate = ! empty($joinDateRaw) && strtotime($joinDateRaw) ? Carbon::parse($joinDateRaw)->format('Y-m-d') : date('Y-m-d');
            $billingCycleDay = ($billingDayRaw >= 1 && $billingDayRaw <= 28) ? $billingDayRaw : 1;

            // Generate unique email if blank
            if (empty($email)) {
                $slugName = Str::slug($name);
                $uniqueSuffix = ! empty($userPppoe) ? Str::slug($userPppoe) : Str::random(4);
                $email = "{$slugName}_{$uniqueSuffix}@{$tenant->slug}.local";
            }

            // Find or create Area
            if (! empty($areaName)) {
                Area::firstOrCreate([
                    'tenant_id' => $tid,
                    'name' => $areaName,
                ]);
            }

            // Find package
            $package = $defaultPackage;
            if (! empty($packageName)) {
                $foundPkg = Package::where('tenant_id', $tid)
                    ->where('name', 'like', "%{$packageName}%")
                    ->first();
                if ($foundPkg) {
                    $package = $foundPkg;
                }
            }

            // Check if customer already exists for this tenant
            $existing = Customer::where('tenant_id', $tid)
                ->where(function ($q) use ($email, $phone, $userPppoe) {
                    $q->where('email', $email);
                    if (! empty($phone)) {
                        $q->orWhere('phone', $phone);
                    }
                    if (! empty($userPppoe)) {
                        $q->orWhereHas('mikrotikAccount', fn ($m) => $m->where('pppoe_secret', $userPppoe));
                    }
                })->first();

            if ($existing) {
                $existing->update([
                    'name' => $name,
                    'phone' => $phone ?: $existing->phone,
                    'address' => $address ?: $existing->address,
                    'area' => $areaName ?: $existing->area,
                    'package_id' => $package->id,
                    'status' => $status,
                    'billing_cycle_day' => $billingCycleDay,
                ]);

                if (! empty($userPppoe)) {
                    MikrotikAccount::updateOrCreate(
                        ['tenant_id' => $tid, 'customer_id' => $existing->id],
                        [
                            'pppoe_secret' => $userPppoe,
                            'pppoe_password' => $passPppoe ?: '123456',
                            'pppoe_service' => 'pppoe',
                            'status' => 'active',
                        ]
                    );
                }
                $updated++;
            } else {
                // Check subscription plan limit
                if (! $limitChecker->canAddCustomer()) {
                    $limitReached = true;
                    $skipped++;
                    continue;
                }

                $customer = Customer::create([
                    'tenant_id' => $tid,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone ?: null,
                    'address' => $address,
                    'area' => $areaName ?: null,
                    'package_id' => $package->id,
                    'status' => $status,
                    'join_date' => $joinDate,
                    'billing_cycle_day' => $billingCycleDay,
                ]);

                if (! empty($userPppoe)) {
                    MikrotikAccount::create([
                        'tenant_id' => $tid,
                        'customer_id' => $customer->id,
                        'pppoe_secret' => $userPppoe,
                        'pppoe_password' => $passPppoe ?: '123456',
                        'pppoe_service' => 'pppoe',
                        'status' => 'active',
                    ]);
                }
                $imported++;
            }
        }

        fclose($handle);

        $msg = "Migrasi selesai! Berhasil menambahkan {$imported} pelanggan baru dan memperbarui {$updated} pelanggan.";
        if ($limitReached) {
            $msg .= " Catatan: Batas kuota paket langganan aktif Anda tercapai, beberapa data terlewati. Silakan upgrade paket untuk kapasitas lebih besar.";
            return back()->with('warning', $msg);
        }

        return back()->with('success', $msg);
    }

    public function syncBeatcom(Request $request)
    {
        $request->validate([
            'files' => 'required',
        ]);

        $tid = tenantId();
        $tenant = Tenant::findOrFail($tid);
        $files = $request->file('files');
        if (! is_array($files)) {
            $files = [$files];
        }

        $totalImported = 0;
        $totalUpdated = 0;
        $processedFiles = [];

        $defaultPackage = Package::where('tenant_id', $tid)->first() ?? Package::create([
            'tenant_id' => $tid,
            'name' => 'Paket Migrasi',
            'price' => 150000,
            'speed' => '20 Mbps',
            'is_taxable' => false,
        ]);

        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $path = $file->getRealPath();
            $handle = fopen($path, 'r');
            if (! $handle) {
                continue;
            }

            // Auto-detect status context from filename
            $forcedStatus = 'active';
            $lowerName = strtolower($originalName);
            if (str_contains($lowerName, 'isolir')) {
                $forcedStatus = 'isolated';
            } elseif (str_contains($lowerName, 'berhenti') || str_contains($lowerName, 'putus')) {
                $forcedStatus = 'suspended';
            }

            $firstLine = fgets($handle);
            rewind($handle);
            $delimiter = str_contains($firstLine, ';') ? ';' : (str_contains($firstLine, "\t") ? "\t" : ',');

            $header = null;
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if (! $header) {
                    $row[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $row[0]);
                    $header = array_map(fn ($h) => strtolower(trim($h)), $row);
                    continue;
                }

                if (empty(array_filter($row))) {
                    continue;
                }

                $data = array_combine($header, array_pad($row, count($header), null));

                // Flexible header matching for Beatcom
                $name = trim($data['nama'] ?? $data['nama_pelanggan'] ?? $data['pelanggan'] ?? $data['customer'] ?? '');
                if (empty($name)) {
                    continue;
                }

                $pppoe = trim($data['user_pppoe'] ?? $data['username'] ?? $data['secret'] ?? $data['id_beatcom'] ?? '');
                $phone = trim($data['telepon'] ?? $data['no_hp'] ?? $data['no_telp'] ?? $data['wa'] ?? '');
                $address = trim($data['alamat'] ?? $data['lokasi'] ?? 'Alamat migrasi');
                $area = trim($data['area'] ?? $data['wilayah'] ?? '');
                $pkgName = trim($data['paket'] ?? $data['nama_paket'] ?? '');

                if (! empty($area)) {
                    Area::firstOrCreate(['tenant_id' => $tid, 'name' => $area]);
                }

                $pkg = $defaultPackage;
                if (! empty($pkgName)) {
                    $pkg = Package::where('tenant_id', $tid)->where('name', 'like', "%{$pkgName}%")->first() ?? $defaultPackage;
                }

                // Check match
                $customer = Customer::where('tenant_id', $tid)
                    ->where(function ($q) use ($name, $phone, $pppoe) {
                        if (! empty($pppoe)) {
                            $q->whereHas('mikrotikAccount', fn ($m) => $m->where('pppoe_secret', $pppoe));
                        }
                        if (! empty($phone)) {
                            $q->orWhere('phone', $phone);
                        }
                        $q->orWhere('name', $name);
                    })->first();

                if ($customer) {
                    $customer->update([
                        'status' => $forcedStatus,
                        'area' => $area ?: $customer->area,
                        'address' => $address ?: $customer->address,
                    ]);
                    $totalUpdated++;
                } else {
                    $emailSlug = Str::slug($name) . '_' . Str::random(4) . "@{$tenant->slug}.local";
                    $newCust = Customer::create([
                        'tenant_id' => $tid,
                        'name' => $name,
                        'email' => $emailSlug,
                        'phone' => $phone ?: null,
                        'address' => $address,
                        'area' => $area ?: null,
                        'package_id' => $pkg->id,
                        'status' => $forcedStatus,
                        'join_date' => date('Y-m-d'),
                        'billing_cycle_day' => 1,
                    ]);

                    if (! empty($pppoe)) {
                        MikrotikAccount::create([
                            'tenant_id' => $tid,
                            'customer_id' => $newCust->id,
                            'pppoe_secret' => $pppoe,
                            'pppoe_password' => '123456',
                            'pppoe_service' => 'pppoe',
                            'status' => 'active',
                        ]);
                    }
                    $totalImported++;
                }
            }
            fclose($handle);
            $processedFiles[] = $originalName;
        }

        $filesStr = implode(', ', $processedFiles);
        return back()->with('success', "Sinkronisasi selesai! Memproses {$filesStr}: {$totalImported} pelanggan baru berhasil diimpor, {$totalUpdated} status pelanggan disinkronkan.");
    }

    public function exportData(Request $request)
    {
        $tid = tenantId();
        $type = $request->query('type', 'pelanggan_semua');
        $filename = "export_{$type}_" . date('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($tid, $type) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM

            if (str_starts_with($type, 'pelanggan')) {
                fputcsv($file, ['No', 'ID Pelanggan', 'Nama', 'Email', 'Telepon', 'Alamat', 'Area', 'Paket', 'Harga (Rp)', 'Status', 'Tgl Gabung', 'Tgl Tagihan', 'User PPPoE']);
                $query = Customer::with(['package', 'mikrotikAccount'])->where('tenant_id', $tid);

                if ($type === 'pelanggan_aktif') {
                    $query->where('status', 'active');
                } elseif ($type === 'pelanggan_isolir') {
                    $query->where('status', 'isolated');
                } elseif ($type === 'pelanggan_berhenti') {
                    $query->where('status', 'suspended');
                }

                $no = 1;
                foreach ($query->get() as $c) {
                    fputcsv($file, [
                        $no++,
                        $c->id,
                        $c->name,
                        $c->email,
                        $c->phone ?? '-',
                        $c->address,
                        $c->area ?? '-',
                        $c->package?->name ?? '-',
                        $c->package?->price ?? 0,
                        strtoupper($c->status),
                        $c->join_date ? $c->join_date->format('Y-m-d') : '-',
                        $c->billing_cycle_day ?? 1,
                        $c->mikrotikAccount?->pppoe_secret ?? '-',
                    ]);
                }
            } elseif ($type === 'tagihan_lunas' || $type === 'tagihan_belum_bayar') {
                fputcsv($file, ['No', 'Nomor Invoice', 'Pelanggan', 'Nominal (Rp)', 'Diskon', 'Pajak', 'Total (Rp)', 'Status', 'Jatuh Tempo', 'Tgl Dibuat']);
                $status = ($type === 'tagihan_lunas') ? 'paid' : 'unpaid';
                $invoices = Invoice::with('customer')->where('tenant_id', $tid)->where('status', $status)->get();

                $no = 1;
                foreach ($invoices as $inv) {
                    fputcsv($file, [
                        $no++,
                        $inv->invoice_number,
                        $inv->customer?->name ?? '-',
                        $inv->amount,
                        $inv->discount ?? 0,
                        $inv->tax ?? 0,
                        ($inv->amount - ($inv->discount ?? 0) + ($inv->tax ?? 0)),
                        strtoupper($inv->status),
                        $inv->due_date ? $inv->due_date->format('Y-m-d') : '-',
                        $inv->created_at->format('Y-m-d H:i'),
                    ]);
                }
            } elseif ($type === 'pembayaran') {
                fputcsv($file, ['No', 'Kode Referensi', 'Pelanggan', 'Jumlah (Rp)', 'Metode', 'Status', 'Tanggal Bayar']);
                $payments = Payment::with('customer')->where('tenant_id', $tid)->latest()->get();

                $no = 1;
                foreach ($payments as $p) {
                    fputcsv($file, [
                        $no++,
                        $p->reference_number ?? $p->id,
                        $p->customer?->name ?? '-',
                        $p->amount,
                        $p->payment_method ?? 'Cash/Transfer',
                        strtoupper($p->status),
                        $p->created_at->format('Y-m-d H:i'),
                    ]);
                }
            } else {
                fputcsv($file, ['No', 'Item', 'Data']);
                fputcsv($file, [1, 'Info', 'Tipe ekspor tidak dikenali.']);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
