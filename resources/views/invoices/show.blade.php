@extends('layouts.app')

@section('title', 'Detail Invoice')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Detail Invoice</h1>
        <div class="flex gap-2">
            <a href="{{ route('invoices.pdf', $invoice->id) }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                Cetak PDF
            </a>
            @if($invoice->status != 'paid')
                <form action="{{ route('invoices.send-whatsapp', $invoice->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Kirim tagihan ini ke WhatsApp pelanggan ({{ $invoice->customer?->phone }})?')">
                    @csrf
                    <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 flex items-center gap-1.5" style="display:inline-flex;align-items:center;gap:6px">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                        Kirim WhatsApp
                    </button>
                </form>
            @endif
            @if($invoice->status == 'unpaid')
                <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Catat Pembayaran
                </a>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-2xl font-bold text-blue-600">NetSaz</h2>
                <p class="text-gray-600 text-sm">ISP Billing Management System</p>
            </div>
            <div class="text-right">
                <p class="text-lg font-bold">{{ $invoice->invoice_number }}</p>
                <p class="text-sm text-gray-600">Tanggal: {{ $invoice->created_at->format('d M Y') }}</p>
                <p class="text-sm text-gray-600">Jatuh Tempo: {{ $invoice->due_date->format('d M Y') }}</p>
            </div>
        </div>

        <div class="border-t pt-4 mb-6">
            <h3 class="font-bold mb-2">Pelanggan:</h3>
            <p>{{ $invoice->customer->name }}</p>
            <p class="text-sm text-gray-600">{{ $invoice->customer->email }}</p>
            <p class="text-sm text-gray-600">{{ $invoice->customer->phone }}</p>
            <p class="text-sm text-gray-600">{{ $invoice->customer->address }}</p>
        </div>

        <table class="w-full mb-6">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Deskripsi</th>
                    <th class="px-4 py-2 text-center">Hari</th>
                    <th class="px-4 py-2 text-right">Harga/Hari</th>
                    <th class="px-4 py-2 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->invoiceItems as $item)
                <tr class="border-b">
                    <td class="px-4 py-2">{{ $item->description }}</td>
                    <td class="px-4 py-2 text-center">{{ $item->days }} hari</td>
                    <td class="px-4 py-2 text-right">Rp{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right font-bold">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gray-100">
                    <td colspan="3" class="px-4 py-2 font-bold text-right">Total:</td>
                    <td class="px-4 py-2 text-right text-xl font-bold text-blue-600">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="flex items-center justify-between">
            <div>
                @if($invoice->status == 'paid')
                    <span class="px-4 py-2 rounded bg-green-100 text-green-800 font-bold">LUNAS</span>
                @elseif($invoice->status == 'cancelled')
                    <span class="px-4 py-2 rounded bg-gray-100 text-gray-800 font-bold">DIBATALKAN</span>
                @else
                    <span class="px-4 py-2 rounded bg-yellow-100 text-yellow-800 font-bold">BELUM BAYAR</span>
                @endif
            </div>
            <a href="{{ route('invoices.index') }}" class="text-gray-600 hover:text-gray-800">
                Kembali ke daftar
            </a>
        </div>
    </div>
</div>
@endsection
