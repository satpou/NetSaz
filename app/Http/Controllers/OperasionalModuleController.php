<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OperasionalModuleController extends Controller
{
    /**
     * Staff & Team Management
     */
    public function staff()
    {
        $tid = tenantId();
        $staffMembers = User::where('tenant_id', $tid)
            ->latest()
            ->paginate(15);

        return view('operasional.staff', compact('staffMembers'));
    }

    public function staffStore(Request $request)
    {
        $tid = tenantId();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,tenant_admin,staff',
        ]);

        User::create([
            'tenant_id' => $tid,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        return back()->with('success', "Staff '{$validated['name']}' berhasil didaftarkan.");
    }

    public function staffDestroy(User $user)
    {
        $tid = tenantId();
        abort_unless($user->tenant_id === $tid, 403);

        // Prevent deleting oneself
        if ($user->id === auth()->id()) {
            return back()->with('warning', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return back()->with('success', 'Akun staff berhasil dihapus.');
    }

    /**
     * Dedicated Operational Export Center
     */
    public function export()
    {
        $tid = tenantId();
        $stats = [
            'customers' => Customer::where('tenant_id', $tid)->count(),
            'invoices' => Invoice::where('tenant_id', $tid)->count(),
            'payments' => Payment::where('tenant_id', $tid)->count(),
            'tickets' => Ticket::where('tenant_id', $tid)->count(),
        ];

        return view('operasional.export', compact('stats'));
    }

    public function downloadData(Request $request, string $type)
    {
        $tid = tenantId();
        $filename = "export_{$type}_" . now()->format('Y-m-d_His') . ".csv";

        return new StreamedResponse(function () use ($tid, $type) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            switch ($type) {
                case 'customers':
                    fputcsv($handle, ['ID', 'Kode', 'Nama', 'No HP', 'Alamat', 'Paket', 'Status', 'IP', 'Tgl Pasang']);
                    Customer::where('tenant_id', $tid)->with('package')->chunk(200, function ($rows) use ($handle) {
                        foreach ($rows as $row) {
                            fputcsv($handle, [
                                $row->id,
                                $row->customer_code,
                                $row->name,
                                $row->phone,
                                $row->address,
                                $row->package?->name ?? '-',
                                $row->status,
                                $row->ip_address ?? '-',
                                $row->installation_date?->format('Y-m-d') ?? '-',
                            ]);
                        }
                    });
                    break;

                case 'invoices':
                    fputcsv($handle, ['No Invoice', 'Pelanggan', 'Bulan/Tahun', 'Total (Rp)', 'Jatuh Tempo', 'Status']);
                    Invoice::where('tenant_id', $tid)->with('customer')->chunk(200, function ($rows) use ($handle) {
                        foreach ($rows as $row) {
                            fputcsv($handle, [
                                $row->invoice_number,
                                $row->customer?->name ?? '-',
                                ($row->month ?? '-') . '/' . ($row->year ?? '-'),
                                $row->total_amount,
                                $row->due_date?->format('Y-m-d') ?? '-',
                                $row->status,
                            ]);
                        }
                    });
                    break;

                case 'payments':
                    fputcsv($handle, ['ID', 'No Invoice', 'Pelanggan', 'Metode', 'Nominal (Rp)', 'Status', 'Waktu']);
                    Payment::where('tenant_id', $tid)->with(['invoice.customer'])->chunk(200, function ($rows) use ($handle) {
                        foreach ($rows as $row) {
                            fputcsv($handle, [
                                $row->id,
                                $row->invoice?->invoice_number ?? '-',
                                $row->invoice?->customer?->name ?? '-',
                                $row->payment_method ?? 'manual',
                                $row->amount,
                                $row->status,
                                $row->created_at->format('Y-m-d H:i'),
                            ]);
                        }
                    });
                    break;

                case 'tickets':
                    fputcsv($handle, ['No Tiket', 'Pelanggan', 'Judul', 'Prioritas', 'Status', 'Dibuat']);
                    Ticket::where('tenant_id', $tid)->with('customer')->chunk(200, function ($rows) use ($handle) {
                        foreach ($rows as $row) {
                            fputcsv($handle, [
                                $row->ticket_number ?? ('#' . $row->id),
                                $row->customer?->name ?? '-',
                                $row->title,
                                $row->priority,
                                $row->status,
                                $row->created_at->format('Y-m-d H:i'),
                            ]);
                        }
                    });
                    break;
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
