<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function index()
    {
        return view('import.index');
    }

    public function store(Request $request)
    {
        // Import logic to be implemented
        return redirect()->route('import.index', ['tenant_slug' => request()->route('tenant_slug')])
            ->with('success', 'Import berhasil diproses.');
    }
}
