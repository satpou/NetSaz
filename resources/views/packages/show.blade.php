@extends('layouts.app')

@section('title', 'Detail Paket')

@section('content')
<div class="mb-8">
    <a href="{{ route('packages.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
    </a>
    <h1 class="text-2xl font-semibold text-gray-900 tracking-tight">{{ $package->name }}</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="card p-6 lg:col-span-2">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-6">Informasi Paket</h2>
        
        <div class="space-y-4">
            <div class="flex justify-between py-3 border-b border-gray-100">
                <span class="text-sm text-gray-500">Deskripsi</span>
                <span class="text-sm font-medium text-gray-900 text-right">{{ $package->description }}</span>
            </div>
            <div class="flex justify-between py-3 border-b border-gray-100">
                <span class="text-sm text-gray-500">Kecepatan</span>
                <span class="text-sm font-medium text-gray-900">{{ $package->speed }}</span>
            </div>
            <div class="flex justify-between py-3 border-b border-gray-100">
                <span class="text-sm text-gray-500">Harga</span>
                <span class="text-base font-semibold text-gray-900">Rp{{ number_format($package->price, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between py-3 border-b border-gray-100">
                <span class="text-sm text-gray-500">Pajak</span>
                <span class="text-sm font-medium text-gray-900">{{ $package->is_taxable ? 'Termasuk' : 'Tidak Termasuk' }}</span>
            </div>
            <div class="flex justify-between py-3">
                <span class="text-sm text-gray-500">Dibuat</span>
                <span class="text-sm font-medium text-gray-900">{{ $package->created_at->format('d M Y H:i') }}</span>
            </div>
        </div>

        <div class="flex gap-3 mt-6">
            <a href="{{ route('packages.edit', $package->id) }}" class="btn-primary">Edit Paket</a>
        </div>
    </div>

    <div class="card p-6">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Pelanggan</h2>
        <p class="text-3xl font-bold text-gray-900 mb-2">{{ $package->customers->count() }}</p>
        <p class="text-sm text-gray-500 mb-6">pengguna aktif</p>
        
        @if($package->customers->count() > 0)
            <div class="space-y-2">
                @foreach($package->customers->take(5) as $customer)
                <a href="{{ route('customers.show', $customer->id) }}" 
                   class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                        <span class="text-xs font-semibold text-white">{{ substr($customer->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $customer->name }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500 text-center py-4">Belum ada pelanggan</p>
        @endif
    </div>
</div>
@endsection