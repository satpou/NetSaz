<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SystemHealth extends Component
{
    public array $checks = [];

    public function mount(): void
    {
        $this->runHealthChecks();
    }

    protected function runHealthChecks(): void
    {
        // Database connectivity
        try {
            DB::connection()->getPdo();
            $this->checks['database'] = ['status' => 'ok', 'label' => 'Database'];
        } catch (\Exception $e) {
            $this->checks['database'] = ['status' => 'error', 'label' => 'Database', 'message' => $e->getMessage()];
        }

        // Cache connectivity
        try {
            cache()->set('health_check', true, 5);
            $this->checks['cache'] = ['status' => 'ok', 'label' => 'Cache'];
        } catch (\Exception $e) {
            $this->checks['cache'] = ['status' => 'error', 'label' => 'Cache', 'message' => $e->getMessage()];
        }

        // Storage writeable
        $this->checks['storage'] = [
            'status' => is_writable(storage_path()) ? 'ok' : 'error',
            'label' => 'Storage',
        ];
    }

    public function render()
    {
        return view('livewire.system-health');
    }
}
