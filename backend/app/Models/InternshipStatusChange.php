<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternshipStatusChange extends Model
{
    use HasFactory;

    protected $table = 'internship_status_change';

    protected $fillable = [
        'internship_id',
        'internship_status_id',
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
    public function internship()
    {
        return $this->belongsTo(Internship::class, 'internship_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo(InternshipStatus::class, 'internship_status_id', 'id');
    }

    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id', 'id');
    }
}
