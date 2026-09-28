<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', [TestController::class, 'index']);
Route::get('/test/hello', [TestController::class, 'hello']);

Route::get('/products', [ProductController::class, 'getProducts']);
Route::get('/products/{id}', [ProductController::class, 'getProductItem']);
Route::post('/products', [ProductController::class, 'createProduct']);