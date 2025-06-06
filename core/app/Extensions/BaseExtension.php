<?php

namespace App\Extensions;

use App\Contracts\ExtensionInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Config;

abstract class BaseExtension implements ExtensionInterface
{
    protected Application $app;
    protected array $config = [];
    protected bool $enabled = false;

    public function __construct(Application $app = null)
    {
        $this->app = $app ?: app();
        $this->loadConfiguration();
    }

    /**
     * Load extension configuration
     */
    protected function loadConfiguration(): void
    {
        $extensionId = $this->getId();
        $configPath = base_path("extensions/{$extensionId}/config");
        
        if (is_dir($configPath)) {
            foreach (glob($configPath . '/*.php') as $file) {
                $name = basename($file, '.php');
                $this->config[$name] = require $file;
            }
        }
    }

    /**
     * Default implementation for register method
     */
    public function register(Application $app): void
    {
        // Register service providers
        foreach ($this->providers() as $provider) {
            $app->register($provider);
        }

        // Register commands
        if ($app->runningInConsole()) {
            foreach ($this->commands() as $command) {
                $app->make('Illuminate\Contracts\Console\Kernel')
                    ->load($command);
            }
        }
    }

    /**
     * Default implementation for boot method
     */
    public function boot(): void
    {
        $this->enabled = true;
        
        // Register event listeners
        foreach ($this->listeners() as $event => $listeners) {
            foreach ((array) $listeners as $listener) {
                $this->app['events']->listen($event, $listener);
            }
        }

        // Register middleware
        foreach ($this->middleware() as $middleware) {
            if (is_array($middleware)) {
                $this->app['router']->aliasMiddleware($middleware['name'], $middleware['class']);
            } else {
                $this->app['router']->pushMiddlewareToGroup('web', $middleware);
            }
        }
    }

    /**
     * Default implementation for deactivate method
     */
    public function deactivate(): void
    {
        $this->enabled = false;
    }

    /**
     * Default implementation for uninstall method
     */
    public function uninstall(): void
    {
        $this->enabled = false;
        // Override in child classes for custom uninstall logic
    }

    /**
     * Default empty implementations - override in child classes
     */
    public function providers(): array
    {
        return [];
    }

    public function middleware(): array
    {
        return [];
    }

    public function listeners(): array
    {
        return [];
    }

    public function commands(): array
    {
        return [];
    }

    public function dependencies(): array
    {
        return [];
    }

    /**
     * Check if extension is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get extension configuration
     */
    public function getConfig(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->config;
        }

        return data_get($this->config, $key, $default);
    }

    /**
     * Set extension configuration
     */
    public function setConfig(string $key, $value): void
    {
        data_set($this->config, $key, $value);
    }

    /**
     * Get extension path
     */
    protected function getExtensionPath(): string
    {
        return base_path("extensions/{$this->getId()}");
    }

    /**
     * Get extension resource path
     */
    protected function getResourcePath(string $path = ''): string
    {
        return $this->getExtensionPath() . '/resources' . ($path ? '/' . ltrim($path, '/') : '');
    }

    /**
     * Get extension config path
     */
    protected function getConfigPath(string $file = ''): string
    {
        return $this->getExtensionPath() . '/config' . ($file ? '/' . ltrim($file, '/') : '');
    }
}