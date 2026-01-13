<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Mail\CompanyApprovedWithCredentialsMail;
use App\Mail\CompanyRejectedMail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class CompanyController extends Controller
{
    // ======================================
    // GET PENDING COMPANIES (Guarantor only)
    // ======================================
    public function getPendingCompanies(Request $request)
    {
        // Check if user is guarantor
        $user = $request->user();

        if (!$user || !$user->hasRole('guarantor')) {
            return response()->json([
                'message' => 'Unauthorized. Only guarantors can access this endpoint.',
            ], 403);
        }

        // Get all companies that are not approved yet
        $pendingCompanies = Company::with(['address', 'users'])
            ->where('approved', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'companies' => $pendingCompanies,
        ], 200);
    }

    // ======================================
    // APPROVE COMPANY (Guarantor only)
    // ======================================
    public function approveCompany(Request $request, $id)
    {
        // Check if user is guarantor
        $user = $request->user();

        if (!$user || !$user->hasRole('guarantor')) {
            return response()->json([
                'message' => 'Unauthorized. Only guarantors can approve companies.',
            ], 403);
        }

        $company = Company::with(['address', 'users'])->find($id);

        if (!$company) {
            return response()->json([
                'message' => 'Company not found.',
            ], 404);
        }

        if ($company->approved) {
            return response()->json([
                'message' => 'Company is already approved.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Update company approval status
            $company->update([
                'approved' => true,
                'approved_at' => now(),
                'approved_by' => $user->id,
            ]);

            // Get the company user
            $companyUser = $company->users->first();

            if ($companyUser) {
                // Generate temporary password
                $temporaryPassword = Str::random(12);

                // Update user with new password
                $companyUser->update([
                    'password' => Hash::make($temporaryPassword),
                ]);

                // Generate activation token
                $activationToken = Str::random(64);
                $companyUser->update([
                    'activation_token' => $activationToken,
                    'activation_token_expires_at' => now()->addHours(48), // 48 hour expiry
                ]);

                // Send single comprehensive email with approval, password, and activation link
                Mail::to($company->contact_person_email)->send(
                    new CompanyApprovedWithCredentialsMail($company, $temporaryPassword, $activationToken)
                );
            }

            DB::commit();

            return response()->json([
                'message' => 'Company approved successfully. Activation email with credentials has been sent.',
                'company' => $company,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to approve company.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ======================================
    // REJECT COMPANY (Guarantor only)
    // ======================================
    public function rejectCompany(Request $request, $id)
    {
        // Check if user is guarantor
        $user = $request->user();

        if (!$user || !$user->hasRole('guarantor')) {
            return response()->json([
                'message' => 'Unauthorized. Only guarantors can reject companies.',
            ], 403);
        }

        $company = Company::with(['address', 'users'])->find($id);

        if (!$company) {
            return response()->json([
                'message' => 'Company not found.',
            ], 404);
        }

        if ($company->approved) {
            return response()->json([
                'message' => 'Cannot reject an already approved company.',
            ], 400);
        }

        $reason = $request->input('reason', null);

        try {
            DB::beginTransaction();

            // Send rejection email to company
            Mail::to($company->contact_person_email)->send(new CompanyRejectedMail($company, $reason));

            // Delete the company and associated user
            $companyUsers = $company->users;
            foreach ($companyUsers as $companyUser) {
                $companyUser->delete();
            }
            $company->delete();

            DB::commit();

            return response()->json([
                'message' => 'Company rejected and removed from the system.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to reject company.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
