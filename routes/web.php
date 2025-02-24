<?php

use App\Http\Controllers\CustomVerificationController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email/verify/{id}/{hash}', [CustomVerificationController::class, 'verify'])
    ->name('verification.verify');