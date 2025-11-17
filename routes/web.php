<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;



Route::get('/', function () {

    return redirect()->route('dashboard');
});



Route::middleware('auth')->group(function () {
    Route::get('/profil', [DashboardController::class, 'profile'])->name('profile');

    // Rute untuk siswa (ditaruh di luar grup admin)

    // (Akan kita buat di langkah selanjutnya)



    // Grup baru ini khusus untuk admin

    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', UserController::class);
        Route::resource('category', CategoryController::class);
        Route::resource('item', ItemController::class);
        Route::resource('loan', LoanController::class);
        Route::put('loan/{loan}/return', [LoanController::class, 'returnItem'])->name('loan.return');
    });
});



require __DIR__ . '/auth.php';
