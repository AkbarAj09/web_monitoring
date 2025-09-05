<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BackController;
use App\Http\Controllers\FrontController;

Route::get('/', [FrontController::class, 'index'])->name('home');
Route::post('/login', [BackController::class, 'login'])->name('login');
Route::get('/register', [FrontController::class, 'register'])->name('register');
Route::post('/register-simpan', [BackController::class, 'registerStore'])->name('register.store');

Route::get('/get-data-rahasia-bisnis-kuesioner', [BackController::class, 'getDataRahasiaBisnis']);

Route::get('/logout', [FrontController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'checkrole:Admin'])->group(function () {
    Route::get('/admin/home', [FrontController::class, 'homeAdmin'])->name('admin.home');
});