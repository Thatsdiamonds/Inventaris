<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'nama_gereja',
        'alamat',
        'logo_path',
        'church_photo_path',
        'maintenance_threshold',
        'currency',
        'auto_download_after_add',
        'auto_download_after_edit',
        'default_pagination',
    ];

    /**
     * Cache key for settings
     */
    const CACHE_KEY = 'app_settings';
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when settings are updated
        static::saved(function ($setting) {
            Cache::forget(self::CACHE_KEY);
            Cache::forget('app_locale');
            Cache::forget('church_settings');
        });

        static::deleted(function ($setting) {
            Cache::forget(self::CACHE_KEY);
            Cache::forget('app_locale');
            Cache::forget('church_settings');
        });
    }

    /**
     * Get cached settings or fetch from database
     */
    public static function getCached(): ?self
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::first();
        });
    }

    /**
     * Get a specific setting value with caching
     */
    public static function getValue(string $key, $default = null)
    {
        $settings = self::getCached();
        return $settings ? ($settings->$key ?? $default) : $default;
    }

    /**
     * Clear the settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('app_locale');
        Cache::forget('church_settings');
    }
}
