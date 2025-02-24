<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrtController;
use Illuminate\Support\Facades\Request;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('email/resend', function(Request $request){
    $request->user()->sendEmailVerificationNotification();
    return response()->json([
        'success' => true,
        'message' => 'Email verified successfully!'
    ]);
});

// Protected routes (ensure you have configured the jwt.auth middleware)
Route::group(['middleware' => ['jwt.auth', 'verified']], function () {
    Route::post('/brts', [BrtController::class, 'store']);
    Route::get('/brts', [BrtController::class, 'index']);
    Route::get('/brts/{id}', [BrtController::class, 'show']);
    Route::put('/brts/{id}', [BrtController::class, 'update']);
    Route::delete('/brts/{id}', [BrtController::class, 'destroy']);
});
