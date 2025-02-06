<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//   return $request->user();
//})->middleware('auth:sanctum');

Route::get('/test', function () {

});

Route::post('/register', [AuthenticationController::class, 'register']);
Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('/logout', [AuthenticationController::class, 'logout'])->middleware('auth:sanctum');

Route::post('/post/store', [PostController::class, 'store'])->middleware('auth:sanctum');

