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
            // Add inspection_date if it doesn't exist
            if (!Schema::hasColumn('inspections', 'inspection_date')) {
                $table->dateTime('inspection_date')->nullable()->after('status');
            }
            
            // Add location columns if they don't exist
            if (!Schema::hasColumn('inspections', 'location')) {
                $table->string('location')->nullable()->after('checklist');
            }
            
            if (!Schema::hasColumn('inspections', 'location_lat')) {
                $table->string('location_lat')->nullable()->after('location');
            }
            
            if (!Schema::hasColumn('inspections', 'location_lng')) {
                $table->string('location_lng')->nullable()->after('location_lat');
            }
            
            if (!Schema::hasColumn('inspections', 'location_timestamp')) {
                $table->timestamp('location_timestamp')->nullable()->after('location_lng');
            }
            
            // Add verification fields
            $table->text('verification_notes')->nullable()->after('notes');
            $table->timestamp('verification_date')->nullable()->after('verification_notes');
            $table->unsignedBigInteger('verified_by')->nullable()->after('verification_date');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            
            // Rename photo fields to match our model
            if (Schema::hasColumn('inspections', 'photo_before') && !Schema::hasColumn('inspections', 'before_image')) {
                $table->renameColumn('photo_before', 'before_image');
            }
            
            if (Schema::hasColumn('inspections', 'photo_after') && !Schema::hasColumn('inspections', 'after_image')) {
                $table->renameColumn('photo_after', 'after_image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            // Drop verification fields
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['verification_notes', 'verification_date', 'verified_by']);
            
            // Rename back image fields
            if (Schema::hasColumn('inspections', 'before_image') && !Schema::hasColumn('inspections', 'photo_before')) {
                $table->renameColumn('before_image', 'photo_before');
            }
            
            if (Schema::hasColumn('inspections', 'after_image') && !Schema::hasColumn('inspections', 'photo_after')) {
                $table->renameColumn('after_image', 'photo_after');
            }
        });
    }
};
