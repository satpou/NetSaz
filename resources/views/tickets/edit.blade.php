@extends('layouts.app')

@section('title', 'Edit Tiket')

@section('content')
<x-page-header title="Edit Tiket" :subtitle="$ticket->ticket_number" />

<div class="panel" style="max-width:640px">
    <div class="panel-body">
        <x-errors />

        <form action="{{ route('tickets.update', ['tenant_slug' => request()->route('tenant_slug'), 'ticket' => $ticket->id]) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px">
                <div>
                    <label class="form-label">Pelanggan</label>
                    <select name="customer_id" class="form-input">
                        <option value="">-- Umum / Internal --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ (string) old('customer_id', $ticket->customer_id) === (string) $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Ditugaskan ke</label>
                    <select name="assigned_to" class="form-input">
                        <option value="">-- Belum ditugaskan --</option>
                        @foreach($staff as $member)
                            <option value="{{ $member->id }}" {{ (string) old('assigned_to', $ticket->assigned_to) === (string) $member->id ? 'selected' : '' }}>
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="margin-bottom:18px">
                <label class="form-label">Subjek <span style="color:var(--red)">*</span></label>
                <input type="text" name="subject" value="{{ old('subject', $ticket->subject) }}" required class="form-input">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px">
                <div>
                    <label class="form-label">Prioritas <span style="color:var(--red)">*</span></label>
                    <select name="priority" required class="form-input">
                        <option value="low" {{ old('priority', $ticket->priority) === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="normal" {{ old('priority', $ticket->priority) === 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="high" {{ old('priority', $ticket->priority) === 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ old('priority', $ticket->priority) === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Status <span style="color:var(--red)">*</span></label>
                    <select name="status" required class="form-input">
                        <option value="open" {{ old('status', $ticket->status) === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in_progress" {{ old('status', $ticket->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ old('status', $ticket->status) === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="closed" {{ old('status', $ticket->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:18px">
                <label class="form-label">Deskripsi Masalah <span style="color:var(--red)">*</span></label>
                <textarea name="message" rows="4" required class="form-input">{{ old('message', $ticket->message) }}</textarea>
            </div>

            <div style="margin-bottom:24px">
                <label class="form-label">Catatan Penyelesaian</label>
                <textarea name="resolution_notes" rows="3" class="form-input">{{ old('resolution_notes', $ticket->resolution_notes) }}</textarea>
            </div>

            <div style="display:flex;gap:12px">
                <button type="submit" class="btn btn-primary">Update Tiket</button>
                <a href="{{ route('tickets.index', ['tenant_slug' => request()->route('tenant_slug')]) }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
