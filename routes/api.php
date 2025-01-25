<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\ArticleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('throttle:api');
Route::post('/password/email', [AuthController::class, 'forgotPassword'])->name('password.email');
Route::post('/password/reset', [AuthController::class, 'resetPassword'])->middleware('signed')->name('password.reset');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user'])->name('user')->middleware('throttle:api');
    Route::post('/user/update', [AuthController::class, 'update'])->name('update')->middleware('throttle:api');
    Route::delete('/user/delete', [AuthController::class, 'delete'])->name('delete')->middleware('throttle:api');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/preferences', [ArticleController::class, 'preferences'])->name('preferences')->middleware('throttle:api');
});

Route::get('/articles', [ArticleController::class, 'index'])->middleware(['throttle:api']);
Route::get('/articles/{article}', [ArticleController::class, 'show'])->middleware(['throttle:api']);