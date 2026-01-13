<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentSeeder extends Seeder
{
    /**
     * Seed test documents for internships (FR-08)
     * Creates various documents including timesheets with different statuses
     */
    public function run(): void
    {
        // Get document types
        $dohodaType = DB::table('document_type')->where('document_type_name', 'Dohoda o odbornej praxi')->first();
        $zmluvType = DB::table('document_type')->where('document_type_name', 'Podpísaná zmluva')->first();
        $vykazType = DB::table('document_type')->where('document_type_name', 'Výkaz hodín')->first();
        $hodnotenieType = DB::table('document_type')->where('document_type_name', 'Hodnotenie firmy')->first();

        // Get timesheet statuses
        $nahranyStatus = DB::table('timesheet_status')->where('timesheet_status_name', 'Nahraný')->first();
        $potvrdenyStatus = DB::table('timesheet_status')->where('timesheet_status_name', 'Potvrdený')->first();
        $zamietnutyStatus = DB::table('timesheet_status')->where('timesheet_status_name', 'Zamietnutý')->first();

        // Get all internships
        $internships = DB::table('internship')->get();

        if ($internships->isEmpty()) {
            $this->command->warn('No internships found. Run InternshipSeeder first!');
            return;
        }

        $documentsCreated = 0;

        foreach ($internships as $index => $internship) {
            // Get student ID for this internship
            $student = DB::table('users')->find($internship->users_id);
            
            // Get company user for verification
            $companyUser = DB::table('users')
                ->where('company_id', $internship->company_id)
                ->where('roles_id', DB::table('roles')->where('role_name', 'company')->value('id'))
                ->first();

            // 1. Create "Dohoda o odbornej praxi" for all internships
            $dohodaId = DB::table('documents')->insertGetId([
                'document_name' => 'Dohoda o odbornej praxi - ' . $student->first_name . ' ' . $student->last_name,
                'description' => 'Automaticky generovaná dohoda',
                'file_path' => '/storage/documents/dohoda_' . $internship->id . '.pdf',
                'file_name' => 'dohoda_' . $internship->id . '.pdf',
                'file_mime_type' => 'application/pdf',
                'file_size' => 125000,
                'is_required' => true,
                'is_verified' => false,
                'internship_id' => $internship->id,
                'document_type_id' => $dohodaType->id,
                'uploaded_by_user_id' => $student->id,
                'uploaded_at' => now()->subDays(rand(1, 30)),
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(1, 30)),
            ]);
            $documentsCreated++;

            // 2. Create "Podpísaná zmluva" for some internships (those with status Schválená or higher)
            $statusName = DB::table('internship_status')->find($internship->current_status_id)->internship_status_name ?? '';
            
            if (in_array($statusName, ['Schválená', 'Obhájená', 'Neobhájená'])) {
                DB::table('documents')->insert([
                    'document_name' => 'Podpísaná zmluva - ' . $student->first_name . ' ' . $student->last_name,
                    'description' => 'Zmluva podpísaná oboma stranami',
                    'file_path' => '/storage/documents/zmluva_' . $internship->id . '.pdf',
                    'file_name' => 'zmluva_' . $internship->id . '.pdf',
                    'file_mime_type' => 'application/pdf',
                    'file_size' => 230000,
                    'is_required' => true,
                    'is_verified' => true,
                    'verification_notes' => 'Zmluva je v poriadku',
                    'internship_id' => $internship->id,
                    'document_type_id' => $zmluvType->id,
                    'uploaded_by_user_id' => $student->id,
                    'verified_by_user_id' => $companyUser?->id,
                    'uploaded_at' => now()->subDays(rand(5, 25)),
                    'verified_at' => now()->subDays(rand(1, 20)),
                    'created_at' => now()->subDays(rand(5, 25)),
                    'updated_at' => now()->subDays(rand(1, 20)),
                ]);
                $documentsCreated++;
            }

            // 3. Create "Výkaz hodín" (TIMESHEET) with different statuses
            // Create timesheets for different scenarios to test the frontend
            
            // Scenario 1: First 2 internships - Nahraný (uploaded, not yet approved)
            if ($index < 2) {
                $vykazId = DB::table('documents')->insertGetId([
                    'document_name' => 'Výkaz hodín - ' . date('m/Y'),
                    'description' => 'Časový výkaz odpracovaných hodín',
                    'file_path' => '/storage/documents/vykaz_' . $internship->id . '.pdf',
                    'file_name' => 'vykaz_' . $internship->id . '.pdf',
                    'file_mime_type' => 'application/pdf',
                    'file_size' => 85000,
                    'is_required' => false,
                    'is_verified' => false,
                    'internship_id' => $internship->id,
                    'document_type_id' => $vykazType->id,
                    'uploaded_by_user_id' => $student->id,
                    'uploaded_at' => now()->subDays(2),
                    'created_at' => now()->subDays(2),
                    'updated_at' => now()->subDays(2),
                ]);

                // Add timesheet status history - Nahraný
                DB::table('timesheet_status_history')->insert([
                    'documents_id' => $vykazId,
                    'timesheet_status_id' => $nahranyStatus->id,
                    'changed_by_user_id' => $student->id,
                    'status_changed_at' => now()->subDays(2),
                    'notes' => 'Výkaz nahraný študentom',
                    'created_at' => now()->subDays(2),
                    'updated_at' => now()->subDays(2),
                ]);
                $documentsCreated++;
            }
            
            // Scenario 2: Next 2 internships - Potvrdený (approved by company)
            elseif ($index >= 2 && $index < 4) {
                $vykazId = DB::table('documents')->insertGetId([
                    'document_name' => 'Výkaz hodín - ' . date('m/Y'),
                    'description' => 'Časový výkaz odpracovaných hodín',
                    'file_path' => '/storage/documents/vykaz_' . $internship->id . '.pdf',
                    'file_name' => 'vykaz_' . $internship->id . '.pdf',
                    'file_mime_type' => 'application/pdf',
                    'file_size' => 92000,
                    'is_required' => false,
                    'is_verified' => true,
                    'verification_notes' => 'Výkaz schválený firmou',
                    'internship_id' => $internship->id,
                    'document_type_id' => $vykazType->id,
                    'uploaded_by_user_id' => $student->id,
                    'verified_by_user_id' => $companyUser?->id,
                    'uploaded_at' => now()->subDays(5),
                    'verified_at' => now()->subDays(1),
                    'created_at' => now()->subDays(5),
                    'updated_at' => now()->subDays(1),
                ]);

                // Add timesheet status history - Nahraný, then Potvrdený
                DB::table('timesheet_status_history')->insert([
                    [
                        'documents_id' => $vykazId,
                        'timesheet_status_id' => $nahranyStatus->id,
                        'changed_by_user_id' => $student->id,
                        'status_changed_at' => now()->subDays(5),
                        'notes' => 'Výkaz nahraný študentom',
                        'created_at' => now()->subDays(5),
                        'updated_at' => now()->subDays(5),
                    ],
                    [
                        'documents_id' => $vykazId,
                        'timesheet_status_id' => $potvrdenyStatus->id,
                        'changed_by_user_id' => $companyUser?->id,
                        'status_changed_at' => now()->subDays(1),
                        'notes' => 'Schválené firmou - výkaz je v poriadku',
                        'created_at' => now()->subDays(1),
                        'updated_at' => now()->subDays(1),
                    ],
                ]);
                $documentsCreated++;
            }
            
            // Scenario 3: Next 2 internships - Zamietnutý (rejected by company)
            elseif ($index >= 4 && $index < 6) {
                $vykazId = DB::table('documents')->insertGetId([
                    'document_name' => 'Výkaz hodín - ' . date('m/Y'),
                    'description' => 'Časový výkaz odpracovaných hodín',
                    'file_path' => '/storage/documents/vykaz_' . $internship->id . '.pdf',
                    'file_name' => 'vykaz_' . $internship->id . '.pdf',
                    'file_mime_type' => 'application/pdf',
                    'file_size' => 88000,
                    'is_required' => false,
                    'is_verified' => false,
                    'verification_notes' => 'Výkaz zamietnutý - nesúhlasia hodiny',
                    'internship_id' => $internship->id,
                    'document_type_id' => $vykazType->id,
                    'uploaded_by_user_id' => $student->id,
                    'uploaded_at' => now()->subDays(4),
                    'created_at' => now()->subDays(4),
                    'updated_at' => now()->subDays(1),
                ]);

                // Add timesheet status history - Nahraný, then Zamietnutý
                DB::table('timesheet_status_history')->insert([
                    [
                        'documents_id' => $vykazId,
                        'timesheet_status_id' => $nahranyStatus->id,
                        'changed_by_user_id' => $student->id,
                        'status_changed_at' => now()->subDays(4),
                        'notes' => 'Výkaz nahraný študentom',
                        'created_at' => now()->subDays(4),
                        'updated_at' => now()->subDays(4),
                    ],
                    [
                        'documents_id' => $vykazId,
                        'timesheet_status_id' => $zamietnutyStatus->id,
                        'changed_by_user_id' => $companyUser?->id,
                        'status_changed_at' => now()->subDays(1),
                        'notes' => 'Zamietnuté - nesúhlasia odpracované hodiny, prosím opravte',
                        'created_at' => now()->subDays(1),
                        'updated_at' => now()->subDays(1),
                    ],
                ]);
                $documentsCreated++;
            }
            
            // Scenario 4: Remaining internships - No timesheet (to test "Bez výkazu")
            // Don't create any timesheet document

            // 4. Create "Hodnotenie firmy" for completed internships
            if (in_array($statusName, ['Obhájená'])) {
                DB::table('documents')->insert([
                    'document_name' => 'Hodnotenie študenta',
                    'description' => 'Hodnotenie študenta firmou po ukončení praxe',
                    'file_path' => '/storage/documents/hodnotenie_' . $internship->id . '.pdf',
                    'file_name' => 'hodnotenie_' . $internship->id . '.pdf',
                    'file_mime_type' => 'application/pdf',
                    'file_size' => 156000,
                    'is_required' => false,
                    'is_verified' => true,
                    'verification_notes' => 'Hodnotenie prijaté',
                    'internship_id' => $internship->id,
                    'document_type_id' => $hodnotenieType->id,
                    'uploaded_by_user_id' => $companyUser?->id,
                    'verified_by_user_id' => $companyUser?->id,
                    'uploaded_at' => now()->subDays(rand(1, 10)),
                    'verified_at' => now()->subDays(rand(1, 10)),
                    'created_at' => now()->subDays(rand(1, 10)),
                    'updated_at' => now()->subDays(rand(1, 10)),
                ]);
                $documentsCreated++;
            }
        }

        $this->command->info("✓ Documents seeded successfully!");
        $this->command->info("  Created {$documentsCreated} documents");
        $this->command->info("  - Timesheets: 6 (2 Nahraný, 2 Potvrdený, 2 Zamietnutý)");
        $this->command->info("  - Some internships without timesheets for testing 'Bez výkazu'");
    }
}
