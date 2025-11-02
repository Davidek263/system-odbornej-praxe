<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternshipStatus extends Model
{
    use HasFactory;

    protected $table = 'internship_status';

    protected $fillable = [
        'internship_status_name',
        'description',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function internships()
    {
        return $this->hasMany(Internship::class, 'current_status_id', 'id');
    }

    public function statusChanges()
    {
        return $this->hasMany(InternshipStatusChange::class, 'internship_status_id', 'id');
    }
}
