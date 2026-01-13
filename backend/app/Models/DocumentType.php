<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * DocumentType Model
 *
 * Represents different types/categories of documents in the internship system.
 * Examples include timesheets, internship agreements, reports, etc.
 * Defines which documents are required and at what internship status they become required.
 *
 * Key Relationships:
 * - Has many Documents (document type categorizes multiple documents)
 *
 * Important Attributes:
 * - document_type_name: Name of the document type (e.g., "Výkaz hodín", "Zmluva")
 * - description: Description of the document type and its purpose
 * - is_required: Whether documents of this type are required for internships
 * - required_at_status: At which internship status this document type becomes required
 */
class DocumentType extends Model
{
    use HasFactory;

    protected $table = 'document_type';

    // ============================================================
    // FILLABLE ATTRIBUTES
    // ============================================================

    protected $fillable = [
        'document_type_name',
        'description',
        'is_required',
        'required_at_status',
    ];

    // ============================================================
    // CASTS
    // ============================================================

    protected $casts = [
        'is_required' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    /**
     * Get all documents of this type.
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'document_type_id', 'id');
    }
}
