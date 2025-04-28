<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User management
            'users-all',
            'users-view',
            'users-create',
            'users-edit',
            'users-delete',

            // Role management
            'roles-all',
            'roles-view',
            'roles-create',
            'roles-edit',
            'roles-delete',

            // Permission management
            'permissions-all',
            'permissions-view',
            'permissions-create',
            'permissions-edit',
            'permissions-delete',

            // Department management
            'departments-all',
            'departments-view',
            'departments-create',
            'departments-edit',
            'departments-delete',

            // Artisan management
            'artisans-all',
            'artisans-view',
            'artisans-create',
            'artisans-edit',
            'artisans-delete',

            // Order management
            'orders-all',
            'orders-view',
            'orders-create',
            'orders-edit',
            'orders-delete',
            'orders-assign',

            // Wool management
            'wool-all',
            'wool-view',
            'wool-create',
            'wool-edit',
            'wool-delete',

            // Attendance management
            'attendance-all',
            'attendance-view',
            'attendance-create',
            'attendance-edit',
            'attendance-delete',

            // Petty cash management
            'petty-cash-view',
            'petty-cash-manage-categories',
            'petty-cash-create',
            'petty-cash-edit',

            // Inventory management
            'inventory-all',
            'inventory-view',
            'inventory-create',
            'inventory-edit',
            'inventory-delete',

            // Payroll management
            'payroll-all',
            'payroll-view',
            'payroll-create',
            'payroll-edit',
            'payroll-delete',
        ];

        $permissions = array_map(function ($name) {
            return [
                'name' => $name,
                'created_at' => now(),
            ];
        }, $permissions);

        DB::table('permissions')->insert($permissions);
    }
}
