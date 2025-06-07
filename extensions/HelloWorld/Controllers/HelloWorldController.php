<?php

namespace Extensions\HelloWorld\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class HelloWorldController extends Controller
{
    /**
     * Display the hello world message
     */
    public function index(): Response
    {
        return response()->json([
            'message' => 'Hello World from Extension!',
            'extension' => 'hello-world',
            'version' => '1.0.0',
            'timestamp' => now()->toISOString(),
        ]);
    }
    
    /**
     * Display extension information
     */
    public function info(): Response
    {
        return response()->json([
            'extension' => [
                'id' => 'hello-world',
                'name' => 'Hello World',
                'version' => '1.0.0',
                'description' => 'A simple hello world extension to demonstrate the extension system',
                'author' => 'KeenDigit Team',
                'status' => 'active',
            ]
        ]);
    }
}