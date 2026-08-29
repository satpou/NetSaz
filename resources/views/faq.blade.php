@extends('layouts.guest')

@section('title', 'FAQ - NetSaz')

@push('scripts')
    @vite(['resources/js/faq.js'])
@endpush

@section('content')
<div class="faq-hero">
  <div class="wrap" style="text-align:center">
    <div class="eyebrow" style="margin:0 auto 20px"><span class="dot"></span>HELP CENTER</div>
    <h1>Pertanyaan yang Sering Diajukan</h1>
    <p>Temukan jawaban untuk pertanyaan Anda. Tidak menemukan jawaban? <a href="{{ route('contact') }}">Hubungi kami</a>.</p>
  </div>
</div>

<div class="faq-section">
  <div class="wrap faq-list">
    <div class="faq-item">
      <button class="faq-item-header" onclick="this.parentElement.classList.toggle('open')">
        <span class="faq-item-title">Apa itu NetSaz?</span>
        <svg class="faq-item-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="faq-item-body">
        <p>NetSaz adalah sistem billing terpadu untuk RT/RW Net dan ISP kecil di Indonesia. Menyediakan manajemen pelanggan, paket layanan, tagihan otomatis, pembayaran online (Midtrans), dan portal pelanggan — semua dalam satu platform.</p>
      </div>
    </div>

    <div class="faq-item">
      <button class="faq-item-header" onclick="this.parentElement.classList.toggle('open')">
        <span class="faq-item-title">Apakah NetSaz bisa di-custom untuk kebutuhan spesifik ISP saya?</span>
        <svg class="faq-item-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="faq-item-body">
        <p>NetSaz dirancang fleksibel untuk berbagai kebutuhan. Anda bisa mengatur paket layanan, harga, tarif prorata, dan branding tenant sesuai kebutuhan. Untuk kustomisasi yang lebih kompleks, tersedia layanan konsultasi dan pengembangan fitur khusus.</p>
      </div>
    </div>

    <div class="faq-item">
      <button class="faq-item-header" onclick="this.parentElement.classList.toggle('open')">
        <span class="faq-item-title">Apakah cocok untuk RT/RW Net?</span>
        <svg class="faq-item-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="faq-item-body">
        <p>Ya. NetSaz dirancang khusus untuk RT/RW Net dan ISP kecil. Anda bisa mengelola puluhan hingga ratusan pelanggan, membuat tagihan otomatis per siklus, dan membiarkan pelanggan membayar sendiri lewat portal — tanpa perlu tim teknis besar.</p>
      </div>
    </div>

    <div class="faq-item">
      <button class="faq-item-header" onclick="this.parentElement.classList.toggle('open')">
        <span class="faq-item-title">Apakah data saya aman?</span>
        <svg class="faq-item-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="faq-item-body">
        <p>Kami menggunakan enkripsi end-to-end untuk data sensitif, backup otomatis harian ke object storage, dan enkripsi SSL untuk semua komunikasi. Data antar-tenant diisolasi secara penuh.</p>
      </div>
    </div>

    <div class="faq-item">
      <button class="faq-item-header" onclick="this.parentElement.classList.toggle('open')">
        <span class="faq-item-title">Metode pembayaran apa saja yang didukung?</span>
        <svg class="faq-item-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="faq-item-body">
        <p>Transfer bank manual, Virtual Account, QRIS, dan e-wallet dapat digunakan melalui integrasi Midtrans dengan callback otomatis. Rekonsiliasi pembayaran tercatat otomatis di dashboard.</p>
      </div>
    </div>

    <div class="faq-item">
      <button class="faq-item-header" onclick="this.parentElement.classList.toggle('open')">
        <span class="faq-item-title">Bagaimana support-nya?</span>
        <svg class="faq-item-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="faq-item-body">
        <p>Setiap tenant mendapat support via email. Kami siap membantu proses setup awal, migrasi data, hingga penanganan kendala penggunaannya.</p>
      </div>
    </div>

    <div class="faq-item">
      <button class="faq-item-header" onclick="this.parentElement.classList.toggle('open')">
        <span class="faq-item-title">Bisa custom invoice dan laporan?</span>
        <svg class="faq-item-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="faq-item-body">
        <p>Ya. Anda bisa atur logo, nama bisnis, struktur paket, dan tarif prorata sesuai kebutuhan. Invoice bisa diunduh sebagai PDF, dan laporan arus kas bisa di-export.</p>
      </div>
    </div>

    <div class="faq-item">
      <button class="faq-item-header" onclick="this.parentElement.classList.toggle('open')">
        <span class="faq-item-title">Berapa lama waktu setup awal?</span>
        <svg class="faq-item-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="faq-item-body">
        <p>Rata-rata di bawah 15 menit. Cukup daftar, atur paket layanan, dan mulai tambahkan pelanggan pertama. Untuk setup awal dengan banyak data, tim kami bisa bantu migrate dari sistem lama.</p>
      </div>
    </div>

    <div class="faq-cta">
      <p>Tidak menemukan jawaban yang Anda cari?</p>
      <a href="{{ route('contact') }}">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        Hubungi Sales kami
      </a>
    </div>
  </div>
</div>
@endsection