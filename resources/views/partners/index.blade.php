@extends('layouts.app')

@section('title', 'Mitra')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Mitra</h1>
        <a href="{{ route('partners.create', ['tenant_slug' => request()->route('tenant_slug')]) }}" class="btn btn-primary">
            <i class="bi bi-plus me-1"></i>Tambah Mitra
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
                            <th>Nama</th>
                            <th>Kontak</th>
                            <th>Telepon</th>
                            <th>Komisi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($partners as $partner)
                        <tr>
                            <td>{{ $partner->name }}</td>
                            <td>{{ $partner->contact_person ?? '-' }}</td>
                            <td>{{ $partner->phone ?? '-' }}</td>
                            <td>{{ $partner->commission_rate }}%</td>
                            <td>
                                <span class="badge bg-{{ $partner->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($partner->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('partners.edit', ['tenant_slug' => request()->route('tenant_slug'), 'partner' => $partner->id]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada mitra.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
