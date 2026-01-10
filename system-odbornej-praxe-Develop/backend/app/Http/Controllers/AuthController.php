<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Address;
use App\Models\Company;
use App\Mail\PasswordMail;
use Illuminate\Support\Str;
use App\Mail\ActivationMail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // ======================================
    // REGISTER STUDENT
    // ======================================
    public function registerStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'student_email' => [
                'required',
                'email',
                'unique:users,student_email',
                'regex:/^[a-z]+\.[a-z]+@student\.ukf\.sk$/', // Enforce UKF student email pattern
            ],
            'alternative_email' => 'nullable|email|max:100',
            'phone_number' => 'nullable|string|max:25',
            'study_field_id' => 'required|exists:study_field,id',
            
            // Address fields (all required)
            'street' => 'required|string|max:100',
            'street_number' => 'required|string|max:20',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create address
            $address = Address::create([
                'street' => $request->street,
                'street_number' => $request->street_number,
                'city' => $request->city,
                'postal_code' => $request->postal_code,
                'country' => $request->country ?? 'Slovakia',
            ]);

            // Generate temporary password
            $temporaryPassword = Str::random(12);
            
            $emailToSend = $request->filled('alternative_email')
                ? $request->alternative_email
                : $request->student_email;

            // Send temporary password
            Mail::to($emailToSend)->send(new PasswordMail($temporaryPassword));

            // Get student role
            $studentRole = DB::table('roles')->where('role_name', 'student')->first();

            // Create user account  
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->student_email, // Primary email is student email
                'student_email' => $request->student_email,
                'alternative_email' => $request->alternative_email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($temporaryPassword),
                'study_field_id' => $request->study_field_id,
                'address_id' => $address->id,
                'roles_id' => $studentRole->id,
                'active' => false, // Inactive until email verified
                'must_change_password' => true, // Must change password on first login
            ]);

            // Generate activation token
            $activationToken = Str::random(64);
            $user->update([
                'activation_token' => $activationToken,
                'activation_token_expires_at' => now()->addHours(48), // 48 hour expiry
            ]);

            Mail::to($emailToSend)->send(new ActivationMail($request, $activationToken));

            // Send activation email with temporary password
            // TODO: Implement email sending
            // Mail::to($user->student_email)->send(new StudentActivationMail($user, $temporaryPassword, $activationToken));

            DB::commit();

            return response()->json([
                'message' => 'Registration successful. Please check your email to activate your account and receive your temporary password.',
                'user_id' => $user->id,
                // For development/testing only - remove in production
                'temp_password' => $temporaryPassword,
                'activation_token' => $activationToken,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Registration failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ======================================
    // ACTIVATE STUDENT ACCOUNT
    // ======================================
  public function activateAccount(Request $request)
{
    $token = $request->query('token');

    $user = User::where('activation_token', $token)->first();

    if (!$user) {
        return response()->json(['message' => 'Invalid activation link.'], 400);
    }

    if ($user->activation_token_expires_at < now()) {
        return response()->json(['message' => 'Activation link expired.'], 400);
    }

    // Activate user
    $user->update([
        'active' => true,
        'activation_token' => null,
        'activation_token_expires_at' => null,
    ]);

    // Redirect to set-password page on frontend
    return redirect()->away(env('FRONTEND_URL') . '/set-password?email=' . urlencode($user->email) . '&activated=1');

}
public function setInitialPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'temporary_password' => 'required|string',
        'password' => 'required|string|min:8|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422);
    }

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'User not found.'], 404);
    }

    // Check temporary password
    if (!Hash::check($request->temporary_password, $user->password)) {
        return response()->json(['message' => 'Temporary password is incorrect.'], 400);
    }

    // Update password
    $user->update([
        'password' => Hash::make($request->password),
        'must_change_password' => false,
        'password_changed_at' => now(),
    ]);

    return response()->json(['message' => 'Password updated successfully.'], 200);
}



    // ======================================
    // REGISTER COMPANY
    // ======================================
    public function registerCompany(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:100',
            'contact_person_name' => 'required|string|max:100',
            'contact_person_last_name' => 'required|string|max:100',
            'contact_person_email' => 'required|email|max:100|unique:company,contact_person_email',
            'contact_person_phone' => 'required|string|max:25',
            
            // Company address fields (all required)
            'street' => 'required|string|max:100',
            'street_number' => 'required|string|max:20',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create address
            $address = Address::create([
                'street' => $request->street,
                'street_number' => $request->street_number,
                'city' => $request->city,
                'postal_code' => $request->postal_code,
                'country' => $request->country ?? 'Slovakia',
            ]);

            // Create company
            $company = Company::create([
                'company_name' => $request->company_name,
                'contact_person_name' => $request->contact_person_name,
                'contact_person_email' => $request->contact_person_email,
                'contact_person_phone' => $request->contact_person_phone,
                'address_id' => $address->id,
            ]);

            // Get company role
            $companyRole = DB::table('roles')->where('role_name', 'company')->first();

            // Create user account for company
            $user = User::create([
                'first_name' => $request->contact_person_name,
                'last_name' => $request->contact_person_last_name, // Company representative
                'email' => $request->contact_person_email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->contact_person_phone,
                'company_id' => $company->id,
                'roles_id' => $companyRole->id,
                'active' => false, // Inactive until email verified
                'must_change_password' => true, // They set password during registration
            ]);

            // Generate activation token
            $activationToken = Str::random(64);
            $user->update([
                'activation_token' => $activationToken,
                'activation_token_expires_at' => now()->addHours(48), // 48 hour expiry
            ]);

            // Send activation email
            // TODO: Implement email sending
            // Mail::to($user->email)->send(new CompanyActivationMail($user, $activationToken));

            DB::commit();

            return response()->json([
                'message' => 'Company registration successful. Please check your email to activate your account.',
                'company_id' => $company->id,
                // For development/testing only - remove in production
                'activation_token' => $activationToken,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Registration failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ======================================
    // LOGIN
    // ======================================
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Find user by email or student_email
        $user = User::where('email', $request->email)
            ->orWhere('student_email', $request->email)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // Check if account is active
        if (!$user->active) {
            return response()->json([
                'message' => 'Account is not activated. Please check your email for activation link.',
            ], 403);
        }

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Load relationships
        $user->load('role', 'studyField', 'company.address', 'address');

        return response()->json([
            'message' => 'Login successful.',
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
                'company' => $user->company ? [
                    'id' => $user->company->id,
                    'company_name' => $user->company->company_name,
                    'contact_person_name' => $user->company->contact_person_name,
                    'contact_person_email' => $user->company->contact_person_email,
                    'contact_person_phone' => $user->company->contact_person_phone,
                    'address' => $user->company->address,
                ] : null,
                'address' => $user->address ?? null,
                'must_change_password' => $user->must_change_password,
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

   // ======================================
// FORGOT PASSWORD (Request Reset)
// ======================================
public function forgotPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422);
    }

    // Find user by primary or student email
    $user = User::where('email', $request->email)
        ->orWhere('student_email', $request->email)
        ->first();

    $genericMessage = 'If the email exists in our system, a password reset link has been sent.';

    if (!$user) {
        return response()->json(['message' => $genericMessage], 200);
    }

    // Generate token
    $token = Str::random(64);

    // Save token
    DB::table('password_resets')->updateOrInsert(
        ['email' => $user->email],
        [
            'token' => $token,
            'created_at' => now(),
        ]
    );

    // Build frontend reset URL
    $frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');

$resetUrl = $frontendUrl
    . '/set-password?token=' . $token
    . '&email=' . urlencode($user->email);


    // Send email
    Mail::send('emails.password_reset', ['url' => $resetUrl], function ($message) use ($user) {
        $message->to($user->email);
        $message->subject('Obnovenie hesla');
    });

    return response()->json([
        'message' => $genericMessage,
    ], 200);
}


    // ======================================
    // RESET PASSWORD
    // ======================================
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Verify token
        $resetRecord = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$resetRecord) {
            return response()->json([
                'message' => 'Invalid or expired reset token.',
            ], 400);
        }

        // Check if token is expired (24 hours)
        if (now()->diffInHours($resetRecord->created_at) > 24) {
            DB::table('password_resets')->where('email', $request->email)->delete();
            return response()->json([
                'message' => 'Reset token has expired.',
            ], 400);
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
            'must_change_password' => false,
        ]);

        // Delete reset token
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Password reset successfully. You can now log in with your new password.',
        ], 200);
    }
    

    // ======================================
    // CHANGE PASSWORD (Authenticated)
    // ======================================
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 400);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
            'must_change_password' => false,
        ]);

        return response()->json([
            'message' => 'Password changed successfully.',
        ], 200);
    }

    // ======================================
    // LOGOUT
    // ======================================
    public function logout(Request $request)
    {
        $user = $request->user();
        
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ], 200);
    }

    // ======================================
    // GET STUDY FIELDS (Public endpoint for registration form)
    // ======================================
    public function getStudyFields()
    {
        $studyFields = DB::table('study_field')
            ->select('id', 'study_field_name', 'abbreviation')
            ->orderBy('study_field_name')
            ->get();

        return response()->json([
            'study_fields' => $studyFields,
        ], 200);
    }

    // ======================================
    // RESEND ACTIVATION EMAIL
    // ======================================
    public function resendActivation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)
            ->orWhere('student_email', $request->email)
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'If the email exists, an activation link has been sent.',
            ], 200);
        }

        if ($user->active) {
            return response()->json([
                'message' => 'Account is already activated.',
            ], 400);
        }

        // Generate new activation token
        $activationToken = Str::random(64);
        $user->update([
            'activation_token' => $activationToken,
            'activation_token_expires_at' => now()->addHours(48),
        ]);

        // Resend activation email
        // TODO: Implement email sending
        // Mail::to($user->email)->send(new ActivationMail($user, $activationToken));

        return response()->json([
            'message' => 'If the email exists, an activation link has been sent.',
            // For development/testing only - remove in production
            'activation_token' => $activationToken,
        ], 200);
    }
}