@extends('layouts.app')

@section('title', 'Catat Pembayaran')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Catat Pembayaran</h1>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('payments.store') }}" method="POST" class="bg-white rounded-lg shadow p-6">
        @csrf
        
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Invoice</label>
            <select name="invoice_id" required
                    class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Pilih Invoice --</option>
                @foreach($invoices as $inv)
                    <option value="{{ $inv->id }}" {{ old('invoice_id', $invoice?->id) == $inv->id ? 'selected' : '' }}>
                        {{ $inv->invoice_number }} - {{ $inv->customer->name }} - Rp{{ number_format($inv->total_amount, 0, ',', '.') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Jumlah Bayar (Rp)</label>
            <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0" required
                   class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Metode Pembayaran</label>
            <select name="method" required
                    class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="manual" {{ old('method') == 'manual' ? 'selected' : '' }}>Transfer Bank (Manual)</option>
                <option value="midtrans" {{ old('method') == 'midtrans' ? 'selected' : '' }}>Midtrans</option>
                <option value="xendit" {{ old('method') == 'xendit' ? 'selected' : '' }}>Xendit</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Nomor Referensi / Bukti Transfer</label>
            <input type="text" name="reference_number" value="{{ old('reference_number') }}" placeholder="Nomor referensi transfer"
                   class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Catatan</label>
            <textarea name="notes" rows="2" placeholder="Catatan tambahan..."
                      class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Simpan Pembayaran
            </button>
            <a href="{{ route('payments.index') }}" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">
                Kembali
            </a>
        </div>
    </form>
</div>
@endsection
