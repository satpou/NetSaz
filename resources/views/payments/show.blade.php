@extends('layouts.app')

@section('title', 'Detail Pembayaran')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Detail Pembayaran</h1>
        <a href="{{ route('payments.receipt', $payment->id) }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
            Cetak Kuitansi
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <table class="w-full">
            <tr class="border-b">
                <td class="py-3 font-bold w-48">Pelanggan</td>
                <td class="py-3">{{ $payment->customer->name }}</td>
            </tr>
            <tr class="border-b">
                <td class="py-3 font-bold">Invoice</td>
                <td class="py-3">
                    <a href="{{ route('invoices.show', $payment->invoice_id) }}" class="text-blue-600 hover:underline">
                        {{ $payment->invoice->invoice_number }}
                    </a>
                </td>
            </tr>
            <tr class="border-b">
                <td class="py-3 font-bold">Jumlah</td>
                <td class="py-3 text-xl font-bold text-blue-600">Rp{{ number_format($payment->amount, 0, ',', '.') }}</td>
            </tr>
            <tr class="border-b">
                <td class="py-3 font-bold">Metode</td>
                <td class="py-3 uppercase">{{ $payment->payment_method }}</td>
            </tr>
            <tr class="border-b">
                <td class="py-3 font-bold">Referensi</td>
                <td class="py-3">{{ $payment->reference_number ?: '-' }}</td>
            </tr>
            <tr class="border-b">
                <td class="py-3 font-bold">Status</td>
                <td class="py-3">
                    @if($payment->status == 'success')
                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Diverifikasi</span>
                    @elseif(in_array($payment->status, ['failed', 'expired', 'refunded']))
                        <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800">Ditolak</span>
                    @else
                        <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800">Menunggu Verifikasi</span>
                    @endif
                </td>
            </tr>
            <tr class="border-b">
                <td class="py-3 font-bold">Catatan</td>
                <td class="py-3">{{ $payment->notes ?: '-' }}</td>
            </tr>
            <tr>
                <td class="py-3 font-bold">Tanggal Bayar</td>
                <td class="py-3">{{ $payment->created_at->format('d M Y H:i') }}</td>
            </tr>
        </table>

        @if($payment->proof_of_payment)
        <div class="mt-4">
            <div class="font-bold mb-2">Bukti Pembayaran</div>
            <a href="{{ Storage::url($payment->proof_of_payment) }}" target="_blank">
                <img src="{{ Storage::url($payment->proof_of_payment) }}" alt="Bukti Pembayaran"
                     class="w-full max-w-xs rounded border border-gray-200" style="cursor:zoom-in">
            </a>
        </div>
        @endif

        @if($payment->status == 'pending')
        <div class="flex gap-2 mt-6">
            <form action="{{ route('payments.verify', $payment->id) }}" method="POST">
                @csrf
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                    Verifikasi Pembayaran
                </button>
            </form>
            <form action="{{ route('payments.reject', $payment->id) }}" method="POST">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700">
                    Tolak Pembayaran
                </button>
            </form>
        </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('payments.index') }}" class="text-gray-600 hover:text-gray-800">
                Kembali ke daftar pembayaran
            </a>
        </div>
    </div>
</div>
@endsection
