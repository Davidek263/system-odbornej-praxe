<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $table = 'document_type';

    protected $fillable = [
        'document_type_name',
        'description',
        'is_required',
        'required_at_status',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function documents()
    {
        return $this->hasMany(Document::class, 'document_type_id', 'id');
    }
}
