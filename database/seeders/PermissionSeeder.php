<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dashboard permissions
        Permission::firstOrCreate(['name' => 'view-dashboard', 'guard_name' => 'web']);

        // Enquiry permissions
        Permission::firstOrCreate(['name' => 'view-enquiries', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create-enquiries', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit-enquiries', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete-enquiries', 'guard_name' => 'web']);

        // User management permissions
        Permission::firstOrCreate(['name' => 'manage-users', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view-users', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create-users', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit-users', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete-users', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'manage-roles', 'guard_name' => 'web']);
    }
}
