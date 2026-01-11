<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = 'company';

    protected $fillable = [
        'company_name',
        'contact_person_name',
        'contact_person_email',
        'contact_person_phone',
        'address_id',
        'approved',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'approved' => 'boolean',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'company_id', 'id');
    }

    public function internships()
    {
        return $this->hasMany(Internship::class, 'company_id', 'id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }
}