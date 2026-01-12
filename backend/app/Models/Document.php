<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Document Model
 *
 * Represents documents uploaded for internships, including timesheets, reports, and other required files.
 * Supports soft deletes for safe document management and maintains verification status and history.
 *
 * Key Relationships:
 * - Belongs to Internship (document is associated with a specific internship)
 * - Belongs to DocumentType (defines the type/category of document)
 * - Belongs to User (uploadedBy - who uploaded the document)
 * - Belongs to User (verifiedBy - who verified the document)
 * - Has many TimesheetStatusHistory (tracks status changes for timesheet documents)
 *
 * Important Attributes:
 * - document_name: Name/title of the document
 * - file_path: Server path to the stored file
 * - file_name: Original filename
 * - file_mime_type: MIME type of the file
 * - file_size: Size of the file in bytes
 * - is_required: Whether the document is required for the internship
 * - is_verified: Whether the document has been verified by a guarantor
 * - verification_notes: Notes added during verification
 * - uploaded_at: Timestamp when the document was uploaded
 * - verified_at: Timestamp when the document was verified
 */
class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'documents';

    // ============================================================
    // FILLABLE ATTRIBUTES
    // ============================================================

    protected $fillable = [
        'document_name',
        'description',
        'file_path',
        'file_name',
        'file_mime_type',
        'file_size',
        'is_required',
        'is_verified',
        'verification_notes',
        'internship_id',
        'document_type_id',
        'uploaded_by_user_id',
        'verified_by_user_id',
        'uploaded_at',
        'verified_at',
    ];

    // ============================================================
    // CASTS
    // ============================================================

    protected $casts = [
        'is_required' => 'boolean',
        'is_verified' => 'boolean',
        'file_size' => 'integer',
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    /**
     * Get the internship this document belongs to.
     */
    public function internship()
    {
        return $this->belongsTo(Internship::class, 'internship_id', 'id');
    }

    /**
     * Get the document type (category) of this document.
     */
    public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id', 'id');
    }

    /**
     * Get the user who uploaded this document.
     */
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id', 'id');
    }

    /**
     * Get the user who verified this document.
     */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id', 'id');
    }

    /**
     * Get the timesheet status history for this document (if it's a timesheet).
     * Ordered by most recent status change first.
     */
    public function timesheetStatusHistory()
    {
        return $this->hasMany(TimesheetStatusHistory::class, 'documents_id', 'id')
            ->orderBy('status_changed_at', 'desc');
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    /**
     * Check if this document is a timesheet.
     *
     * @return bool
     */
    public function isTimesheet()
    {
        return $this->documentType && $this->documentType->document_type_name === 'Výkaz hodín';
    }

    /**
     * Get the latest timesheet status for this document.
     * Returns null if the document is not a timesheet or has no status history.
     *
     * @return TimesheetStatus|null
     */
    public function getLatestTimesheetStatus()
    {
        if (!$this->isTimesheet()) {
            return null;
        }

        $latestHistory = $this->timesheetStatusHistory()->first();
        return $latestHistory ? $latestHistory->status : null;
    }
}