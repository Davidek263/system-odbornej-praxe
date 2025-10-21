<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    public $timestamps = false;
    protected $table = 'users';
    protected $fillable = ['first_name', 'email', 'password'];
    protected $hidden = ['password'];
}
