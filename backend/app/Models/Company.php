<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Company extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'CompanyName', 
        'CompanyEmail', 
        'CompanyAddress', 
        'phone', 
        'password'
    ];
    protected $hidden = ['Password'];
}
