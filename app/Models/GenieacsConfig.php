<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GenieacsConfig extends Model
{
    protected $table = 'genieacs_configs';

    protected $fillable = [
        'tenant_id',
        'enabled',
        'host',
        'port',
        'nbi_base_url',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function baseUrl(): string
    {
        if ($this->nbi_base_url) {
            return rtrim($this->nbi_base_url, '/');
        }

        $port = $this->port ?: '7557';

        return "http://{$this->host}:{$port}";
    }
}
