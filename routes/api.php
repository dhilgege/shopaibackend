<?php

use App\Http\Controllers\Ai\ChatController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderItemController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

// AI Chat Routes
Route::post('/ai/chat', [ChatController::class, 'chat']);
Route::post('/ai/chat/stream', [ChatController::class, 'chatStream']);
Route::get('/ai/conversations', [ChatController::class, 'conversations']);
Route::get('/ai/conversations/{conversation}', [ChatController::class, 'show']);
Route::delete('/ai/conversations/{conversation}', [ChatController::class, 'destroy']);

// Product Routes
Route::apiResource('/products', ProductController::class);
Route::get('/products/search', [ProductController::class, 'index'])->name('products.search');

// Category Routes
Route::apiResource('/categories', CategoryController::class);

// Order Routes
Route::apiResource('/orders', OrderController::class);

// Order Item Routes
Route::get('/orders/{order}/items', [OrderItemController::class, 'index']);
Route::post('/orders/{order}/items', [OrderItemController::class, 'store']);
Route::get('/orders/{order}/items/{item}', [OrderItemController::class, 'show']);
Route::put('/orders/{order}/items/{item}', [OrderItemController::class, 'update']);
Route::patch('/orders/{order}/items/{item}', [OrderItemController::class, 'update']);
Route::delete('/orders/{order}/items/{item}', [OrderItemController::class, 'destroy']);
