<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrtController;
use Illuminate\Support\Facades\Request;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// Protected routes (ensure you have configured the jwt.auth middleware)
Route::group(['middleware' => ['jwt.auth']], function () {
    
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('email/resend', [AuthController::class, 'resendVerificationEmail']);

    Route::group(['middleware' => ['verified']], function(){
        Route::post('/brts', [BrtController::class, 'store']);
        Route::get('/brts', [BrtController::class, 'index']);
        Route::get('/brts/{id}', [BrtController::class, 'show']);
        Route::put('/brts/{id}', [BrtController::class, 'update']);
        Route::delete('/brts/{id}', [BrtController::class, 'destroy']);
    });
});
