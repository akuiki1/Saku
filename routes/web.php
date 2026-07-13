<?php

use App\Http\Controllers\CetakController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/cetak/kwitansi/{berkas}', [CetakController::class, 'kwitansi'])
    ->middleware('auth')
    ->name('cetak.kwitansi');
