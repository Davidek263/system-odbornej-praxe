<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info(' Starting database seeding...');
        $this->command->newLine();

        $this->call([
            // Lookup tables first
            RoleSeeder::class,
            InternshipStatusSeeder::class,
            DocumentSeeder::class,
            DocumentTypeSeeder::class,
            TimesheetStatusSeeder::class,
            StudyFieldSeeder::class,
            
            // Then users (creates addresses and companies too)
            UserSeeder::class,
            
            // Finally internships (needs users and companies)
            InternshipSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info(' Database seeding completed successfully!');
        $this->command->newLine();
        
        $this->command->info('  Test Login Credentials:');
        $this->command->info('  Student 1: jan.student@stuba.sk / password');
        $this->command->info('  Student 2: maria.novakova@stuba.sk / password');
        $this->command->info('  Student 3: peter.horvath@stuba.sk / password');
        $this->command->info('  Company:   company@techsolutions.sk / password');
        $this->command->info('  Guarantor: garant@stuba.sk / password');
        $this->command->newLine();
        
        $this->command->info('   Data Created:');
        $this->command->info('  - 4 Roles');
        $this->command->info('  - 6 Internship Statuses');
        $this->command->info('  - 4 Document Types');
        $this->command->info('  - 3 Timesheet Statuses');
        $this->command->info('  - 7 Study Fields');
        $this->command->info('  - 5 Users (3 students, 1 company, 1 guarantor)');
        $this->command->info('  - 3 Companies with addresses');
        $this->command->info('  - 10 Internships with status history');
    }
}