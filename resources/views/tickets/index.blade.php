@extends('layouts.app')

@section('title', 'Tiket')

@section('content')
<x-page-header title="Tiket" subtitle="{{ $tickets->count() }} tiket terdaftar">
    <a href="{{ route('tickets.create', ['tenant_slug' => request()->route('tenant_slug')]) }}" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
        Buat Tiket
    </a>
</x-page-header>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="panel">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nomor Tiket</th>
                    <th>Pelanggan</th>
                    <th>Subjek</th>
                    <th>Prioritas</th>
                    <th>Status</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                <tr>
                    <td class="mono">{{ $ticket->ticket_number }}</td>
                    <td>{{ $ticket->customer->name ?? '-' }}</td>
                    <td style="font-weight:500;color:var(--ink)">{{ $ticket->subject }}</td>
                    <td>
                        @if($ticket->priority === 'urgent' || $ticket->priority === 'high')
                            <span class="badge badge-late">{{ ucfirst($ticket->priority) }}</span>
                        @else
                            <span class="badge badge-primary">{{ ucfirst($ticket->priority) }}</span>
                        @endif
                    </td>
                    <td>
                        @if($ticket->status === 'resolved' || $ticket->status === 'closed')
                            <span class="badge badge-paid">{{ ucfirst($ticket->status) }}</span>
                        @elseif($ticket->status === 'open')
                            <span class="badge badge-late">{{ ucfirst($ticket->status) }}</span>
                        @else
                            <span class="badge badge-primary">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                        @endif
                    </td>
                    <td style="text-align:right">
                        <div style="display:inline-flex;gap:8px">
                            <a href="{{ route('tickets.show', ['tenant_slug' => request()->route('tenant_slug'), 'ticket' => $ticket->id]) }}" class="btn btn-ghost" style="padding:6px 12px">Detail</a>
                            <a href="{{ route('tickets.edit', ['tenant_slug' => request()->route('tenant_slug'), 'ticket' => $ticket->id]) }}" class="btn btn-outline" style="padding:6px 12px">Edit</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:var(--ink-faint);padding:36px 16px">Belum ada tiket.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
