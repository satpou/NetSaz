@extends('layouts.guest')

@section('title', 'Harga - NetSaz')

@push('scripts')
    @vite(['resources/js/landing.js'])
@endpush

@section('content')
<section class="hero">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow"><span class="dot"></span>HARGA TRANSPARAN</div>
      <h1>Pilih paket yang sesuai dengan kebutuhan ISP Anda</h1>
      <p style="font-size:17px;color:var(--ink-soft);max-width:600px;">Tanpa biaya setup. Naik atau turun paket kapan saja sesuai pertumbuhan jaringan Anda. Tidak ada biaya tersembunyi.</p>
    </div>
  </div>
</section>

<section class="pricing" id="harga">
  <div class="wrap">
    <div class="pricing-grid">
      @foreach (config('pricing_plans') as $plan)
      <div class="price-card reveal {{ $plan['highlight'] ? 'highlight' : '' }} {{ $plan['is_enterprise'] ? 'enterprise' : '' }}">
        @if ($plan['badge'])
        <span class="tag">{{ $plan['badge'] }}</span>
        @endif
        <h3>{{ $plan['name'] }}</h3>
        <div class="desc">{{ $plan['description'] }}</div>
        <div class="amount">{{ $plan['price'] }}</div>
        <div class="per">/bulan, {{ $plan['kuota'] }}</div>
        <ul>
          @foreach ($plan['benefits'] as $benefit)
          <li>{{ $benefit }}</li>
          @endforeach
        </ul>
        <a class="btn {{ $plan['highlight'] ? 'btn-primary' : 'btn-outline' }}"
           href="{{ $plan['cta'] }}"
           target="_blank"
           rel="noopener">{{ $plan['cta_label'] }}</a>
      </div>
      @endforeach
    </div>
  </div>
</section>

<section class="final-cta" id="kontak">
  <div class="wrap">
    <div class="cta-panel reveal">
      <h2>Berhenti mengejar tagihan secara manual.</h2>
      <p>Ceritakan jumlah pelanggan dan area layanan Anda, tim sales kami akan bantu pilih paket yang sesuai.</p>
      <div class="cta-row">
        <a class="btn btn-primary" href="{{ route('register') }}">Daftar Sekarang</a>
        <a class="btn btn-outline" href="{{ route('contact') }}">Hubungi Sales</a>
      </div>
    </div>
  </div>
</section>
@endsection