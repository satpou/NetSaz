@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
@php $ts = $user && $user->tenant ? $user->tenant->slug : null; @endphp
<div class="breadcrumb reveal">Home &gt; <b>Profil Saya</b></div>

<div class="profile-wrap">
    <div class="profile-header reveal">
        <div class="profile-avatar">{{ substr($user->name ?? 'U', 0, 1) }}</div>
        <div class="profile-info">
            <h2 class="profile-name">{{ $user->name }}</h2>
            <p class="profile-email">{{ $user->email }}</p>
            <div class="profile-meta">
                <span class="profile-badge">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                @if($user->tenant)
                    <span class="profile-badge primary">{{ $user->tenant->name }}</span>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any() && !$errors->has('current_password') && !$errors->has('password'))
        <div class="alert-error">
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="profile-grid">
        <div class="panel reveal" id="info">
            <div class="panel-head-stack">
                <h3>Informasi Akun</h3>
                <span class="panel-sub">Perbarui nama dan email Anda.</span>
            </div>
            <form action="{{ $ts ? route('tenant.profile.update', ['tenant_slug' => $ts]) : route('profile.update') }}" method="POST" class="profile-form">
                @csrf
                @method('PUT')

                <div class="form-field">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required class="form-input">
                </div>

                <div class="form-field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input">
                </div>

                <div class="form-field">
                    <label>Role</label>
                    <input type="text" value="{{ ucfirst(str_replace('_', ' ', $user->role)) }}" disabled class="form-input readonly">
                </div>

                @if($user->tenant)
                <div class="form-field">
                    <label>Perusahaan / ISP</label>
                    <input type="text" value="{{ $user->tenant->name }}" disabled class="form-input readonly">
                </div>
                @endif

                <button type="submit" class="btn-save">Simpan Perubahan</button>
            </form>
        </div>

        <div class="panel reveal d1" id="password">
            <div class="panel-head-stack">
                <h3>Ubah Password</h3>
                <span class="panel-sub">Gunakan password yang kuat minimal 8 karakter.</span>
            </div>

            @if($errors->has('current_password') || $errors->has('password'))
                <div class="alert-error">
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ $ts ? route('tenant.profile.password', ['tenant_slug' => $ts]) : route('profile.password') }}" method="POST" class="profile-form">
                @csrf
                @method('PUT')

                <div class="form-field">
                    <label for="current_password">Password Saat Ini</label>
                    <input type="password" id="current_password" name="current_password" required class="form-input" placeholder="Password saat ini">
                </div>

                <div class="form-field">
                    <label for="password">Password Baru</label>
                    <input type="password" id="password" name="password" required class="form-input" placeholder="Min. 8 karakter">
                </div>

                <div class="form-field">
                    <label for="password_confirmation">Konfirmasi Password Baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required class="form-input" placeholder="Ulangi password baru">
                </div>

                <button type="submit" class="btn-save">Ubah Password</button>
            </form>
        </div>
    </div>
</div>

<style>
  .profile-wrap { max-width:920px; margin:0 auto; }
  .profile-header { display:flex; align-items:center; gap:20px; padding:24px; background:var(--panel); border:1px solid var(--line); border-radius:16px; margin-bottom:24px; box-shadow:var(--shadow); }
  .profile-avatar { width:72px; height:72px; border-radius:50%; background:linear-gradient(135deg, var(--primary), var(--primary-dark)); color:#fff; display:flex; align-items:center; justify-content:center; font-family:'Space Grotesk'; font-weight:700; font-size:28px; flex-shrink:0; }
  .profile-name { font-family:'Space Grotesk'; font-size:22px; font-weight:700; color:var(--ink); margin-bottom:4px; letter-spacing:-0.02em; }
  .profile-email { font-size:13.5px; color:var(--ink-soft); margin-bottom:12px; }
  .profile-meta { display:flex; gap:8px; flex-wrap:wrap; }
  .profile-badge { font-family:'JetBrains Mono'; font-size:11px; font-weight:600; padding:4px 10px; border-radius:100px; background:var(--bg-alt); color:var(--ink-soft); }
  .profile-badge.primary { background:var(--primary-tint); color:var(--primary-dark); }
  .profile-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
  @media (max-width:760px) { .profile-grid { grid-template-columns:1fr; } }
  .form-input.readonly { background:var(--bg-alt); color:var(--ink-faint); cursor:not-allowed; }
</style>
@endsection