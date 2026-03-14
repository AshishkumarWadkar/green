<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $ceoRole = Role::where('name', 'CEO')->first();
        $mdRole = Role::where('name', 'MD')->first();
        $salesManagerRole = Role::where('name', 'Sales Manager')->first();
        $salesRole = Role::where('name', 'Sales')->first();

        // Create Users
        $users = [
            [
                'name' => 'CEO User',
                'email' => 'ceo@crm.com',
                'password' => Hash::make('Password@1'),
                'role' => $ceoRole
            ],
            [
                'name' => 'MD User',
                'email' => 'md@crm.com',
                'password' => Hash::make('Password@1'),
                'role' => $mdRole
            ],
            [
                'name' => 'Sales Manager User',
                'email' => 'salesmanager@crm.com',
                'password' => Hash::make('Password@1'),
                'role' => $salesManagerRole
            ],
            [
                'name' => 'Sales User',
                'email' => 'sales@crm.com',
                'password' => Hash::make('Password@1'),
                'role' => $salesRole
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
            if ($role && !$user->hasRole($role->name)) {
                $user->assignRole($role);
            }
        }

        $this->command->info('Users created successfully!');
        $this->command->info('CEO: ceo@crm.com / Password@1');
        $this->command->info('MD: md@crm.com / Password@1');
        $this->command->info('Sales Manager: salesmanager@crm.com / Password@1');
        $this->command->info('Sales: sales@crm.com / Password@1');
    }
}
