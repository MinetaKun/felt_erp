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

        $this->call(UserTableSeeder::class);
        $this->call(RoleTableSeeder::class);
        $this->call(PermissionTableSeeder::class);
        $this->call(RoleUserTableSeeder::class);
        $this->call(PermissionRoleTableSeeder::class);
        $this->call(DepartmentSeeder::class); // Runs first because Artisan depends on Department
        $this->call(ArtisanSeeder::class);
        $this->call(AttendanceTableSeeder::class);
        $this->call(PettyCashCategorySeeder::class);
        $this->call(PettyCashTransactionSeeder::class);
        $this->call(WoolSupplierSeeder::class);
        $this->call(WoolStockSeeder::class);
        $this->call(RawMaterialSeeder::class);
        $this->call(FinishedProductSeeder::class);
        $this->call([
            OrderSeeder::class,
            ArtisanProductivitySeeder::class,
        ]);
    }
}
