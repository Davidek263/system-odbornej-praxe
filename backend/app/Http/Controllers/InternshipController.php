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
use Barryvdh\DomPDF\Facade\Pdf;
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

            // Check if user has permission
            $user = auth()->user();
            
            if ($user->hasRole('student') && $internship->users_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized to generate Dohoda for this internship.',
                ], 403);
            }

            // Prepare data for the PDF
            $data = [
                'internship' => $internship,
                'student' => $internship->student,
                'company' => $internship->company,
            ];

            // Generate PDF
            $pdf = Pdf::loadView('pdfs.dohoda-template', $data);
            
            // Set paper size and orientation
            $pdf->setPaper('A4', 'portrait');
            
            // Generate filename
            $filename = 'Dohoda_' 
                . str_replace(' ', '_', $internship->student->last_name) 
                . '_' 
                . str_replace(' ', '_', $internship->company->company_name) 
                . '_' 
                . $internship->academic_year 
                . '.pdf';
            
            $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $filename);

            // Return PDF as download
            return $pdf->download($filename);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate Dohoda PDF.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

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

        /**
     * Update internship (Guarantor only)
     * Garant môže meniť firmu, študenta, dátumy
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

            // Store old values for notification
            $oldData = [
                'student' => $internship->student,
                'company' => $internship->company,
                'date_start' => $internship->date_start,
                'date_end' => $internship->date_end,
            ];

            // Update internship
            $internship->users_id = $request->users_id;
            $internship->company_id = $request->company_id;
            $internship->academic_year = $request->academic_year;
            $internship->semester = $request->semester;

            // Only update internship_type if provided
            if ($request->has('internship_type')) {
                $internship->internship_type = $request->internship_type;
            }

            $internship->date_start = $request->date_start;
            $internship->date_end = $request->date_end;
            $internship->save();

            // Load fresh data with relationships
            $internship->load(['student', 'company', 'currentStatus']);

            // TODO: Send email notifications to:
            // - Old student (if changed)
            // - New student (if changed)
            // - Old company (if changed)
            // - New company (if changed)

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
     * Zmena stavu vyvolá emailovú notifikáciu
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

            // Get new status
            $newStatus = InternshipStatus::where('internship_status_name', $request->status)->first();
            
            if (!$newStatus) {
                return response()->json([
                    'message' => 'Invalid status name.',
                    'provided_status' => $request->status,
                ], 400);
            }

            // Validate status transition
            if ($newStatus == $oldStatus) {
                return response()->json([
                    'message' => 'Invalid status transition.',
                ], 400);
            }

            // Update internship status
            $internship->current_status_id = $newStatus->id;
            $internship->save();

            // Create status change history
            InternshipStatusChange::create([
                'internship_id' => $internship->id,
                'internship_status_id' => $newStatus->id,
                'changed_by_user_id' => $user->id,
                'status_changed_at' => now(),
                'notes' => $request->notes ?? "Zmena stavu garantom: {$oldStatus} → {$request->status}",
            ]);

            // Load fresh data
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
     * Get all students (for guarantor dropdown)
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

            $students = \App\Models\User::with('studyField')
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

            $companies = \App\Models\Company::with('address')
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
    /**
     * Get all companies for student (no role check)
     * GET /student/companies
     */
    public function getCompaniesForStudent()
    {
        try {
            $companies = \App\Models\Company::with('address')
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
                'regex:/^\d{4}\/\d{4}$/', // Format: 2024/2025
            ],
            'semester' => 'required|integer|in:1,2', // 1 = Winter, 2 = Summer
            'internship_type' => 'required|in:prax,brigada', // prax = school agreement, brigada = employer agreement
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
            
            // Verify user is a student
            if (!$user->hasRole('student')) {
                return response()->json([
                    'message' => 'Only students can create internships.',
                ], 403);
            }

            // Get "Vytvorená" status (Created)
            $createdStatus = \App\Models\InternshipStatus::where('internship_status_name', 'Vytvorená')->first();
            
            if (!$createdStatus) {
                throw new \Exception('Internship status "Vytvorená" not found in database.');
            }

            // Create internship
            $internship = \App\Models\Internship::create([
                'users_id' => $user->id,
                'company_id' => $request->company_id,
                'academic_year' => $request->academic_year,
                'semester' => $request->semester,
                'internship_type' => $request->internship_type,
                'date_start' => $request->date_start,
                'date_end' => $request->date_end,
                'current_status_id' => $createdStatus->id,
            ]);

            // Create initial status change history
            DB::table('internship_status_change')->insert([
                'internship_id' => $internship->id,
                'internship_status_id' => $createdStatus->id,
                'changed_by_user_id' => $user->id,
                'status_changed_at' => now(),
                'notes' => 'Prax vytvorená študentom',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Load relationships for email
            $internship->load(['student.studyField', 'company']);

            // Send email notification to company
            $recipients = $this->emailService->getRecipientsForCreation($internship);
            if (!empty($recipients)) {
                $this->emailService->sendEmail(
                    recipients: $recipients,
                    mailable: new InternshipCreatedMail($internship),
                    type: 'internship_created',
                    relatedModel: $internship
                );
            }

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
    public function updateStudentInternship(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:company,id',
            'academic_year' => [
                'required',
                'regex:/^\d{4}\/\d{4}$/', // Format: 2024/2025
            ],
            'semester' => 'required|integer|in:1,2', // 1 = Winter, 2 = Summer
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
            
            // Verify user is a student
            if (!$user->hasRole('student')) {
                return response()->json([
                    'message' => 'Only students can edit internships.',
                ], 403);
            }

            // Find internship
            $internship = \App\Models\Internship::find($id);
            
            if (!$internship) {
                return response()->json([
                    'message' => 'Internship not found.',
                ], 404);
            }

            // Verify ownership
            if ($internship->users_id !== $user->id) {
                return response()->json([
                    'message' => 'You can only edit your own internships.',
                ], 403);
            }

            // Students can only edit internships in "Vytvorená" status
            if ($internship->currentStatus->internship_status_name !== 'Vytvorená') {
                return response()->json([
                    'message' => 'Môžete upravovať iba praxe v stave "Vytvorená".',
                ], 403);
            }

            // Update internship
            $internship->update([
                'company_id' => $request->company_id,
                'academic_year' => $request->academic_year,
                'semester' => $request->semester,
                'internship_type' => $request->internship_type,
                'date_start' => $request->date_start,
                'date_end' => $request->date_end,
            ]);

            // Log the change
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

    public function markDefendedExternal(Request $request, $id)
    {
        // Security check 1: Must be using token authentication (not session)
        if (!$request->user()->currentAccessToken()) {
            return response()->json([
                'success' => false,
                'message' => 'This endpoint requires API token authentication.',
                'error' => 'TOKEN_REQUIRED'
            ], 401);
        }

        // Security check 2: Token must have 'internship:defend' ability
        if (!$request->user()->tokenCan('internship:defend')) {
            return response()->json([
                'success' => false,
                'message' => 'Token does not have required permission: internship:defend',
                'error' => 'INSUFFICIENT_PERMISSIONS'
            ], 403);
        }

        // Validate request data
        $validator = Validator::make($request->all(), [
            'defense_date' => 'required|date',
            'defense_result' => 'required|string|max:500',
            'defense_grade' => 'nullable|string|max:10',
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

            // Find internship
            $internship = Internship::with('currentStatus')->findOrFail($id);

            // Critical check: Internship must be in "Schválená" status
            if ($internship->currentStatus->internship_status_name !== 'Schválená') {
                return response()->json([
                    'success' => false,
                    'message' => 'Internship must be in "Schválená" (Approved) status to be marked as defended.',
                    'current_status' => $internship->currentStatus->internship_status_name,
                    'error' => 'INVALID_STATUS'
                ], 400);
            }

            // Get "Obhájená" status
            $defendedStatus = InternshipStatus::where(
                'internship_status_name',
                'Obhájená'
            )->first();

            if (!$defendedStatus) {
                throw new \Exception('Status "Obhájená" not found in database.');
            }

            // Update internship status
            $internship->current_status_id = $defendedStatus->id;
            $internship->save();

            // Create status change history with defense information
            InternshipStatusChange::create([
                'internship_id' => $internship->id,
                'internship_status_id' => $defendedStatus->id,
                'changed_by_user_id' => null, // null = external system
                'status_changed_at' => now(),
                'notes' =>
                    "Obhájená externým systémom\n" .
                    "Dátum obhajoby: {$request->defense_date}\n" .
                    "Výsledok: {$request->defense_result}\n" .
                    ($request->defense_grade ? "Známka: {$request->defense_grade}\n" : "") .
                    "API Token ID: " . $request->user()->currentAccessToken()->id,
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
                    'defense_date' => $request->defense_date,
                    'defense_result' => $request->defense_result,
                    'defense_grade' => $request->defense_grade,
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