<?php

namespace Extensions\HelloWorld\Providers;

use Illuminate\Support\ServiceProvider;

class HelloWorldServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register extension services
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load extension routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        
        // Load extension views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'hello-world');
        
        // Publish extension assets
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../resources/js' => public_path('build/extensions/hello-world'),
            ], 'hello-world-assets');
        }
    }
}