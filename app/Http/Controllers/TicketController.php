<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = Ticket::with('customer')->latest()->get();
        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('tickets.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'assigned_to' => 'nullable|exists:users,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'source' => 'nullable|string',
        ]);

        $validated['tenant_id'] = session('active_tenant_id');
        $validated['ticket_number'] = 'TCK-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        $validated['status'] = 'open';
        $validated['source'] = $validated['source'] ?? 'staff';

        Ticket::create($validated);

        return redirect()->route('tickets.index', ['tenant_slug' => request()->route('tenant_slug')])
            ->with('success', 'Tiket berhasil dibuat.');
    }

    public function show(Ticket $ticket)
    {
        return view('tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        return view('tickets.edit', compact('ticket'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $ticket->update($validated);

        return redirect()->route('tickets.index', ['tenant_slug' => request()->route('tenant_slug')])
            ->with('success', 'Tiket berhasil diperbarui.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'resolution_notes' => 'nullable|string',
        ]);

        $data = ['status' => $validated['status']];

        if (isset($validated['resolution_notes'])) {
            $data['resolution_notes'] = $validated['resolution_notes'];
        }

        if ($validated['status'] === 'resolved' || $validated['status'] === 'closed') {
            $data['resolved_at'] = now();
        }

        $ticket->update($data);

        return redirect()->back()->with('success', 'Status tiket berhasil diperbarui.');
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        return redirect()->route('tickets.index', ['tenant_slug' => request()->route('tenant_slug')])
            ->with('success', 'Tiket berhasil dihapus.');
    }
}
