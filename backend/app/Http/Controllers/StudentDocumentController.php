<?php

namespace App\Http\Controllers;

// ============================================================
// IMPORTS
// ============================================================
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Student Document Controller
 *
 * Manages document operations for students including upload, download, and deletion.
 * Handles document type retrieval and internship-specific document access.
 * Ensures students can only access documents for their own internships.
 */
class StudentDocumentController extends Controller
{
    // ======================================
    // GET INTERNSHIP DOCUMENTS
    // ======================================
    /**
     * Get all documents for a specific internship
     * GET /student/internships/{internshipId}/documents
     */
    public function getInternshipDocuments($internshipId)
    {
        try {
            $user = auth()->user();

            // Get the internship and verify ownership
            $internship = Internship::with([
                'documents.documentType',
                'documents.uploadedBy',
                'documents.verifiedBy',
                'documents.timesheetStatusHistory.status',
                'documents.timesheetStatusHistory.changedByUser'
            ])->findOrFail($internshipId);

            // Verify that the student owns this internship
            if ($internship->users_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized to view these documents.',
                ], 403);
            }

            return response()->json([
                'documents' => $internship->documents,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch documents.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ======================================
    // UPLOAD DOCUMENT
    // ======================================
    /**
     * Upload a new document
     * POST /student/internships/{internshipId}/documents
     */
    public function uploadDocument(Request $request, $internshipId)
    {
        $validator = Validator::make($request->all(), [
            'document_type_id' => 'required|exists:document_type,id',
            'document_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = auth()->user();

            // Get the internship and verify ownership
            $internship = Internship::findOrFail($internshipId);

            if ($internship->users_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized to upload documents for this internship.',
                ], 403);
            }

            // Handle file upload
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('documents', $filename, 'public');

            // Get document type
            $documentType = DocumentType::find($request->document_type_id);

            // Create document record
            $document = Document::create([
                'document_name' => $request->document_name ?? $documentType->document_type_name,
                'description' => $request->description,
                'file_path' => '/storage/' . $path,
                'file_name' => $filename,
                'file_mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'is_required' => false,
                'is_verified' => false,
                'internship_id' => $internshipId,
                'document_type_id' => $request->document_type_id,
                'uploaded_by_user_id' => $user->id,
                'uploaded_at' => now(),
            ]);

            // Load relationships
            $document->load('documentType', 'uploadedBy');

            return response()->json([
                'message' => 'Document uploaded successfully.',
                'document' => $document,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload document.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ======================================
    // DOWNLOAD DOCUMENT
    // ======================================
    /**
     * Download a document
     * GET /student/documents/{documentId}/download
     */
    public function downloadDocument($documentId)
    {
        try {
            $user = auth()->user();

            $document = Document::with('internship')->findOrFail($documentId);

            // Verify that the student owns the internship
            if ($document->internship->users_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized to download this document.',
                ], 403);
            }

            // Get the file path (remove /storage/ prefix to get actual storage path)
            $filePath = str_replace('/storage/', '', $document->file_path);

            if (!Storage::disk('public')->exists($filePath)) {
                return response()->json([
                    'message' => 'File not found.',
                ], 404);
            }

            return Storage::disk('public')->download($filePath, $document->file_name);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to download document.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ======================================
    // DELETE DOCUMENT
    // ======================================
    /**
     * Delete a document
     * DELETE /student/documents/{documentId}
     */
    public function deleteDocument($documentId)
    {
        try {
            $user = auth()->user();

            $document = Document::with('internship')->findOrFail($documentId);

            // Verify that the student owns the internship
            if ($document->internship->users_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized to delete this document.',
                ], 403);
            }

            // Delete the file from storage
            $filePath = str_replace('/storage/', '', $document->file_path);
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            // Delete the document record
            $document->delete();

            return response()->json([
                'message' => 'Document deleted successfully.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete document.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ======================================
    // GET DOCUMENT TYPES
    // ======================================
    /**
     * Get all available document types
     * GET /student/document-types
     */
    public function getDocumentTypes()
    {
        try {
            $documentTypes = DocumentType::all();

            return response()->json([
                'documentTypes' => $documentTypes,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch document types.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
