<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController\CategoryController;



Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::middleware('auth:sanctum')->get('/me', [UserController::class, 'getMe']);
Route::post('/upload-image', [UserController::class, 'uploadImage']);
Route::post('/upload-video', [UserController::class, 'uploadVideo']);


// Route::get('/categories', [CategoryController::class, 'index']);
// Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::post('/categories', [CategoryController::class, 'create']);
