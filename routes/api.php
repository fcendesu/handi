<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PostController;
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
