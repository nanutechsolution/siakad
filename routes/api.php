
<?php

use App\Http\Controllers\Api\PmbWebhookController;
use App\Http\Controllers\Api\WilayahStatistikController;
use App\Http\Controllers\MidtransWebhookController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
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
    ->withoutMiddleware([PreventRequestForgery::class]);

// Statistik mahasiswa per wilayah (publik, hanya agregat non-pribadi).
Route::get('/v1/wilayah/statistik', [WilayahStatistikController::class, 'index'])
    ->middleware('throttle:60,1');
