<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->date('enquiry_date');
            $table->string('customer_name');
            $table->string('mobile_number', 20);
            $table->string('alternate_mobile', 20)->nullable();
            $table->string('email')->nullable();
            $table->foreignId('enquiry_source_id')->constrained('enquiry_sources')->onDelete('restrict');
            $table->string('product_service')->nullable();
            $table->foreignId('assigned_to')->constrained('users')->onDelete('restrict');
            $table->text('initial_remark')->nullable();
            $table->enum('lead_type', ['Hot', 'Cold', 'Warm']);
            $table->enum('status', ['Accepted', 'Cancelled'])->default('Accepted');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('enquiry_date');
            $table->index('mobile_number');
            $table->index('assigned_to');
            $table->index('lead_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
