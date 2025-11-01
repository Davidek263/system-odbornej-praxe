<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed test users - one for each role
     */
    public function run(): void
    {
        // Get role IDs
        $studentRole = DB::table('roles')->where('role_name', 'student')->first();
        $companyRole = DB::table('roles')->where('role_name', 'company')->first();
        $guarantorRole = DB::table('roles')->where('role_name', 'guarantor')->first();
        
        // Get study field ID
        $studyField = DB::table('study_field')->first();
        
        // Get or create companies
        $companies = [];
        
        // Create addresses for companies
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
        
        foreach ($companyAddresses as $addr) {
            $addressId = DB::table('address')->insertGetId(array_merge($addr, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
            
            $companies[] = $addressId;
        }
        
        // Create company records
        $companyData = [
            [
                'company_name' => 'Tech Solutions s.r.o.',
                'contact_person_name' => 'Peter Novák',
                'contact_person_email' => 'peter.novak@techsolutions.sk',
                'contact_person_phone' => '+421 901 234 567',
                'address_id' => $companies[0]
            ],
            [
                'company_name' => 'Digital Agency Plus',
                'contact_person_name' => 'Jana Horváthová',
                'contact_person_email' => 'jana.horvath@digitalagency.sk',
                'contact_person_phone' => '+421 902 345 678',
                'address_id' => $companies[1]
            ],
            [
                'company_name' => 'Software House Pro',
                'contact_person_name' => 'Martin Kováč',
                'contact_person_email' => 'martin.kovac@swhouse.sk',
                'contact_person_phone' => '+421 903 456 789',
                'address_id' => $companies[2]
            ],
        ];
        
        $companyIds = [];
        foreach ($companyData as $company) {
            $companyIds[] = DB::table('company')->insertGetId(array_merge($company, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
        
        // Create student addresses
        $studentAddresses = [
            [
                'street' => 'Družstevná',
                'street_number' => '15',
                'city' => 'Bratislava',
                'postal_code' => '82105',
                'country' => 'Slovakia'
            ],
            [
                'street' => 'Botanická',
                'street_number' => '23',
                'city' => 'Bratislava',
                'postal_code' => '84104',
                'country' => 'Slovakia'
            ],
            [
                'street' => 'Štefánikova',
                'street_number' => '67',
                'city' => 'Košice',
                'postal_code' => '04001',
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
        
        // Create users
        $users = [
            // Student 1
            [
                'first_name' => 'Ján',
                'last_name' => 'Študent',
                'email' => 'jan.student@stuba.sk',
                'student_email' => 'xstudent01@stuba.sk',
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
            // Student 2
            [
                'first_name' => 'Mária',
                'last_name' => 'Nováková',
                'email' => 'maria.novakova@stuba.sk',
                'student_email' => 'xnovakova02@stuba.sk',
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
            // Student 3
            [
                'first_name' => 'Peter',
                'last_name' => 'Horváth',
                'email' => 'peter.horvath@stuba.sk',
                'student_email' => 'xhorvath03@stuba.sk',
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
            // Company user
            [
                'first_name' => 'Company',
                'last_name' => 'Representative',
                'email' => 'company@techsolutions.sk',
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
            // Guarantor
            [
                'first_name' => 'Doc.',
                'last_name' => 'Garant',
                'email' => 'garant@stuba.sk',
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
        
        $this->command->info('✓ Users seeded successfully!');
        $this->command->info('  Student 1: jan.student@stuba.sk / password');
        $this->command->info('  Student 2: maria.novakova@stuba.sk / password');
        $this->command->info('  Student 3: peter.horvath@stuba.sk / password');
        $this->command->info('  Company:   company@techsolutions.sk / password');
        $this->command->info('  Guarantor: garant@stuba.sk / password');
    }
}
