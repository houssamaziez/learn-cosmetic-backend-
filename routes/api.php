<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController\CategoryController;
use App\Http\Controllers\Api\PlaylistController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\PromotionController;



Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::middleware('auth:sanctum')->get('/me', [UserController::class, 'getMe']);
Route::post('/upload-image', [UserController::class, 'uploadImage']);
Route::post('/upload-video', [UserController::class, 'uploadVideo']);


Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'create']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);



Route::get('/playlists', [PlaylistController::class, 'index']);
Route::post('/playlists', [PlaylistController::class, 'create']);
Route::get('/playlists/{playlist}', [PlaylistController::class, 'show']);
Route::get('/playlists/category/{category}', [PlaylistController::class, 'getByCategory']);



Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{id}', [CourseController::class, 'show']);
Route::post('/courses', [CourseController::class, 'store']);
Route::put('/courses/{id}', [CourseController::class, 'update']);
Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

Route::get('/courses/playlist/{playlistId}', [CourseController::class, 'getAllCoursesByPlaylist']);


Route::get('/search', [SearchController::class, 'search']);



Route::get('/promotions', [PromotionController::class, 'index']);
Route::post('/promotions', [PromotionController::class, 'store']);
