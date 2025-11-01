<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimesheetStatusSeeder extends Seeder
{
    /**
     * Seed timesheet statuses (FR-08)
     */
    public function run(): void
    {
        $statuses = [
            [
                'timesheet_status_name' => 'Nahraný',
                'description' => 'Výkaz nahraný študentom, čaká na schválenie firmou'
            ],
            [
                'timesheet_status_name' => 'Potvrdený',
                'description' => 'Výkaz potvrdený a schválený firmou'
            ],
            [
                'timesheet_status_name' => 'Zamietnutý',
                'description' => 'Výkaz zamietnutý firmou, vyžaduje opravu'
            ],
        ];

        foreach ($statuses as $status) {
            DB::table('timesheet_status')->updateOrInsert(
                ['timesheet_status_name' => $status['timesheet_status_name']],
                array_merge($status, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        $this->command->info('✓ Timesheet statuses seeded successfully!');
    }
}
