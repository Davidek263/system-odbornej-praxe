<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Company extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'companyName',
        'email',
        'address',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
    ];
}
