<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Customer;
use App\Models\Package;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with('package');

        if ($request->has('search') && $request->search) {
            $s = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('area', 'like', "%{$s}%");
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('area') && $request->area) {
            $query->where('area', $request->area);
        }

        $customers = $query->paginate(15);
        $areas = Area::where('tenant_id', tenantId())->pluck('name');

        return view('customers.index', compact('customers', 'areas'));
    }

    public function create()
    {
        $packages = Package::all();
        $areas = Area::where('tenant_id', tenantId())->pluck('name');

        return view('customers.create', compact('packages', 'areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'area' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'ktp_id' => 'nullable|string|max:50',
            'ktp_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'package_id' => 'required|exists:packages,id',
            'status' => 'required|in:active,isolated,suspended',
            'join_date' => 'required|date',
            'billing_cycle_day' => 'nullable|integer|min:1|max:28',
        ]);

        if ($request->hasFile('ktp_image')) {
            $validated['ktp_image'] = $request->file('ktp_image')->store('ktp', 'public');
        }

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['package', 'invoices.payments']);

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $packages = Package::all();
        $areas = Area::where('tenant_id', tenantId())->pluck('name');

        return view('customers.edit', compact('customer', 'packages', 'areas'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,'.$customer->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'area' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'ktp_id' => 'nullable|string|max:50',
            'ktp_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'package_id' => 'required|exists:packages,id',
            'status' => 'required|in:active,isolated,suspended',
            'join_date' => 'required|date',
            'billing_cycle_day' => 'nullable|integer|min:1|max:28',
        ]);

        if ($request->hasFile('ktp_image')) {
            $validated['ktp_image'] = $request->file('ktp_image')->store('ktp', 'public');
        }

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }
}
