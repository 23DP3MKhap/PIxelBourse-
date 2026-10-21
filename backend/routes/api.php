<?php

use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TradeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClickerController;
use App\Http\Controllers\ImageController;

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

Route::get('/users/{user}', [ProfileController::class, 'showById'])->whereNumber('user');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('user', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('user/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('user', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::apiResource('users', AdminUserController::class)->except('store');
    });
});

Route::get('listings', [ListingController::class, 'index'])->name('listings.index');
Route::get('listings/{listing}', [ListingController::class, 'show'])->whereNumber('listing')->name('listings.show');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('my-listings', [ListingController::class, 'mine'])->name('listings.mine');
    Route::post('listings', [ListingController::class, 'store'])->name('listings.store');
    Route::patch('listings/{listing}', [ListingController::class, 'update'])->whereNumber('listing')->name('listings.update');
    Route::delete('listings/{listing}', [ListingController::class, 'destroy'])->whereNumber('listing')->name('listings.destroy');
    Route::post('listings/{listing}/buy', [ListingController::class, 'buy'])->whereNumber('listing')->name('listings.buy');

    Route::get('trades', [TradeController::class, 'index'])->name('trades.index');
    Route::post('trades', [TradeController::class, 'store'])->name('trades.store');
    Route::get('trades/{trade}', [TradeController::class, 'show'])->whereNumber('trade')->name('trades.show');
    Route::post('trades/{trade}/accept', [TradeController::class, 'accept'])->whereNumber('trade')->name('trades.accept');
    Route::post('trades/{trade}/decline', [TradeController::class, 'decline'])->whereNumber('trade')->name('trades.decline');
    Route::post('trades/{trade}/cancel', [TradeController::class, 'cancel'])->whereNumber('trade')->name('trades.cancel');

    Route::get('history', HistoryController::class)->name('history');
});

// clicker
Route::get('/images', [ImageController::class, 'index']);
Route::get('/images/{id}', [ImageController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-images', [ImageController::class, 'mine']);
    Route::post('/images', [ImageController::class, 'store']);
    Route::post('/images/{id}', [ImageController::class, 'update']);
    Route::delete('/images/{id}', [ImageController::class, 'destroy']);
    Route::post('/clicker/sync', [ClickerController::class, 'sync'])->middleware('throttle:30,1');
});
