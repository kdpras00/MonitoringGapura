<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        // Hanya buat tabel jika belum ada
        if (!Schema::hasTable('maintenances')) {
            Schema::create('maintenances', function (Blueprint $table) {
                $table->id();
    
                // Foreign key ke tabel equipments
                $table->unsignedBigInteger('equipment_id');
                $table->foreign('equipment_id')->references('id')->on('equipments')->onDelete('cascade');
    
                // Jadwal maintenance
                $table->dateTime('schedule_date');
                $table->dateTime('next_service_date');
                $table->dateTime('actual_date');
    
                // Foreign key ke tabel users (teknisi)
                $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
    
                // Informasi tambahan
                $table->string('maintenance_type')->nullable();
                $table->string('status')->default('scheduled');
                $table->decimal('cost', 10, 2);
                $table->text('notes');
                $table->text('description')->nullable();
    
                $table->timestamps();
            });
        }
    }

    /**
     * Rollback migrasi.
     */
    public function down(): void
    {
        // Nothing to do, we don't want to drop the table if it's already in use
    }
};
