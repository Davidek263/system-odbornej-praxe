<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * InternshipStatus Model
 *
 * Represents the various workflow statuses an internship can have throughout its lifecycle.
 * Examples include: "Nová žiadosť", "Schválená", "V procese", "Ukončená", etc.
 * Statuses are ordered to represent the workflow progression.
 *
 * Key Relationships:
 * - Has many Internships (status can be current status for multiple internships)
 * - Has many InternshipStatusChange (status can appear in many status change records)
 *
 * Important Attributes:
 * - internship_status_name: Name of the status (e.g., "Nová žiadosť", "Schválená")
 * - description: Detailed description of what this status represents
 * - order: Numerical order for displaying/sorting statuses in workflow sequence
 */
class InternshipStatus extends Model
{
    use HasFactory;

    protected $table = 'internship_status';

    // ============================================================
    // FILLABLE ATTRIBUTES
    // ============================================================

    protected $fillable = [
        'internship_status_name',
        'description',
        'order',
    ];

    // ============================================================
    // CASTS
    // ============================================================

    protected $casts = [
        'order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    /**
     * Get all internships currently having this status.
     */
    public function internships()
    {
        return $this->hasMany(Internship::class, 'current_status_id', 'id');
    }

    /**
     * Get all status change records that involve this status.
     */
    public function statusChanges()
    {
        return $this->hasMany(InternshipStatusChange::class, 'internship_status_id', 'id');
    }
}