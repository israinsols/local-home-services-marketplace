<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    protected static function booted(): void
    {
        static::saved(fn (SiteSetting $setting) => Cache::forget('site-setting.'.$setting->key));
        static::deleted(fn (SiteSetting $setting) => Cache::forget('site-setting.'.$setting->key));
    }

    public static function valueFor(string $key, ?string $default = null): ?string
    {
        return Cache::remember(
            'site-setting.'.$key,
            now()->addMinutes(10),
            fn () => static::query()->whereKey($key)->value('value') ?? $default,
        );
    }
}
