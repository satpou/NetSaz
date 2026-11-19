<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Customer;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index()
    {
        $tid = tenantId();
        $areas = Area::where('tenant_id', $tid)->withCount('customers')->get();
        $customersWithCoords = Customer::where('tenant_id', $tid)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('package:id,name')
            ->select('id', 'name', 'phone', 'address', 'area', 'package_id', 'latitude', 'longitude', 'status', 'house_image')
            ->get();

        return view('areas.index', compact('areas', 'customersWithCoords'));
    }

    public function create()
    {
        return view('areas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $validated['tenant_id'] = tenantId();
        Area::create($validated);

        $ts = request()->route('tenant_slug');
        return redirect()->route('areas.index', $ts ? ['tenant_slug' => $ts] : [])
            ->with('success', 'Area berhasil ditambahkan.');
    }

    public function edit(Area $area)
    {
        abort_if($area->tenant_id !== tenantId(), 403);
        return view('areas.edit', compact('area'));
    }

    public function update(Request $request, Area $area)
    {
        abort_if($area->tenant_id !== tenantId(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $area->update($validated);

        $ts = request()->route('tenant_slug');
        return redirect()->route('areas.index', $ts ? ['tenant_slug' => $ts] : [])
            ->with('success', 'Area berhasil diperbarui.');
    }

    public function destroy(Area $area)
    {
        abort_if($area->tenant_id !== tenantId(), 403);

        $area->delete();

        $ts = request()->route('tenant_slug');
        return redirect()->route('areas.index', $ts ? ['tenant_slug' => $ts] : [])
            ->with('success', 'Area berhasil dihapus.');
    }
}
