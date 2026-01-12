<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Company Model
 *
 * Represents a company that provides internship opportunities for students.
 * Stores company information including contact details and associated address.
 *
 * Key Relationships:
 * - Belongs to Address (company has a physical address)
 * - Has many Users (company representatives/contacts)
 * - Has many Internships (company provides internship positions)
 *
 * Important Attributes:
 * - company_name: Official company name
 * - contact_person_name: Primary contact person at the company
 * - contact_person_email: Email of the primary contact
 * - contact_person_phone: Phone number of the primary contact
 * - address_id: Foreign key to associated address
 */
class Company extends Model
{
    use HasFactory;

    protected $table = 'company';

    // ============================================================
    // FILLABLE ATTRIBUTES
    // ============================================================

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

    // ============================================================
    // CASTS
    // ============================================================

    protected $casts = [
        'approved' => 'boolean',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    /**
     * Get the address associated with this company.
     */
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'id');
    }

    /**
     * Get all users (company representatives) associated with this company.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'company_id', 'id');
    }

    /**
     * Get all internships provided by this company.
     */
    public function internships()
    {
        return $this->hasMany(Internship::class, 'company_id', 'id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }
}