<?php

/**
 * Post-create-project script for KeenDigit CMS Template
 * This script runs after composer create-project to set up the project structure
 */

echo "Setting up KeenDigit CMS Template...\n";

// 1. 删除错误的 core/bootstrap 目录（如果存在）
if (file_exists('core/bootstrap')) {
    function removeDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
    
    removeDirectory('core/bootstrap');
    echo "✓ Removed incorrect core/bootstrap directory\n";
}

// 2. 确保根目录bootstrap存在且正确
if (!file_exists('bootstrap')) {
    echo "✗ Error: bootstrap directory is missing from root\n";
    exit(1);
}

// 3. 创建临时 routes 目录 (为了兼容 Laravel 包的发布过程)
if (!file_exists('routes')) {
    mkdir('routes', 0755, true);
    echo "✓ Created temporary routes directory\n";
}

// 从 core/routes 复制现有路由文件到临时 routes 目录
if (file_exists('core/routes')) {
    $routeFiles = glob('core/routes/*');
    foreach ($routeFiles as $file) {
        $filename = basename($file);
        copy($file, "routes/$filename");
        echo "✓ Copied $filename to routes/\n";
    }
}

// 4. 修复扩展目录结构 (PSR-4 兼容)
if (file_exists('extensions/hello-world')) {
    rename('extensions/hello-world', 'extensions/HelloWorld');
    echo "✓ Fixed extension directory structure for PSR-4\n";
}

// 5. 创建必要的存储目录
$directories = [
    'storage/logs',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
        echo "✓ Created $dir directory\n";
    }
}

// 6. 复制 .env.example 到 .env (如果不存在)
if (!file_exists('.env') && file_exists('.env.example')) {
    copy('.env.example', '.env');
    echo "✓ Created .env file\n";
}

// 7. 刷新composer自动加载（确保命令可用）
echo "✓ Refreshing composer autoload...\n";
exec('composer dump-autoload', $output, $returnVar);
if ($returnVar === 0) {
    echo "✓ Composer autoload refreshed successfully\n";
} else {
    echo "✗ Warning: Failed to refresh composer autoload\n";
}

echo "\n🎉 KeenDigit CMS Template setup completed!\n";
echo "\n📋 Next steps:\n";
echo "2. Configure your .env file\n";
echo "3. Run: php artisan key:generate\n";
echo "4. Run: php artisan cms:finalize-setup\n";
echo "5. Run: php artisan migrate\n";
echo "6. Run: npm install && npm run dev\n";
echo "7. Run: php artisan serve\n"; 