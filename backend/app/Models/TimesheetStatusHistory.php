<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'timesheet_status_history';

    protected $fillable = [
        'documents_id',
        'timesheet_status_id',
        'changed_by_user_id',
        'status_changed_at',
        'notes',
    ];

    protected $casts = [
        'status_changed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function document()
    {
        return $this->belongsTo(Document::class, 'documents_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo(TimesheetStatus::class, 'timesheet_status_id', 'id');
    }

    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id', 'id');
    }
}