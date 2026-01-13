<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * InternshipStatusChange Model
 *
 * Records every status change that occurs during an internship's lifecycle.
 * Maintains a complete audit trail of status transitions, including who made the change,
 * when it occurred, and optional notes explaining the change.
 *
 * Key Relationships:
 * - Belongs to Internship (the internship that had its status changed)
 * - Belongs to InternshipStatus (the new status that was set)
 * - Belongs to User (changedByUser - who performed the status change)
 *
 * Important Attributes:
 * - internship_id: Foreign key to the internship
 * - internship_status_id: Foreign key to the new status
 * - changed_by_user_id: Foreign key to the user who made the change
 * - status_changed_at: Timestamp when the status change occurred
 * - notes: Optional notes or reason for the status change
 */
class InternshipStatusChange extends Model
{
    use HasFactory;

    protected $table = 'internship_status_change';

    // ============================================================
    // FILLABLE ATTRIBUTES
    // ============================================================

    protected $fillable = [
        'internship_id',
        'internship_status_id',
        'changed_by_user_id',
        'status_changed_at',
        'notes',
    ];

    // ============================================================
    // CASTS
    // ============================================================

    protected $casts = [
        'status_changed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    /**
     * Get the internship associated with this status change.
     */
    public function internship()
    {
        return $this->belongsTo(Internship::class, 'internship_id', 'id');
    }

    /**
     * Get the status that was set during this change.
     */
    public function status()
    {
        return $this->belongsTo(InternshipStatus::class, 'internship_status_id', 'id');
    }

    /**
     * Get the user who performed this status change.
     */
    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id', 'id');
    }
}
