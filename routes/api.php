<?php

use App\Infrastructure\Http\Controllers\AuthController;
use App\Infrastructure\Http\Controllers\FavoriteGifController;
use App\Infrastructure\Http\Controllers\GifController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/gifs/search', [GifController::class, 'search']);
    Route::get('/gifs/{id}', [GifController::class, 'show']);
    Route::post('/favorites', [FavoriteGifController::class, 'store']);
});
