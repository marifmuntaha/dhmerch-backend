<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\N8NController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WhatsappController;
use App\Http\Middleware\EnsureTokenIsValid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::apiResource('/order', OrderController::class);
    Route::apiResource('/product', ProductController::class);
    Route::post('/payment/midtrans/create', [PaymentController::class, 'midtrans']);

});
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
//    Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
//    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});
Route::get('/notifications', function (Request $request) {
    return response([
        'result' => [],
    ]);
});

Route::group(['middleware' => EnsureTokenIsValid::class, 'prefix' => 'n8n'], function () {
    Route::post('/whatsapp', [WhatsappController::class, 'index']);
    Route::post('/order', [N8NController::class, 'store']);
});
Route::post('/order/callback', [N8NController::class, 'handle']);
