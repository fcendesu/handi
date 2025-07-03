<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\DiscoveryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\CompanySiteController;
// use App\Http\Controllers\PostController;
use App\Http\Controllers\WorkGroupController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\PropertyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//   return $request->user();
//})->middleware('auth:sanctum');

Route::get('/test', function () {

});

// Route::get('/posts', [PostController::class, 'index'])->middleware('auth:sanctum');

Route::post('/register', [AuthenticationController::class, 'register']);
Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('/logout', [AuthenticationController::class, 'logout'])->middleware('auth:sanctum');

// Route::post('/post/store', [PostController::class, 'store'])->middleware('auth:sanctum');

Route::post('/item/store', [ItemController::class, 'store'])->middleware('auth:sanctum');
Route::get('/item/index', [ItemController::class, 'index'])->middleware('auth:sanctum');
Route::get('/item/show/{item}', [ItemController::class, 'show'])->middleware('auth:sanctum');

Route::put('/item/update/{item}', [ItemController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/item/destroy/{item}', [ItemController::class, 'destroy'])->middleware('auth:sanctum');

Route::get('/token/validate', [AuthenticationController::class, 'validateToken'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // Discovery API routes
    Route::get('/discoveries/list', [DiscoveryController::class, 'apiList']);
    Route::post('/discoveries', [DiscoveryController::class, 'apiStore']);
    Route::get('/discoveries/{discovery}', [DiscoveryController::class, 'apiShow']);
    Route::put('/discoveries/{discovery}', [DiscoveryController::class, 'apiUpdate']);
    Route::delete('/discoveries/{discovery}', [DiscoveryController::class, 'apiDestroy']);
    Route::patch('/discoveries/{discovery}/status', [DiscoveryController::class, 'apiUpdateStatus']);
    Route::post('/discoveries/{discovery}/assign', [DiscoveryController::class, 'assignToSelf']);
    Route::delete('/discoveries/{discovery}/assign', [DiscoveryController::class, 'unassignFromSelf']);
    Route::get('/discoveries/{discovery}/share', [DiscoveryController::class, 'apiGetShareUrl']);

    // Payment Methods API routes
    Route::get('/payment-methods/accessible', [PaymentMethodController::class, 'getAccessiblePaymentMethods']);

    // Work Group API routes
    Route::get('/work-groups', [WorkGroupController::class, 'apiList']);
    Route::post('/work-groups', [WorkGroupController::class, 'apiStore']);
    Route::get('/work-groups/{workGroup}', [WorkGroupController::class, 'apiShow']);

    // Company API routes
    Route::get('/company', [CompanyController::class, 'apiShow']);
    Route::post('/company/employees', [CompanyController::class, 'apiCreateEmployee']);
    Route::patch('/company/employees/{employee}', [CompanyController::class, 'apiUpdateEmployee']);
    Route::delete('/company/employees/{employee}', [CompanyController::class, 'apiDeleteEmployee']);

    // Property API routes
    Route::get('/properties', [PropertyController::class, 'apiList']);
    Route::post('/properties', [PropertyController::class, 'apiStore']);

    // Address data routes (specific routes must come BEFORE parameterized routes)
    Route::get('/properties/cities/list', [PropertyController::class, 'apiGetCities']);
    Route::get('/properties/districts/{city}', [PropertyController::class, 'apiGetDistricts']);
    Route::get('/properties/neighborhoods/{city}/{district}', [PropertyController::class, 'apiGetNeighborhoods']);
    Route::get('/properties/combined-neighborhoods', [CompanySiteController::class, 'getCombinedNeighborhoods']);

    // Property CRUD routes with {property} parameter (these must come LAST)
    Route::get('/properties/{property}', [PropertyController::class, 'apiShow']);
    Route::put('/properties/{property}', [PropertyController::class, 'apiUpdate']);
    Route::delete('/properties/{property}', [PropertyController::class, 'apiDestroy']);

    // Combined neighborhoods API route (alternative endpoint - moved above for compatibility)
    Route::get('/combined-neighborhoods', [CompanySiteController::class, 'getCombinedNeighborhoods']);
});
