<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait HasCache
{
    /**
     * Default cache TTL in seconds (1 hour)
     */
    protected static int $cacheTTL = 3600;

    /**
     * Get the cache key prefix for this model
     */
    protected static function getCachePrefix(): string
    {
        return strtolower(class_basename(static::class)) . '_cache';
    }

    /**
     * Get all records with caching
     */
    public static function getCachedAll()
    {
        $key = static::getCachePrefix() . '_all';
        
        return Cache::remember($key, static::$cacheTTL, function () {
            return static::all();
        });
    }

    /**
     * Get a single record by ID with caching
     */
    public static function getCachedById(int $id)
    {
        $key = static::getCachePrefix() . '_' . $id;
        
        return Cache::remember($key, static::$cacheTTL, function () use ($id) {
            return static::find($id);
        });
    }

    /**
     * Clear all cache for this model
     */
    public static function clearModelCache(): void
    {
        // Clear the 'all' cache
        Cache::forget(static::getCachePrefix() . '_all');
        
        // Note: Individual record caches will expire naturally
        // For a more thorough clear, implement cache tags if using a driver that supports them
    }

    /**
     * Boot the trait
     */
    protected static function bootHasCache()
    {
        // Clear cache when model is created, updated, or deleted
        static::created(function ($model) {
            static::clearModelCache();
        });

        static::updated(function ($model) {
            static::clearModelCache();
            Cache::forget(static::getCachePrefix() . '_' . $model->id);
        });

        static::deleted(function ($model) {
            static::clearModelCache();
            Cache::forget(static::getCachePrefix() . '_' . $model->id);
        });
    }
}
