<?php

use App\Http\Controllers\Akademik\CetakKrsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/krs/{id}/cetak', CetakKrsController::class)->name('krs.cetak');
});
Route::get('/', function () {
    return view('welcome');
});
