<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Core tables
        $this->call([
            RoleTableSeeder::class,
            PermissionTableSeeder::class,
            PermissionRoleTableSeeder::class,
            DepartmentTableSeeder::class,
            UserTableSeeder::class,
            RoleUserTableSeeder::class,
        ]);

        // Artisan and related tables
        $this->call([
            ArtisanTableSeeder::class,
            ArtisanSalaryTableSeeder::class,
            PayrollAdvanceTableSeeder::class,
            AttendanceTableSeeder::class,
            ArtisanAttendanceTableSeeder::class,
        ]);

        // Product and Order related tables
        $this->call([
            ProductTableSeeder::class,
            OrderTableSeeder::class,
            OrderAssignmentTableSeeder::class,
        ]);

        // Wool related tables
        $this->call([
            WoolSupplierTableSeeder::class,
            WoolUsageTableSeeder::class,
        ]);

        // Financial tables
        $this->call([
            PettyCashCategoryTableSeeder::class,
            PettyCashTransactionTableSeeder::class,
        ]);

        // Inventory
        $this->call([
            RawMaterialTableSeeder::class,
        ]);
    }
}
