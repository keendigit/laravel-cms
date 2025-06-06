# KeenDigit CMS Template

A modern, extensible CMS template built on Laravel 12 with a powerful extension system.

## Features

- **Laravel 12** - Built on the latest Laravel framework
- **Extension System** - Hot-pluggable extensions with isolated namespaces
- **Modern Frontend** - Vue 3 + Inertia.js + Ant Design Vue
- **High Performance** - Swoole/RoadRunner support for production
- **Flexible Architecture** - Core/Extension separation for maintainability
- **PostgreSQL** - Primary database with JSONB support for flexible data storage

## Quick Start

### Installation

```bash
composer create-project keendigit/laravel-cms my-cms-project
cd my-cms-project
```

### Environment Setup

```bash
# Copy environment configuration
cp .env.example .env

# Configure your database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --seed
```

### Frontend Setup

```bash
# Install frontend dependencies
npm install

# Build frontend assets
npm run build

# Or run development server
npm run dev
```

### Development Server

```bash
# Using Laravel's built-in server
php artisan serve

# Or using Swoole (recommended for production)
php artisan swoole:http start
```

## Directory Structure

```
keendigit-cms-template/
├── core/                    # Laravel core application
│   ├── app/                 # Application code
│   ├── config/              # Configuration files
│   ├── database/            # Migrations, factories, seeders
│   ├── resources/           # Views, frontend assets
│   └── routes/              # Route definitions
├── extensions/              # Extension modules
├── public/                  # Web server entry point
├── storage/                 # File storage
├── tests/                   # Test suites
└── vendor/                  # Composer dependencies
```

## Extension System

### Creating an Extension

```bash
php artisan extension:make my-extension
```

This creates a new extension in `extensions/my-extension/` with the following structure:

```
extensions/my-extension/
├── Extension.php           # Main extension class
├── extension.json          # Extension manifest
├── src/                    # Extension source code
│   ├── Controllers/        # Controllers
│   ├── Models/             # Eloquent models
│   └── Services/           # Business logic
├── resources/              # Frontend resources
│   ├── js/                 # Vue components
│   └── css/                # Stylesheets
├── routes/                 # Extension routes
├── config/                 # Extension configuration
└── database/               # Extension migrations
```

### Extension Example

```php
<?php

namespace Extensions\MyExtension;

use App\Extensions\BaseExtension;

class Extension extends BaseExtension
{
    public function getId(): string
    {
        return 'my-extension';
    }
    
    public function info(): array
    {
        return [
            'name' => 'My Extension',
            'version' => '1.0.0',
            'description' => 'A sample extension',
            'author' => 'Your Name',
        ];
    }
    
    public function providers(): array
    {
        return [
            \Extensions\MyExtension\Providers\MyExtensionServiceProvider::class,
        ];
    }
}
```

## Tech Stack

- **Backend**: PHP 8.2+, Laravel 12
- **Frontend**: Vue 3, Inertia.js, Ant Design Vue 4, Tailwind CSS 4
- **Database**: PostgreSQL 16
- **Runtime**: Swoole/RoadRunner (optional)
- **Authentication**: Laravel Jetstream + Spatie Permissions
- **Media**: Spatie Laravel Media Library

## Configuration

### Extension Configuration

Extensions can have their own configuration files in `extensions/{extension-name}/config/`:

```php
// extensions/my-extension/config/settings.php
return [
    'api_endpoint' => env('MY_EXTENSION_API_ENDPOINT', 'https://api.example.com'),
    'cache_ttl' => env('MY_EXTENSION_CACHE_TTL', 3600),
    'features' => [
        'notifications' => env('MY_EXTENSION_NOTIFICATIONS_ENABLED', true),
    ],
];
```

### Environment Variables

Extensions can use their own environment variables in `.env.{environment}` files within their directories.

## API

The CMS provides RESTful APIs with version control:

```
GET    /api/v1/extensions          # List extensions
POST   /api/v1/extensions/{id}     # Install extension
PUT    /api/v1/extensions/{id}     # Enable/disable extension
DELETE /api/v1/extensions/{id}     # Uninstall extension
```

## Testing

```bash
# Run tests
php artisan test

# Run with coverage
php artisan test --coverage
```

## Production Deployment

### Using Swoole

```bash
# Install Swoole extension
pecl install swoole

# Configure environment
ENABLE_SWOOLE=true
SWOOLE_HTTP_HOST=0.0.0.0
SWOOLE_HTTP_PORT=8080
SWOOLE_HTTP_WORKER_NUM=4

# Start Swoole server
php artisan swoole:http start
```

### Using RoadRunner

```bash
# Download RoadRunner
./vendor/bin/rr get

# Start RoadRunner
./rr serve
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

- [Documentation](https://docs.keendigit.com/cms-template)
- [Issues](https://github.com/keendigit/cms-template/issues)
- [Discussions](https://github.com/keendigit/cms-template/discussions)

## Acknowledgments

- Laravel Framework
- Vue.js Community
- Ant Design Vue
- Spatie Packages