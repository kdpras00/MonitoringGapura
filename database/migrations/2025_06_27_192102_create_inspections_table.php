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
        // Cek apakah tabel inspections sudah ada
        if (!Schema::hasTable('inspections')) {
            Schema::create('inspections', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('equipment_id'); // Ubah dari string ke unsignedBigInteger
                $table->unsignedBigInteger('technician_id')->nullable();
                $table->dateTime('inspection_date')->nullable();
                $table->dateTime('completion_date')->nullable();
                $table->enum('status', ['pending', 'completed'])->default('pending');
                $table->text('notes')->nullable();
                $table->string('before_image')->nullable();
                $table->string('after_image')->nullable();
                $table->json('checklist')->nullable();
                $table->string('location')->nullable();
                $table->decimal('location_lat', 10, 7)->nullable();
                $table->decimal('location_lng', 10, 7)->nullable();
                $table->dateTime('location_timestamp')->nullable();
                $table->timestamps();
                
                // Tidak perlu menambahkan foreign key di sini karena sudah ada di migrasi terpisah
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak menghapus tabel karena mungkin digunakan
    }
};
