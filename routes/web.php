<?php

use App\Http\Controllers\ArsipController;
use App\Http\Controllers\CetakController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::middleware('auth')->group(function () {
    Route::get('/cetak/kwitansi/{berkas}', [CetakController::class, 'kwitansi'])
        ->name('cetak.kwitansi');

    Route::get('/arsip/{file}/unduh', [ArsipController::class, 'unduh'])
        ->name('arsip.unduh');
});
