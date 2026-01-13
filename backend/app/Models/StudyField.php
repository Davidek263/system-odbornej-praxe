<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * StudyField Model
 *
 * Represents academic study fields or programs that students can be enrolled in.
 * Examples include: "Informatika", "Počítačové siete", "Softvérové inžinierstvo", etc.
 * Study fields are used to categorize students and track their academic programs.
 *
 * Key Relationships:
 * - Has many Users (students belong to study fields)
 *
 * Important Attributes:
 * - study_field_name: Full name of the study field
 * - abbreviation: Short abbreviation of the study field (e.g., "INF", "SI")
 */
class StudyField extends Model
{
    use HasFactory;

    protected $table = 'study_field';

    // ============================================================
    // FILLABLE ATTRIBUTES
    // ============================================================

    protected $fillable = [
        'study_field_name',
        'abbreviation',
    ];

    // ============================================================
    // CASTS
    // ============================================================

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    /**
     * Get all users (students) in this study field.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'study_field_id', 'id');
    }
}
