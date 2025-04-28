<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Wet Felting',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Quality Control',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Needling',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Knitting',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dyeing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Weaving',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Research',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('departments')->insert($departments);
    }
}
