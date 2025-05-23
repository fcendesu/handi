<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\DiscoveryController;
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;
use App\Models\Discovery;

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
        $discoveries = [
            'in_progress' => Discovery::where('status', Discovery::STATUS_IN_PROGRESS)->latest()->get(),
            'pending' => Discovery::where('status', Discovery::STATUS_PENDING)->latest()->get(),
            'completed' => Discovery::where('status', Discovery::STATUS_COMPLETED)->latest()->get(),
            'cancelled' => Discovery::where('status', Discovery::STATUS_CANCELLED)->latest()->get(),
        ];
        return view('dashboard', compact('discoveries'));
    })->name('dashboard');

    // Discovery routes
    Route::get('/discovery', [DiscoveryController::class, 'index'])->name('discovery');
    Route::get('/discovery/{discovery}', [DiscoveryController::class, 'show'])->name('discovery.show');
    Route::post('/discovery', [DiscoveryController::class, 'store'])->name('discovery.store');
    Route::patch('/discovery/{discovery}', [DiscoveryController::class, 'update'])->name('discovery.update');
    Route::patch('/discovery/{discovery}/status', [DiscoveryController::class, 'updateStatus'])->name('discovery.update-status');
    Route::delete('/discovery/{discovery}', [DiscoveryController::class, 'destroy'])->name('discovery.destroy');

    // Web-specific item routes
    Route::get('/items', [ItemController::class, 'webIndex'])->name('items');
    Route::get('/items/search-for-discovery', [ItemController::class, 'webSearchForDiscovery'])->name('items.search-for-discovery');
    Route::get('/items/search', [ItemController::class, 'webSearch'])->name('items.search');
    Route::post('/items/store', [ItemController::class, 'webStore'])->name('items.store');
    Route::get('/items/{item}/edit', [ItemController::class, 'webEdit'])->name('items.edit');
    Route::put('/items/{item}', [ItemController::class, 'webUpdate'])->name('items.update');
    Route::delete('/items/{item}', [ItemController::class, 'webDestroy'])->name('items.destroy');

    Route::post('/logout', [AuthenticationController::class, 'logout'])->name('logout');
});

Route::get('/shared/discovery/{token}', [DiscoveryController::class, 'sharedView'])
    ->name('discovery.shared');
