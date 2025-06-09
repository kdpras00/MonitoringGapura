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
        // Skip jika tabel sudah ada
        if (Schema::hasTable('maintenance_histories')) {
            return;
        }
        
        Schema::create('maintenance_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_id')->constrained('maintenances')->onDelete('cascade');
            $table->string('equipment_id');
            $table->string('status')->comment('completed, approval, etc');
            $table->json('data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Jangan hapus tabel jika migrasi ini dijalankan dalam rollback
        // Schema::dropIfExists('maintenance_histories');
    }
}; 