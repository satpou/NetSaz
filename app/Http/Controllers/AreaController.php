<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index()
    {
        $areas = Area::where('tenant_id', tenantId())->get();
        return view('areas.index', compact('areas'));
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
        ]);

        $validated['tenant_id'] = tenantId();
        Area::create($validated);

        return redirect()->route('areas.index')
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
        ]);

        $area->update($validated);

        return redirect()->route('areas.index')
            ->with('success', 'Area berhasil diperbarui.');
    }

    public function destroy(Area $area)
    {
        abort_if($area->tenant_id !== tenantId(), 403);

        $area->delete();

        return redirect()->route('areas.index')
            ->with('success', 'Area berhasil dihapus.');
    }
}
