<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PlatformSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("platform_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            if (! $setting || $setting->value === null) {
                return $default;
            }

            $decoded = json_decode($setting->value, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $setting->value;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        $serialized = is_array($value) || is_object($value) ? json_encode($value) : $value;
        static::updateOrCreate(['key' => $key], ['value' => $serialized]);
        Cache::forget("platform_setting_{$key}");
    }

    public static function forget(string $key): void
    {
        static::where('key', $key)->delete();
        Cache::forget("platform_setting_{$key}");
    }
}
