<?php

use AdminExtAuth\Http\Controllers;
use Illuminate\Support\Facades\Route;

if (config('admin.auth.enable', true)) {
    Route::get('auth/login', Controllers\AuthController::class . '@getLogin');
    Route::post('auth/login', Controllers\AuthController::class . '@postLogin');
    Route::get('auth/setting', Controllers\AuthController::class . '@getSetting');
    Route::put('auth/setting', Controllers\AuthController::class . '@putSetting');
    Route::resource('auth/users', Controllers\AdminUserController::class);
}
