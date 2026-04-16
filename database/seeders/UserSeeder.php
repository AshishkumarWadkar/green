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
                'username' => 'ceo',
                'password' => Hash::make('Password@1'),
                'role' => $ceoRole
            ],
            [
                'name' => 'MD User',
                'username' => 'md',
                'password' => Hash::make('Password@1'),
                'role' => $mdRole
            ],
            [
                'name' => 'Sales Manager User',
                'username' => 'salesmanager',
                'password' => Hash::make('Password@1'),
                'role' => $salesManagerRole
            ],
            [
                'name' => 'Sales User',
                'username' => 'sales',
                'password' => Hash::make('Password@1'),
                'role' => $salesRole
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['username' => $userData['username']],
                $userData
            );
            if ($role && !$user->hasRole($role->name)) {
                $user->assignRole($role);
            }
        }

        $this->command->info('Users created successfully!');
        $this->command->info('CEO: ceo / Password@1');
        $this->command->info('MD: md / Password@1');
        $this->command->info('Sales Manager: salesmanager / Password@1');
        $this->command->info('Sales: sales / Password@1');
    }
}
