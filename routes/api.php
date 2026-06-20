<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\QrController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\WebhookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::post('/qr/validate', [QrController::class, 'validatePayload']);
    Route::post('/payment/request-url', [PaymentController::class, 'requestUrl']);
    Route::get('/payment/status/{order_id}', [PaymentController::class, 'checkStatus']);
    Route::post('/payment/midtrans-callback', [WebhookController::class, 'handleMidtransCallback']);
});
