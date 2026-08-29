<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

abstract class BaseModel extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }
}