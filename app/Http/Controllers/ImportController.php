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
use DOMDocument;
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

    public function downloadTemplate(Request $request)
    {
        $format = $request->query('format', 'csv');

        if ($format === 'beatcom' || $format === 'beatcom_xls') {
            return $this->exportBeatcomTemplate();
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_pelanggan_netsaz.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM

            // Header columns matching NetSaz & Beatcom flexible format
            fputcsv($file, [
                'nama',
                'telepon',
                'ppoe',
                'paket',
                'area',
                'alamat',
                'biaya',
                'tanggal tagihan',
                'lat long',
                'nik',
                'status',
            ]);

            // Sample rows
            fputcsv($file, [
                'Adit',
                '6283856637059',
                'hendra@legok',
                'Paket Voucher',
                'Legok Kemiri',
                'Kp. Legok Kemiri RT 01/02',
                '0',
                '05',
                '-6.11338253, 106.45015284',
                '',
                'active',
            ]);

            fputcsv($file, [
                'Agus Riyanto',
                '6281318284696',
                'savanaC5/32@agus',
                'Paket Home 10 Mbps',
                'Savana Residence Munjul Solear',
                'Perumahan Savana Residence Blok C3/32 Rt.09/Rw.06',
                '165000',
                '01',
                '-6.28201997, 106.45913389',
                '3603123456780001',
                'active',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Parse uploaded file (supports HTML Table disguised as .xls from Beatcom, or standard CSV/TSV)
     */
    protected function parseFileContent(string $path, string $originalName = ''): array
    {
        $content = file_get_contents($path);
        if ($content === false || empty(trim($content))) {
            return ['title' => '', 'headers' => [], 'records' => []];
        }

        // 1. Detect if it is an HTML Table (Beatcom Excel export format)
        if (str_contains($content, '<table') || str_contains($content, '<html') || str_contains($content, '<TR') || str_contains($content, '<tr')) {
            return $this->parseHtmlTable($content);
        }

        // 2. Otherwise parse as CSV / TSV text
        return $this->parseCsvContent($path);
    }

    /**
     * Parse HTML table format exported by Beatcom
     */
    protected function parseHtmlTable(string $html): array
    {
        $dom = new DOMDocument();
        // Suppress HTML5 / malformed warnings
        @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $rows = $dom->getElementsByTagName('tr');

        $title = '';
        $headers = [];
        $records = [];

        foreach ($rows as $r) {
            $ths = $r->getElementsByTagName('th');
            // If row has 1 TH with colspan (e.g. "Belum Bayar - November 2026")
            if ($ths->length === 1 && empty($title)) {
                $title = trim($ths->item(0)->textContent);
                continue;
            }

            // If row has multiple THs, this is the header row
            if ($ths->length > 1 && empty($headers)) {
                foreach ($ths as $th) {
                    $headers[] = strtolower(trim($th->textContent));
                }
                continue;
            }

            // Data row
            $tds = $r->getElementsByTagName('td');
            if ($tds->length > 0 && count($headers) > 0) {
                $row = [];
                foreach ($tds as $idx => $td) {
                    $h = $headers[$idx] ?? "col_{$idx}";
                    $row[$h] = trim($td->textContent);
                }
                $records[] = $row;
            }
        }

        return [
            'title' => $title,
            'headers' => $headers,
            'records' => $records,
        ];
    }

    /**
     * Parse CSV or TSV content
     */
    protected function parseCsvContent(string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            return ['title' => '', 'headers' => [], 'records' => []];
        }

        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = str_contains($firstLine, ';') ? ';' : (str_contains($firstLine, "\t") ? "\t" : ',');

        $headers = [];
        $records = [];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (empty(array_filter($row))) {
                continue;
            }

            if (empty($headers)) {
                // Remove UTF-8 BOM
                $row[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $row[0]);
                $headers = array_map(fn ($h) => strtolower(trim($h)), $row);
                continue;
            }

            $record = [];
            foreach ($headers as $idx => $h) {
                $record[$h] = trim($row[$idx] ?? '');
            }
            $records[] = $record;
        }

        fclose($handle);

        return [
            'title' => '',
            'headers' => $headers,
            'records' => $records,
        ];
    }

    /**
     * Process a single customer record from Beatcom or CSV into NetSaz database
     */
    protected function processCustomerRecord(array $data, Tenant $tenant, string $titleContext = '', ?PlanLimitChecker $limitChecker = null, ?Package $defaultPackage = null): array
    {
        $tid = $tenant->id;

        // 1. Name
        $name = trim($data['nama'] ?? $data['nama_pelanggan'] ?? $data['pelanggan'] ?? $data['customer'] ?? $data['name'] ?? '');
        if (empty($name)) {
            return ['status' => 'skipped', 'reason' => 'Nama kosong'];
        }

        // Clean values wrapped in quotes
        $name = trim($name, " \t\n\r\0\x0B\"'");

        // 2. Phone
        $phoneRaw = trim($data['telepon'] ?? $data['phone'] ?? $data['no_hp'] ?? $data['no_telp'] ?? $data['wa'] ?? '');
        $phone = preg_replace('/[^0-9]/', '', $phoneRaw);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // 3. PPPoE Secret
        $pppoe = trim($data['ppoe'] ?? $data['user_pppoe'] ?? $data['pppoe_user'] ?? $data['username'] ?? $data['secret'] ?? $data['id_beatcom'] ?? '');
        $pppoe = trim($pppoe, " \t\n\r\0\x0B\"'");

        // 4. Address & Area
        $address = trim($data['alamat'] ?? $data['address'] ?? $data['lokasi'] ?? 'Alamat belum diatur');
        $address = trim($address, " \t\n\r\0\x0B\"'");
        $areaName = trim($data['area'] ?? $data['wilayah'] ?? '');
        $areaName = trim($areaName, " \t\n\r\0\x0B\"'");

        if (! empty($areaName)) {
            Area::firstOrCreate(['tenant_id' => $tid, 'name' => $areaName]);
        }

        // 5. Package & Price (Auto-create Package if missing!)
        $pkgName = trim($data['paket'] ?? $data['nama_paket'] ?? $data['package'] ?? '');
        $pkgName = trim($pkgName, " \t\n\r\0\x0B\"'");
        $biaya = (int) preg_replace('/[^0-9]/', '', $data['biaya'] ?? $data['harga'] ?? $data['total bayar'] ?? '0');

        $package = $defaultPackage;
        if (! empty($pkgName)) {
            $package = Package::firstOrCreate(
                ['tenant_id' => $tid, 'name' => $pkgName],
                [
                    'price' => $biaya > 0 ? $biaya : 150000,
                    'speed' => '20 Mbps',
                    'is_taxable' => false,
                ]
            );
        }

        // 6. Billing Cycle Day (Tanggal Tagihan)
        $billingDayRaw = trim($data['tanggal tagihan'] ?? $data['tgl_tagihan'] ?? $data['billing_day'] ?? '1');
        $bDay = (int) preg_replace('/[^0-9]/', '', $billingDayRaw);
        $billingCycleDay = ($bDay >= 1 && $bDay <= 28) ? $bDay : 1;

        // 7. Coordinates (Lat Long)
        $latLong = trim($data['lat long'] ?? $data['lat_long'] ?? $data['koordinat'] ?? '');
        $latitude = null;
        $longitude = null;
        if (! empty($latLong) && ! str_contains(strtolower($latLong), 'null') && str_contains($latLong, ',')) {
            $parts = explode(',', $latLong);
            if (isset($parts[0]) && is_numeric(trim($parts[0]))) {
                $latitude = (float) trim($parts[0]);
            }
            if (isset($parts[1]) && is_numeric(trim($parts[1]))) {
                $longitude = (float) trim($parts[1]);
            }
        }

        // 8. NIK / KTP
        $nik = trim($data['nik'] ?? $data['ktp'] ?? $data['no_ktp'] ?? '');
        $nik = trim($nik, " \t\n\r\0\x0B\"'");

        // 9. Customer Status from context
        $status = 'active';
        $ctxLower = strtolower($titleContext . ' ' . ($data['status'] ?? ''));
        if (str_contains($ctxLower, 'isolir') || str_contains($ctxLower, 'isolated')) {
            $status = 'isolated';
        } elseif (str_contains($ctxLower, 'berhenti') || str_contains($ctxLower, 'suspend') || str_contains($ctxLower, 'putus')) {
            $status = 'suspended';
        }

        // 10. Generate Email if not present
        $email = trim($data['email'] ?? '');
        if (empty($email)) {
            if (! empty($pppoe)) {
                $cleanPppoe = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $pppoe);
                $email = "{$cleanPppoe}@{$tenant->slug}.local";
            } else {
                $email = Str::slug($name) . '_' . Str::random(4) . "@{$tenant->slug}.local";
            }
        }

        // 11. Customer Matching (upsert)
        $customer = Customer::where('tenant_id', $tid)
            ->where(function ($q) use ($email, $phone, $pppoe, $name) {
                if (! empty($pppoe)) {
                    $q->whereHas('mikrotikAccount', fn ($m) => $m->where('pppoe_secret', $pppoe));
                }
                if (! empty($phone)) {
                    $q->orWhere('phone', $phone);
                }
                $q->orWhere('email', $email);
                $q->orWhere('name', $name);
            })->first();

        $action = 'created';

        if ($customer) {
            $customer->update([
                'name' => $name,
                'phone' => $phone ?: $customer->phone,
                'address' => $address ?: $customer->address,
                'area' => $areaName ?: $customer->area,
                'package_id' => $package ? $package->id : $customer->package_id,
                'status' => $status,
                'billing_cycle_day' => $billingCycleDay,
                'latitude' => $latitude ?? $customer->latitude,
                'longitude' => $longitude ?? $customer->longitude,
                'ktp_id' => $nik ?: $customer->ktp_id,
            ]);
            $action = 'updated';
        } else {
            if ($limitChecker && ! $limitChecker->canAddCustomer()) {
                return ['status' => 'limit_reached'];
            }

            $customer = Customer::create([
                'tenant_id' => $tid,
                'name' => $name,
                'email' => $email,
                'phone' => $phone ?: null,
                'address' => $address,
                'area' => $areaName ?: null,
                'package_id' => $package ? $package->id : null,
                'status' => $status,
                'join_date' => date('Y-m-d'),
                'billing_cycle_day' => $billingCycleDay,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'ktp_id' => $nik ?: null,
            ]);
            $action = 'created';
        }

        // 12. MikrotikAccount Link
        if (! empty($pppoe)) {
            MikrotikAccount::updateOrCreate(
                ['tenant_id' => $tid, 'customer_id' => $customer->id],
                [
                    'pppoe_secret' => $pppoe,
                    'pppoe_password' => '123456',
                    'pppoe_service' => 'pppoe',
                    'status' => 'active',
                ]
            );
        }

        // 13. Create/Sync Invoice if tagihan data present
        $bulanTagihan = trim($data['bulan tagihan'] ?? $data['periode'] ?? '');
        $totalBayar = (int) preg_replace('/[^0-9]/', '', $data['total bayar'] ?? $data['biaya'] ?? '0');
        $diskon = (int) preg_replace('/[^0-9]/', '', $data['diskon'] ?? '0');
        $tglBayar = trim($data['tanggal bayar'] ?? '');

        if ($biaya > 0 || $totalBayar > 0) {
            $yearMonth = date('Ym');
            $yearMonthDash = date('Y-m');

            // Parse month from Bulan Tagihan (e.g. "November 2026")
            if (! empty($bulanTagihan)) {
                $parsedTime = strtotime($bulanTagihan);
                if ($parsedTime) {
                    $yearMonth = date('Ym', $parsedTime);
                    $yearMonthDash = date('Y-m', $parsedTime);
                }
            }

            $invNumber = "INV-{$tid}-{$yearMonth}-" . str_pad($customer->id, 4, '0', STR_PAD_LEFT);
            $invStatus = 'unpaid';

            if (str_contains($ctxLower, 'lunas') || str_contains($ctxLower, 'sudah bayar') || ! empty($tglBayar)) {
                $invStatus = 'paid';
            }

            $dueDate = "{$yearMonthDash}-" . sprintf('%02d', $billingCycleDay);

            Invoice::firstOrCreate(
                [
                    'tenant_id' => $tid,
                    'customer_id' => $customer->id,
                    'invoice_number' => $invNumber,
                ],
                [
                    'amount' => $biaya > 0 ? $biaya : ($package->price ?? 150000),
                    'discount' => $diskon,
                    'tax' => 0,
                    'status' => $invStatus,
                    'due_date' => $dueDate,
                    'notes' => 'Tagihan impor Beatcom ' . ($bulanTagihan ?: date('F Y')),
                ]
            );
        }

        return ['status' => $action, 'customer' => $customer];
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB
        ]);

        $tid = tenantId();
        $tenant = Tenant::findOrFail($tid);
        $limitChecker = new PlanLimitChecker($tenant);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $originalName = $file->getClientOriginalName();

        $parsed = $this->parseFileContent($path, $originalName);
        $records = $parsed['records'];
        $title = $parsed['title'] ?: $originalName;

        if (empty($records)) {
            return back()->with('error', 'Gagal membaca data dari file. Pastikan file berisi tabel pelanggan yang valid.');
        }

        $defaultPackage = Package::where('tenant_id', $tid)->first() ?? Package::create([
            'tenant_id' => $tid,
            'name' => 'Paket Standar',
            'price' => 150000,
            'speed' => '20 Mbps',
            'is_taxable' => false,
        ]);

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $limitReached = false;

        foreach ($records as $row) {
            $res = $this->processCustomerRecord($row, $tenant, $title, $limitChecker, $defaultPackage);

            if ($res['status'] === 'created') {
                $imported++;
            } elseif ($res['status'] === 'updated') {
                $updated++;
            } elseif ($res['status'] === 'limit_reached') {
                $limitReached = true;
                $skipped++;
            } else {
                $skipped++;
            }
        }

        $msg = "Impor data selesai! Berhasil menambahkan {$imported} pelanggan baru dan memperbarui {$updated} pelanggan.";
        if ($limitReached) {
            $msg .= " Catatan: Batas kuota paket langganan aktif Anda tercapai ({$skipped} terlewati). Silakan upgrade paket untuk kapasitas lebih besar.";
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
        $limitChecker = new PlanLimitChecker($tenant);

        $files = $request->file('files');
        if (! is_array($files)) {
            $files = [$files];
        }

        $totalImported = 0;
        $totalUpdated = 0;
        $processedFiles = [];
        $limitReached = false;

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

            $parsed = $this->parseFileContent($path, $originalName);
            $records = $parsed['records'];
            $contextTitle = $parsed['title'] ? ($parsed['title'] . ' ' . $originalName) : $originalName;

            foreach ($records as $row) {
                $res = $this->processCustomerRecord($row, $tenant, $contextTitle, $limitChecker, $defaultPackage);

                if ($res['status'] === 'created') {
                    $totalImported++;
                } elseif ($res['status'] === 'updated') {
                    $totalUpdated++;
                } elseif ($res['status'] === 'limit_reached') {
                    $limitReached = true;
                }
            }

            $processedFiles[] = $originalName;
        }

        $filesStr = implode(', ', $processedFiles);
        $msg = "Sinkronisasi Beatcom selesai! Memproses {$filesStr}: {$totalImported} pelanggan baru berhasil diimpor, {$totalUpdated} data pelanggan disinkronkan.";
        if ($limitReached) {
            $msg .= ' Beberapa data terlewati karena batas kuota pelanggan tercapai.';
            return back()->with('warning', $msg);
        }

        return back()->with('success', $msg);
    }

    public function exportData(Request $request)
    {
        $tid = tenantId();
        $tenant = Tenant::find($tid);
        $type = $request->query('type', 'pelanggan_semua');
        $format = $request->query('format', 'csv');

        // Beatcom Format Export (HTML XLS or CSV)
        if (str_contains($type, 'beatcom') || $format === 'beatcom_xls') {
            return $this->exportInBeatcomFormat($tenant, $type, $format);
        }

        $filename = "export_{$type}_" . date('Y-m-d') . '.csv';

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

    /**
     * Export data matching the exact 23-column Beatcom format
     */
    protected function exportInBeatcomFormat(Tenant $tenant, string $type, string $format)
    {
        $tid = $tenant->id;
        $customers = Customer::with(['package', 'mikrotikAccount'])->where('tenant_id', $tid)->get();

        $monthYear = date('F Y');
        $dateStr = date('d M Y');
        $filename = "export_beatcom_{$tenant->slug}_" . date('Y-m-d');

        // Excel HTML table format (Beatcom .xls)
        if ($format === 'beatcom_xls' || ! str_contains($format, 'csv')) {
            $filename .= '.xls';
            $headers = [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($customers, $tenant, $monthYear, $dateStr) {
                echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
                echo '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><style>br {mso-data-placement: same-cell;}</style></head><body>';
                echo '<table><thead>';
                echo "<tr><th colspan=\"23\">Data Pelanggan - {$tenant->name} - {$monthYear} - Per {$dateStr}</th></tr>";
                echo '<tr>';
                echo '<th>No</th><th>ID</th><th>Nama</th><th>Telepon</th><th>PPOE</th><th>Paket</th><th>Area</th><th>Alamat</th><th>Biaya</th><th>Tanggal Tagihan</th>';
                echo '<th>Rincian Tambahan 1</th><th>Biaya Tambahan 1</th><th>Rincian Tambahan 2</th><th>Biaya Tambahan 2</th><th>Diskon</th><th>PPN</th><th>Total Bayar</th><th>Bulan Tagihan</th>';
                echo '<th>Lat Long</th><th>NIK</th><th>Tanggal Bayar</th><th>Admin By</th><th>Via</th>';
                echo '</tr></thead><tbody>';

                $no = 1;
                foreach ($customers as $c) {
                    $pppoe = htmlspecialchars($c->mikrotikAccount?->pppoe_secret ?? '', ENT_QUOTES, 'UTF-8');
                    $pkgName = htmlspecialchars($c->package?->name ?? 'Paket Standar', ENT_QUOTES, 'UTF-8');
                    $area = htmlspecialchars($c->area ?? '', ENT_QUOTES, 'UTF-8');
                    $address = htmlspecialchars($c->address, ENT_QUOTES, 'UTF-8');
                    $price = $c->package?->price ?? 0;
                    $bDay = sprintf('%02d', $c->billing_cycle_day ?? 1);
                    $latLong = ($c->latitude && $c->longitude) ? "{$c->latitude}, {$c->longitude}" : 'null, null';
                    $nik = htmlspecialchars($c->ktp_id ?? '', ENT_QUOTES, 'UTF-8');

                    echo "<tr><td>{$no}</td><td>{$c->id}</td><td>{$c->name}</td><td>{$c->phone}</td><td>{$pppoe}</td><td>{$pkgName}</td><td>{$area}</td><td>{$address}</td><td>{$price}</td><td>{$bDay}</td>";
                    echo "<td></td><td></td><td></td><td></td><td>0</td><td>0%</td><td>{$price}</td><td>{$monthYear}</td>";
                    echo "<td>{$latLong}</td><td>{$nik}</td><td></td><td></td><td></td></tr>";
                    $no++;
                }

                echo '</tbody></table></body></html>';
            };

            return response()->stream($callback, 200, $headers);
        }

        // CSV Beatcom Format
        $filename .= '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($customers, $monthYear) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'No', 'ID', 'Nama', 'Telepon', 'PPOE', 'Paket', 'Area', 'Alamat', 'Biaya', 'Tanggal Tagihan',
                'Rincian Tambahan 1', 'Biaya Tambahan 1', 'Rincian Tambahan 2', 'Biaya Tambahan 2', 'Diskon', 'PPN',
                'Total Bayar', 'Bulan Tagihan', 'Lat Long', 'NIK', 'Tanggal Bayar', 'Admin By', 'Via',
            ]);

            $no = 1;
            foreach ($customers as $c) {
                $pppoe = $c->mikrotikAccount?->pppoe_secret ?? '';
                $pkgName = $c->package?->name ?? 'Paket Standar';
                $price = $c->package?->price ?? 0;
                $latLong = ($c->latitude && $c->longitude) ? "{$c->latitude}, {$c->longitude}" : 'null, null';

                fputcsv($file, [
                    $no++,
                    $c->id,
                    $c->name,
                    $c->phone ?? '',
                    $pppoe,
                    $pkgName,
                    $c->area ?? '',
                    $c->address,
                    $price,
                    sprintf('%02d', $c->billing_cycle_day ?? 1),
                    '',
                    '',
                    '',
                    '',
                    '0',
                    '0%',
                    $price,
                    $monthYear,
                    $latLong,
                    $c->ktp_id ?? '',
                    '',
                    '',
                    '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportBeatcomTemplate()
    {
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_beatcom_format.xls"',
        ];

        $callback = function () {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><style>br {mso-data-placement: same-cell;}</style></head><body>';
            echo '<table><thead>';
            echo '<tr><th colspan="23">Template Impor Beatcom - Format Standar</th></tr>';
            echo '<tr>';
            echo '<th>No</th><th>ID</th><th>Nama</th><th>Telepon</th><th>PPOE</th><th>Paket</th><th>Area</th><th>Alamat</th><th>Biaya</th><th>Tanggal Tagihan</th>';
            echo '<th>Rincian Tambahan 1</th><th>Biaya Tambahan 1</th><th>Rincian Tambahan 2</th><th>Biaya Tambahan 2</th><th>Diskon</th><th>PPN</th><th>Total Bayar</th><th>Bulan Tagihan</th>';
            echo '<th>Lat Long</th><th>NIK</th><th>Tanggal Bayar</th><th>Admin By</th><th>Via</th>';
            echo '</tr></thead><tbody>';
            echo '<tr><td>1</td><td>101</td><td>Contoh Pelanggan</td><td>6281234567890</td><td>contoh@isp</td><td>Paket 20 Mbps</td><td>Area Barat</td><td>Jl. Merdeka No. 1</td><td>150000</td><td>01</td>';
            echo '<td></td><td></td><td></td><td></td><td>0</td><td>0%</td><td>150000</td><td>November 2026</td>';
            echo '<td>-6.123456, 106.123456</td><td>3603123456780001</td><td></td><td></td><td></td></tr>';
            echo '</tbody></table></body></html>';
        };

        return response()->stream($callback, 200, $headers);
    }
}
