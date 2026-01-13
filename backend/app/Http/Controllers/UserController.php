<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Company;
use App\Mail\EmailChangeVerificationMail;
use App\Mail\EmailChangedConfirmationMail;

class UserController extends Controller
{
    /**
     * Request student email change (sends verification email to old email)
     */
    public function requestStudentEmailChange(Request $request)
    {
        $user = $request->user();

        // Verify user is a student
        if (!$user->isStudent()) {
            return response()->json([
                'message' => 'Forbidden. Only students can update student email.',
            ], 403);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'new_email' => [
                'required',
                'email',
                'max:100',
                'unique:users,student_email,' . $user->id,
                'regex:/^[a-z]+\.[a-z]+@student\.ukf\.sk$/',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Delete any existing pending requests for this user
            DB::table('email_change_requests')
                ->where('user_id', $user->id)
                ->delete();

            // Generate token
            $token = Str::random(64);

            // Create email change request
            DB::table('email_change_requests')->insert([
                'user_id' => $user->id,
                'old_email' => $user->email,
                'new_email' => $request->new_email,
                'email_type' => 'student',
                'token' => $token,
                'expires_at' => now()->addHours(24),
                'created_at' => now(),
            ]);

            // Send verification email to OLD email
            Mail::to($user->email)->send(
                new EmailChangeVerificationMail($user, $request->new_email, $token, 'student')
            );

            return response()->json([
                'message' => 'Verifikačný email bol odoslaný na váš aktuálny email.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to request email change.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update alternative email for students
     */
    public function updateAlternativeEmail(Request $request)
    {
        $user = $request->user();

        // Verify user is a student
        if (!$user->isStudent()) {
            return response()->json([
                'message' => 'Forbidden. Only students can update alternative email.',
            ], 403);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'alternative_email' => 'nullable|email|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Update alternative email
            $user->update([
                'alternative_email' => $request->alternative_email,
            ]);

            // Load relationships
            $user->load('role', 'studyField', 'company');

            return response()->json([
                'message' => 'Alternative email updated successfully.',
                'user' => $user,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update alternative email.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Request company email change (sends verification email to old email)
     */
    public function requestCompanyEmailChange(Request $request)
    {
        $user = $request->user();

        // Verify user is a company
        if (!$user->isCompany()) {
            return response()->json([
                'message' => 'Forbidden. Only companies can update company email.',
            ], 403);
        }

        // Verify user has associated company record
        if (!$user->company) {
            return response()->json([
                'message' => 'Company record not found.',
            ], 404);
        }

        // Validation - must be unique in both tables
        $validator = Validator::make($request->all(), [
            'new_email' => [
                'required',
                'email',
                'max:100',
                'unique:users,email,' . $user->id,
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Additional validation: check uniqueness in company table
        $emailExistsInCompany = Company::where('contact_person_email', $request->new_email)
            ->where('id', '!=', $user->company_id)
            ->exists();

        if ($emailExistsInCompany) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => [
                    'new_email' => ['This email is already used by another company.'],
                ],
            ], 422);
        }

        try {
            // Delete any existing pending requests for this user
            DB::table('email_change_requests')
                ->where('user_id', $user->id)
                ->delete();

            // Generate token
            $token = Str::random(64);

            // Create email change request
            DB::table('email_change_requests')->insert([
                'user_id' => $user->id,
                'old_email' => $user->email,
                'new_email' => $request->new_email,
                'email_type' => 'company',
                'token' => $token,
                'expires_at' => now()->addHours(24),
                'created_at' => now(),
            ]);

            // Send verification email to OLD email
            Mail::to($user->email)->send(
                new EmailChangeVerificationMail($user, $request->new_email, $token, 'company')
            );

            return response()->json([
                'message' => 'Verifikačný email bol odoslaný na váš aktuálny email.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to request email change.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify email change (public route - no authentication required)
     */
    public function verifyEmailChange(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return view('email_change_redirect', [
                'redirectUrl' => env('FRONTEND_URL') . '/login?email_change_error=invalid'
            ]);
        }

        // Find the email change request
        $changeRequest = DB::table('email_change_requests')
            ->where('token', $token)
            ->first();

        // Check if request exists
        if (!$changeRequest) {
            return view('email_change_redirect', [
                'redirectUrl' => env('FRONTEND_URL') . '/login?email_change_error=invalid'
            ]);
        }

        // Check if token has expired
        if (now()->isAfter($changeRequest->expires_at)) {
            DB::table('email_change_requests')->where('id', $changeRequest->id)->delete();
            return view('email_change_redirect', [
                'redirectUrl' => env('FRONTEND_URL') . '/login?email_change_error=expired'
            ]);
        }

        // Find the user
        $user = User::find($changeRequest->user_id);

        if (!$user) {
            DB::table('email_change_requests')->where('id', $changeRequest->id)->delete();
            return view('email_change_redirect', [
                'redirectUrl' => env('FRONTEND_URL') . '/login?email_change_error=user_not_found'
            ]);
        }

        DB::beginTransaction();

        try {
            $oldEmail = $changeRequest->old_email;
            $newEmail = $changeRequest->new_email;

            // Update email based on type
            if ($changeRequest->email_type === 'student') {
                // Update both email and student_email for students
                $user->update([
                    'email' => $newEmail,
                    'student_email' => $newEmail,
                ]);
            } else if ($changeRequest->email_type === 'company') {
                // Update users.email
                $user->update([
                    'email' => $newEmail,
                ]);

                // Update company.contact_person_email
                if ($user->company) {
                    $user->company->update([
                        'contact_person_email' => $newEmail,
                    ]);
                }
            }

            // Delete the email change request
            DB::table('email_change_requests')->where('id', $changeRequest->id)->delete();

            // Revoke all existing tokens for this user to force logout
            DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\User')
                ->where('tokenable_id', $user->id)
                ->delete();

            DB::commit();

            // Send confirmation email to NEW email
            Mail::to($newEmail)->send(
                new EmailChangedConfirmationMail($user, $oldEmail)
            );

            // Return view that clears localStorage and redirects to login
            return view('email_change_redirect', [
                'redirectUrl' => env('FRONTEND_URL') . '/login?logout=true&email_changed=true'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return view('email_change_redirect', [
                'redirectUrl' => env('FRONTEND_URL') . '/login?email_change_error=failed'
            ]);
        }
    }
}
