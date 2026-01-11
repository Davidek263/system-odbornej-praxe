<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds the roles table with system roles (FR-01)
     */
    public function run(): void
    {
        $roles = [
            [
                'role_name' => 'student',
                'description' => 'Študent absolvujúci odbornú prax'
            ],
            [
                'role_name' => 'company',
                'description' => 'Firma poskytujúca odbornú prax'
            ],
            [
                'role_name' => 'guarantor',
                'description' => 'Garant odbornej praxe'
            ],
            [
                'role_name' => 'external_system',
                'description' => 'Externý systém pre API prístup (FR-09)'
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['role_name' => $role['role_name']],
                array_merge($role, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        $this->command->info('✓ Roles seeded successfully!');
    }
}
