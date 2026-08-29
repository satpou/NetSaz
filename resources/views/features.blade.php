@extends('layouts.guest')

@section('title', 'Fitur - NetSaz')

@push('scripts')
    @vite(['resources/js/landing.js'])
@endpush

@section('content')
<section class="hero">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow"><span class="dot"></span>FITUR UNGGULAN</div>
      <h1>Solusi lengkap untuk mengelola ISP Anda</h1>
      <p class="text-lg text-gray-500 max-w-3xl mx-auto">Platform billing terpadu dengan manajemen pelanggan, tagihan otomatis, pembayaran online Midtrans, dan portal pelanggan — semua dalam satu dashboard.</p>
    </div>
  </div>
</section>

<section class="features" id="fitur">
  <div class="wrap">
    <div class="feature-grid">
      <div class="feature-card reveal">
        <div class="feature-icon">01</div>
        <h3>Manajemen Pelanggan</h3>
        <p>CRUD lengkap, pencarian canggih, filter status aktif/isolir/suspend. Semua data pelanggan tersimpan rapi.</p>
      </div>
      <div class="feature-card reveal reveal-delay-1">
        <div class="feature-icon">02</div>
        <h3>Billing Otomatis</h3>
        <p>Invoice otomatis per siklus bulanan dengan prorata untuk pelanggan baru, lengkap dengan status lunas dan nunggak.</p>
      </div>
      <div class="feature-card reveal reveal-delay-2">
        <div class="feature-icon">03</div>
        <h3>Portal Pelanggan</h3>
        <p>Pelanggan login ke portal untuk melihat tagihan dan membayar sendiri — mengurangi beban kerja tim Anda.</p>
      </div>
      <div class="feature-card reveal">
        <div class="feature-icon">04</div>
        <h3>Dashboard Arus Kas</h3>
        <p>Statistik real-time tentang total tertagih, tertunggak, dan proyeksi bulan berjalan dalam satu layar.</p>
      </div>
      <div class="feature-card reveal reveal-delay-1">
        <div class="feature-icon">05</div>
        <h3>Pembayaran Online</h3>
        <p>Terima pembayaran via transfer bank, Virtual Account, dan QRIS melalui Midtrans dengan callback otomatis.</p>
      </div>
      <div class="feature-card reveal reveal-delay-2">
        <div class="feature-icon">06</div>
        <h3>Laporan Siap Pajak</h3>
        <p>Rekap invoice dan penerimaan otomatis tersusun rapi, tinggal unduh saat lapor pajak atau audit internal.</p>
      </div>
    </div>
  </div>
</section>

<section class="preview">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow"><span class="dot"></span>TAMPILAN PRODUK</div>
      <h2>Satu dashboard, seluruh status tagihan</h2>
      <p>Setiap pelanggan, setiap paket, setiap status pembayaran — terlihat jelas tanpa perlu buka spreadsheet.</p>
    </div>
    <div class="preview-panel reveal">
      <div class="preview-bar"><i></i><i></i><i></i></div>
      <div class="preview-body">
        <div class="preview-side">
          <div class="item active">Ringkasan</div>
          <div class="item">Pelanggan</div>
          <div class="item">Invoice</div>
          <div class="item">Paket layanan</div>
          <div class="item">Pembayaran</div>
          <div class="item">Laporan</div>
        </div>
        <div class="preview-main">
          <h4>Invoice bulan berjalan</h4>
          <table class="invoice-table">
            <tr><th>Pelanggan</th><th>Paket</th><th>Nominal</th><th>Status</th></tr>
            <tr><td>Budi Santoso</td><td class="mono">20 Mbps</td><td class="mono">Rp 165.000</td><td><span class="badge-pill paid">Lunas</span></td></tr>
            <tr><td>Warkop Sinar Jaya</td><td class="mono">50 Mbps</td><td class="mono">Rp 350.000</td><td><span class="badge-pill due">Jatuh tempo</span></td></tr>
            <tr><td>Siti Amelia</td><td class="mono">10 Mbps</td><td class="mono">Rp 120.000</td><td><span class="badge-pill paid">Lunas</span></td></tr>
            <tr><td>Kos Melati Indah</td><td class="mono">30 Mbps</td><td class="mono">Rp 250.000</td><td><span class="badge-pill late">Nunggak</span></td></tr>
            <tr><td>Toko Makmur</td><td class="mono">20 Mbps</td><td class="mono">Rp 165.000</td><td><span class="badge-pill paid">Lunas</span></td></tr>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="final-cta" id="kontak">
  <div class="wrap">
    <div class="cta-panel reveal">
      <h2>Siap memulai kelola ISP lebih efisien?</h2>
      <p>Daftar sekarang dan nikmati semua fitur Premium. Berhenti kapan saja.</p>
      <div class="cta-row">
        <a class="btn btn-primary" href="{{ route('register') }}">Daftar Sekarang</a>
        <a class="btn btn-outline" href="{{ route('contact') }}">Hubungi Sales</a>
      </div>
    </div>
  </div>
</section>
@endsection