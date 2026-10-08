<?php

use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

/*
| All routes here are prefixed with /api. Authenticated routes expect the
| header "Authorization: Bearer <token>" (token comes from register/login).
*/

Route::prefix('auth')->name('auth.')->group(function () {
    Route::middleware('throttle:6,1')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->name('register');
        Route::post('login', [AuthController::class, 'login'])->name('login');
    });

    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('user', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('user/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('user', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::apiResource('users', AdminUserController::class)->except('store');
    });
});
