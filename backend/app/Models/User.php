<?php

namespace App\Models;

// ============================================================
// IMPORTS
// ============================================================

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 *
 * Represents all users in the system including students, company representatives, and guarantors.
 * Extends Laravel's Authenticatable for authentication functionality and includes API token support
 * via Sanctum. Supports soft deletes and email verification.
 *
 * Key Relationships:
 * - Belongs to Role (defines user's role and permissions)
 * - Belongs to StudyField (for students - their field of study)
 * - Belongs to Company (for company representatives)
 * - Belongs to Address (user's physical address)
 * - Has many Internships (for students - their internships)
 *
 * Important Attributes:
 * - first_name, last_name: User's name
 * - email: Primary email address
 * - student_email: Student-specific email (for students)
 * - alternative_email: Alternative contact email
 * - phone_number: Contact phone number
 * - password: Hashed password
 * - must_change_password: Flag indicating user must change password on next login
 * - active: Whether the user account is active
 * - activation_token: Token for account activation
 * - roles_id: Foreign key to user's role
 * - study_field_id: Foreign key to study field (for students)
 * - company_id: Foreign key to company (for company representatives)
 * - address_id: Foreign key to address
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    // ============================================================
    // FILLABLE ATTRIBUTES
    // ============================================================

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'student_email',
        'alternative_email',
        'phone_number',
        'password',
        'must_change_password',
        'password_changed_at',
        'active',
        'activation_token',
        'activation_token_expires_at',
        'activated_at',
        'email_verified_at',
        'student_email_verified_at',
        'study_field_id',
        'company_id',
        'address_id',
        'roles_id',
    ];

    // ============================================================
    // HIDDEN ATTRIBUTES
    // ============================================================

    protected $hidden = [
        'password',
        'activation_token',
    ];

    // ============================================================
    // CASTS
    // ============================================================

    protected $casts = [
        'email_verified_at' => 'datetime',
        'student_email_verified_at' => 'datetime',
        'activated_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'activation_token_expires_at' => 'datetime',
        'active' => 'boolean',
        'must_change_password' => 'boolean',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    /**
     * Get the role assigned to this user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'roles_id', 'id');
    }

    /**
     * Get the study field for this user (applicable to students).
     */
    public function studyField()
    {
        return $this->belongsTo(StudyField::class, 'study_field_id', 'id');
    }

    /**
     * Get the company associated with this user (applicable to company representatives).
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    /**
     * Get the address associated with this user.
     */
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'id');
    }

    /**
     * Get all internships for this user (applicable to students).
     */
    public function internships()
    {
        return $this->hasMany(Internship::class, 'users_id', 'id');
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    /**
     * Check if the user has a specific role.
     *
     * @param string $roleName
     * @return bool
     */
    public function hasRole($roleName)
    {
        return $this->role && $this->role->role_name === $roleName;
    }

    /**
     * Check if the user is a student.
     *
     * @return bool
     */
    public function isStudent()
    {
        return $this->hasRole('student');
    }

    /**
     * Check if the user is a company representative.
     *
     * @return bool
     */
    public function isCompany()
    {
        return $this->hasRole('company');
    }

    /**
     * Check if the user is a guarantor.
     *
     * @return bool
     */
    public function isGuarantor()
    {
        return $this->hasRole('guarantor');
    }

    /**
     * Check if the user account is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->active === true;
    }

    /**
     * Check if the user must change their password on next login.
     *
     * @return bool
     */
    public function needsPasswordChange()
    {
        return $this->must_change_password === true;
    }
}