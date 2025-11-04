<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetStatus extends Model
{
    use HasFactory;

    protected $table = 'timesheet_status';

    protected $fillable = [
        'timesheet_status_name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function timesheetStatusHistory()
    {
        return $this->hasMany(TimesheetStatusHistory::class, 'timesheet_status_id', 'id');
    }
}