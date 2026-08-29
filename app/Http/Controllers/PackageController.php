<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::all();
        return view('packages.index', compact('packages'));
    }

    public function create()
    {
        return view('packages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'speed' => 'required|string|max:50',
            'is_taxable' => 'boolean',
        ]);

        $validated['is_taxable'] = $request->boolean('is_taxable');
        Package::create($validated);

        return redirect()->route('packages.index')
            ->with('success', 'Paket berhasil ditambahkan.');
    }

    public function show(Package $package)
    {
        $package->load('customers');
        return view('packages.show', compact('package'));
    }

    public function edit(Package $package)
    {
        return view('packages.edit', compact('package'));
    }

    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'speed' => 'required|string|max:50',
            'is_taxable' => 'boolean',
        ]);

        $validated['is_taxable'] = $request->boolean('is_taxable');
        $package->update($validated);

        return redirect()->route('packages.index')
            ->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(Package $package)
    {
        if ($package->customers()->count() > 0 || $package->invoices()->count() > 0) {
            return redirect()->route('packages.index')
                ->with('error', 'Tidak dapat menghapus paket yang sudah digunakan pelanggan atau memiliki riwayat invoice.');
        }

        $package->delete();

        return redirect()->route('packages.index')
            ->with('success', 'Paket berhasil dihapus.');
    }
}
