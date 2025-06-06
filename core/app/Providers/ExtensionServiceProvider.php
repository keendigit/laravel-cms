<?php

namespace App\Providers;

use App\Services\ExtensionManager;
use Illuminate\Support\ServiceProvider;

class ExtensionServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the Extension Manager
        $this->app->singleton(ExtensionManager::class, function ($app) {
            return new ExtensionManager($app);
        });

        // Register extensions
        $extensionManager = $this->app->make(ExtensionManager::class);
        $extensionManager->registerExtensions();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Boot extensions
        $extensionManager = $this->app->make(ExtensionManager::class);
        $extensionManager->bootExtensions();

        // Publish extension assets if needed
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../extensions' => base_path('extensions'),
            ], 'extensions');
        }
    }
}