<?php

namespace App\Providers;

use App\Services\ImageOptimizationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register ImageOptimizationService as singleton
        $this->app->singleton(ImageOptimizationService::class, function ($app) {
            return new ImageOptimizationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Optimize Eloquent models
        Model::preventLazyLoading(!app()->isProduction());
        Model::preventSilentlyDiscardingAttributes(!app()->isProduction());
        
        // Enable query logging only in development
        if (!app()->isProduction()) {
            DB::enableQueryLog();
        }
        
        // Database performance optimization
        if (app()->isProduction()) {
            // Disable strict mode for better performance on production
            Model::unguard();
        }
    }
}
