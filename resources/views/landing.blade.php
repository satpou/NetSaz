@extends('layouts.guest')

@section('title', 'NetSaz — Billing Management untuk ISP')

@push('scripts')
    @vite(['resources/js/landing.js'])
@endpush

@section('content')
<section class="hero">
  <div class="wrap hero-grid">
    <div>
      <div class="eyebrow"><span class="dot"></span>TENTANG NETSAZ</div>
      <h1>Platform Billing Modern untuk RT/RW Net &amp; ISP Kecil</h1>
      <p class="lead">NetSaz dirancang khusus untuk kebutuhan RT/RW Net dan ISP kecil di Indonesia — manajemen pelanggan, tagihan otomatis, dan pembayaran online dalam satu platform yang mudah dioperasikan.</p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="https://wa.me/6281287282084" target="_blank" rel="noopener">Daftar Sekarang</a>
        <a class="btn btn-outline" href="{{ route('features') }}">Lihat Fitur</a>
      </div>
      <div class="hero-note">
        <span>Respon sales kurang dari 1 hari kerja</span>
        <span>Setup di bawah 15 menit</span>
        <span>Support Bahasa Indonesia</span>
      </div>
    </div>

    <div class="signature reveal">
      <div class="hero-bento">
        <div class="bento-card bento-featured">
          <div class="bento-head">
            <span class="bento-chip">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
            </span>
            <span class="bento-badge">Otomatis</span>
          </div>
          <div class="bento-value">100%</div>
          <div class="bento-desc">Tagihan, pengingat, dan pembayaran berjalan otomatis — tanpa input manual.</div>
          <div class="bento-meter"><i></i></div>
        </div>

        <div class="bento-card">
          <span class="bento-chip">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
          </span>
          <div class="bento-value">15 mnt</div>
          <div class="bento-desc">Setup awal, langsung pakai</div>
        </div>

        <div class="bento-card">
          <span class="bento-chip">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
          </span>
          <div class="bento-value">6 fitur</div>
          <div class="bento-desc">Dari billing sampai laporan pajak</div>
        </div>

        <div class="bento-card bento-wide">
          <div class="bento-inline">
            <span class="bento-chip">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </span>
            <div>
              <div class="bento-value">Rp 0</div>
              <div class="bento-desc">Biaya setup &amp; ganti paket kapan saja</div>
            </div>
          </div>
          <div class="bento-note">Hanya bayar langganan bulanan</div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="stats">
  <div class="wrap stats-grid">
    <div class="reveal"><div class="stat-num" data-count="15" data-suffix=" mnt">0</div><div class="stat-label">Waktu setup awal rata-rata</div></div>
    <div class="reveal reveal-delay-1"><div class="stat-num" data-count="24" data-suffix="/7">0</div><div class="stat-label">Dukungan teknis lokal</div></div>
    <div class="reveal reveal-delay-2"><div class="stat-num" data-count="6">0</div><div class="stat-label">Fitur utama terintegrasi</div></div>
    <div class="reveal reveal-delay-3"><div class="stat-num" data-count="100" data-suffix="%">0</div><div class="stat-label">Fokus ISP Indonesia</div></div>
  </div>
</section>

<section class="features" id="fitur">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow"><span class="dot"></span>FITUR INTI</div>
      <h2>Semua yang dibutuhkan operator ISP kecil-menengah</h2>
      <p>Dari penagihan sampai pembayaran, dirancang untuk cara kerja RT/RW net dan ISP lokal — bukan billing generik yang dipaksakan.</p>
    </div>
    <div class="feature-grid">
      <div class="feature-card reveal"><div class="feature-icon">01</div><h3>Tagihan otomatis per siklus</h3><p>Invoice dibuat otomatis sesuai tanggal tagih tiap pelanggan atau area, lengkap dengan jatuh tempo dan status lunas / nunggak.</p></div>
      <div class="feature-card reveal reveal-delay-1"><div class="feature-icon">02</div><h3>Portal pelanggan</h3><p>Pelanggan login ke portal untuk melihat tagihan dan melakukan pembayaran online sendiri, mengurangi beban kerja tim Anda.</p></div>
      <div class="feature-card reveal reveal-delay-2"><div class="feature-icon">03</div><h3>Multi metode pembayaran</h3><p>Virtual account, QRIS, e-wallet, dan transfer manual — semua rekonsiliasi otomatis masuk ke dashboard tanpa input ulang.</p></div>
      <div class="feature-card reveal"><div class="feature-icon">04</div><h3>Dashboard arus kas real-time</h3><p>Lihat total tertagih, tertunggak, dan proyeksi bulan berjalan dalam satu layar, per paket layanan atau per area.</p></div>
      <div class="feature-card reveal reveal-delay-1"><div class="feature-icon">05</div><h3>Manajemen paket & harga</h3><p>Atur paket bandwidth, harga per wilayah, dan promo instalasi baru tanpa perlu bantuan tim teknis.</p></div>
      <div class="feature-card reveal reveal-delay-2"><div class="feature-icon">06</div><h3>Laporan siap pajak</h3><p>Rekap invoice dan penerimaan otomatis tersusun rapi, tinggal unduh saat lapor pajak atau audit internal.</p></div>
    </div>
  </div>
</section>

<section class="how" id="cara-kerja">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow"><span class="dot"></span>CARA KERJA</div>
      <h2>Aktif dalam tiga langkah</h2>
      <p>Tidak perlu migrasi rumit atau ganti perangkat jaringan yang sudah ada.</p>
    </div>
    <div class="how-grid">
      <div class="how-step reveal"><div class="how-num">01</div><h3>Impor/masukkan pelanggan</h3><p>Tambahkan pelanggan dan tentukan paketnya. Data area, nomor, dan detail lain mudah dikelola lewat satu tempat.</p></div>
      <div class="how-step reveal reveal-delay-1"><div class="how-num">02</div><h3>Atur paket & jatuh tempo</h3><p>Tentukan paket, harga, dan tanggal tagih per pelanggan atau per area layanan sekaligus.</p></div>
      <div class="how-step reveal reveal-delay-2"><div class="how-num">03</div><h3>Biarkan berjalan otomatis</h3><p>Invoice dan pembayaran berjalan otomatis. Pelanggan bisa bayar sendiri, Anda tinggal memantau dashboard.</p></div>
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

<section class="pricing" id="harga">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow"><span class="dot"></span>HARGA</div>
      <h2>Harga mengikuti jumlah pelanggan Anda</h2>
      <p>Tanpa biaya setup. Naik atau turun paket kapan saja sesuai pertumbuhan jaringan Anda.</p>
    </div>
    
<div class="pricing-carousel-wrapper">
      <div class="pricing-carousel-track">
        @foreach (config('pricing_plans') as $plan)
        <div class="price-card carousel-card {{ $plan['highlight'] ? 'highlight' : '' }}">
          @if ($plan['badge'])
          <span class="tag">{{ $plan['badge'] }}</span>
          @endif
          <h3>{{ $plan['name'] }}</h3>
          <div class="desc">{{ $plan['description'] }}</div>
          <div class="amount">{{ $plan['price'] }}</div>
          <div class="per">/bulan, {{ $plan['kuota'] }}</div>
          <ul>
            @foreach (array_slice($plan['benefits'], 0, 3) as $benefit)
            <li>{{ $benefit }}</li>
            @endforeach
          </ul>
          <a class="btn {{ $plan['highlight'] ? 'btn-primary' : 'btn-outline' }}"
               href="{{ route('price') }}#harga">Lihat Paket</a>
        </div>
        @endforeach
        {{-- Duplicate ALL cards for seamless infinite loop --}}
        @foreach (config('pricing_plans') as $plan)
        <div class="price-card carousel-card {{ $plan['highlight'] ? 'highlight' : '' }}">
          @if ($plan['badge'])
          <span class="tag">{{ $plan['badge'] }}</span>
          @endif
          <h3>{{ $plan['name'] }}</h3>
          <div class="desc">{{ $plan['description'] }}</div>
          <div class="amount">{{ $plan['price'] }}</div>
          <div class="per">/bulan, {{ $plan['kuota'] }}</div>
          <ul>
            @foreach (array_slice($plan['benefits'], 0, 3) as $benefit)
            <li>{{ $benefit }}</li>
            @endforeach
          </ul>
          <a class="btn {{ $plan['highlight'] ? 'btn-primary' : 'btn-outline' }}"
               href="{{ route('price') }}#harga">Lihat Paket</a>
        </div>
        @endforeach
      </div>
    </div>

    <div style="text-align:center;margin-top:24px;" class="reveal">
      <a href="{{ route('price') }}" style="font-size:13px;color:var(--primary);text-decoration:underline;font-weight:500;">Lihat 6 paket lengkap →</a>
    </div>
  </div>
</section>

<section class="testimonial" id="testimoni">
  <div class="wrap quote-panel reveal">
    <p class="quote">"Dulu kami habiskan berjam-jam tiap awal bulan hanya untuk mengingatkan pelanggan yang belum bayar. Sekarang tagihan otomatis dan pelanggan bisa bayar sendiri lewat portal — tim fokus ke layanan."</p>
    <div class="quote-author"><span class="avatar">RH</span>Lukman Hadi — Pemilik, PRIMANET Tangerang</div>
  </div>
</section>

<section class="final-cta" id="kontak">
  <div class="wrap">
    <div class="cta-panel reveal">
      <h2>Berhenti mengejar tagihan secara manual.</h2>
      <p>Ceritakan jumlah pelanggan dan area layanan Anda, tim sales kami akan bantu pilih paket yang sesuai.</p>
      <div class="contact-card">
        <div class="contact-avatar">AS</div>
        <div class="contact-info">
          <div class="contact-name">Andika Saputra</div>
          <div class="contact-role">Sales Consultant, NetSaz</div>
          <div class="contact-detail mono">+62 812-8728-2084 &nbsp;•&nbsp; srahmaddhani@gmail.com</div>
        </div>
      </div>
      <div class="cta-row">
        <a class="btn btn-primary" href="https://wa.me/6281287282084" target="_blank" rel="noopener">Chat via WhatsApp</a>
        <a class="btn btn-outline" href="mailto:srahmaddhani@gmail.com">Kirim Email</a>
      </div>
    </div>
  </div>
</section>
@endsection