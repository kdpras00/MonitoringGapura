<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            // Tambahkan kolom maintenance_id
            $table->unsignedBigInteger('maintenance_id')->nullable()->after('equipment_id');
            
            // Tambahkan foreign key constraint dengan onDelete cascade
            $table->foreign('maintenance_id')
                  ->references('id')
                  ->on('maintenances')
                  ->onDelete('cascade');
        });
        
        // Update data yang sudah ada dengan mengisi maintenance_id berdasarkan equipment_id dan technician_id
        DB::statement("
            UPDATE inspections i
            JOIN maintenances m ON i.equipment_id = m.equipment_id AND i.technician_id = m.technician_id
            SET i.maintenance_id = m.id
            WHERE i.maintenance_id IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            // Hapus foreign key constraint
            $table->dropForeign(['maintenance_id']);
            
            // Hapus kolom maintenance_id
            $table->dropColumn('maintenance_id');
        });
    }
};
