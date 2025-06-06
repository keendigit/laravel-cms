<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class PathServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Configure paths for the reorganized directory structure
        $this->app->bind('path', function () {
            return $this->app->basePath('core/app');
        });

        $this->app->bind('path.config', function () {
            return $this->app->basePath('core/config');
        });

        $this->app->bind('path.database', function () {
            return $this->app->basePath('core/database');
        });

        $this->app->bind('path.resources', function () {
            return $this->app->basePath('core/resources');
        });

        $this->app->bind('path.bootstrap', function () {
            return $this->app->basePath('bootstrap');
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}