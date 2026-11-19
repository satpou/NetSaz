<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = Ticket::with('customer')->latest()->get();

        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        $tid = tenantId();
        $customers = Customer::where('tenant_id', $tid)->orderBy('name')->get();
        $staff = User::where('tenant_id', $tid)->where('is_active', true)->orderBy('name')->get();

        return view('tickets.create', compact('customers', 'staff'));
    }

    public function store(Request $request)
    {
        $tid = tenantId();

        $validated = $request->validate([
            'customer_id' => [
                'nullable',
                Rule::exists('customers', 'id')->where(fn ($q) => $q->where('tenant_id', $tid)),
            ],
            'assigned_to' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('tenant_id', $tid)),
            ],
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'source' => 'nullable|string',
        ]);

        $validated['tenant_id'] = $tid;
        $validated['ticket_number'] = 'TCK-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        $validated['status'] = 'open';
        $validated['source'] = $validated['source'] ?? 'staff';

        Ticket::create($validated);

        $ts = request()->route('tenant_slug');
        return redirect()->route('tickets.index', $ts ? ['tenant_slug' => $ts] : [])
            ->with('success', 'Tiket berhasil dibuat.');
    }

    public function show(Ticket $ticket)
    {
        abort_unless($ticket->tenant_id === tenantId(), 403);
        $ticket->load(['customer', 'assignee']);

        return view('tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        abort_unless($ticket->tenant_id === tenantId(), 403);
        $tid = tenantId();
        $customers = Customer::where('tenant_id', $tid)->orderBy('name')->get();
        $staff = User::where('tenant_id', $tid)->where('is_active', true)->orderBy('name')->get();

        return view('tickets.edit', compact('ticket', 'customers', 'staff'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        abort_unless($ticket->tenant_id === tenantId(), 403);
        $tid = tenantId();

        $validated = $request->validate([
            'customer_id' => [
                'nullable',
                Rule::exists('customers', 'id')->where(fn ($q) => $q->where('tenant_id', $tid)),
            ],
            'assigned_to' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('tenant_id', $tid)),
            ],
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'status' => 'required|in:open,in_progress,resolved,closed',
            'resolution_notes' => 'nullable|string',
        ]);

        if (in_array($validated['status'], ['resolved', 'closed'], true) && ! $ticket->resolved_at) {
            $validated['resolved_at'] = now();
        }

        $ticket->update($validated);

        $ts = request()->route('tenant_slug');
        return redirect()->route('tickets.index', $ts ? ['tenant_slug' => $ts] : [])
            ->with('success', 'Tiket berhasil diperbarui.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        abort_unless($ticket->tenant_id === tenantId(), 403);
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
        abort_unless($ticket->tenant_id === tenantId(), 403);
        $ticket->delete();

        $ts = request()->route('tenant_slug');
        return redirect()->route('tickets.index', $ts ? ['tenant_slug' => $ts] : [])
            ->with('success', 'Tiket berhasil dihapus.');
    }
}
