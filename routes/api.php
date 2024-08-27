<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

//public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::resource('/products', ProductController::class)->except(['update', 'destroy', 'store']);
Route::get('/discounted', [ProductController::class, 'discounted']);
Route::get('/category/{categoryName}', [ProductController::class, 'category']);

// private routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/products', [ProductController::class, 'store'])->middleware('dealer');
    Route::put('/products/{product}', [ProductController::class, 'update'])->middleware('dealer');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('dealer');
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
