<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register-person', [AuthController::class, 'registerUser']);
Route::post('/register-company', [AuthController::class, 'registerCompany']);
Route::post('/login-person', [AuthController::class, 'loginUser']);
Route::post('/login-company', [AuthController::class, 'loginCompany']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/persons', fn() => \App\Models\Person::all());
    Route::get('/companies', fn() => \App\Models\Company::all());
});
