<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderItemsController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Resources\ProductResource;

Route::middleware(['auth:api'])->get('/user', function (Request $request) {
    return response()->json([
        'user' => $request->user()
    ]);
});

// Route Register
Route::post('/register', RegisterController::class)->name('register');


// Route Login
Route::post('/login', LoginController::class)->name('login');
Route::put('/login/{id}', [App\Http\Controllers\UserController::class, 'update'])->name('login.update');

// Route Logout
Route::post('/logout', LogoutController::class)->name('logout');

Route::group(['middleware' => 'auth:api'], function(){
    // Route::apiResource('nama-route', routeController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('orderitems', OrderItemsController::class);
    Route::apiResource('payments', PaymentController::class);
});