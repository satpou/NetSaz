@php
    $user = Auth::user();
    $host = request()->getHost();
    $tenantDomain = config('app.tenant_domain');
    $isTenantDomain = $host !== $tenantDomain && str_ends_with($host, '.'.$tenantDomain);
    $ts = $isTenantDomain ? str_replace('.'.$tenantDomain, '', $host) : ($user?->tenant?->slug ?? session('tenant_slug') ?? null);
    if ($ts) {
        \Illuminate\Support\Facades\URL::defaults(['tenant_slug' => $ts]);
    }
@endphp
@extends('layouts.app')

@section('title', 'Network Logs')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('tenant.dashboard', $ts ? ['tenant_slug' => $ts] : []) }}" style="color:inherit;text-decoration:none">Home</a> &gt; <span>Network</span> &gt; <b>Logs</b>
</div>

<x-page-header title="Network &amp; System Logs" subtitle="Jejak aktivitas sistem, sinkronisasi secret MikroTik, dan riwayat tindakan jaringan">
    <form action="{{ route('network.logs', $ts ? ['tenant_slug' => $ts] : []) }}" method="GET" style="display:flex;gap:8px">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari action / IP / tipe..." class="form-input" style="padding:8px 12px;font-size:13px;width:240px">
        <button type="submit" class="btn btn-outline" style="padding:8px 14px">Filter</button>
    </form>
</x-page-header>

<div class="panel" style="background:var(--bg-card);border:1px solid var(--line);border-radius:12px;overflow:hidden;margin-bottom:24px">
    <div style="overflow-x:auto">
        <table class="data-table" style="width:100%;border-collapse:collapse;text-align:left;font-size:13.5px">
            <thead>
                <tr style="border-bottom:1px solid var(--line);background:var(--bg-alt);color:var(--ink-soft);font-size:12px;text-transform:uppercase">
                    <th style="padding:14px 20px">Waktu</th>
                    <th style="padding:14px 16px">Tindakan / Action</th>
                    <th style="padding:14px 16px">Model Objek</th>
                    <th style="padding:14px 16px">IP Pengakses</th>
                    <th style="padding:14px 20px">Detail Perubahan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr style="border-bottom:1px solid var(--line)">
                        <td style="padding:14px 20px;font-size:12px;color:var(--ink-faint);white-space:nowrap">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td style="padding:14px 16px">
                            <span class="status-pill ok" style="padding:2px 8px;font-size:11px">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td style="padding:14px 16px;font-size:12px;color:var(--ink)">
                            {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                        </td>
                        <td style="padding:14px 16px;font-family:'JetBrains Mono';font-size:12px;color:var(--primary)">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                        <td style="padding:14px 20px;font-size:12px;color:var(--ink-soft);max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                            @if($log->new_values)
                                <code>{{ json_encode($log->new_values) }}</code>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding:32px;text-align:center;color:var(--ink-faint)">
                            Belum ada riwayat aktivitas log jaringan yang tercatat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div style="padding:14px 20px;border-top:1px solid var(--line)">
            {{ $logs->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
