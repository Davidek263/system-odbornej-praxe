<?php

namespace App\Http\Controllers;

// ============================================================
// IMPORTS
// ============================================================

use App\Models\Company;
use App\Models\Document;
use App\Models\Internship;
use App\Models\InternshipStatus;
use App\Models\InternshipStatusChange;
use App\Models\TimesheetStatus;
use App\Models\TimesheetStatusHistory;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * InternshipController
 *
 * Manages internship lifecycle operations including:
 * - Student internship creation and management
 * - Company internship confirmations and rejections
 * - Guarantor oversight and status management
 * - Timesheet approval/rejection workflows
 * - PDF document generation (Dohoda)
 * - CSV export functionality for reporting
 * - External API integration for defense marking
 *
 * Supports three primary user roles:
 * - Student: Create, view, and edit own internships
 * - Company: View, confirm, reject internships and timesheets
 * - Guarantor: Full oversight, status changes, and reporting
 */
use App\Services\EmailNotificationService;
use App\Mail\InternshipCreatedMail;
use App\Mail\InternshipConfirmedMail;
use App\Mail\InternshipRejectedMail;
use App\Mail\InternshipApprovedMail;
use App\Mail\InternshipDefendedMail;
use App\Mail\InternshipNotDefendedMail;
use App\Mail\TimesheetApprovedMail;
use App\Mail\TimesheetRejectedMail;

class InternshipController extends Controller
{
    protected $emailService;

    public function __construct(EmailNotificationService $emailService)
    {
        $this->emailService = $emailService;
    }
    // ============================================================
    // STUDENT METHODS - Internship Creation & Management
    // ============================================================

    /**
     * Create new internship (Student creates internship)
     * POST /internships
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:company,id',
            'academic_year' => [
                'required',
                'regex:/^\d{4}\/\d{4}$/',
            ],
            'semester' => 'required|integer|in:1,2',
            'internship_type' => 'required|in:prax,brigada',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after:date_start',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();

            if (!$user->hasRole('student')) {
                return response()->json([
                    'message' => 'Only students can create internships.',
                ], 403);
            }

            $createdStatus = InternshipStatus::where('internship_status_name', 'Vytvorená')->first();

            if (!$createdStatus) {
                throw new \Exception('Internship status "Vytvorená" not found in database.');
            }

            $internship = Internship::create([
                'users_id' => $user->id,
                'company_id' => $request->company_id,
                'academic_year' => $request->academic_year,
                'semester' => $request->semester,
                'internship_type' => $request->internship_type,
                'date_start' => $request->date_start,
                'date_end' => $request->date_end,
                'current_status_id' => $createdStatus->id,
            ]);

            DB::table('internship_status_change')->insert([
                'internship_id' => $internship->id,
                'internship_status_id' => $createdStatus->id,
                'changed_by_user_id' => $user->id,
                'status_changed_at' => now(),
                'notes' => 'Prax vytvorená študentom',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Prax bola úspešne vytvorená.',
                'internship' => $internship->load([
                    'company.address',
                    'currentStatus',
                    'student',
                ]),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Nepodarilo sa vytvoriť prax.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update internship (Student can edit only in "Vytvorená" status)
     * PUT /internships/{id}
     */
    public function updateStudentInternship(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:company,id',
            'academic_year' => [
                'required',
                'regex:/^\d{4}\/\d{4}$/',
            ],
            'semester' => 'required|integer|in:1,2',
            'internship_type' => 'required|in:prax,brigada',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after:date_start',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();

            if (!$user->hasRole('student')) {
                return response()->json([
                    'message' => 'Only students can edit internships.',
                ], 403);
            }

            $internship = Internship::find($id);

            if (!$internship) {
                return response()->json([
                    'message' => 'Internship not found.',
                ], 404);
            }

            if ($internship->users_id !== $user->id) {
                return response()->json([
                    'message' => 'You can only edit your own internships.',
                ], 403);
            }

            if ($internship->currentStatus->internship_status_name !== 'Vytvorená') {
                return response()->json([
                    'message' => 'Môžete upravovať iba praxe v stave "Vytvorená".',
                ], 403);
            }

            $internship->update([
                'company_id' => $request->company_id,
                'academic_year' => $request->academic_year,
                'semester' => $request->semester,
                'internship_type' => $request->internship_type,
                'date_start' => $request->date_start,
                'date_end' => $request->date_end,
            ]);

            DB::table('internship_status_change')->insert([
                'internship_id' => $internship->id,
                'internship_status_id' => $internship->current_status_id,
                'changed_by_user_id' => $user->id,
                'status_changed_at' => now(),
                'notes' => 'Prax upravená študentom',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Prax bola úspešne upravená.',
                'internship' => $internship->load([
                    'company.address',
                    'currentStatus',
                    'student',
                ]),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update internship.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all internships for logged-in student
     * GET /student/internships
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
                'statusHistory.changedByUser',
                'documents.documentType',
                'documents.timesheetStatusHistory.status',
                'documents.timesheetStatusHistory.changedByUser',
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
     * Get all companies for student (no role check)
     * GET /student/companies
     */
    public function getCompaniesForStudent()
    {
        try {
            $companies = Company::with('address')
                ->orderBy('company_name')
                ->get(['id', 'company_name', 'address_id', 'contact_person_name', 'contact_person_email', 'contact_person_phone']);

            return response()->json([
                'companies' => $companies,
                'total' => $companies->count(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch companies.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // COMPANY METHODS - Internship & Timesheet Management
    // ============================================================

    /**
     * Get all internships for a specific company
     * GET /company/{companyId}/internships
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
     * GET /internships/{id}
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
     * POST /internships/{id}/confirm
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

            $user = auth()->user();
            if ($user->hasRole('company') && $internship->company_id !== $user->company_id) {
                return response()->json([
                    'message' => 'Unauthorized to confirm this internship.',
                ], 403);
            }

            if ($internship->currentStatus->internship_status_name !== 'Vytvorená') {
                return response()->json([
                    'message' => 'Internship cannot be confirmed in its current state.',
                    'current_status' => $internship->currentStatus->internship_status_name,
                ], 400);
            }

            $confirmedStatus = InternshipStatus::where('internship_status_name', 'Potvrdená')->first();

            if (!$confirmedStatus) {
                throw new \Exception('Potvrdená status not found in database.');
            }

            $internship->current_status_id = $confirmedStatus->id;
            $internship->save();

            InternshipStatusChange::create([
                'internship_id' => $internship->id,
                'internship_status_id' => $confirmedStatus->id,
                'changed_by_user_id' => $user->id,
                'status_changed_at' => now(),
                'notes' => $request->notes ?? 'Potvrdené firmou',
            ]);

            // Send email notification to student and guarantor
            $recipients = $this->emailService->getRecipientsForConfirmation($internship);
            if (!empty($recipients)) {
                $this->emailService->sendEmail(
                    recipients: $recipients,
                    mailable: new InternshipConfirmedMail($internship),
                    type: 'internship_confirmed',
                    relatedModel: $internship
                );
            }

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
     * POST /internships/{id}/reject
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

            $user = auth()->user();
            if ($user->hasRole('company') && $internship->company_id !== $user->company_id) {
                return response()->json([
                    'message' => 'Unauthorized to reject this internship.',
                ], 403);
            }

            if ($internship->currentStatus->internship_status_name !== 'Vytvorená') {
                return response()->json([
                    'message' => 'Internship cannot be rejected in its current state.',
                    'current_status' => $internship->currentStatus->internship_status_name,
                ], 400);
            }

            $rejectedStatus = InternshipStatus::where('internship_status_name', 'Zamietnutá')->first();

            if (!$rejectedStatus) {
                throw new \Exception('Zamietnutá status not found in database.');
            }

            $internship->current_status_id = $rejectedStatus->id;
            $internship->save();

            InternshipStatusChange::create([
                'internship_id' => $internship->id,
                'internship_status_id' => $rejectedStatus->id,
                'changed_by_user_id' => $user->id,
                'status_changed_at' => now(),
                'notes' => $request->notes ?? 'Zamietnuté firmou',
            ]);

            // Send email notification to student and guarantor (company rejected, so exclude company)
            $recipients = $this->emailService->getRecipientsForRejection($internship, $user->id, rejectedByCompany: true);
            if (!empty($recipients)) {
                $this->emailService->sendEmail(
                    recipients: $recipients,
                    mailable: new InternshipRejectedMail($internship, $user, $request->notes),
                    type: 'internship_rejected',
                    relatedModel: $internship
                );
            }

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
     * Company approves timesheet (Nahraný -> Potvrdený)
     * POST /timesheets/{documentId}/approve
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

            if ($document->documentType->document_type_name !== 'Výkaz hodín') {
                return response()->json([
                    'message' => 'This document is not a timesheet.',
                ], 400);
            }

            $user = auth()->user();
            if ($user->hasRole('company') && $document->internship->company_id !== $user->company_id) {
                return response()->json([
                    'message' => 'Unauthorized to approve this timesheet.',
                ], 403);
            }

            $approvedStatus = TimesheetStatus::where('timesheet_status_name', 'Potvrdený')->first();

            if (!$approvedStatus) {
                throw new \Exception('Potvrdený timesheet status not found in database.');
            }

            TimesheetStatusHistory::create([
                'documents_id' => $document->id,
                'timesheet_status_id' => $approvedStatus->id,
                'changed_by_user_id' => $user->id,
                'status_changed_at' => now(),
                'notes' => $request->notes ?? 'Schválené firmou',
            ]);

            $document->is_verified = true;
            $document->verified_by_user_id = $user->id;
            $document->verified_at = now();
            $document->save();

            // Load internship with student for email
            $internship = $document->internship()->with('student')->first();

            // Send email notification to student
            $recipients = $this->emailService->getRecipientsForTimesheet($internship);
            if (!empty($recipients)) {
                $this->emailService->sendEmail(
                    recipients: $recipients,
                    mailable: new TimesheetApprovedMail($internship, $document),
                    type: 'timesheet_approved',
                    relatedModel: $document
                );
            }

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
     * POST /timesheets/{documentId}/reject
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

            if ($document->documentType->document_type_name !== 'Výkaz hodín') {
                return response()->json([
                    'message' => 'This document is not a timesheet.',
                ], 400);
            }

            $user = auth()->user();
            if ($user->hasRole('company') && $document->internship->company_id !== $user->company_id) {
                return response()->json([
                    'message' => 'Unauthorized to reject this timesheet.',
                ], 403);
            }

            $rejectedStatus = TimesheetStatus::where('timesheet_status_name', 'Zamietnutý')->first();

            if (!$rejectedStatus) {
                throw new \Exception('Zamietnutý timesheet status not found in database.');
            }

            TimesheetStatusHistory::create([
                'documents_id' => $document->id,
                'timesheet_status_id' => $rejectedStatus->id,
                'changed_by_user_id' => $user->id,
                'status_changed_at' => now(),
                'notes' => $request->notes ?? 'Zamietnuté firmou',
            ]);

            $document->is_verified = false;
            $document->verified_by_user_id = null;
            $document->verified_at = null;
            $document->save();

            // Load internship with student for email
            $internship = $document->internship()->with('student')->first();

            // Send email notification to student
            $recipients = $this->emailService->getRecipientsForTimesheet($internship);
            if (!empty($recipients)) {
                $this->emailService->sendEmail(
                    recipients: $recipients,
                    mailable: new TimesheetRejectedMail($internship, $document, $request->notes),
                    type: 'timesheet_rejected',
                    relatedModel: $document
                );
            }

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

    // ============================================================
    // GUARANTOR METHODS - Oversight & Management
    // ============================================================

    /**
     * Get all internships for guarantor with filters
     * GET /guarantor/internships
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
     * Update internship (Guarantor only)
     * PUT /guarantor/internships/{id}
     */
    public function updateInternship(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'users_id' => 'required|exists:users,id',
            'company_id' => 'required|exists:company,id',
            'academic_year' => 'required|string|max:9',
            'semester' => 'required|integer|in:1,2',
            'internship_type' => 'nullable|in:prax,brigada',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after:date_start',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = auth()->user();

            if (!$user->hasRole('guarantor')) {
                return response()->json([
                    'message' => 'Unauthorized. Only guarantors can update internships.',
                ], 403);
            }

            DB::beginTransaction();

            $internship = Internship::findOrFail($id);

            $internship->users_id = $request->users_id;
            $internship->company_id = $request->company_id;
            $internship->academic_year = $request->academic_year;
            $internship->semester = $request->semester;

            if ($request->has('internship_type')) {
                $internship->internship_type = $request->internship_type;
            }

            $internship->date_start = $request->date_start;
            $internship->date_end = $request->date_end;
            $internship->save();

            $internship->load(['student', 'company', 'currentStatus']);

            DB::commit();

            return response()->json([
                'message' => 'Internship updated successfully.',
                'internship' => $internship,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update internship.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change internship status (Guarantor only)
     * POST /guarantor/internships/{id}/change-status
     */
    public function changeInternshipStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = auth()->user();

            if (!$user->hasRole('guarantor')) {
                return response()->json([
                    'message' => 'Unauthorized. Only guarantors can change internship status.',
                ], 403);
            }

            DB::beginTransaction();

            $internship = Internship::with(['currentStatus', 'student', 'company'])->findOrFail($id);

            $oldStatus = $internship->currentStatus->internship_status_name;

            $newStatus = InternshipStatus::where('internship_status_name', $request->status)->first();

            if (!$newStatus) {
                return response()->json([
                    'message' => 'Invalid status name.',
                    'provided_status' => $request->status,
                ], 400);
            }

            if ($newStatus == $oldStatus) {
                return response()->json([
                    'message' => 'Invalid status transition.',
                ], 400);
            }

            $internship->current_status_id = $newStatus->id;
            $internship->save();

            InternshipStatusChange::create([
                'internship_id' => $internship->id,
                'internship_status_id' => $newStatus->id,
                'changed_by_user_id' => $user->id,
                'status_changed_at' => now(),
                'notes' => $request->notes ?? "Zmena stavu garantom: {$oldStatus} → {$request->status}",
            ]);

            $internship->load('currentStatus', 'statusHistory.status');

            // Send email notifications based on new status
            if ($request->status === 'Schválená') {
                $recipients = $this->emailService->getRecipientsForApproval($internship);
                if (!empty($recipients)) {
                    $this->emailService->sendEmail(
                        recipients: $recipients,
                        mailable: new InternshipApprovedMail($internship),
                        type: 'internship_approved',
                        relatedModel: $internship
                    );
                }
            } elseif ($request->status === 'Obhájená') {
                $recipients = $this->emailService->getRecipientsForDefended($internship);
                if (!empty($recipients)) {
                    $this->emailService->sendEmail(
                        recipients: $recipients,
                        mailable: new InternshipDefendedMail($internship),
                        type: 'internship_defended',
                        relatedModel: $internship
                    );
                }
            } elseif ($request->status === 'Neobhájená') {
                $recipients = $this->emailService->getRecipientsForDefended($internship);
                if (!empty($recipients)) {
                    $this->emailService->sendEmail(
                        recipients: $recipients,
                        mailable: new InternshipNotDefendedMail($internship),
                        type: 'internship_not_defended',
                        relatedModel: $internship
                    );
                }
            } elseif ($request->status === 'Zamietnutá') {
                $recipients = $this->emailService->getRecipientsForRejection($internship, $user->id, rejectedByCompany: false);
                if (!empty($recipients)) {
                    $this->emailService->sendEmail(
                        recipients: $recipients,
                        mailable: new InternshipRejectedMail($internship, $user, $request->notes),
                        type: 'internship_rejected',
                        relatedModel: $internship
                    );
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Status changed successfully. Notifications sent.',
                'internship' => $internship,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to change status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export internships to CSV (Guarantor only)
     * POST /guarantor/internships/export
     */
    public function exportGuarantorInternships(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user->hasRole('guarantor')) {
                return response()->json([
                    'message' => 'Unauthorized. Only guarantors can export internships.',
                ], 403);
            }

            $query = Internship::with([
                'student.studyField',
                'company',
                'currentStatus',
            ]);

            if ($request->filled('academic_year')) {
                $query->where('academic_year', $request->input('academic_year'));
            }

            if ($request->filled('status')) {
                $statusName = $request->input('status');
                $query->whereHas('currentStatus', function ($q) use ($statusName) {
                    $q->where('internship_status_name', $statusName);
                });
            }

            if ($request->filled('company')) {
                $companyName = $request->input('company');
                $query->whereHas('company', function ($q) use ($companyName) {
                    $q->where('company_name', $companyName);
                });
            }

            if ($request->filled('study_field')) {
                $fieldName = $request->input('study_field');
                $query->whereHas('student.studyField', function ($q) use ($fieldName) {
                    $q->where('study_field_name', $fieldName);
                });
            }

            if ($request->filled('student')) {
                $needle = mb_strtolower($request->input('student'));
                $query->whereHas('student', function ($q) use ($needle) {
                    $q->whereRaw('LOWER(first_name) LIKE ?', ["%{$needle}%"])
                      ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$needle}%"]);
                });
            }

            if ($request->filled('search')) {
                $s = mb_strtolower($request->input('search'));
                $query->where(function ($q) use ($s) {
                    $q->whereHas('student', function ($qs) use ($s) {
                        $qs->whereRaw('LOWER(first_name) LIKE ?', ["%{$s}%"])
                           ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$s}%"])
                           ->orWhereRaw('LOWER(email) LIKE ?', ["%{$s}%"])
                           ->orWhereRaw('LOWER(student_email) LIKE ?', ["%{$s}%"]);
                    })
                    ->orWhereHas('company', function ($qc) use ($s) {
                        $qc->whereRaw('LOWER(company_name) LIKE ?', ["%{$s}%"]);
                    })
                    ->orWhereHas('student.studyField', function ($qf) use ($s) {
                        $qf->whereRaw('LOWER(study_field_name) LIKE ?', ["%{$s}%"])
                           ->orWhereRaw('LOWER(abbreviation) LIKE ?', ["%{$s}%"]);
                    })
                    ->orWhereRaw('LOWER(academic_year) LIKE ?', ["%{$s}%"])
                    ->orWhereHas('currentStatus', function ($qst) use ($s) {
                        $qst->whereRaw('LOWER(internship_status_name) LIKE ?', ["%{$s}%"]);
                    });
                });
            }

            $exportFilters = $request->input('filters', []);

            if (!empty($exportFilters['studyField'])) {
                $query->whereHas('student.studyField', function ($q) use ($exportFilters) {
                    $q->whereRaw('LOWER(study_field_name) LIKE ?', ['%' . mb_strtolower($exportFilters['studyField']) . '%']);
                });
            }

            if (!empty($exportFilters['academicYear'])) {
                $query->whereRaw('LOWER(academic_year) LIKE ?', ['%' . mb_strtolower($exportFilters['academicYear']) . '%']);
            }

            if (!empty($exportFilters['firstName'])) {
                $query->whereHas('student', function ($q) use ($exportFilters) {
                    $q->whereRaw('LOWER(first_name) LIKE ?', ['%' . mb_strtolower($exportFilters['firstName']) . '%']);
                });
            }

            if (!empty($exportFilters['lastName'])) {
                $query->whereHas('student', function ($q) use ($exportFilters) {
                    $q->whereRaw('LOWER(last_name) LIKE ?', ['%' . mb_strtolower($exportFilters['lastName']) . '%']);
                });
            }

            if (!empty($exportFilters['email'])) {
                $query->whereHas('student', function ($q) use ($exportFilters) {
                    $q->whereRaw('LOWER(email) LIKE ?', ['%' . mb_strtolower($exportFilters['email']) . '%']);
                });
            }

            if (!empty($exportFilters['studentEmail'])) {
                $query->whereHas('student', function ($q) use ($exportFilters) {
                    $q->whereRaw('LOWER(student_email) LIKE ?', ['%' . mb_strtolower($exportFilters['studentEmail']) . '%']);
                });
            }

            if (!empty($exportFilters['alternativeEmail'])) {
                $query->whereHas('student', function ($q) use ($exportFilters) {
                    $q->whereRaw('LOWER(alternative_email) LIKE ?', ['%' . mb_strtolower($exportFilters['alternativeEmail']) . '%']);
                });
            }

            if (!empty($exportFilters['company'])) {
                $query->whereHas('company', function ($q) use ($exportFilters) {
                    $q->whereRaw('LOWER(company_name) LIKE ?', ['%' . mb_strtolower($exportFilters['company']) . '%']);
                });
            }

            if (!empty($exportFilters['dateStart'])) {
                $query->where('date_start', '>=', $exportFilters['dateStart']);
            }

            if (!empty($exportFilters['dateEnd'])) {
                $query->where('date_end', '<=', $exportFilters['dateEnd']);
            }

            if (!empty($exportFilters['status'])) {
                $query->whereHas('currentStatus', function ($q) use ($exportFilters) {
                    $q->whereRaw('LOWER(internship_status_name) LIKE ?', ['%' . mb_strtolower($exportFilters['status']) . '%']);
                });
            }

            $internships = $query->orderBy('created_at', 'desc')->get();

            $columns = $request->input('columns', []);

            $columnMap = [
                'studyField' => ['label' => 'Studijny odbor', 'value' => fn($i) => $i->student->studyField->study_field_name ?? '—'],
                'academicYear' => ['label' => 'Akademicky rok', 'value' => fn($i) => $i->academic_year ?? '—'],
                'firstName' => ['label' => 'Meno', 'value' => fn($i) => $i->student->first_name ?? '—'],
                'lastName' => ['label' => 'Priezvisko', 'value' => fn($i) => $i->student->last_name ?? '—'],
                'email' => ['label' => 'Email', 'value' => fn($i) => $i->student->email ?? '—'],
                'studentEmail' => ['label' => 'Studentsky email', 'value' => fn($i) => $i->student->student_email ?? '—'],
                'alternativeEmail' => ['label' => 'Alternativny email', 'value' => fn($i) => $i->student->alternative_email ?? '—'],
                'company' => ['label' => 'Firma', 'value' => fn($i) => $i->company->company_name ?? '—'],
                'dateStart' => ['label' => 'Datum zaciatku', 'value' => fn($i) => $i->date_start ?? '—'],
                'dateEnd' => ['label' => 'Datum konca', 'value' => fn($i) => $i->date_end ?? '—'],
                'status' => ['label' => 'Stav praxe', 'value' => fn($i) => $i->currentStatus->internship_status_name ?? '—'],
            ];

            $output = fopen('php://temp', 'r+');

            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            $headers = [];
            foreach ($columns as $col) {
                if (isset($columnMap[$col])) {
                    $headers[] = $columnMap[$col]['label'];
                }
            }

            fwrite($output, implode(';', $headers) . "\n");

            foreach ($internships as $internship) {
                $row = [];
                foreach ($columns as $col) {
                    if (isset($columnMap[$col])) {
                        $row[] = ($columnMap[$col]['value'])($internship);
                    }
                }
                fwrite($output, implode(';', $row) . "\n");
            }

            rewind($output);
            $csv = stream_get_contents($output);
            fclose($output);

            $timestamp = now()->format('Y-m-d_H-i');
            $filename = "report_praxe_{$timestamp}.csv";

            return response($csv, 200)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to export internships.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // HELPER METHODS - Lookup Data for Dropdowns
    // ============================================================

    /**
     * Get all students (for guarantor dropdown)
     * GET /guarantor/students
     */
    public function getAllStudents()
    {
        try {
            $user = auth()->user();

            if (!$user->hasRole('guarantor')) {
                return response()->json([
                    'message' => 'Unauthorized.',
                ], 403);
            }

            $students = User::with('studyField')
                ->whereHas('role', function($q) {
                    $q->where('role_name', 'student');
                })
                ->where('active', true)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'last_name', 'email', 'student_email', 'study_field_id']);

            return response()->json([
                'students' => $students,
                'total' => $students->count(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch students.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all companies (for guarantor dropdown)
     * GET /guarantor/companies
     */
    public function getAllCompanies()
    {
        try {
            $user = auth()->user();

            if (!$user->hasRole('guarantor')) {
                return response()->json([
                    'message' => 'Unauthorized.',
                ], 403);
            }

            $companies = Company::with('address')
                ->orderBy('company_name')
                ->get(['id', 'company_name', 'address_id', 'contact_person_name', 'contact_person_email']);

            return response()->json([
                'companies' => $companies,
                'total' => $companies->count(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch companies.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // PDF GENERATION
    // ============================================================

    /**
     * Generate Dohoda PDF for an internship
     * GET /internships/{id}/generate-dohoda
     */
    public function generateDohoda($id)
    {
        try {
            $internship = Internship::with([
                'student.studyField',
                'student.address',
                'company.address',
            ])->findOrFail($id);

            $user = auth()->user();

            if ($user->hasRole('student') && $internship->users_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized to generate Dohoda for this internship.',
                ], 403);
            }

            $data = [
                'internship' => $internship,
                'student' => $internship->student,
                'company' => $internship->company,
            ];

            $pdf = Pdf::loadView('pdfs.dohoda-template', $data);

            $pdf->setPaper('A4', 'portrait');

            $filename = 'Dohoda_'
                . str_replace(' ', '_', $internship->student->last_name)
                . '_'
                . str_replace(' ', '_', $internship->company->company_name)
                . '_'
                . $internship->academic_year
                . '.pdf';

            $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $filename);

            return $pdf->download($filename);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate Dohoda PDF.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // Security check 1: Must be using token authentication (not session)
    // ============================================================
    // EXTERNAL API - Defense Marking Integration
    // ============================================================

    /**
     * Mark internship as defended via external API
     * POST /api/internships/{id}/mark-defended
     */
    public function markDefendedExternal(Request $request, $id)
    {
        if (!$request->user()->currentAccessToken()) {
            return response()->json([
                'success' => false,
                'message' => 'This endpoint requires API token authentication.',
                'error' => 'TOKEN_REQUIRED'
            ], 401);
        }

        if (!$request->user()->tokenCan('internship:defend')) {
            return response()->json([
                'success' => false,
                'message' => 'Token does not have required permission: internship:defend',
                'error' => 'INSUFFICIENT_PERMISSIONS'
            ], 403);
        }

        // Validate request data (only optional notes field)
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $internship = Internship::with('currentStatus')->findOrFail($id);

            if ($internship->currentStatus->internship_status_name !== 'Schválená') {
                return response()->json([
                    'success' => false,
                    'message' => 'Internship must be in "Schválená" (Approved) status to be marked as defended.',
                    'current_status' => $internship->currentStatus->internship_status_name,
                    'error' => 'INVALID_STATUS'
                ], 400);
            }

            $defendedStatus = InternshipStatus::where('internship_status_name', 'Obhájená')->first();

            if (!$defendedStatus) {
                throw new \Exception('Status "Obhájená" not found in database.');
            }

            $internship->current_status_id = $defendedStatus->id;
            $internship->save();

            // Create status change history
            InternshipStatusChange::create([
                'internship_id' => $internship->id,
                'internship_status_id' => $defendedStatus->id,
                'changed_by_user_id' => null,
                'status_changed_at' => now(),
                'notes' => $request->notes ?? 'Obhájená externým systémom',
            ]);

            // Load relationships for email
            $internship->load('student');

            // Send email notification to student
            $recipients = $this->emailService->getRecipientsForDefended($internship);
            if (!empty($recipients)) {
                $this->emailService->sendEmail(
                    recipients: $recipients,
                    mailable: new InternshipDefendedMail($internship),
                    type: 'internship_defended',
                    relatedModel: $internship
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Internship successfully marked as defended.',
                'data' => [
                    'internship_id' => $internship->id,
                    'old_status' => 'Schválená',
                    'new_status' => 'Obhájená',
                    'notes' => $request->notes ?? 'Obhájená externým systémom',
                    'changed_at' => now()->toIso8601String(),
                ],
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Internship not found.',
                'error' => 'NOT_FOUND'
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark internship as defended.',
                'error' => 'SERVER_ERROR',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
