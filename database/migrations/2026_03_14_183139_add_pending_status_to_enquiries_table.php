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
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE enquiries MODIFY COLUMN status ENUM('Pending', 'Accepted', 'Cancelled') NOT NULL DEFAULT 'Pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE enquiries MODIFY COLUMN status ENUM('Accepted', 'Cancelled') NOT NULL DEFAULT 'Accepted'");
    }
};
