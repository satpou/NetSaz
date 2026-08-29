@extends('layouts.guest')

@section('title', 'Kontak - NetSaz')

@push('scripts')
    @vite(['resources/js/landing.js'])
@endpush

@section('content')
<section class="hero">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow"><span class="dot"></span>HUBUNGI KAMI</div>
      <h1>Kami siap membantu Anda</h1>
      <p style="font-size:17px;color:var(--ink-soft)">Tim sales dan support kami siap menjawab semua pertanyaan Anda terkait NetSaz.</p>
    </div>
  </div>
</section>

<section class="contact-section" style="padding:0 0 80px">
  <div class="wrap" style="max-width:960px">
    <div class="contact-card-main">
      <div class="contact-card-topbar" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding:20px 28px;background:var(--bg-alt);border-bottom:1px solid var(--line)">
        <div style="display:flex;align-items:center;gap:14px">
          <div style="width:44px;height:44px;border-radius:50%;background:var(--primary-tint);color:var(--primary-dark);display:flex;align-items:center;justify-content:center;font-family:'JetBrains Mono';font-weight:700;font-size:14px">AS</div>
          <div>
            <div style="font-size:15px;font-weight:600;color:var(--ink)">Andika Saputra</div>
            <div style="font-size:13px;color:var(--ink-soft);margin-top:2px">Sales Consultant, NetSaz</div>
          </div>
        </div>
        <div style="font-size:13px;color:var(--ink-faint);font-family:'JetBrains Mono'">+62 812-3456-7890 &nbsp;•&nbsp; srahmaddhani@gmail.com</div>
      </div>
      <div class="contact-card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:0">
        <div class="contact-left" style="padding:32px 28px;border-right:1px solid var(--line)">
          <h3 style="font-family:'Space Grotesk';font-size:18px;font-weight:700;color:var(--ink);margin-bottom:8px">Kirim Pesan</h3>
          <p style="font-size:14px;color:var(--ink-soft);line-height:1.7;margin-bottom:24px">Isi form di samping dan tim kami akan merespon dalam waktu kurang dari 1 hari kerja.</p>
          <div style="display:flex;flex-direction:column;gap:14px">
            <div style="display:flex;align-items:center;gap:12px;font-size:14px;color:var(--ink-soft)">
              <svg width="18" height="18" fill="none" stroke="var(--primary)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              <a href="mailto:srahmaddhani@gmail.com" style="color:var(--primary);text-decoration:none">srahmaddhani@gmail.com</a>
            </div>
            <div style="display:flex;align-items:center;gap:12px;font-size:14px;color:var(--ink-soft)">
              <svg width="18" height="18" fill="none" stroke="var(--primary)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14m-10 4V5a2 2 0 012-2h11.232a2 2 0 011.414.586l7.768 7.768a2 2 0 010 2.828l-7.768 7.768a2 2 0 01-1.414.586H2a2 2 0 01-2-2z"/></svg>
              <a href="https://wa.me/6281234567890" style="color:var(--primary);text-decoration:none">+62 812-3456-7890</a>
            </div>
          </div>
        </div>
        <div class="contact-right" style="padding:32px 28px">
          @if(session('success'))
            <div style="padding:12px 16px;border-radius:8px;background:var(--success-tint);color:var(--success-dark);font-size:14px;margin-bottom:20px;border:1px solid var(--success)">{{ session('success') }}</div>
          @endif
          @if($errors->any())
            <div style="padding:12px 16px;border-radius:8px;background:var(--danger-tint);color:var(--danger);font-size:14px;margin-bottom:20px;border:1px solid var(--danger)">
              <ul style="margin:0;padding-left:16px">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif
          <form method="POST" action="{{ route('contact.store') }}">
            @csrf
            <div style="margin-bottom:20px">
              <label for="name" style="display:block;font-size:13.5px;font-weight:500;color:var(--ink);margin-bottom:6px">Nama Lengkap</label>
              <input type="text" id="name" name="name" value="{{ old('name') }}" required style="width:100%;padding:10px 14px;border:1px solid var(--line);border-radius:8px;font-size:14px;font-family:'Inter';color:var(--ink);background:var(--bg);outline:none" placeholder="Nama Anda">
            </div>
            <div style="margin-bottom:20px">
              <label for="email" style="display:block;font-size:13.5px;font-weight:500;color:var(--ink);margin-bottom:6px">Email</label>
              <input type="email" id="email" name="email" value="{{ old('email') }}" required style="width:100%;padding:10px 14px;border:1px solid var(--line);border-radius:8px;font-size:14px;font-family:'Inter';color:var(--ink);background:var(--bg);outline:none" placeholder="email@anda.com">
            </div>
            <div style="margin-bottom:20px">
              <label for="message" style="display:block;font-size:13.5px;font-weight:500;color:var(--ink);margin-bottom:6px">Pesan</label>
              <textarea id="message" name="message" rows="4" required style="width:100%;padding:10px 14px;border:1px solid var(--line);border-radius:8px;font-size:14px;font-family:'Inter';color:var(--ink);background:var(--bg);resize:vertical;outline:none" placeholder="Tulis pesan Anda...">{{ old('message') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Kirim Pesan</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
  .contact-card-main { background:var(--panel); border:1px solid var(--line); border-radius:18px; box-shadow:var(--shadow); overflow:hidden; }
  input:focus, textarea:focus { border-color:var(--primary) !important; box-shadow:0 0 0 3px var(--primary-tint); }
  @media (max-width:860px) {
    .contact-card-body { grid-template-columns:1fr !important; }
    .contact-left { border-right:none !important; border-bottom:1px solid var(--line); }
  }
</style>
@endsection