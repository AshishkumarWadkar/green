<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiry_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_id')->constrained('enquiries')->onDelete('cascade');
            $table->date('follow_up_date');
            $table->enum('previous_status', ['Pending', 'Accepted', 'Cancelled']);
            $table->enum('new_status', ['Pending', 'Accepted', 'Cancelled']);
            $table->text('remark');
            $table->date('next_follow_up_date')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['enquiry_id', 'follow_up_date']);
            $table->index('new_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiry_follow_ups');
    }
};
