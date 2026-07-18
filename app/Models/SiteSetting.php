<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('site-settings'));
        static::deleted(fn () => Cache::forget('site-settings'));
    }
}
