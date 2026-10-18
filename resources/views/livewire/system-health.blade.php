<div>
    <h5 class="mb-4">Status Sistem</h5>
    <div class="row g-3">
        @foreach($checks as $key => $check)
        <div class="col-md-4">
            <div class="card border-{{ $check['status'] === 'ok' ? 'success' : 'danger' }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-{{ $check['status'] === 'ok' ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' }} fs-4"></i>
                    <div>
                        <div class="fw-semibold">{{ $check['label'] }}</div>
                        <small class="text-muted">{{ $check['status'] === 'ok' ? 'Normal' : ($check['message'] ?? 'Error') }}</small>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
