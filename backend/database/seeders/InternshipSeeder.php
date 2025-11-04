<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InternshipSeeder extends Seeder
{
    /**
     * Seed 10 test internships with various statuses
     */
    public function run(): void
    {
        // Get users
        $students = DB::table('users')
            ->join('roles', 'users.roles_id', '=', 'roles.id')
            ->where('roles.role_name', 'student')
            ->select('users.id')
            ->get()
            ->pluck('id')
            ->toArray();
        
        // Get companies
        $companies = DB::table('company')->pluck('id')->toArray();
        
        // Get statuses
        $statuses = DB::table('internship_status')->get()->keyBy('internship_status_name');
        
        if (empty($students) || empty($companies)) {
            $this->command->error('No students or companies found! Run UserSeeder first.');
            return;
        }
        
        // Create 10 internships with different statuses
        $internships = [
            // Recently created internships (Vytvorená)
            [
                'academic_year' => '2024/2025',
                'semester' => 2,
                'date_start' => '2025-02-01',
                'date_end' => '2025-05-31',
                'users_id' => $students[0] ?? $students[0],
                'company_id' => $companies[0] ?? $companies[0],
                'current_status_id' => $statuses['Vytvorená']->id ?? null,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2)
            ],
            [
                'academic_year' => '2024/2025',
                'semester' => 2,
                'date_start' => '2025-02-15',
                'date_end' => '2025-06-15',
                'users_id' => $students[1] ?? $students[0],
                'company_id' => $companies[1] ?? $companies[0],
                'current_status_id' => $statuses['Vytvorená']->id ?? null,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1)
            ],
            
            // Confirmed by company (Potvrdená)
            [
                'academic_year' => '2024/2025',
                'semester' => 2,
                'date_start' => '2025-02-01',
                'date_end' => '2025-05-31',
                'users_id' => $students[2] ?? $students[0],
                'company_id' => $companies[2] ?? $companies[0],
                'current_status_id' => $statuses['Potvrdená']->id ?? null,
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(5)
            ],
            [
                'academic_year' => '2024/2025',
                'semester' => 2,
                'date_start' => '2025-03-01',
                'date_end' => '2025-06-30',
                'users_id' => $students[0] ?? $students[0],
                'company_id' => $companies[1] ?? $companies[0],
                'current_status_id' => $statuses['Potvrdená']->id ?? null,
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(8)
            ],
            
            // Approved by guarantor (Schválená)
            [
                'academic_year' => '2024/2025',
                'semester' => 2,
                'date_start' => '2025-02-01',
                'date_end' => '2025-05-31',
                'users_id' => $students[1] ?? $students[0],
                'company_id' => $companies[0] ?? $companies[0],
                'current_status_id' => $statuses['Schválená']->id ?? null,
                'created_at' => now()->subDays(20),
                'updated_at' => now()->subDays(15)
            ],
            [
                'academic_year' => '2024/2025',
                'semester' => 1,
                'date_start' => '2024-09-01',
                'date_end' => '2024-12-31',
                'users_id' => $students[2] ?? $students[0],
                'company_id' => $companies[1] ?? $companies[0],
                'current_status_id' => $statuses['Schválená']->id ?? null,
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subMonths(2)
            ],
            
            // Successfully defended (Obhájená)
            [
                'academic_year' => '2023/2024',
                'semester' => 2,
                'date_start' => '2024-02-01',
                'date_end' => '2024-05-31',
                'users_id' => $students[0] ?? $students[0],
                'company_id' => $companies[2] ?? $companies[0],
                'current_status_id' => $statuses['Obhájená']->id ?? null,
                'created_at' => now()->subMonths(9),
                'updated_at' => now()->subMonths(6)
            ],
            [
                'academic_year' => '2023/2024',
                'semester' => 1,
                'date_start' => '2023-09-01',
                'date_end' => '2023-12-31',
                'users_id' => $students[1] ?? $students[0],
                'company_id' => $companies[0] ?? $companies[0],
                'current_status_id' => $statuses['Obhájená']->id ?? null,
                'created_at' => now()->subYear(),
                'updated_at' => now()->subMonths(11)
            ],
            
            // Not defended (Neobhájená)
            [
                'academic_year' => '2023/2024',
                'semester' => 2,
                'date_start' => '2024-02-15',
                'date_end' => '2024-06-15',
                'users_id' => $students[2] ?? $students[0],
                'company_id' => $companies[1] ?? $companies[0],
                'current_status_id' => $statuses['Neobhájená']->id ?? null,
                'created_at' => now()->subMonths(8),
                'updated_at' => now()->subMonths(6)
            ],
            
            // Rejected (Zamietnutá)
            [
                'academic_year' => '2024/2025',
                'semester' => 1,
                'date_start' => '2024-09-15',
                'date_end' => '2025-01-15',
                'users_id' => $students[0] ?? $students[0],
                'company_id' => $companies[2] ?? $companies[0],
                'current_status_id' => $statuses['Zamietnutá']->id ?? null,
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subMonths(2)->addDays(3)
            ],
        ];
        
        foreach ($internships as $internship) {
            $internshipId = DB::table('internship')->insertGetId($internship);
            
            // Create status change history for each internship
            $this->createStatusHistory($internshipId, $internship['current_status_id'], $internship['created_at']);
        }
        
        $this->command->info('✓ Internships seeded successfully!');
        $this->command->info('  2 x Vytvorená (Created)');
        $this->command->info('  2 x Potvrdená (Confirmed by company)');
        $this->command->info('  2 x Schválená (Approved by guarantor)');
        $this->command->info('  2 x Obhájená (Successfully defended)');
        $this->command->info('  1 x Neobhájená (Not defended)');
        $this->command->info('  1 x Zamietnutá (Rejected)');
    }
    
    /**
     * Create realistic status change history
     */
    private function createStatusHistory($internshipId, $currentStatusId, $createdAt)
    {
        if (!$currentStatusId) return;
        
        // Get all statuses in order
        $statuses = DB::table('internship_status')
            ->orderBy('order')
            ->get()
            ->keyBy('internship_status_name');
        
        $currentStatus = DB::table('internship_status')->find($currentStatusId);
        
        // Get a guarantor user for status changes
        $guarantor = DB::table('users')
            ->join('roles', 'users.roles_id', '=', 'roles.id')
            ->where('roles.role_name', 'guarantor')
            ->select('users.id')
            ->first();
        
        // Build status progression based on current status
        $progression = [];
        
        switch ($currentStatus->internship_status_name) {
            case 'Vytvorená':
                $progression = [
                    ['status' => 'Vytvorená', 'days_ago' => 0]
                ];
                break;
                
            case 'Potvrdená':
                $progression = [
                    ['status' => 'Vytvorená', 'days_ago' => 3],
                    ['status' => 'Potvrdená', 'days_ago' => 0]
                ];
                break;
                
            case 'Schválená':
                $progression = [
                    ['status' => 'Vytvorená', 'days_ago' => 7],
                    ['status' => 'Potvrdená', 'days_ago' => 5],
                    ['status' => 'Schválená', 'days_ago' => 0]
                ];
                break;
                
            case 'Obhájená':
            case 'Neobhájená':
                $progression = [
                    ['status' => 'Vytvorená', 'days_ago' => 120],
                    ['status' => 'Potvrdená', 'days_ago' => 115],
                    ['status' => 'Schválená', 'days_ago' => 110],
                    ['status' => $currentStatus->internship_status_name, 'days_ago' => 0]
                ];
                break;
                
            case 'Zamietnutá':
                $progression = [
                    ['status' => 'Vytvorená', 'days_ago' => 5],
                    ['status' => 'Zamietnutá', 'days_ago' => 0]
                ];
                break;
        }
        
        foreach ($progression as $change) {
            $status = $statuses[$change['status']] ?? null;
            if (!$status) continue;
            
            $changedAt = Carbon::parse($createdAt)->addDays($change['days_ago']);
            
            DB::table('internship_status_change')->insert([
                'internship_id' => $internshipId,
                'internship_status_id' => $status->id,
                'changed_by_user_id' => $guarantor?->id,
                'status_changed_at' => $changedAt,
                'notes' => $this->getStatusChangeNote($change['status']),
                'created_at' => $changedAt,
                'updated_at' => $changedAt
            ]);
        }
    }
    
    /**
     * Get realistic notes for status changes
     */
    private function getStatusChangeNote($statusName)
    {
        $notes = [
            'Vytvorená' => 'Prax vytvorená študentom',
            'Potvrdená' => 'Potvrdené firmou, študent môže začať',
            'Schválená' => 'Schválené garantom, všetky dokumenty v poriadku',
            'Obhájená' => 'Úspešne obhájená, hodnotenie: výborne',
            'Neobhájená' => 'Neúspešná obhajoba, nedostatočná prezentácia',
            'Zamietnutá' => 'Zamietnuté z dôvodu nedostatočných informácií'
        ];
        
        return $notes[$statusName] ?? null;
    }
}
