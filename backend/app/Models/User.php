<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'roles_id',
    ];

    protected $hidden = [
        'password',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'roles_id', 'id');
    }

    public function hasRole($roleName)
    {
        return $this->role && $this->role->role_name === $roleName;
    }
}
