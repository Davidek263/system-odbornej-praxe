<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $table = 'address';

    protected $fillable = [
        'street',
        'street_number',
        'city',
        'postal_code',
        'country',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class, 'address_id', 'id');
    }

    public function companies()
    {
        return $this->hasMany(Company::class, 'address_id', 'id');
    }

    // Helper method to get full address as string
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