@extends('layouts.app')

@section('title', 'Import Data')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Import Data</h1>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-magic fs-1 text-primary"></i>
                    </div>
                    <h5 class="card-title fw-bold">Import Cepat (Deteksi Otomatis)</h5>
                    <p class="card-text text-muted">
                        Unggah file dari sistem lain dan biarkan sistem mendeteksi formatnya secara otomatis.
                        Mendukung format CSV, Excel, dan JSON.
                    </p>
                    <a href="#" class="btn btn-primary">
                        <i class="bi bi-upload me-2"></i>Mulai Import Cepat
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-file-earmark-spreadsheet fs-1 text-success"></i>
                    </div>
                    <h5 class="card-title fw-bold">Import Manual (CSV)</h5>
                    <p class="card-text text-muted">
                        Import data menggunakan template CSV yang disediakan.
                        Cocok untuk migrasi data dari sistem custom.
                    </p>
                    <a href="#" class="btn btn-success">
                        <i class="bi bi-file-arrow-down me-2"></i>Download Template CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
