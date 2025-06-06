# KeenDigit CMS Template 构建文档

本文档详细记录了 KeenDigit CMS Template 项目的完整构建过程，包括遇到的问题和解决方案。

## 构建目标

根据 `keendigit-cms/docs/develop/design.md` 的设计文档，构建一个标准的 Composer 包，用于发布到 Packagist，使用户可以通过 `composer create-project keendigit/cms-template my-cms-project` 快速创建 CMS 项目。

## 构建步骤

### 1. 项目结构初始化

#### 1.1 创建 Laravel 12 基础项目
```bash
composer create-project laravel/laravel temp-laravel "^12.0" --prefer-dist
```

#### 1.2 复制文件到目标目录
```bash
cp -r temp-laravel/* .
rm -rf temp-laravel
```

#### 1.3 重组目录结构（核心步骤）
根据设计文档要求，将 Laravel 核心文件移至 `core/` 目录：

```bash
mkdir -p core extensions
mv app config database resources routes bootstrap core/
```

**设计理念**：实现"核心+扩展"的清晰架构分离，便于管理和维护。

### 2. Composer 配置调整

#### 2.1 更新 composer.json 为 Packagist 分发
主要变更：
- 包名：`laravel/laravel` → `keendigit/cms-template`
- 自动加载路径调整：`"App\\": "app/"` → `"App\\": "core/app/"`
- 添加扩展命名空间：`"Extensions\\": "extensions/"`
- 增加 CMS 相关依赖包

```json
{
    "name": "keendigit/cms-template",
    "autoload": {
        "psr-4": {
            "App\\": "core/app/",
            "Database\\Factories\\": "core/database/factories/",
            "Database\\Seeders\\": "core/database/seeders/",
            "Extensions\\": "extensions/"
        }
    }
}
```

### 3. Bootstrap 路径配置 ⚠️ 

#### 3.1 遇到的问题
Laravel 12 的新配置方式与之前版本不同，初始尝试使用传统的路径配置方法失败：

```php
// ❌ 这种方法在 Laravel 12 中不可用
$app->useAppPath($app->basePath('core/app'));
$app->useConfigPath($app->basePath('core/config'));
```

**错误信息**：
```
BadMethodCallException: Method Illuminate\Foundation\Application::useResourcePath does not exist.
```

#### 3.2 解决方案
创建专门的 `PathServiceProvider` 来处理路径配置：

```php
// core/app/Providers/PathServiceProvider.php
class PathServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('path', function () {
            return $this->app->basePath('core/app');
        });
        
        $this->app->bind('path.config', function () {
            return $this->app->basePath('core/config');
        });
        // ... 其他路径绑定
    }
}
```

#### 3.3 更新入口文件
- 更新 `public/index.php`：`require_once __DIR__.'/../bootstrap/app.php'` → `require_once __DIR__.'/../core/bootstrap/app.php'`
- 更新 `artisan`：`require_once __DIR__.'/bootstrap/app.php'` → `require_once __DIR__.'/core/bootstrap/app.php'`

#### 3.4 缓存目录问题 ⚠️ 

**遇到的问题**：
```
The /Users/.../bootstrap/cache directory must be present and writable.
```

**解决方案**：
保持 `bootstrap/cache/` 在项目根目录，因为 Laravel 12 期望在标准位置找到缓存文件：

```bash
mkdir -p bootstrap/cache
cp -r core/bootstrap/cache/* bootstrap/cache/
```

### 4. 扩展系统实现

#### 4.1 扩展接口设计
创建 `ExtensionInterface` 合约，定义扩展必须实现的方法：

```php
// core/app/Contracts/ExtensionInterface.php
interface ExtensionInterface
{
    public function getId(): string;
    public function info(): array;
    public function register(Application $app): void;
    public function boot(): void;
    // ... 其他方法
}
```

#### 4.2 基础扩展类
创建 `BaseExtension` 抽象类，提供默认实现：

```php
// core/app/Extensions/BaseExtension.php
abstract class BaseExtension implements ExtensionInterface
{
    // 提供通用功能实现
}
```

#### 4.3 扩展管理器
创建 `ExtensionManager` 服务，负责发现、注册和管理扩展：

```php
// core/app/Services/ExtensionManager.php
class ExtensionManager
{
    public function discover(): Collection
    public function registerExtensions(): void
    public function bootExtensions(): void
    // ... 其他管理方法
}
```

#### 4.4 扩展服务提供者
创建 `ExtensionServiceProvider`，集成到 Laravel 生命周期：

```php
// core/app/Providers/ExtensionServiceProvider.php
class ExtensionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 注册 ExtensionManager
        // 注册所有扩展
    }
    
    public function boot(): void
    {
        // 启动所有扩展
    }
}
```

### 5. 前端配置

#### 5.1 更新 package.json
添加 CMS 模板所需的前端依赖：
- Vue 3 + Inertia.js
- Ant Design Vue 4
- Tailwind CSS 4
- Pinia 状态管理

#### 5.2 Vite 配置调整
更新 `vite.config.js` 以支持新的目录结构：

```javascript
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'core/resources/css/app.css',  // 调整路径
                'core/resources/js/app.js'
            ],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': resolve(__dirname, 'core/resources/js'),
            '~': resolve(__dirname, 'core/resources'),
        },
    },
})
```

### 6. 示例扩展创建

#### 6.1 Hello World 扩展
创建示例扩展来验证系统：

```
extensions/hello-world/
├── Extension.php           # 主扩展类
├── extension.json          # 扩展清单
├── Controllers/            # 控制器
├── Providers/              # 服务提供者
└── routes/                 # 路由定义
```

#### 6.2 PSR-4 自动加载问题 ⚠️ 

**遇到的问题**：
```
Class Extensions\HelloWorld\Extension located in ./extensions/hello-world/Extension.php 
does not comply with psr-4 autoloading standard
```

**原因分析**：
初始创建的目录结构为：
```
extensions/hello-world/
└── src/
    ├── Controllers/
    └── Providers/
```

但 PSR-4 规则 `"Extensions\\": "extensions/"` 期望：
```
extensions/hello-world/
├── Controllers/     # 直接在扩展根目录
└── Providers/
```

**解决方案**：
1. 调整目录结构，移除中间的 `src/` 目录
2. 为扩展创建独立的 `composer.json`：

```json
{
    "autoload": {
        "psr-4": {
            "Extensions\\HelloWorld\\": ""
        }
    }
}
```

### 7. 配置文件完善

#### 7.1 环境配置
创建 `.env.example`，包含 CMS 特定配置：
- 扩展系统配置
- 数据库配置（PostgreSQL）
- 性能配置（Swoole 支持）

#### 7.2 文档和许可证
- 创建详细的 `README.md`
- 添加 MIT `LICENSE` 文件
- 配置 `.gitignore` 支持扩展开发

### 8. 验证和测试

#### 8.1 自动加载验证
```bash
composer dump-autoload
php artisan --version  # 验证 Laravel 正常运行
```

#### 8.2 扩展系统验证
通过 Artisan 命令验证扩展发现功能正常。

## 关键技术决策

### 1. 目录结构选择
**决策**：采用 `core/` 目录分离
**原因**：
- 清晰分离核心应用与扩展
- 便于维护和版本控制
- 符合"核心+扩展"设计理念

### 2. Laravel 12 适配
**挑战**：新版本的配置方式变化
**解决**：使用服务提供者模式而非直接路径配置

### 3. PSR-4 规范遵循
**原则**：严格遵循 PSR-4 自动加载标准
**实现**：调整目录结构以符合命名空间规范

## 最终成果

✅ **完整的 Composer 包**：可发布到 Packagist
✅ **扩展系统**：支持热插拔扩展
✅ **现代技术栈**：Laravel 12 + Vue 3 + Inertia.js
✅ **开发友好**：清晰的文档和示例

## 使用方法

用户可以通过以下命令创建新项目：

```bash
composer create-project keendigit/cms-template my-cms-project
cd my-cms-project
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

## 未来扩展

该模板为未来的功能提供了坚实基础：
- 前端热插拔功能
- 路由动态管理
- 扩展市场集成
- 高性能运行时支持（Swoole/RoadRunner）