<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerProfessionSeeder extends Seeder
{
    public function run(): void
    {
        $professions = [
            'Business Owner',
            'Salaried',
            'Farmer',
            'Self Employed',
            'Shop Keeper',
            'Contractor',
            'Government Employee',
            'Retired',
            'Other',
        ];

        foreach ($professions as $index => $profession) {
            DB::table('customer_professions')->updateOrInsert(
                ['name' => $profession],
                [
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
