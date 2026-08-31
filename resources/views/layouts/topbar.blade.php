<style>
  .topbar {
    background: rgba(10,14,26,.62);
    backdrop-filter: saturate(180%) blur(18px);
    -webkit-backdrop-filter: saturate(180%) blur(18px);
    border-bottom: 1px solid var(--glass-border);
    padding: 0 32px;
    height: 68px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 30;
  }

  .topbar-search-wrap {
    position: relative;
    width: 100%;
    max-width: 340px;
  }

  .topbar-search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--ink-faint);
    pointer-events: none;
  }

  .topbar-search {
    width: 100%;
    padding: 10px 16px 10px 42px;
    border-radius: 10px;
    border: 1px solid var(--line);
    background: var(--panel-soft);
    color: var(--ink);
    font-size: 14px;
    font-family: 'Inter', sans-serif;
    transition: border-color .15s, box-shadow .15s;
  }

  .topbar-search:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-tint);
  }

  .topbar-search::placeholder {
    color: var(--ink-faint);
  }

  .topbar-right {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .topbar-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    color: var(--ink-soft);
    background: transparent;
    border: none;
    cursor: pointer;
    transition: background .15s, color .15s;
    position: relative;
  }

  .topbar-btn:hover {
    background: var(--primary-tint);
    color: var(--primary);
  }

  .topbar-profile {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 6px 12px 6px 6px;
    border-radius: 12px;
    cursor: pointer;
    transition: background .15s;
    position: relative;
  }

  .topbar-profile:hover {
    background: var(--panel-soft);
  }

  .topbar-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--primary-tint-solid);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'JetBrains Mono', monospace;
    font-weight: 600;
    font-size: 13px;
    color: var(--primary);
    overflow: hidden;
  }

  .topbar-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
  }

  .topbar-profile-info {
    display: none;
  }

  @media (min-width: 768px) {
    .topbar-profile-info {
      display: block;
    }

    .topbar-profile-name {
      font-size: 13.5px;
      font-weight: 600;
      color: var(--ink);
      white-space: nowrap;
    }

    .topbar-profile-email {
      font-size: 11.5px;
      color: var(--ink-faint);
      white-space: nowrap;
      max-width: 160px;
      overflow: hidden;
      text-overflow: ellipsis;
    }
  }

  .topbar-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    background: var(--glass-bg-strong);
    border: 1px solid var(--glass-border);
    border-radius: 14px;
    box-shadow: var(--shadow-glass);
    min-width: 220px;
    z-index: 100;
    display: none;
  }

  .topbar-dropdown.show {
    display: block;
  }

  .topbar-dropdown-header {
    padding: 16px 18px 12px;
    border-bottom: 1px solid var(--line);
  }

  .topbar-dropdown-header p {
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
  }

  .topbar-dropdown-header span {
    font-size: 12.5px;
    color: var(--ink-faint);
  }

  .topbar-dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 18px;
    color: var(--ink);
    font-size: 14px;
    text-decoration: none;
    transition: background .15s;
  }

  .topbar-dropdown-item:hover {
    background: var(--panel-soft);
  }

  .topbar-dropdown-item.danger {
    color: var(--red);
  }

  .topbar-dropdown-divider {
    height: 1px;
    background: var(--line);
    margin: 4px 0;
  }

  /* Quick action dropdown */
  .topbar-dropdown-label {
    padding: 8px 18px 4px;
    font-size: 11px;
    font-weight: 600;
    color: var(--ink-faint);
    text-transform: uppercase;
    letter-spacing: .06em;
    font-family: 'JetBrains Mono', monospace;
  }
</style>

<header class="topbar">
    {{-- Search --}}
    <div class="topbar-search-wrap">
        <svg class="topbar-search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input id="search" name="search" class="topbar-search" placeholder="Cari pelanggan, invoice..." type="search" autocomplete="off">
    </div>

    <div class="topbar-right">
        @if(Auth::check() && Auth::user()->tenant)
        {{-- Quick Action --}}
        <div style="position:relative">
            <button onclick="var d=this.nextElementSibling;d.classList.toggle('show');event.stopPropagation();"
                    class="btn btn-outline"
                    style="padding:8px 16px;font-size:13.5px;border-color:var(--line)">
                Quick Action
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-left:4px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="topbar-dropdown" onclick="event.stopPropagation()">
                <div class="topbar-dropdown-label">Aksi Cepat</div>
                <a href="{{ route('customers.create', ['tenant_slug' => Auth::user()->tenant->slug]) }}"
                   class="topbar-dropdown-item" style="text-decoration:none;color:var(--ink)">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    Tambah Pelanggan
                </a>
                <a href="{{ route('invoices.create', ['tenant_slug' => Auth::user()->tenant->slug]) }}"
                   class="topbar-dropdown-item" style="text-decoration:none;color:var(--ink)">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m2 0a9 9 0 11-12 0v6a9 9 0 0112 0z"/></svg>
                    Tambah Invoice
                </a>
            </div>
        </div>
        @endif

        {{-- Notifications --}}
        <button class="topbar-btn" title="Notifikasi">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        </button>

        {{-- Profile --}}
        @if(Auth::check())
        <div style="position:relative">
            <div onclick="var d=this.nextElementSibling;d.classList.toggle('show');event.stopPropagation();"
                 class="topbar-profile">
                <div class="topbar-avatar">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'U') }}&color={{ urlencode('#EAF0FB') }}&background={{ urlencode('#2B3A58') }}" alt="">
                </div>
                <div class="topbar-profile-info">
                    <div class="topbar-profile-name">{{ Auth::user()->name }}</div>
                    <div class="topbar-profile-email">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <div class="topbar-dropdown">
                @if(Auth::user()->tenant)
                <a href="{{ route('tenant.profile.show', ['tenant_slug' => Auth::user()->tenant->slug]) }}"
                   class="topbar-dropdown-item" style="text-decoration:none">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profil Saya
                </a>
                @endif
                <div class="topbar-dropdown-divider"></div>
                <form method="POST" action="{{ route('tenant.logout', ['tenant_slug' => Auth::user()->tenant->slug]) }}" style="padding:0;margin:0">
                    @csrf
                    <a href="#" onclick="event.preventDefault();this.closest('form').submit();"
                       class="topbar-dropdown-item danger" style="text-decoration:none">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Keluar
                    </a>
                </form>
            </div>
        </div>
        @endif
    </div>
</header>

<script>
  document.addEventListener('click', function(e) {
    document.querySelectorAll('.topbar-dropdown').forEach(function(d) {
      d.classList.remove('show');
    });
  });
</script>