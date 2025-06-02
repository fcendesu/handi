<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\DiscoveryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\WorkGroupController;
use App\Http\Controllers\CompanyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//   return $request->user();
//})->middleware('auth:sanctum');

Route::get('/test', function () {

});

Route::get('/posts', [PostController::class, 'index'])->middleware('auth:sanctum');

Route::post('/register', [AuthenticationController::class, 'register']);
Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('/logout', [AuthenticationController::class, 'logout'])->middleware('auth:sanctum');

Route::post('/post/store', [PostController::class, 'store'])->middleware('auth:sanctum');

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
    Route::patch('/discoveries/{discovery}/status', [DiscoveryController::class, 'apiUpdateStatus']);
    Route::post('/discoveries/{discovery}/assign', [DiscoveryController::class, 'assignToSelf']);
    Route::delete('/discoveries/{discovery}/assign', [DiscoveryController::class, 'unassignFromSelf']);
    Route::get('/discoveries/{discovery}/share', [DiscoveryController::class, 'apiGetShareUrl']);

    // Work Group API routes
    Route::get('/work-groups', [WorkGroupController::class, 'apiList']);
    Route::post('/work-groups', [WorkGroupController::class, 'apiStore']);
    Route::get('/work-groups/{workGroup}', [WorkGroupController::class, 'apiShow']);

    // Company API routes
    Route::get('/company', [CompanyController::class, 'apiShow']);
    Route::post('/company/employees', [CompanyController::class, 'apiCreateEmployee']);
    Route::patch('/company/employees/{employee}', [CompanyController::class, 'apiUpdateEmployee']);
    Route::delete('/company/employees/{employee}', [CompanyController::class, 'apiDeleteEmployee']);
});
