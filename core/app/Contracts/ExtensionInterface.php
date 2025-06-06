<?php

namespace App\Contracts;

use Illuminate\Contracts\Foundation\Application;

interface ExtensionInterface
{
    /**
     * Get the extension unique identifier
     */
    public function getId(): string;
    
    /**
     * Get extension information and metadata
     */
    public function info(): array;
    
    /**
     * Register extension services to Laravel container
     */
    public function register(Application $app): void;
    
    /**
     * Boot extension functionality
     */
    public function boot(): void;
    
    /**
     * Deactivate extension
     */
    public function deactivate(): void;
    
    /**
     * Uninstall extension
     */
    public function uninstall(): void;
    
    /**
     * Get extension service providers
     */
    public function providers(): array;
    
    /**
     * Get extension middleware
     */
    public function middleware(): array;
    
    /**
     * Get extension event listeners
     */
    public function listeners(): array;
    
    /**
     * Get extension Artisan commands
     */
    public function commands(): array;
    
    /**
     * Get extension dependencies
     */
    public function dependencies(): array;
    
    /**
     * Check if extension is enabled
     */
    public function isEnabled(): bool;
    
    /**
     * Get extension configuration
     */
    public function getConfig(string $key = null, $default = null);
    
    /**
     * Set extension configuration
     */
    public function setConfig(string $key, $value): void;
}