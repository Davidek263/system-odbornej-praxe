<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Seed document types (FR-08)
     */
    public function run(): void
    {
        $types = [
            [
                'document_type_name' => 'Dohoda o odbornej praxi',
                'description' => 'Automaticky generovaná dohoda pri vytvorení praxe',
                'is_required' => true,
                'required_at_status' => 'Vytvorená'
            ],
            [
                'document_type_name' => 'Podpísaná zmluva',
                'description' => 'Podpísaná zmluva medzi študentom a firmou',
                'is_required' => true,
                'required_at_status' => 'Schválená'
            ],
            [
                'document_type_name' => 'Výkaz hodín',
                'description' => 'Časový výkaz odpracovaných hodín',
                'is_required' => false,
                'required_at_status' => null
            ],
            [
                'document_type_name' => 'Hodnotenie firmy',
                'description' => 'Hodnotenie študenta firmou',
                'is_required' => false,
                'required_at_status' => null
            ],
        ];

        foreach ($types as $type) {
            DB::table('document_type')->updateOrInsert(
                ['document_type_name' => $type['document_type_name']],
                array_merge($type, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        $this->command->info('✓ Document types seeded successfully!');
    }
}
