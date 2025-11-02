<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InternshipController;

/*
|--------------------------------------------------------------------------
| Authentication Routes (Public)
|--------------------------------------------------------------------------
*/

// Registration
Route::post('/register-student', [AuthController::class, 'registerStudent']);
Route::post('/register-company', [AuthController::class, 'registerCompany']);

// Account Activation
Route::post('/activate-account', [AuthController::class, 'activateAccount']);
Route::post('/resend-activation', [AuthController::class, 'resendActivation']);

// Login
Route::post('/login', [AuthController::class, 'login']);

// Password Reset
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Public data
Route::get('/study-fields', [AuthController::class, 'getStudyFields']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Change Password
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // Get current user
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $user->load('role', 'studyField', 'company', 'address');
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'student_email' => $user->student_email,
                'alternative_email' => $user->alternative_email,
                'phone_number' => $user->phone_number,
                'role_id' => $user->roles_id,
                'role_name' => $user->role->role_name ?? null,
                'study_field' => $user->studyField ?? null,
                'company' => $user->company ?? null,
                'address' => $user->address ?? null,
                'must_change_password' => $user->must_change_password,
                'active' => $user->active,
            ],
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | Company Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('company-internships')->group(function () {
        // Get all internships for company
        Route::get('/{companyId}', [InternshipController::class, 'getCompanyInternships']);
    });

    /*
    |--------------------------------------------------------------------------
    | Internship Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('internships')->group(function () {
        // Get single internship
        Route::get('/{id}', [InternshipController::class, 'getInternship']);
        
        // Company actions
        Route::post('/{id}/confirm', [InternshipController::class, 'confirmInternship']);
        Route::post('/{id}/reject', [InternshipController::class, 'rejectInternship']);
    });

    /*
    |--------------------------------------------------------------------------
    | Student Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('student-internships')->group(function () {
        // Get all internships for student
        Route::get('/', [InternshipController::class, 'getStudentInternships']);
    });

    /*
    |--------------------------------------------------------------------------
    | Guarantor Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('guarantor-internships')->group(function () {
        // Get all internships with filters
        Route::get('/', [InternshipController::class, 'getGuarantorInternships']);
    });
});