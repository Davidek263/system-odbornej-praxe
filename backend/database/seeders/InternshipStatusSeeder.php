<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InternshipStatusSeeder extends Seeder
{
    /**
     * Seed internship status workflow (FR-05, FR-06, FR-07)
     */
    public function run(): void
    {
        $statuses = [
            [
                'internship_status_name' => 'Vytvorená',
                'description' => 'Prax vytvorená študentom, čaká na potvrdenie firmou',
                'order' => 1
            ],
            [
                'internship_status_name' => 'Potvrdená',
                'description' => 'Prax potvrdená firmou, čaká na schválenie garantom',
                'order' => 2
            ],
            [
                'internship_status_name' => 'Schválená',
                'description' => 'Prax schválená garantom, môže prebiehať',
                'order' => 3
            ],
            [
                'internship_status_name' => 'Obhájená',
                'description' => 'Prax úspešne obhájená študentom',
                'order' => 4
            ],
            [
                'internship_status_name' => 'Neobhájená',
                'description' => 'Prax nebola úspešne obhájená',
                'order' => 5
            ],
            [
                'internship_status_name' => 'Zamietnutá',
                'description' => 'Prax zamietnutá firmou alebo garantom',
                'order' => 0
            ],
        ];

        foreach ($statuses as $status) {
            DB::table('internship_status')->updateOrInsert(
                ['internship_status_name' => $status['internship_status_name']],
                array_merge($status, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        $this->command->info('✓ Internship statuses seeded successfully!');
    }
}
