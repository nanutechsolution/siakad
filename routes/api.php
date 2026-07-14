<?php

use App\Http\Controllers\Api\PmbWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/webhooks/pmb/camaba', [PmbWebhookController::class, 'store']);
});
