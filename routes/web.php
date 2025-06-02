<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\DiscoveryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\WorkGroupController;
use App\Http\Controllers\CompanyController;
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

Route::middleware(['auth', 'restrict.employee.dashboard'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();

        // Scope discoveries based on user type for dashboard
        $query = Discovery::query();

        if ($user->isSoloHandyman()) {
            $query->where('creator_id', $user->id);
        } elseif ($user->isCompanyAdmin()) {
            $query->where('company_id', $user->company_id);
        }

        $discoveries = [
            'in_progress' => $query->clone()->where('status', Discovery::STATUS_IN_PROGRESS)->latest()->get(),
            'pending' => $query->clone()->where('status', Discovery::STATUS_PENDING)->latest()->get(),
            'completed' => $query->clone()->where('status', Discovery::STATUS_COMPLETED)->latest()->get(),
            'cancelled' => $query->clone()->where('status', Discovery::STATUS_CANCELLED)->latest()->get(),
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

    // Work Group Management Routes
    Route::get('/work-groups', [WorkGroupController::class, 'index'])->name('work-groups.index');
    Route::post('/work-groups', [WorkGroupController::class, 'store'])->name('work-groups.store');
    Route::get('/work-groups/{workGroup}', [WorkGroupController::class, 'show'])->name('work-groups.show');
    Route::patch('/work-groups/{workGroup}', [WorkGroupController::class, 'update'])->name('work-groups.update');
    Route::delete('/work-groups/{workGroup}', [WorkGroupController::class, 'destroy'])->name('work-groups.destroy');
    Route::post('/work-groups/{workGroup}/assign-user', [WorkGroupController::class, 'assignUser'])->name('work-groups.assign-user');
    Route::post('/work-groups/{workGroup}/remove-user', [WorkGroupController::class, 'removeUser'])->name('work-groups.remove-user');

    // Company Management Routes
    Route::get('/company', [CompanyController::class, 'index'])->name('company.index');
    Route::get('/company/{company}', [CompanyController::class, 'show'])->name('company.show');
    Route::patch('/company/{company}', [CompanyController::class, 'update'])->name('company.update');
    Route::post('/company/employees', [CompanyController::class, 'createEmployee'])->name('company.create-employee');
    Route::patch('/company/employees/{employee}', [CompanyController::class, 'updateEmployee'])->name('company.update-employee');
    Route::delete('/company/employees/{employee}', [CompanyController::class, 'deleteEmployee'])->name('company.delete-employee');

    Route::post('/logout', [AuthenticationController::class, 'logout'])->name('logout');
});

Route::get('/shared/discovery/{token}', [DiscoveryController::class, 'sharedView'])
    ->name('discovery.shared');
