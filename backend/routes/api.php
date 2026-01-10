<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InternshipController;
use App\Http\Controllers\ExternalSystemTokenController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Registration
Route::post('/register-student', [AuthController::class, 'registerStudent']);
Route::post('/register-company', [AuthController::class, 'registerCompany']);

// Account Activation
Route::get('/activate-account', [AuthController::class, 'activateAccount']);
Route::post('/resend-activation', [AuthController::class, 'resendActivation']);
Route::post('/set-initial-password', [AuthController::class, 'setInitialPassword']);

// Login
Route::post('/login', [AuthController::class, 'login']);

// Password Reset
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Public Data
Route::get('/study-fields', [AuthController::class, 'getStudyFields']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Require Authentication)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    */
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Change Password
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // Get Current User
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
    | Student Routes
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('student')->group(function () {
        // Get companies for student (no role check)
        Route::get('/companies', [InternshipController::class, 'getCompaniesForStudent']);
    });
    
    Route::prefix('student-internships')->group(function () {
        // Get all internships for student
        Route::get('/{studentId}', [InternshipController::class, 'getStudentInternships']);
    });
    
    Route::get('/internships/{id}/generate-dohoda', [InternshipController::class, 'generateDohoda'])
        ->middleware('auth:sanctum')
        ->name('internships.generate-dohoda');

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
    | Internship Routes (Student & Company Actions)
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('internships')->group(function () {
        // Get single internship
        Route::get('/{id}', [InternshipController::class, 'getInternship']);
        
        // Student: Create & Update (only "Vytvorená" status)
        Route::post('/', [InternshipController::class, 'store']);
        Route::put('/{id}', [InternshipController::class, 'updateStudentInternship']);
        
        // Company: Confirm or Reject internship
        Route::post('/{id}/confirm', [InternshipController::class, 'confirmInternship']);
        Route::post('/{id}/reject', [InternshipController::class, 'rejectInternship']);
    });

    /*
    |--------------------------------------------------------------------------
    | Document/Timesheet Routes
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('documents')->group(function () {
        // Company: Approve or Reject timesheet (FR-08)
        Route::post('/{id}/approve-timesheet', [InternshipController::class, 'approveTimesheet']);
        Route::post('/{id}/reject-timesheet', [InternshipController::class, 'rejectTimesheet']);
    });

    /*
    |--------------------------------------------------------------------------
    | Guarantor Routes
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('guarantor')->group(function () {
        // Get all internships with filters
        Route::get('/internships', [InternshipController::class, 'getGuarantorInternships']);
        
        // Update internship (full access)
        Route::put('/internships/{id}', [InternshipController::class, 'updateInternship']);
        
        // Change internship status
        Route::post('/internships/{id}/change-status', [InternshipController::class, 'changeInternshipStatus']);
        
        // Get students and companies for dropdowns
        Route::get('/students', [InternshipController::class, 'getAllStudents']);
        Route::get('/companies', [InternshipController::class, 'getAllCompanies']);

        Route::get('/external-system-tokens', [ExternalSystemTokenController::class, 'index']);
        Route::post('/external-system-tokens', [ExternalSystemTokenController::class, 'store']);
        Route::delete('/external-system-tokens/{id}', [ExternalSystemTokenController::class, 'destroy']);
    });
    
    // Legacy route (kept for backwards compatibility)
    Route::prefix('guarantor-internships')->group(function () {
        Route::get('/', [InternshipController::class, 'getGuarantorInternships']);
    });
});

/*
|--------------------------------------------------------------------------
| External System API (OAuth2 – Sanctum)
| Note: Ability check is done in controller for better error messages
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/external/mark-defended/{id}', [InternshipController::class, 'markDefendedExternal']);
});