<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->string('location')->nullable()->after('email');
            $table->string('pincode', 10)->nullable()->after('location');
            $table->enum('enquiry_type', ['Residential', 'Industrial', 'Commercial'])->nullable()->after('product_service');
            $table->date('next_follow_up_date')->nullable()->after('initial_remark');
            $table->text('follow_up_remark')->nullable()->after('next_follow_up_date');
            $table->decimal('capacity_kw', 10, 2)->nullable()->after('next_follow_up_date');
            $table->enum('finance_type', ['Credit', 'Cash', 'EMI', 'Other'])->nullable()->after('capacity_kw');
            $table->decimal('shadow_free_area_sqft', 10, 2)->nullable()->after('finance_type');
            $table->string('customer_profession')->nullable()->after('shadow_free_area_sqft');
            $table->string('consumer_number', 50)->nullable()->after('customer_profession');

            $table->index('location');
            $table->index('pincode');
            $table->index('next_follow_up_date');
            $table->index('enquiry_type');
        });
    }

    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropIndex(['location']);
            $table->dropIndex(['pincode']);
            $table->dropIndex(['next_follow_up_date']);
            $table->dropIndex(['enquiry_type']);

            $table->dropColumn([
                'location',
                'pincode',
                'enquiry_type',
                'next_follow_up_date',
                'follow_up_remark',
                'capacity_kw',
                'finance_type',
                'shadow_free_area_sqft',
                'customer_profession',
                'consumer_number',
            ]);
        });
    }
};
