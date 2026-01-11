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

// ✅ Export
use App\Exports\GuarantorInternshipsExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;

class InternshipController extends Controller
{
    /**
     * ✅ Export internships to CSV (Guarantor only)
     * POST /api/guarantor/internships/export
     *
     * FE posiela:
     * columns[] = studyField, academicYear, student, company, dateRange, status
     * stats[]   = created, confirmed, approved, defended, failed, rejected, total
     * filtre: academic_year, status, company, study_field, student, search
     */
    public function exportGuarantorInternships(Request $request)
    {
        $user = auth()->user();

        if (!$user || !$user->hasRole('guarantor')) {
            return response()->json(['message' => 'Unauthorized. Only guarantors can export.'], 403);
        }

        $validated = $request->validate([
            'columns' => ['nullable', 'array'],
            'columns.*' => ['string', 'in:studyField,academicYear,student,company,dateRange,status'],

            'stats' => ['nullable', 'array'],
            'stats.*' => ['string', 'in:created,confirmed,approved,defended,failed,rejected,total'],

            'academic_year' => ['nullable', 'string', 'max:20'],
            'status'        => ['nullable', 'string', 'max:50'],
            'company'       => ['nullable', 'string', 'max:255'],
            'study_field'   => ['nullable', 'string', 'max:255'],
            'student'       => ['nullable', 'string', 'max:255'],
            'search'        => ['nullable', 'string', 'max:255'],
        ]);

        $columns = $validated['columns'] ?? ['studyField','academicYear','student','company','dateRange','status'];
        $stats   = $validated['stats'] ?? ['created','confirmed','approved','defended','failed','rejected','total'];

        $filters = [
            'academic_year' => $validated['academic_year'] ?? null,
            'status'        => $validated['status'] ?? null,
            'company'       => $validated['company'] ?? null,
            'study_field'   => $validated['study_field'] ?? null,
            'student'       => $validated['student'] ?? null,
            'search'        => $validated['search'] ?? null,
        ];

        $filename = 'report_praxe_' . now()->format('Y-m-d_H-i') . '.csv';

        return Excel::download(
            new GuarantorInternshipsExport($filters, $columns, $stats),
            $filename,
            ExcelFormat::CSV,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]
        );
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
     * Get all internships for guarantor with filters (pôvodné filtre cez ID)
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
                $query->whereHas('student', function ($q) use ($request) {
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
     */
    public function updateInternship(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'users_id' => 'required|exists:users,id',
            'company_id' => 'required|exists:company,id',
            'academic_year' => 'required|string|max:9',
            'semester' => 'required|integer|in:1,2',
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
                ->whereHas('role', function ($q) {
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
                'regex:/^\d{4}\/\d{4}$/',
            ],
            'semester' => 'required|integer|in:1,2',
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
     * Update internship (Student only, only when status = Vytvorená)
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

            $internship = Internship::with('currentStatus')->find($id);
            if (!$internship) {
                return response()->json(['message' => 'Internship not found.'], 404);
            }

            if ($internship->users_id !== $user->id) {
                return response()->json([
                    'message' => 'You can only edit your own internships.',
                ], 403);
            }

            if (($internship->currentStatus->internship_status_name ?? null) !== 'Vytvorená') {
                return response()->json([
                    'message' => 'Môžete upravovať iba praxe v stave "Vytvorená".',
                ], 403);
            }

            $internship->update([
                'company_id' => $request->company_id,
                'academic_year' => $request->academic_year,
                'semester' => $request->semester,
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
}
