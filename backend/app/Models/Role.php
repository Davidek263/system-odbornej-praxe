<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Role Model
 *
 * Represents user roles in the system that define access levels and permissions.
 * Common roles include: "student", "company", "guarantor", and potentially "admin".
 * Roles determine what actions users can perform and what data they can access.
 *
 * Key Relationships:
 * - Has many Users (role can be assigned to multiple users)
 *
 * Important Attributes:
 * - role_name: Name of the role (e.g., "student", "company", "guarantor")
 *
 * Note: This model does not use timestamps.
 */
class Role extends Model
{
    use HasFactory;

    public $timestamps = false;

    // ============================================================
    // FILLABLE ATTRIBUTES
    // ============================================================

    protected $fillable = [
        'role_name',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    /**
     * Get all users assigned to this role.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'roles_id', 'id');
    }
}
