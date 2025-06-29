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
            // Add completion_date if it doesn't exist
            if (!Schema::hasColumn('inspections', 'completion_date')) {
                $table->dateTime('completion_date')->nullable()->after('inspection_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            // Drop completion_date if exists
            if (Schema::hasColumn('inspections', 'completion_date')) {
                $table->dropColumn('completion_date');
            }
        });
    }
};
