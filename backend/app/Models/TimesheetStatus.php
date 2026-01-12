<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TimesheetStatus Model
 *
 * Represents the various approval statuses a timesheet document can have.
 * Timesheets (výkaz hodín) go through an approval workflow separate from general documents.
 * Common statuses include: "Nový", "Schválený", "Zamietnutý", etc.
 *
 * Key Relationships:
 * - Has many TimesheetStatusHistory (status can appear in many history records)
 *
 * Important Attributes:
 * - timesheet_status_name: Name of the timesheet status (e.g., "Nový", "Schválený")
 * - description: Detailed description of what this status represents
 */
class TimesheetStatus extends Model
{
    use HasFactory;

    protected $table = 'timesheet_status';

    // ============================================================
    // FILLABLE ATTRIBUTES
    // ============================================================

    protected $fillable = [
        'timesheet_status_name',
        'description',
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
     * Get all history records that involve this timesheet status.
     */
    public function timesheetStatusHistory()
    {
        return $this->hasMany(TimesheetStatusHistory::class, 'timesheet_status_id', 'id');
    }
}