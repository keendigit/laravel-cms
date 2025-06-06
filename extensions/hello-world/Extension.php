<?php

namespace Extensions\HelloWorld;

use App\Extensions\BaseExtension;

class Extension extends BaseExtension
{
    public function getId(): string
    {
        return 'hello-world';
    }
    
    public function info(): array
    {
        return [
            'name' => 'Hello World',
            'version' => '1.0.0',
            'description' => 'A simple hello world extension to demonstrate the extension system',
            'author' => 'KeenDigit Team',
        ];
    }
    
    public function providers(): array
    {
        return [
            \Extensions\HelloWorld\Providers\HelloWorldServiceProvider::class,
        ];
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
}