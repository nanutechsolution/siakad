
<?php

use App\Http\Controllers\Api\PmbWebhookController;
use App\Http\Controllers\MidtransWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/webhooks/pmb/camaba', [PmbWebhookController::class, 'store']);
});


Route::post('/webhook/midtrans', [MidtransWebhookController::class, 'handle'])
    ->name('midtrans.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);
