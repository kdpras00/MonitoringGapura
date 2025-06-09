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
        if (Schema::hasTable('maintenances')) {
            return;
        }
        
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('equipment_id');
            $table->string('equipment_name');
            $table->date('scheduled_date');
            $table->date('completion_date')->nullable();
            $table->string('technician');
            $table->string('equipment_type')->comment('elektrik atau non-elektrik');
            $table->string('priority')->comment('merah, kuning, hijau');
            $table->text('notes')->nullable();
            $table->string('before_image')->nullable();
            $table->timestamp('before_image_time')->nullable();
            $table->string('after_image')->nullable();
            $table->timestamp('after_image_time')->nullable();
            $table->json('checklist')->nullable();
            $table->string('status')->default('scheduled')->comment('scheduled, completed');
            $table->integer('duration')->nullable()->comment('durasi dalam menit');
            $table->string('location')->nullable();
            $table->string('location_lat')->nullable();
            $table->string('location_lng')->nullable();
            $table->timestamp('location_timestamp')->nullable();
            $table->text('completion_notes')->nullable();
            $table->string('result')->nullable()->comment('good, partial, failed');
            $table->string('approval_status')->nullable()->comment('pending, approved, rejected');
            $table->text('approval_notes')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('approval_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Jangan hapus tabel jika migrasi ini dijalankan dalam rollback
        // Schema::dropIfExists('maintenances');
    }
}; 