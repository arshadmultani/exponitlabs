<?php

namespace App\Models;

use Database\Factories\AppSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    /** @use HasFactory<AppSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("app_setting_{$key}", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            return $setting !== null ? $setting->value : $default;
        });
    }

    public static function getFloat(string $key, float $default = 0.0): float
    {
        $val = static::get($key, $default);

        return $val !== null ? (float) $val : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value !== null ? (string) $value : null]
        );

        Cache::forget("app_setting_{$key}");
    }
}
