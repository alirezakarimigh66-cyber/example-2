<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', fn (): RedirectResponse => redirect()->route('home', ['auth' => 'login']))->name('login');
    Route::get('/register', fn (): RedirectResponse => redirect()->route('home', ['auth' => 'register']))->name('register');
    Route::post('/login', [SessionController::class, 'store'])->middleware('throttle:30,1')->name('login.store');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:5,1')->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->middleware('role:user')->name('dashboard');
    Route::get('/admin/dashboard', DashboardController::class)->middleware('role:admin')->name('admin.dashboard');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('products/{product}/image', ProductImageController::class)->name('products.image');
    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class)->except(['create', 'show']);
});
