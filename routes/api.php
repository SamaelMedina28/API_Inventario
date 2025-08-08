<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});

Route::middleware(['auth'])->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/logout', 'logout');
        Route::get('/user', 'getUser');
    });

    Route::apiResource('categories', CategoryController::class);
});
