<?php

/**
 * Post-create-project script for KeenDigit CMS Template
 * This script runs after composer create-project to set up the project structure
 */

echo "Setting up KeenDigit CMS Template...\n";

// 1. 创建临时 routes 目录 (为了兼容 Laravel 包的发布过程)
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

// 2. 修复扩展目录结构 (PSR-4 兼容)
if (file_exists('extensions/hello-world')) {
    rename('extensions/hello-world', 'extensions/HelloWorld');
    echo "✓ Fixed extension directory structure for PSR-4\n";
}

// 3. 创建必要的存储目录
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

// 4. 复制 .env.example 到 .env (如果不存在)
if (!file_exists('.env') && file_exists('.env.example')) {
    copy('.env.example', '.env');
    echo "✓ Created .env file\n";
}

echo "\n🎉 KeenDigit CMS Template setup completed!\n";
echo "\n📋 Next steps:\n";
echo "1. Run: composer install\n";
echo "2. Configure your .env file\n";
echo "3. Run: php artisan key:generate\n";
echo "4. Run: php artisan cms:finalize-setup\n";
echo "5. Run: php artisan migrate\n";
echo "6. Run: npm install && npm run dev\n";
echo "7. Run: php artisan serve\n"; 