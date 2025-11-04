<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

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

    protected $hidden = [
        'password',
        'activation_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'student_email_verified_at' => 'datetime',
        'activated_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'activation_token_expires_at' => 'datetime',
        'active' => 'boolean',
        'must_change_password' => 'boolean',
    ];

    // Relationships
    public function role()
    {
        return $this->belongsTo(Role::class, 'roles_id', 'id');
    }

    public function studyField()
    {
        return $this->belongsTo(StudyField::class, 'study_field_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'id');
    }

    public function internships()
    {
        return $this->hasMany(Internship::class, 'users_id', 'id');
    }

    // Helper methods
    public function hasRole($roleName)
    {
        return $this->role && $this->role->role_name === $roleName;
    }

    public function isStudent()
    {
        return $this->hasRole('student');
    }

    public function isCompany()
    {
        return $this->hasRole('company');
    }

    public function isGuarantor()
    {
        return $this->hasRole('guarantor');
    }

    public function isActive()
    {
        return $this->active === true;
    }

    public function needsPasswordChange()
    {
        return $this->must_change_password === true;
    }
}