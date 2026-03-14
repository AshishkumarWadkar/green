<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define common user permissions
        $userManagement = [
            'manage-users',
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'manage-roles',
        ];

        $enquiryManagement = [
            'view-enquiries',
            'create-enquiries',
            'edit-enquiries',
            'delete-enquiries',
        ];

        // CEO
        $ceoRole = Role::firstOrCreate(['name' => 'CEO', 'guard_name' => 'web']);
        $ceoRole->givePermissionTo(array_merge(['view-dashboard'], $userManagement, $enquiryManagement));

        // MD
        $mdRole = Role::firstOrCreate(['name' => 'MD', 'guard_name' => 'web']);
        $mdRole->givePermissionTo(array_merge(['view-dashboard'], $userManagement, $enquiryManagement));

        // Sales Manager
        $salesManagerRole = Role::firstOrCreate(['name' => 'Sales Manager', 'guard_name' => 'web']);
        $salesManagerRole->givePermissionTo(array_merge(['view-dashboard'], $enquiryManagement));

        // Sales
        $salesRole = Role::firstOrCreate(['name' => 'Sales', 'guard_name' => 'web']);
        $salesRole->givePermissionTo([
            'view-dashboard',
            'view-enquiries',
            'create-enquiries',
            'edit-enquiries',
        ]);
    }
}
