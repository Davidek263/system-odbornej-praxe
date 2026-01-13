<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Internship Model
 *
 * Represents a student internship or work placement at a company.
 * Tracks the internship period, status, associated documents, and manages the workflow
 * from application through completion. Supports both "prax" (internship) and "brigada" (job/brigade) types.
 *
 * Key Relationships:
 * - Belongs to User (student - the student performing the internship)
 * - Belongs to Company (the company providing the internship)
 * - Belongs to InternshipStatus (currentStatus - current workflow status)
 * - Has many InternshipStatusChange (statusHistory - complete status change history)
 * - Has many Documents (all documents related to this internship)
 *
 * Important Attributes:
 * - academic_year: Academic year when the internship takes place
 * - semester: Semester number (e.g., 1, 2)
 * - internship_type: Type of work ("prax" or "brigada")
 * - date_start: Start date of the internship
 * - date_end: End date of the internship
 * - current_status_id: Foreign key to the current workflow status
 * - users_id: Foreign key to the student user
 * - company_id: Foreign key to the company
 */
class Internship extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'internship';

    // ============================================================
    // FILLABLE ATTRIBUTES
    // ============================================================

    protected $fillable = [
        'academic_year',
        'semester',
        'internship_type',
        'date_start',
        'date_end',
        'current_status_id',
        'users_id',
        'company_id',
    ];

    // ============================================================
    // CASTS
    // ============================================================

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
        'semester' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    /**
     * Get the student associated with this internship.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

    /**
     * Get the company providing this internship.
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    /**
     * Get the current status of this internship.
     */
    public function currentStatus()
    {
        return $this->belongsTo(InternshipStatus::class, 'current_status_id', 'id');
    }

    /**
     * Get the complete status change history for this internship.
     * Ordered by most recent change first.
     */
    public function statusHistory()
    {
        return $this->hasMany(InternshipStatusChange::class, 'internship_id', 'id')
            ->orderBy('status_changed_at', 'desc');
    }

    /**
     * Get all documents associated with this internship.
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'internship_id', 'id');
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    /**
     * Get the duration of the internship in days.
     *
     * @return int Number of days
     */
    public function getDurationInDays()
    {
        if (!$this->date_start || !$this->date_end) {
            return 0;
        }

        return \Carbon\Carbon::parse($this->date_start)->diffInDays(\Carbon\Carbon::parse($this->date_end));
    }

    /**
     * Get the duration of the internship in weeks.
     *
     * @return int Number of weeks (floored)
     */
    public function getDurationInWeeks()
    {
        return floor($this->getDurationInDays() / 7);
    }

    /**
     * Check if the internship is currently active (between start and end dates).
     *
     * @return bool
     */
    public function isActive()
    {
        return now()->between($this->date_start, $this->date_end);
    }

    /**
     * Check if the internship has ended.
     *
     * @return bool
     */
    public function hasEnded()
    {
        return now()->gt($this->date_end);
    }

    /**
     * Check if the internship has started.
     *
     * @return bool
     */
    public function hasStarted()
    {
        return now()->gte($this->date_start);
    }

    /**
     * Check if this is an internship (prax).
     *
     * @return bool
     */
    public function isPrax()
    {
        return $this->internship_type === 'prax';
    }

    /**
     * Check if this is a job/brigade (brigada).
     *
     * @return bool
     */
    public function isBrigada()
    {
        return $this->internship_type === 'brigada';
    }

    /**
     * Get a human-readable label for the internship type.
     *
     * @return string
     */
    public function getInternshipTypeLabel()
    {
        return $this->internship_type === 'prax' ? 'Odborná prax' : 'Brigáda/Práca';
    }
}