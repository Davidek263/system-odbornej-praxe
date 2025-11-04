<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'documents';

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

    // Relationships
    public function internship()
    {
        return $this->belongsTo(Internship::class, 'internship_id', 'id');
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id', 'id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id', 'id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id', 'id');
    }

    public function timesheetStatusHistory()
    {
        return $this->hasMany(TimesheetStatusHistory::class, 'documents_id', 'id')
            ->orderBy('status_changed_at', 'desc');
    }
}