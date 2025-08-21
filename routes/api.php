<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\n8n\OrderController;
use App\Http\Controllers\n8n\WhatsappController;
use App\Http\Middleware\EnsureTokenIsValid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
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
    Route::post('/order', [OrderController::class, 'store']);
});
Route::post('/order/callback', [OrderController::class, 'handle']);
