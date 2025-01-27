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
    Route::get('/user', [AuthController::class, 'user'])->name('user.details')->middleware('throttle:api');
    Route::post('/user/update', [AuthController::class, 'update'])->name('user.update')->middleware('throttle:api');
    Route::delete('/user/delete', [AuthController::class, 'delete'])->name('delete')->middleware('throttle:api');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/user-preferences', [ArticleController::class, 'preferences'])->name('preferences')->middleware('throttle:api');
});

Route::get('/articles', [ArticleController::class, 'index'])->middleware(['throttle:api'])->name('articles.index');
Route::get('/articles/{article}', [ArticleController::class, 'show'])->middleware(['throttle:api'])->name('articles.show');
Route::get('/authors', [ArticleController::class, 'authors'])->middleware(['throttle:api'])->name('authors.index');
Route::get('/categories', [ArticleController::class, 'categories'])->middleware(['throttle:api'])->name('categories.index');
Route::get('/sources', [ArticleController::class, 'sources'])->middleware(['throttle:api'])->name('sources.index');