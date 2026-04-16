<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiry_follow_ups', function (Blueprint $table) {
            $table->boolean('is_done')->default(false)->after('next_follow_up_date');
            $table->index('is_done');
        });

        DB::table('enquiry_follow_ups')
            ->whereIn('new_status', ['Accepted', 'Cancelled'])
            ->update(['is_done' => true]);
    }

    public function down(): void
    {
        Schema::table('enquiry_follow_ups', function (Blueprint $table) {
            $table->dropIndex(['is_done']);
            $table->dropColumn('is_done');
        });
    }
};
