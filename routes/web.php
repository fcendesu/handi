<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\DiscoveryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\WorkGroupController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanySiteController;
use App\Http\Controllers\PriorityController;

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

        // Get available work groups for the user
        $workGroups = collect();
        if ($user->isSoloHandyman()) {
            $workGroups = $user->createdWorkGroups;
        } elseif ($user->isCompanyAdmin()) {
            $workGroups = $user->company->workGroups;
        } elseif ($user->isCompanyEmployee()) {
            $workGroups = $user->workGroups;
        }

        // Scope discoveries based on user type for dashboard
        $query = Discovery::query()->with(['workGroup', 'priorityBadge', 'assignee']);

        if ($user->isSoloHandyman()) {
            $query->where('creator_id', $user->id);
        } elseif ($user->isCompanyAdmin()) {
            $query->where('company_id', $user->company_id);
        }

        // Apply workgroup filter if specified
        $selectedWorkGroupId = request('work_group_id');
        if ($selectedWorkGroupId && $selectedWorkGroupId !== 'all') {
            $query->where('work_group_id', $selectedWorkGroupId);
        }

        $discoveries = [
            'in_progress' => $query->clone()->where('status', Discovery::STATUS_IN_PROGRESS)->latest()->get(),
            'pending' => $query->clone()->where('status', Discovery::STATUS_PENDING)->latest()->get(),
            'completed' => $query->clone()->where('status', Discovery::STATUS_COMPLETED)->latest()->get(),
            'cancelled' => $query->clone()->where('status', Discovery::STATUS_CANCELLED)->latest()->get(),
        ];

        // Sort in_progress and pending discoveries by priority level (highest first)
        $discoveries['in_progress'] = $discoveries['in_progress']->sortByDesc(function ($discovery) {
            return $discovery->priorityBadge ? $discovery->priorityBadge->level : 0;
        });

        $discoveries['pending'] = $discoveries['pending']->sortByDesc(function ($discovery) {
            return $discovery->priorityBadge ? $discovery->priorityBadge->level : 0;
        });

        return view('dashboard', compact('discoveries', 'workGroups', 'selectedWorkGroupId'));
    })->name('dashboard');

    // Discovery routes
    Route::get('/discovery', [DiscoveryController::class, 'index'])->name('discovery');
    Route::get('/discovery/{discovery}', [DiscoveryController::class, 'show'])->name('discovery.show');
    Route::post('/discovery', [DiscoveryController::class, 'store'])->name('discovery.store');
    Route::patch('/discovery/{discovery}', [DiscoveryController::class, 'update'])->name('discovery.update');
    Route::patch('/discovery/{discovery}/address', [DiscoveryController::class, 'updateAddress'])->name('discovery.update-address');
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

    // Get assignable employees for company admin (moved to API routes)


    // Company Admin Management Routes
    Route::post('/company/admins', [CompanyController::class, 'createAdmin'])->name('company.create-admin');
    Route::patch('/company/employees/{employee}/promote', [CompanyController::class, 'promoteToAdmin'])->name('company.promote-admin');
    Route::patch('/company/admins/{admin}/demote', [CompanyController::class, 'demoteFromAdmin'])->name('company.demote-admin');
    Route::patch('/company/transfer-primary-admin', [CompanyController::class, 'transferPrimaryAdmin'])->name('company.transfer-primary-admin');

    // Property Management Routes
    Route::resource('properties', PropertyController::class);
    Route::get('/api/districts', [PropertyController::class, 'getDistricts'])->name('api.districts');
    Route::get('/api/neighborhoods', [PropertyController::class, 'getNeighborhoods'])->name('api.neighborhoods'); // Deprecated
    Route::get('/api/neighborhoods-for-district', [PropertyController::class, 'getNeighborhoodsForDistrict'])->name('api.neighborhoods-for-district');
    Route::get('/api/company-properties', [PropertyController::class, 'getCompanyProperties'])->name('api.company-properties');

    // Company Sites Management Routes
    Route::get('/company-sites', [CompanySiteController::class, 'indexView'])->name('company-sites.index');
    Route::get('/api/company-sites', [CompanySiteController::class, 'index'])->name('api.company-sites');
    Route::post('/api/company-sites', [CompanySiteController::class, 'store'])->name('api.company-sites.store');
    Route::delete('/api/company-sites/{companySite}', [CompanySiteController::class, 'destroy'])->name('api.company-sites.destroy');
    Route::get('/api/combined-neighborhoods', [CompanySiteController::class, 'getCombinedNeighborhoods'])->name('api.combined-neighborhoods');

    // Payment Method Management Routes
    Route::resource('payment-methods', PaymentMethodController::class);
    Route::get('/api/payment-methods', [PaymentMethodController::class, 'getAccessiblePaymentMethods'])->name('api.payment-methods');

    // Priority Management Routes
    Route::resource('priorities', PriorityController::class);

    // Transaction Logs Routes (for admins to view activity)
    Route::get('/transaction-logs', [DiscoveryController::class, 'transactionLogs'])->name('transaction-logs');
    Route::post('/transaction-logs/cleanup', [DiscoveryController::class, 'cleanupTransactionLogs'])->name('transaction-logs.cleanup');

    Route::post('/logout', [AuthenticationController::class, 'logout'])->name('logout');
});

Route::get('/shared/discovery/{token}', [DiscoveryController::class, 'sharedView'])
    ->name('discovery.shared');

// Public routes for customer approval/rejection from shared discovery
Route::post('/shared/discovery/{token}/approve', [DiscoveryController::class, 'customerApprove'])
    ->name('discovery.customer-approve');
Route::post('/shared/discovery/{token}/reject', [DiscoveryController::class, 'customerReject'])
    ->name('discovery.customer-reject');

// Test route that bypasses authentication
Route::get('/test-properties', function () {
    // Simulate authentication for testing
    $user = \App\Models\User::where('company_id', 1)->first();
    if ($user) {
        auth()->login($user);
    }
    $properties = collect(); // Empty collection for testing
    return view('property.index', compact('properties'));
});
