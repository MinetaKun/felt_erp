<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ArtisanTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $artisans = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone_number' => '1234567890',
                'profile_photo' => null,
                'citizenship_photo' => null,
                'pan_number' => 'ABCDE1234F',
                'department_id' => 1,
                'status' => 'active',
                'skills' => json_encode(['knitting', 'weaving', 'dyeing']),
                'basic_salary' => 50000,
                'bank_account_number' => '12345678901234',
                'is_production_based' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone_number' => '1234567891',
                'profile_photo' => null,
                'citizenship_photo' => null,
                'pan_number' => 'FGHIJ5678K',
                'department_id' => 1,
                'status' => 'active',
                'skills' => json_encode(['knitting', 'quality control']),
                'basic_salary' => 45000,
                'bank_account_number' => '23456789012345',
                'is_production_based' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mike Johnson',
                'email' => 'mike.johnson@example.com',
                'phone_number' => '1234567892',
                'profile_photo' => null,
                'citizenship_photo' => null,
                'pan_number' => 'LMNOP9012Q',
                'department_id' => 2,
                'status' => 'active',
                'skills' => json_encode(['weaving', 'pattern making']),
                'basic_salary' => 48000,
                'bank_account_number' => '34567890123456',
                'is_production_based' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sarah Wilson',
                'email' => 'sarah.wilson@example.com',
                'phone_number' => '1234567893',
                'profile_photo' => null,
                'citizenship_photo' => null,
                'pan_number' => 'QRSTU3456V',
                'department_id' => 2,
                'status' => 'active',
                'skills' => json_encode(['dyeing', 'color matching']),
                'basic_salary' => 42000,
                'bank_account_number' => '45678901234567',
                'is_production_based' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'David Brown',
                'email' => 'david.brown@example.com',
                'phone_number' => '1234567894',
                'profile_photo' => null,
                'citizenship_photo' => null,
                'pan_number' => 'WXYZ7890A',
                'department_id' => 3,
                'status' => 'active',
                'skills' => json_encode(['knitting', 'quality control', 'pattern making']),
                'basic_salary' => 55000,
                'bank_account_number' => '56789012345678',
                'is_production_based' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily.davis@example.com',
                'phone_number' => '1234567895',
                'profile_photo' => null,
                'citizenship_photo' => null,
                'pan_number' => 'BCDEF5678G',
                'department_id' => 1,
                'status' => 'active',
                'skills' => json_encode(['knitting', 'weaving']),
                'basic_salary' => 40000,
                'bank_account_number' => '67890123456789',
                'is_production_based' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Robert Taylor',
                'email' => 'robert.taylor@example.com',
                'phone_number' => '1234567896',
                'profile_photo' => null,
                'citizenship_photo' => null,
                'pan_number' => 'HIJKL9012M',
                'department_id' => 2,
                'status' => 'active',
                'skills' => json_encode(['dyeing', 'quality control']),
                'basic_salary' => 52000,
                'bank_account_number' => '78901234567890',
                'is_production_based' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('artisans')->insert($artisans);
    }
}
