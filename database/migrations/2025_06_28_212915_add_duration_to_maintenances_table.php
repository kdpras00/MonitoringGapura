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
        Schema::table('maintenances', function (Blueprint $table) {
            // Add the duration column to store maintenance duration in minutes
            if (!Schema::hasColumn('maintenances', 'duration')) {
                $table->integer('duration')->nullable()->after('actual_date')->comment('Duration in minutes');
            }
            
            // Add location fields if they don't exist yet
            if (!Schema::hasColumn('maintenances', 'location_lat')) {
                $table->string('location_lat')->nullable()->after('duration');
            }
            
            if (!Schema::hasColumn('maintenances', 'location_lng')) {
                $table->string('location_lng')->nullable()->after('location_lat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            if (Schema::hasColumn('maintenances', 'duration')) {
                $table->dropColumn('duration');
            }
            
            if (Schema::hasColumn('maintenances', 'location_lat')) {
                $table->dropColumn('location_lat');
            }
            
            if (Schema::hasColumn('maintenances', 'location_lng')) {
                $table->dropColumn('location_lng');
            }
        });
    }
};
