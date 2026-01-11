<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyField extends Model
{
    use HasFactory;

    protected $table = 'study_field';

    protected $fillable = [
        'study_field_name',
        'abbreviation',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class, 'study_field_id', 'id');
    }
}
