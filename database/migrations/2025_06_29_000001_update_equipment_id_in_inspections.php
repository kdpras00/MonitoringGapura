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
        // First, drop the existing foreign key constraint
        Schema::table('inspections', function (Blueprint $table) {
            // Check if the foreign key exists before trying to drop it
            if (Schema::hasColumn('inspections', 'equipment_id')) {
                // Get the foreign key name
                $foreignKeys = collect(\DB::select("SHOW CREATE TABLE inspections"))->first()->{"Create Table"};
                if (preg_match('/CONSTRAINT `(.+?)` FOREIGN KEY \(`equipment_id`\) REFERENCES `equipments`/', $foreignKeys, $matches)) {
                    $foreignKeyName = $matches[1];
                    $table->dropForeign($foreignKeyName);
                }
            }
        });

        // Then, modify the equipment_id column to be an unsignedBigInteger
        Schema::table('inspections', function (Blueprint $table) {
            // Change the equipment_id column type if it exists
            if (Schema::hasColumn('inspections', 'equipment_id')) {
                $table->unsignedBigInteger('equipment_id')->change();
            } else {
                $table->unsignedBigInteger('equipment_id');
            }
            
            // Add the foreign key constraint
            $table->foreign('equipment_id')->references('id')->on('equipments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign key constraint
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropForeign(['equipment_id']);
        });

        // Change the equipment_id column back to a string
        Schema::table('inspections', function (Blueprint $table) {
            $table->string('equipment_id')->change();
        });
    }
}; 