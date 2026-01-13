<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Address Model
 *
 * Represents a physical address that can be associated with users and companies.
 * Stores complete address information including street, city, postal code, and country.
 *
 * Key Relationships:
 * - Has many Users (users can have addresses)
 * - Has many Companies (companies must have addresses)
 *
 * Important Attributes:
 * - street: Street name
 * - street_number: Building/house number
 * - city: City name
 * - postal_code: Postal/ZIP code
 * - country: Country name
 */
class Address extends Model
{
    use HasFactory;

    protected $table = 'address';

    // ============================================================
    // FILLABLE ATTRIBUTES
    // ============================================================

    protected $fillable = [
        'street',
        'street_number',
        'city',
        'postal_code',
        'country',
    ];

    // ============================================================
    // CASTS
    // ============================================================

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    /**
     * Get all users associated with this address.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'address_id', 'id');
    }

    /**
     * Get all companies associated with this address.
     */
    public function companies()
    {
        return $this->hasMany(Company::class, 'address_id', 'id');
    }

    // ============================================================
    // ACCESSORS
    // ============================================================

    /**
     * Get the full address as a formatted string.
     */
    public function getFullAddressAttribute()
    {
        return trim(sprintf(
            '%s %s, %s %s, %s',
            $this->street,
            $this->street_number,
            $this->postal_code,
            $this->city,
            $this->country
        ));
    }
}