<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TimesheetStatusHistory Model
 *
 * Records every status change for timesheet documents.
 * Maintains a complete audit trail of timesheet approvals/rejections, including who made
 * the change, when it occurred, and optional notes explaining the decision.
 *
 * Key Relationships:
 * - Belongs to Document (the timesheet document that had its status changed)
 * - Belongs to TimesheetStatus (the new status that was set)
 * - Belongs to User (changedByUser - who performed the status change)
 *
 * Important Attributes:
 * - documents_id: Foreign key to the timesheet document
 * - timesheet_status_id: Foreign key to the new timesheet status
 * - changed_by_user_id: Foreign key to the user who made the change
 * - status_changed_at: Timestamp when the status change occurred
 * - notes: Optional notes or reason for the status change (e.g., rejection reason)
 */
class TimesheetStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'timesheet_status_history';

    // ============================================================
    // FILLABLE ATTRIBUTES
    // ============================================================

    protected $fillable = [
        'documents_id',
        'timesheet_status_id',
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
     * Get the timesheet document associated with this status change.
     */
    public function document()
    {
        return $this->belongsTo(Document::class, 'documents_id', 'id');
    }

    /**
     * Get the timesheet status that was set during this change.
     */
    public function status()
    {
        return $this->belongsTo(TimesheetStatus::class, 'timesheet_status_id', 'id');
    }

    /**
     * Get the user who performed this status change.
     */
    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id', 'id');
    }
}