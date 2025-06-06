<?php

namespace App\Services;

use App\Contracts\ExtensionInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ExtensionManager
{
    protected Application $app;
    protected Collection $extensions;
    protected Collection $enabledExtensions;
    protected string $extensionsPath;
    
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->extensions = collect();
        $this->enabledExtensions = collect();
        $this->extensionsPath = base_path('extensions');
    }
    
    /**
     * Discover all available extensions
     */
    public function discover(): Collection
    {
        if (!File::isDirectory($this->extensionsPath)) {
            File::makeDirectory($this->extensionsPath, 0755, true);
        }
        
        $extensionDirs = collect(File::directories($this->extensionsPath));
        
        return $extensionDirs->map(function ($dir) {
            $name = basename($dir);
            $extensionFile = $dir . '/Extension.php';
            $manifestFile = $dir . '/extension.json';
            
            if (File::exists($extensionFile) && File::exists($manifestFile)) {
                return [
                    'name' => $name,
                    'path' => $dir,
                    'manifest' => json_decode(File::get($manifestFile), true),
                ];
            }
            
            return null;
        })->filter();
    }
    
    /**
     * Register all extensions
     */
    public function registerExtensions(): void
    {
        $discoveries = $this->discover();
        
        foreach ($discoveries as $discovery) {
            try {
                $this->registerExtension($discovery['name'], $discovery['path']);
            } catch (\Exception $e) {
                Log::error("Failed to register extension {$discovery['name']}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Register single extension
     */
    public function registerExtension(string $name, string $path): ?ExtensionInterface
    {
        if ($this->extensions->has($name)) {
            return $this->extensions->get($name);
        }
        
        $extensionClass = $this->getExtensionClass($name, $path);
        
        if (!$extensionClass || !class_exists($extensionClass)) {
            Log::warning("Extension class not found for extension: {$name}");
            return null;
        }
        
        if (!is_subclass_of($extensionClass, ExtensionInterface::class)) {
            Log::warning("Extension {$name} does not implement ExtensionInterface");
            return null;
        }
        
        try {
            $extension = new $extensionClass($this->app);
            
            // Check dependencies
            if (!$this->checkDependencies($extension)) {
                Log::warning("Extension {$name} dependencies not met");
                return null;
            }
            
            // Register the extension
            $extension->register($this->app);
            $this->extensions->put($name, $extension);
            
            Log::info("Extension {$name} registered successfully");
            return $extension;
            
        } catch (\Exception $e) {
            Log::error("Failed to instantiate extension {$name}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Boot all registered extensions
     */
    public function bootExtensions(): void
    {
        foreach ($this->extensions as $name => $extension) {
            try {
                $extension->boot();
                $this->enabledExtensions->put($name, $extension);
                Log::info("Extension {$name} booted successfully");
            } catch (\Exception $e) {
                Log::error("Failed to boot extension {$name}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Get extension class name
     */
    protected function getExtensionClass(string $name, string $path): ?string
    {
        $manifestFile = $path . '/extension.json';
        
        if (File::exists($manifestFile)) {
            $manifest = json_decode(File::get($manifestFile), true);
            if (isset($manifest['class'])) {
                return $manifest['class'];
            }
        }
        
        // Default naming convention
        $className = str_replace('-', '', ucwords($name, '-'));
        return "Extensions\\{$className}\\Extension";
    }
    
    /**
     * Check extension dependencies
     */
    protected function checkDependencies(ExtensionInterface $extension): bool
    {
        $dependencies = $extension->dependencies();
        
        foreach ($dependencies as $dependencyId => $version) {
            if (!$this->isDependencyMet($dependencyId, $version)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check if a dependency is met
     */
    protected function isDependencyMet(string $dependencyId, string $versionConstraint): bool
    {
        // For now, just check if the extension exists
        // In a full implementation, you would check version constraints
        return $this->extensions->has($dependencyId);
    }
    
    /**
     * Get all registered extensions
     */
    public function getExtensions(): Collection
    {
        return $this->extensions;
    }
    
    /**
     * Get all enabled extensions
     */
    public function getEnabledExtensions(): Collection
    {
        return $this->enabledExtensions;
    }
    
    /**
     * Get specific extension
     */
    public function getExtension(string $name): ?ExtensionInterface
    {
        return $this->extensions->get($name);
    }
    
    /**
     * Check if extension is enabled
     */
    public function isEnabled(string $name): bool
    {
        return $this->enabledExtensions->has($name);
    }
    
    /**
     * Enable extension
     */
    public function enable(string $name): bool
    {
        $extension = $this->getExtension($name);
        
        if (!$extension) {
            return false;
        }
        
        try {
            $extension->boot();
            $this->enabledExtensions->put($name, $extension);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to enable extension {$name}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Disable extension
     */
    public function disable(string $name): bool
    {
        $extension = $this->getExtension($name);
        
        if (!$extension) {
            return false;
        }
        
        try {
            $extension->deactivate();
            $this->enabledExtensions->forget($name);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to disable extension {$name}: " . $e->getMessage());
            return false;
        }
    }
}