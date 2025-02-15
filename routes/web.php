<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\DiscoveryController;
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

// Redirect root to login or dashboard based on auth status
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.login');
})->name('home');

Route::middleware('guest')->group(function () {
    // Show login/register forms
    Route::get('/login', [AuthenticationController::class, 'showLogin'])->name('login');
    Route::get('/register', [AuthenticationController::class, 'showRegister'])->name('register');

    // Handle form submissions
    Route::post('/login', [AuthenticationController::class, 'webLogin']);
    Route::post('/register', [AuthenticationController::class, 'webRegister']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Discovery routes
    Route::get('/discovery', [DiscoveryController::class, 'index'])->name('discovery');
    Route::get('/discovery/create', [DiscoveryController::class, 'create'])->name('discovery.create');
    Route::post('/discovery', [DiscoveryController::class, 'store'])->name('discovery.store');
    Route::get('/discovery/{discovery}', [DiscoveryController::class, 'show'])->name('discovery.show');
    Route::get('/discovery/{discovery}/edit', [DiscoveryController::class, 'edit'])->name('discovery.edit');
    Route::put('/discovery/{discovery}', [DiscoveryController::class, 'update'])->name('discovery.update');
    Route::delete('/discovery/{discovery}', [DiscoveryController::class, 'destroy'])->name('discovery.destroy');

    // Web-specific item routes
    Route::get('/items', [ItemController::class, 'webIndex'])->name('items');
    Route::post('/items/store', [ItemController::class, 'webStore'])->name('items.store');
    Route::get('/items/{item}/edit', [ItemController::class, 'webEdit'])->name('items.edit');
    Route::put('/items/{item}', [ItemController::class, 'webUpdate'])->name('items.update');
    Route::delete('/items/{item}', [ItemController::class, 'webDestroy'])->name('items.destroy');
    Route::get('/items/search', [ItemController::class, 'webSearch'])->name('items.search');

    Route::post('/logout', [AuthenticationController::class, 'logout'])->name('logout');
});
