<?php

use Illuminate\Support\Facades\Route;
use Extensions\HelloWorld\Controllers\HelloWorldController;

/*
|--------------------------------------------------------------------------
| Hello World Extension Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Hello World extension.
|
*/

Route::prefix('hello-world')->name('hello-world.')->group(function () {
    Route::get('/', [HelloWorldController::class, 'index'])->name('index');
    Route::get('/info', [HelloWorldController::class, 'info'])->name('info');
});