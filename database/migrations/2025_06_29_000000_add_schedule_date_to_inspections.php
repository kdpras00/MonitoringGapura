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
        Schema::table('inspections', function (Blueprint $table) {
            // Add schedule_date column if it doesn't exist
            if (!Schema::hasColumn('inspections', 'schedule_date')) {
                $table->dateTime('schedule_date')->after('inspection_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            // Drop schedule_date column if it exists
            if (Schema::hasColumn('inspections', 'schedule_date')) {
                $table->dropColumn('schedule_date');
            }
        });
    }
}; 