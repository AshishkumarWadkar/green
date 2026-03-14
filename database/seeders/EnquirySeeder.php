<?php

namespace Database\Seeders;

use App\Models\Enquiry;
use App\Models\EnquirySource;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnquirySeeder extends Seeder
{
    public function run(): void
    {
        $salesUser = User::role('Sales')->first();
        $source = EnquirySource::first();

        if ($salesUser && $source) {
            Enquiry::create([
                'enquiry_date' => now(),
                'customer_name' => 'John Doe',
                'mobile_number' => '9876543210',
                'enquiry_source_id' => $source->id,
                'assigned_to' => $salesUser->id,
                'lead_type' => 'Hot',
                'status' => 'Accepted',
                'created_by' => $salesUser->id,
                'updated_by' => $salesUser->id,
            ]);

            Enquiry::create([
                'enquiry_date' => now()->subDay(),
                'customer_name' => 'Jane Smith',
                'mobile_number' => '8888888888',
                'enquiry_source_id' => $source->id,
                'assigned_to' => $salesUser->id,
                'lead_type' => 'Cold',
                'status' => 'Cancelled',
                'created_by' => $salesUser->id,
                'updated_by' => $salesUser->id,
            ]);
        }
    }
}
