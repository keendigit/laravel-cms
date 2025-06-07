<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CmsFinalizeSetupCommand extends Command
{
    protected $signature = 'cms:finalize-setup';
    protected $description = 'Finalize CMS setup after composer install';

    public function handle()
    {
        $this->info('Finalizing KeenDigit CMS setup...');

        // 1. 发布 Laravel 包的资产
        $this->publishPackageAssets();

        // 2. 合并路由文件到 core/routes
        $this->mergeRoutesToCore();

        // 3. 清理临时目录
        $this->cleanupTemporaryDirectories();

        // 4. 优化应用
        $this->optimizeApplication();

        $this->info('CMS setup finalized successfully!');
    }

    protected function publishPackageAssets()
    {
        $this->info('Publishing package assets...');

        // 发布 Jetstream 资产
        if ($this->confirm('Install Jetstream with Inertia.js?', true)) {
            $this->call('jetstream:install', ['stack' => 'inertia']);
        }

        // 发布其他必要的资产
        $this->call('vendor:publish', [
            '--tag' => 'laravel-assets',
            '--force' => true
        ]);
    }

    protected function mergeRoutesToCore()
    {
        $this->info('Merging routes to core directory...');

        $routesDir = base_path('routes');
        $coreRoutesDir = base_path('core/routes');

        if (!File::exists($routesDir)) {
            return;
        }

        // 确保 core/routes 目录存在
        if (!File::exists($coreRoutesDir)) {
            File::makeDirectory($coreRoutesDir, 0755, true);
        }

        $routeFiles = File::files($routesDir);
        
        foreach ($routeFiles as $file) {
            $filename = $file->getFilename();
            $sourceFile = $file->getPathname();
            $targetFile = $coreRoutesDir . '/' . $filename;

            // 如果目标文件已存在，合并内容而不是覆盖
            if (File::exists($targetFile)) {
                $existingContent = File::get($targetFile);
                $newContent = File::get($sourceFile);
                
                // 简单的内容合并（避免重复的开头注释）
                if (!str_contains($existingContent, trim($newContent))) {
                    $mergedContent = $existingContent . "\n\n// Added by package installation\n" . $newContent;
                    File::put($targetFile, $mergedContent);
                    $this->info("✓ Merged $filename to core/routes/");
                } else {
                    $this->info("✓ $filename already contains the required routes");
                }
            } else {
                // 直接复制新文件
                File::copy($sourceFile, $targetFile);
                $this->info("✓ Moved $filename to core/routes/");
            }
        }
    }

    protected function cleanupTemporaryDirectories()
    {
        $this->info('Cleaning up temporary directories...');

        $directoriesToClean = [
            'routes',
            'app',
            'config',
            'resources',
            'database/migrations',
            'database/factories',
            'database/seeders'
        ];

        foreach ($directoriesToClean as $dir) {
            $fullPath = base_path($dir);
            if (File::exists($fullPath)) {
                File::deleteDirectory($fullPath);
                $this->info("✓ Cleaned up $dir");
            }
        }
    }

    protected function optimizeApplication()
    {
        $this->info('Optimizing application...');
        
        $this->call('optimize:clear');
        $this->call('config:cache');
        $this->call('route:cache');
    }
} 