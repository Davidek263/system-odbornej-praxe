<?php

use App\Http\Controllers\AuthController;
use App\Models\Person;
use App\Models\Company;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register-person', [AuthController::class, 'registerPerson']);
Route::post('/register-company', [AuthController::class, 'registerCompany']);
Route::post('/login-person', [AuthController::class, 'loginPerson']);
Route::post('/login-company', [AuthController::class, 'loginCompany']);

// Protected routes (token required)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Example of protected endpoints
    Route::get('/persons', function() { return Person::all(); });
    Route::get('/companies', function() { return Company::all(); });
});
