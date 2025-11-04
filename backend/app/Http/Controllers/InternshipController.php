<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use App\Models\InternshipStatus;
use App\Models\InternshipStatusChange;
use App\Models\Document;
use App\Models\TimesheetStatus;
use App\Models\TimesheetStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InternshipController extends Controller
{
    /**
     * Get all internships for a specific company
     * Used by company dashboard
     */
    public function getCompanyInternships($companyId)
    {
        try {
            $internships = Internship::with([
                'student.studyField',
                'student.address',
                'student.role',
                'currentStatus',
                'statusHistory.status',
                'statusHistory.changedByUser',
                'documents.documentType',
                'documents.timesheetStatusHistory.status',
                'documents.timesheetStatusHistory.changedByUser',
                'company'
            ])
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->get();

            return response()->json([
                'internships' => $internships,
                'total' => $internships->count(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch internships.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single internship details
     */
    public function getInternship($id)
    {
        try {
            $internship = Internship::with([
                'student.studyField',
                'student.address',
                'student.role',
                'currentStatus',
                'statusHistory.status',
                'statusHistory.changedByUser',
                'documents.documentType',
                'company.address'
            ])->findOrFail($id);

            // Check if user has permission to view this internship
            $user = auth()->user();
            
            if ($user->hasRole('company') && $internship->company_id !== $user->company_id) {
                return response()->json([
                    'message' => 'Unauthorized to view this internship.',
                ], 403);
            }

            return response()->json([
                'internship' => $internship,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch internship.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Company confirms internship (Vytvorená -> Potvrdená)
     */
    public function confirmInternship(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $internship = Internship::with('currentStatus', 'student', 'company')->findOrFail($id);
            
            // Check if user has permission
            $user = auth()->user();
            if ($user->hasRole('company') && $internship->company_id !== $user->company_id) {
                return response()->json([
                    'message' => 'Unauthorized to confirm this internship.',
                ], 403);
            }

            // Check if internship is in correct state
            if ($internship->currentStatus->internship_status_name !== 'Vytvorená') {
                return response()->json([
                    'message' => 'Internship cannot be confirmed in its current state.',
                    'current_status' => $internship->currentStatus->internship_status_name,
                ], 400);
            }

            // Get "Potvrdená" status
            $confirmedStatus = InternshipStatus::where('internship_status_name', 'Potvrdená')->first();
            
            if (!$confirmedStatus) {
                throw new \Exception('Potvrdená status not found in database.');
            }

            // Update internship status
            $internship->current_status_id = $confirmedStatus->id;
            $internship->save();

            // Create status change history
            InternshipStatusChange::create([
                'internship_id' => $internship->id,
                'internship_status_id' => $confirmedStatus->id,
                'changed_by_user_id' => $user->id,
                'status_changed_at' => now(),
                'notes' => $request->notes ?? 'Potvrdené firmou',
            ]);

            // TODO: Send email notification to student and guarantor

            DB::commit();

            return response()->json([
                'message' => 'Internship confirmed successfully.',
                'internship' => $internship->load('currentStatus'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to confirm internship.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Company rejects internship (Vytvorená -> Zamietnutá)
     */
    public function rejectInternship(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $internship = Internship::with('currentStatus', 'student', 'company')->findOrFail($id);
            
            // Check if user has permission
            $user = auth()->user();
            if ($user->hasRole('company') && $internship->company_id !== $user->company_id) {
                return response()->json([
                    'message' => 'Unauthorized to reject this internship.',
                ], 403);
            }

            // Check if internship is in correct state
            if ($internship->currentStatus->internship_status_name !== 'Vytvorená') {
                return response()->json([
                    'message' => 'Internship cannot be rejected in its current state.',
                    'current_status' => $internship->currentStatus->internship_status_name,
                ], 400);
            }

            // Get "Zamietnutá" status
            $rejectedStatus = InternshipStatus::where('internship_status_name', 'Zamietnutá')->first();
            
            if (!$rejectedStatus) {
                throw new \Exception('Zamietnutá status not found in database.');
            }

            // Update internship status
            $internship->current_status_id = $rejectedStatus->id;
            $internship->save();

            // Create status change history
            InternshipStatusChange::create([
                'internship_id' => $internship->id,
                'internship_status_id' => $rejectedStatus->id,
                'changed_by_user_id' => $user->id,
                'status_changed_at' => now(),
                'notes' => $request->notes ?? 'Zamietnuté firmou',
            ]);

            // TODO: Send email notification to student

            DB::commit();

            return response()->json([
                'message' => 'Internship rejected successfully.',
                'internship' => $internship->load('currentStatus'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to reject internship.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all internships for student
     */
    public function getStudentInternships()
    {
        try {
            $user = auth()->user();

            if (!$user->hasRole('student')) {
                return response()->json([
                    'message' => 'Unauthorized. Only students can access this endpoint.',
                ], 403);
            }

            $internships = Internship::with([
                'company.address',
                'currentStatus',
                'statusHistory.status',
                'documents.documentType'
            ])
            ->where('users_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

            return response()->json([
                'internships' => $internships,
                'total' => $internships->count(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch internships.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all internships for guarantor with filters
     */
    public function getGuarantorInternships(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user->hasRole('guarantor')) {
                return response()->json([
                    'message' => 'Unauthorized. Only guarantors can access this endpoint.',
                ], 403);
            }

            $query = Internship::with([
                'student.studyField',
                'student.address',
                'company.address',
                'currentStatus',
                'statusHistory.status',
                'documents.documentType'
            ]);

            // Apply filters
            if ($request->has('academic_year')) {
                $query->where('academic_year', $request->academic_year);
            }

            if ($request->has('semester')) {
                $query->where('semester', $request->semester);
            }

            if ($request->has('status_id')) {
                $query->where('current_status_id', $request->status_id);
            }

            if ($request->has('company_id')) {
                $query->where('company_id', $request->company_id);
            }

            if ($request->has('study_field_id')) {
                $query->whereHas('student', function($q) use ($request) {
                    $q->where('study_field_id', $request->study_field_id);
                });
            }

            $internships = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'internships' => $internships,
                'total' => $internships->count(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch internships.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Company approves timesheet (Nahraný -> Potvrdený)
     * FR-08: Firma môže potvrdiť výkaz
     */
    public function approveTimesheet(Request $request, $documentId)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $document = Document::with(['documentType', 'internship.company'])->findOrFail($documentId);
            
            // Check if document is a timesheet
            if ($document->documentType->document_type_name !== 'Výkaz hodín') {
                return response()->json([
                    'message' => 'This document is not a timesheet.',
                ], 400);
            }

            // Check if user has permission (must be from the same company)
            $user = auth()->user();
            if ($user->hasRole('company') && $document->internship->company_id !== $user->company_id) {
                return response()->json([
                    'message' => 'Unauthorized to approve this timesheet.',
                ], 403);
            }

            // Get "Potvrdený" timesheet status
            $approvedStatus = TimesheetStatus::where('timesheet_status_name', 'Potvrdený')->first();
            
            if (!$approvedStatus) {
                throw new \Exception('Potvrdený timesheet status not found in database.');
            }

            // Create timesheet status history
            TimesheetStatusHistory::create([
                'documents_id' => $document->id,
                'timesheet_status_id' => $approvedStatus->id,
                'changed_by_user_id' => $user->id,
                'status_changed_at' => now(),
                'notes' => $request->notes ?? 'Schválené firmou',
            ]);

            // Update document verification status
            $document->is_verified = true;
            $document->verified_by_user_id = $user->id;
            $document->verified_at = now();
            $document->save();

            // TODO: Send email notification to student

            DB::commit();

            return response()->json([
                'message' => 'Timesheet approved successfully.',
                'document' => $document->load('timesheetStatusHistory.status'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to approve timesheet.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Company rejects timesheet (Nahraný/Potvrdený -> Zamietnutý)
     * FR-08: Firma môže zamietnuť výkaz
     */
    public function rejectTimesheet(Request $request, $documentId)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $document = Document::with(['documentType', 'internship.company'])->findOrFail($documentId);
            
            // Check if document is a timesheet
            if ($document->documentType->document_type_name !== 'Výkaz hodín') {
                return response()->json([
                    'message' => 'This document is not a timesheet.',
                ], 400);
            }

            // Check if user has permission (must be from the same company)
            $user = auth()->user();
            if ($user->hasRole('company') && $document->internship->company_id !== $user->company_id) {
                return response()->json([
                    'message' => 'Unauthorized to reject this timesheet.',
                ], 403);
            }

            // Get "Zamietnutý" timesheet status
            $rejectedStatus = TimesheetStatus::where('timesheet_status_name', 'Zamietnutý')->first();
            
            if (!$rejectedStatus) {
                throw new \Exception('Zamietnutý timesheet status not found in database.');
            }

            // Create timesheet status history
            TimesheetStatusHistory::create([
                'documents_id' => $document->id,
                'timesheet_status_id' => $rejectedStatus->id,
                'changed_by_user_id' => $user->id,
                'status_changed_at' => now(),
                'notes' => $request->notes ?? 'Zamietnuté firmou',
            ]);

            // Update document verification status
            $document->is_verified = false;
            $document->verified_by_user_id = null;
            $document->verified_at = null;
            $document->save();

            // TODO: Send email notification to student

            DB::commit();

            return response()->json([
                'message' => 'Timesheet rejected successfully.',
                'document' => $document->load('timesheetStatusHistory.status'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to reject timesheet.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}