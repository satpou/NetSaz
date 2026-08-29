<p align="center"><a href="https://netsaz.id" target="_blank"><img src="public/images/netsaz/NetSaz_logo_transparent.png" width="300" alt="NetSaz Logo"></a></p>

<h3 align="center">Platform Billing Modern untuk RT/RW Net &amp; ISP Kecil</h3>

---

## Tentang NetSaz

**NetSaz** adalah platform SaaS multi-tenant untuk billing RT/RW Net dan ISP kecil-menengah di Indonesia. Fokus pada manajemen pelanggan, paket layanan, tagihan otomatis, dan pembayaran online — sederhana dan mudah dioperasikan tanpa perlu tim teknis besar.

### Fitur Utama

**Billing & Pembayaran**
- Invoice otomatis dengan perhitungan prorata
- PDF invoice (DomPDF) dengan QR code
- Integrasi Midtrans (Snap) & Xendit (VA)
- QRIS payment untuk pelanggan
- Transfer manual dengan workflow verifikasi
- Portal self-service pelanggan (login PIN/magic link)

**Manajemen**
- Pelanggan, paket, area
- Audit log
- Laporan pelanggan, pembayaran, dan arus kas
- Export laporan

**Portal Pelanggan**
- Login via PIN atau magic link
- Lihat tagihan & riwayat pembayaran
- Bayar via payment gateway (Midtrans/Xendit)
- Bayar via QRIS
- Bayar via transfer manual
- Profil pelanggan

### Arsitektur Multi-Tenant

| Aspek | Implementasi |
|---|---|
| Pendekatan | Shared database, shared schema (`tenant_id`) |
| Tenant Resolution | Subdomain (`{slug}.netsaz.id`) |
| Roles | Super Admin, Admin, Staff |
| Scoping | Global scope per tenant |

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel 13, PHP 8.3 |
| Frontend | Livewire 4, Blade, Tailwind CSS 4 |
| Build Tool | Vite 8 |
| PDF | barryvdh/laravel-dompdf |
| QR Code | endroid/qr-code |
| API Auth | Laravel Sanctum |
| Database | MySQL (SQLite untuk development) |
| Testing | PHPUnit, Paratest |

## Prerequisites

- PHP >= 8.3
- Composer
- Node.js >= 18
- MySQL / SQLite

## Setup

```bash
# Install dependencies (PHP + Node)
composer setup

# Atau manual:
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install && npm run build
```

## Development

```bash
# Jalankan semua service (server, queue, logs, vite)
composer dev

# Atau manual
php artisan serve
php artisan queue:listen
npm run dev
```

## Struktur Aplikasi

```
app/
├── Http/
│   ├── Controllers/  # Web & API controllers
│   └── Middleware/   # Tenant resolution
├── Models/           # Eloquent models
├── Services/         # Billing, Midtrans, Xendit, Invoice number
└── helpers.php
```

## Artisan Commands

| Command | Fungsi |
|---|---|
| `invoices:generate-monthly` | Generate invoice bulanan untuk pelanggan aktif (support `--tenant`, `--date`, `--dry-run`) |
| `invoices:mark-overdue` | Tandai invoice unpaid/partial sebagai overdue |

## Roles

| Role | Deskripsi |
|---|---|
| `super_admin` | Milik NetSaz (null tenant_id) atau pemilik ISP (ada tenant_id) |
| `admin` | Admin di tenant/ISP tertentu |
| `staff` | Staf ISP, akses terbatas |

## License

Proprietary. Hak cipta dilindungi.
