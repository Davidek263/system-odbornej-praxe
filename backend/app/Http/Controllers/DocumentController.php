<?php

namespace App\Http\Controllers;

// ============================================================
// IMPORTS
// ============================================================
use App\Models\Document;
use App\Models\TimesheetStatus;
use App\Models\TimesheetStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Document Controller
 *
 * Manages timesheet approval and rejection workflows for companies.
 * Handles document verification, status tracking, and access control.
 * FR-08: Firma môže potvrdiť/zamietnuť výkaz
 */
class DocumentController extends Controller
{
    // ======================================
    // APPROVE TIMESHEET
    // ======================================
    /**
     * Approve timesheet (Company confirms timesheet)
     * POST /documents/{documentId}/approve
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

            $document = Document::with('documentType', 'internship.company')
                ->findOrFail($documentId);
            
            // Check if document is a timesheet
            if (!$document->isTimesheet()) {
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

            // Get "Potvrdený" status
            $approvedStatus = TimesheetStatus::where('timesheet_status_name', 'Potvrdený')->first();

            if (!$approvedStatus) {
                throw new \Exception('Potvrdený status not found in database.');
            }

            // Create status change history
            TimesheetStatusHistory::create([
                'documents_id' => $document->id,
                'timesheet_status_id' => $approvedStatus->id,
                'changed_by_user_id' => $user->id,
                'status_changed_at' => now(),
                'notes' => $request->notes ?? 'Schválené firmou',
            ]);

            // Update document verification
            $document->is_verified = true;
            $document->verified_by_user_id = $user->id;
            $document->verified_at = now();
            $document->verification_notes = $request->notes ?? 'Schválené firmou';
            $document->save();

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

    // ======================================
    // REJECT TIMESHEET
    // ======================================
    /**
     * Reject timesheet (Company rejects timesheet)
     * POST /documents/{documentId}/reject
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

            $document = Document::with('documentType', 'internship.company')
                ->findOrFail($documentId);
            
            // Check if document is a timesheet
            if (!$document->isTimesheet()) {
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

            // Get "Zamietnutý" status
            $rejectedStatus = TimesheetStatus::where('timesheet_status_name', 'Zamietnutý')->first();

            if (!$rejectedStatus) {
                throw new \Exception('Zamietnutý status not found in database.');
            }

            // Create status change history
            TimesheetStatusHistory::create([
                'documents_id' => $document->id,
                'timesheet_status_id' => $rejectedStatus->id,
                'changed_by_user_id' => $user->id,
                'status_changed_at' => now(),
                'notes' => $request->notes ?? 'Zamietnuté firmou',
            ]);

            // Update document verification
            $document->is_verified = false;
            $document->verified_by_user_id = $user->id;
            $document->verified_at = now();
            $document->verification_notes = $request->notes ?? 'Zamietnuté firmou';
            $document->save();

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

    // ======================================
    // GET DOCUMENT DETAILS
    // ======================================
    /**
     * Get document details with timesheet status history
     * GET /documents/{documentId}
     */
    public function getDocument($documentId)
    {
        try {
            $document = Document::with([
                'documentType',
                'internship',
                'uploadedBy',
                'verifiedBy',
                'timesheetStatusHistory.status',
                'timesheetStatusHistory.changedByUser'
            ])->findOrFail($documentId);

            // Check if user has permission to view
            $user = auth()->user();

            if ($user->hasRole('company') && $document->internship->company_id !== $user->company_id) {
                return response()->json([
                    'message' => 'Unauthorized to view this document.',
                ], 403);
            }

            if ($user->hasRole('student') && $document->internship->users_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized to view this document.',
                ], 403);
            }

            return response()->json([
                'document' => $document,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch document.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}