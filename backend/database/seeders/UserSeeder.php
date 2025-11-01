<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::insert([
            [
                'first_name'    => 'John',
                'last_name'     => 'Student',
                'email'         => 'student@example.com',
                'password'      => Hash::make('password123'),
                'roles_id'      => 1, // Student
            ],
            [
                'first_name'    => 'Mary',
                'last_name'     => 'Guarantor',
                'email'         => 'guarantor@example.com',
                'password'      => Hash::make('password123'),
                'roles_id'      => 2, // Guarantor
            ],
            [
                'first_name'    => 'Mike',
                'last_name'     => 'Admin',
                'email'         => 'admin@example.com',
                'password'      => Hash::make('password123'),
                'roles_id'      => 3, // Admin
            ],
        ]);
    }
}
