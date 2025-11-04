<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Internship extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'internship';

    protected $fillable = [
        'academic_year',
        'semester',
        'date_start',
        'date_end',
        'current_status_id',
        'users_id',
        'company_id',
    ];

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
        'semester' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function currentStatus()
    {
        return $this->belongsTo(InternshipStatus::class, 'current_status_id', 'id');
    }

    public function statusHistory()
    {
        return $this->hasMany(InternshipStatusChange::class, 'internship_id', 'id')
            ->orderBy('status_changed_at', 'desc');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'internship_id', 'id');
    }

    // Helper methods
    public function getDurationInDays()
    {
        if (!$this->date_start || !$this->date_end) {
            return 0;
        }

        return \Carbon\Carbon::parse($this->date_start)->diffInDays(\Carbon\Carbon::parse($this->date_end));
    }

    public function getDurationInWeeks()
    {
        return floor($this->getDurationInDays() / 7);
    }

    public function isActive()
    {
        return now()->between($this->date_start, $this->date_end);
    }

    public function hasEnded()
    {
        return now()->gt($this->date_end);
    }

    public function hasStarted()
    {
        return now()->gte($this->date_start);
    }
}