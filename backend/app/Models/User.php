<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'users';
    // ➕ pridávame last_name, aby ho Laravel mohol uložiť
    protected $fillable = ['first_name', 'last_name', 'email', 'password'];
    protected $hidden = ['password'];
}
