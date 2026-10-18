<div>
    <div class="d-flex gap-2 mb-4">
        <button wire:click="setView('list')" class="btn btn-sm btn-outline-secondary">Daftar NAS</button>
        <button wire:click="setView('hub')" class="btn btn-sm btn-outline-primary">Radius Hub</button>
        <button wire:click="setView('buatdb')" class="btn btn-sm btn-outline-success">Buat Hub Baru</button>
    </div>

    @if($view === 'buatdb')
    <div class="card">
        <div class="card-header">Buat Radius Hub Baru</div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Nama Database</label>
                <input type="text" wire:model="newDbName" class="form-control @error('newDbName') is-invalid @enderror" placeholder="rad_nama_isp">
                @error('newDbName') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Host Server</label>
                <input type="text" wire:model="newDbServerHost" class="form-control @error('newDbServerHost') is-invalid @enderror" placeholder="103.x.x.x">
                @error('newDbServerHost') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <button wire:click="createRadiusHub" class="btn btn-success">Buat Hub</button>
        </div>
    </div>
    @elseif($view === 'hub')
    <div class="card">
        <div class="card-header">Manajemen Radius Hub</div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Pilih NAS untuk Bergabung</label>
                <input type="text" wire:model="selectedNasId" class="form-control" placeholder="NAS ID">
                @error('selectedNasId') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <button wire:click="joinNas" class="btn btn-primary">Bergabung ke Hub</button>

            @if($generatedScript)
            <div class="mt-4">
                <h6>Script untuk: {{ $scriptContext }}</h6>
                <pre class="bg-dark text-light p-3 rounded"><code>{{ $generatedScript }}</code></pre>
            </div>
            @endif
        </div>
    </div>
    @else
    <div class="card">
        <div class="card-header">Daftar NAS</div>
        <div class="card-body">
            <p class="text-muted">Daftar NAS server akan ditampilkan di sini.</p>
        </div>
    </div>
    @endif
</div>
