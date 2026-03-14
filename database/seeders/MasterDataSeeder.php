<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Enquiry Sources
        $sources = [
            ['name' => 'Justdial', 'description' => 'Enquiries from Justdial', 'is_active' => true, 'sort_order' => 1],
            ['name' => 'IndiaMART', 'description' => 'Enquiries from IndiaMART', 'is_active' => true, 'sort_order' => 2],
            ['name' => 'Newspaper', 'description' => 'Enquiries from Newspaper ads', 'is_active' => true, 'sort_order' => 3],
            ['name' => 'Facebook', 'description' => 'Enquiries from Facebook', 'is_active' => true, 'sort_order' => 4],
            ['name' => 'Website', 'description' => 'Enquiries from Website', 'is_active' => true, 'sort_order' => 5],
            ['name' => 'Referral', 'description' => 'Enquiries from Referrals', 'is_active' => true, 'sort_order' => 6],
            ['name' => 'Walk-in', 'description' => 'Walk-in enquiries', 'is_active' => true, 'sort_order' => 7],
            ['name' => 'Other', 'description' => 'Other sources', 'is_active' => true, 'sort_order' => 8],
        ];

        foreach ($sources as $source) {
            DB::table('enquiry_sources')->updateOrInsert(
                ['name' => $source['name']],
                $source
            );
        }
    }
}
