<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed test users with proper email formats
     * Student emails: meno.priezvisko@student.ukf.sk (no diacritics, lowercase)
     */
    public function run(): void
    {
        // Get role IDs
        $studentRole = DB::table('roles')->where('role_name', 'student')->first();
        $companyRole = DB::table('roles')->where('role_name', 'company')->first();
        $guarantorRole = DB::table('roles')->where('role_name', 'guarantor')->first();
        
        // Get study field ID
        $studyField = DB::table('study_field')->where('study_field_name', 'Aplikovaná informatika')->first();
        
        // ============================================
        // CREATE COMPANIES WITH ADDRESSES
        // ============================================
        
        $companyAddresses = [
            [
                'street' => 'Hlavná',
                'street_number' => '123',
                'city' => 'Bratislava',
                'postal_code' => '81101',
                'country' => 'Slovakia'
            ],
            [
                'street' => 'Mlynské Nivy',
                'street_number' => '56',
                'city' => 'Bratislava',
                'postal_code' => '82109',
                'country' => 'Slovakia'
            ],
            [
                'street' => 'Technická',
                'street_number' => '89',
                'city' => 'Košice',
                'postal_code' => '04012',
                'country' => 'Slovakia'
            ],
        ];
        
        $companyAddressIds = [];
        foreach ($companyAddresses as $addr) {
            $companyAddressIds[] = DB::table('address')->insertGetId(array_merge($addr, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
        
        // Create company records
        $companyData = [
            [
                'company_name' => 'Tech Solutions s.r.o.',
                'contact_person_name' => 'Peter Novák',
                'contact_person_email' => 'peter.novak@techsolutions.sk',
                'contact_person_phone' => '+421 901 234 567',
                'address_id' => $companyAddressIds[0]
            ],
            [
                'company_name' => 'Digital Agency Plus',
                'contact_person_name' => 'Jana Horvátová',
                'contact_person_email' => 'jana.horvath@digitalagency.sk',
                'contact_person_phone' => '+421 902 345 678',
                'address_id' => $companyAddressIds[1]
            ],
            [
                'company_name' => 'Software House Pro',
                'contact_person_name' => 'Martin Kováč',
                'contact_person_email' => 'martin.kovac@swhouse.sk',
                'contact_person_phone' => '+421 903 456 789',
                'address_id' => $companyAddressIds[2]
            ],
        ];
        
        $companyIds = [];
        foreach ($companyData as $company) {
            $companyIds[] = DB::table('company')->insertGetId(array_merge($company, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
        
        // ============================================
        // CREATE STUDENT ADDRESSES
        // ============================================
        
        $studentAddresses = [
            [
                'street' => 'Družstevná',
                'street_number' => '15',
                'city' => 'Nitra',
                'postal_code' => '94901',
                'country' => 'Slovakia'
            ],
            [
                'street' => 'Botanická',
                'street_number' => '23',
                'city' => 'Nitra',
                'postal_code' => '94901',
                'country' => 'Slovakia'
            ],
            [
                'street' => 'Štúrova',
                'street_number' => '67',
                'city' => 'Nitra',
                'postal_code' => '94901',
                'country' => 'Slovakia'
            ],
        ];
        
        $studentAddressIds = [];
        foreach ($studentAddresses as $addr) {
            $studentAddressIds[] = DB::table('address')->insertGetId(array_merge($addr, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
        
        // ============================================
        // CREATE USERS
        // ============================================
        
        $users = [
            // ========== STUDENT 1 ==========
            [
                'first_name' => 'Ján',
                'last_name' => 'Študent',
                'email' => 'jan.student@student.ukf.sk',
                'student_email' => 'jan.student@student.ukf.sk',
                'alternative_email' => 'jan.student@gmail.com',
                'phone_number' => '+421 910 111 222',
                'password' => Hash::make('password'),
                'active' => true,
                'must_change_password' => false,
                'activated_at' => now(),
                'email_verified_at' => now(),
                'student_email_verified_at' => now(),
                'study_field_id' => $studyField?->id,
                'address_id' => $studentAddressIds[0],
                'roles_id' => $studentRole?->id,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // ========== STUDENT 2 ==========
            [
                'first_name' => 'Mária',
                'last_name' => 'Nováková',
                'email' => 'maria.novakova@student.ukf.sk',
                'student_email' => 'maria.novakova@student.ukf.sk',
                'alternative_email' => 'maria.novakova@gmail.com',
                'phone_number' => '+421 910 222 333',
                'password' => Hash::make('password'),
                'active' => true,
                'must_change_password' => false,
                'activated_at' => now(),
                'email_verified_at' => now(),
                'student_email_verified_at' => now(),
                'study_field_id' => $studyField?->id,
                'address_id' => $studentAddressIds[1],
                'roles_id' => $studentRole?->id,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // ========== STUDENT 3 ==========
            [
                'first_name' => 'Peter',
                'last_name' => 'Horváth',
                'email' => 'peter.horvath@student.ukf.sk',
                'student_email' => 'peter.horvath@student.ukf.sk',
                'alternative_email' => 'peter.horvath@gmail.com',
                'phone_number' => '+421 910 333 444',
                'password' => Hash::make('password'),
                'active' => true,
                'must_change_password' => false,
                'activated_at' => now(),
                'email_verified_at' => now(),
                'student_email_verified_at' => now(),
                'study_field_id' => $studyField?->id,
                'address_id' => $studentAddressIds[2],
                'roles_id' => $studentRole?->id,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // ========== COMPANY USER 1 ==========
            [
                'first_name' => 'Peter',
                'last_name' => 'Novák',
                'email' => 'peter.novak@techsolutions.sk',
                'student_email' => null,
                'alternative_email' => null,
                'phone_number' => '+421 901 234 567',
                'password' => Hash::make('password'),
                'active' => true,
                'must_change_password' => false,
                'activated_at' => now(),
                'email_verified_at' => now(),
                'company_id' => $companyIds[0],
                'address_id' => null,
                'roles_id' => $companyRole?->id,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // ========== COMPANY USER 2 ==========
            [
                'first_name' => 'Jana',
                'last_name' => 'Horvátová',
                'email' => 'jana.horvath@digitalagency.sk',
                'student_email' => null,
                'alternative_email' => null,
                'phone_number' => '+421 902 345 678',
                'password' => Hash::make('password'),
                'active' => true,
                'must_change_password' => false,
                'activated_at' => now(),
                'email_verified_at' => now(),
                'company_id' => $companyIds[1],
                'address_id' => null,
                'roles_id' => $companyRole?->id,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // ========== COMPANY USER 3 ==========
            [
                'first_name' => 'Martin',
                'last_name' => 'Kováč',
                'email' => 'martin.kovac@swhouse.sk',
                'student_email' => null,
                'alternative_email' => null,
                'phone_number' => '+421 903 456 789',
                'password' => Hash::make('password'),
                'active' => true,
                'must_change_password' => false,
                'activated_at' => now(),
                'email_verified_at' => now(),
                'company_id' => $companyIds[2],
                'address_id' => null,
                'roles_id' => $companyRole?->id,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // ========== GUARANTOR ==========
            [
                'first_name' => 'Doc. Ing.',
                'last_name' => 'Garant PhD.',
                'email' => 'garant@ukf.sk',
                'student_email' => null,
                'alternative_email' => 'garant.personal@gmail.com',
                'phone_number' => '+421 905 123 456',
                'password' => Hash::make('password'),
                'active' => true,
                'must_change_password' => false,
                'activated_at' => now(),
                'email_verified_at' => now(),
                'address_id' => null,
                'roles_id' => $guarantorRole?->id,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];
        
        foreach ($users as $user) {
            DB::table('users')->insert($user);
        }
        
        $this->command->info(' Users seeded successfully!');
        $this->command->newLine();
        $this->command->info(' Student Login Credentials:');
        $this->command->info('  Student 1: jan.student@student.ukf.sk / password');
        $this->command->info('  Student 2: maria.novakova@student.ukf.sk / password');
        $this->command->info('  Student 3: peter.horvath@student.ukf.sk / password');
        $this->command->newLine();
        $this->command->info(' Company Login Credentials:');
        $this->command->info('  Company 1: peter.novak@techsolutions.sk / password');
        $this->command->info('  Company 2: jana.horvath@digitalagency.sk / password');
        $this->command->info('  Company 3: martin.kovac@swhouse.sk / password');
        $this->command->newLine();
        $this->command->info(' Guarantor Login Credentials:');
        $this->command->info('  Guarantor: garant@ukf.sk / password');
        $this->command->newLine();
        $this->command->info(' Note: Student emails follow format: meno.priezvisko@student.ukf.sk (no diacritics)');
    }
}