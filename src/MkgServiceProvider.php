<?php

namespace Darvis\Mkg;

use Illuminate\Support\ServiceProvider;

class MkgServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/mkg.php', 'mkg'
        );

        // Register Mkg service
        $this->app->singleton(Mkg::class, function ($app) {
            return new Mkg();
        });

        // Register facade
        $this->app->alias(Mkg::class, 'mkg');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publiceer config
        $this->publishes([
            __DIR__ . '/../config/mkg.php' => config_path('mkg.php'),
        ], 'mkg-config');
    }
}
