<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudyFieldSeeder extends Seeder
{
    /**
     * Seed study fields (FR-03)
     */
    public function run(): void
    {
        $fields = [
            ['study_field_name' => 'Aplikovaná informatika', 'abbreviation' => 'AI'],
            ['study_field_name' => 'Informatika', 'abbreviation' => 'INF'],
            ['study_field_name' => 'Počítačové siete', 'abbreviation' => 'PS'],
            ['study_field_name' => 'Softvérové inžinierstvo', 'abbreviation' => 'SI'],
            ['study_field_name' => 'Informačné systémy', 'abbreviation' => 'IS'],
            ['study_field_name' => 'Umelá inteligencia', 'abbreviation' => 'UI'],
            ['study_field_name' => 'Kybernetická bezpečnosť', 'abbreviation' => 'KB'],
        ];

        foreach ($fields as $field) {
            DB::table('study_field')->updateOrInsert(
                ['study_field_name' => $field['study_field_name']],
                array_merge($field, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        $this->command->info('✓ Study fields seeded successfully!');
    }
}
