@extends('layouts.app')

@section('title', 'Detail Tiket')

@section('content')
<x-page-header :title="$ticket->subject" :subtitle="$ticket->ticket_number">
    <div style="display:flex;gap:10px">
        <a href="{{ route('tickets.edit', ['tenant_slug' => request()->route('tenant_slug'), 'ticket' => $ticket->id]) }}" class="btn btn-primary">Edit</a>
        <form action="{{ route('tickets.destroy', ['tenant_slug' => request()->route('tenant_slug'), 'ticket' => $ticket->id]) }}" method="POST" onsubmit="return confirm('Hapus tiket ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline" style="color:var(--red)">Hapus</button>
        </form>
    </div>
</x-page-header>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="lower-grid">
    <div class="panel" style="padding:24px">
        <div class="panel-head">
            <h3 style="font-size:16px;font-weight:600">Informasi Tiket</h3>
        </div>
        <div class="summary-row">
            <span class="summary-label">Nomor Tiket</span>
            <span class="summary-value mono">{{ $ticket->ticket_number }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Pelanggan</span>
            <span class="summary-value" style="font-family:'Inter'">{{ $ticket->customer->name ?? '-' }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Ditugaskan ke</span>
            <span class="summary-value" style="font-family:'Inter'">{{ $ticket->assignee->name ?? '-' }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Prioritas</span>
            <span class="badge badge-primary">{{ ucfirst($ticket->priority) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Status</span>
            <span class="badge {{ in_array($ticket->status, ['resolved', 'closed']) ? 'badge-paid' : 'badge-late' }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
        </div>
        <div class="summary-row" style="border-bottom:none">
            <span class="summary-label">Diselesaikan pada</span>
            <span class="summary-value">{{ $ticket->resolved_at?->format('d M Y H:i') ?? '-' }}</span>
        </div>

        <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--line)">
            <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;margin-bottom:8px">Pesan / Keluhan</div>
            <p style="font-size:14px;color:var(--ink);line-height:1.6;white-space:pre-line">{{ $ticket->message }}</p>
        </div>

        @if($ticket->resolution_notes)
        <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--line)">
            <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;margin-bottom:8px">Catatan Penyelesaian</div>
            <p style="font-size:14px;color:var(--ink-soft);line-height:1.6;white-space:pre-line">{{ $ticket->resolution_notes }}</p>
        </div>
        @endif
    </div>

    <div class="panel" style="padding:24px">
        <div class="panel-head">
            <h3 style="font-size:16px;font-weight:600">Perbarui Status</h3>
        </div>
        <form action="{{ route('tickets.status', ['tenant_slug' => request()->route('tenant_slug'), 'ticket' => $ticket->id]) }}" method="POST" style="margin-top:12px">
            @csrf
            @method('PUT')

            <div style="margin-bottom:16px">
                <label class="form-label">Status</label>
                <select name="status" class="form-input" required>
                    <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>

            <div style="margin-bottom:20px">
                <label class="form-label">Catatan Penyelesaian</label>
                <textarea name="resolution_notes" rows="4" class="form-input" placeholder="Tindakan yang sudah dilakukan">{{ old('resolution_notes', $ticket->resolution_notes) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Simpan Status</button>
        </form>
    </div>
</div>
@endsection
