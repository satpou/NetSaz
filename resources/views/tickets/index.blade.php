@extends('layouts.app')

@section('title', 'Tiket')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Tiket</h1>
        <a href="{{ route('tickets.create', ['tenant_slug' => request()->route('tenant_slug')]) }}" class="btn btn-primary">
            <i class="bi bi-plus me-1"></i>Buat Tiket
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nomor Tiket</th>
                            <th>Pelanggan</th>
                            <th>Subjek</th>
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                        <tr>
                            <td><code>{{ $ticket->ticket_number }}</code></td>
                            <td>{{ $ticket->customer->name ?? '-' }}</td>
                            <td>{{ $ticket->subject }}</td>
                            <td>{{ ucfirst($ticket->priority) }}</td>
                            <td>
                                <span class="badge bg-{{ $ticket->status === 'open' ? 'warning' : ($ticket->status === 'resolved' ? 'success' : 'secondary') }}">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('tickets.show', ['tenant_slug' => request()->route('tenant_slug'), 'ticket' => $ticket->id]) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada tiket.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
