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
        // Hapus dulu foreign key constraint dari tabel maintenance_comments
        Schema::table('maintenance_comments', function (Blueprint $table) {
            $table->dropForeign(['maintenance_id']);
        });

        // Hapus tabel maintenances
        Schema::dropIfExists('maintenances');

        // Buat ulang tabel maintenances dengan struktur yang sudah diperbaiki
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();

            // Foreign key ke tabel equipments
            $table->unsignedBigInteger('equipment_id');
            $table->foreign('equipment_id')->references('id')->on('equipments')->onDelete('cascade');

            // Jadwal maintenance
            $table->dateTime('schedule_date');
            $table->dateTime('next_service_date');
            $table->dateTime('actual_date')->nullable();

            // Foreign key ke tabel users (teknisi)
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');

            // Informasi tambahan
            $table->string('maintenance_type')->nullable();
            $table->string('status')->default('scheduled');
            $table->decimal('cost', 10, 2);
            $table->text('notes');
            $table->text('description')->nullable();
            $table->json('attachments')->nullable();

            $table->timestamps();
        });

        // Kembalikan foreign key constraint ke tabel maintenance_comments
        Schema::table('maintenance_comments', function (Blueprint $table) {
            $table->foreign('maintenance_id')->references('id')->on('maintenances')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus dulu foreign key constraint dari tabel maintenance_comments
        Schema::table('maintenance_comments', function (Blueprint $table) {
            $table->dropForeign(['maintenance_id']);
        });

        // Hapus tabel maintenances
        Schema::dropIfExists('maintenances');

        // Buat ulang tabel maintenances dengan struktur lama
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

        // Kembalikan foreign key constraint ke tabel maintenance_comments
        Schema::table('maintenance_comments', function (Blueprint $table) {
            $table->foreign('maintenance_id')->references('id')->on('maintenances')->onDelete('cascade');
        });
    }
};
