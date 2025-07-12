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
        // Periksa apakah tabel equipment sudah ada
        if (!Schema::hasTable('equipment')) {
            // Jika tabel equipments ada, kita akan menggunakan view atau tabel baru
            if (Schema::hasTable('equipments')) {
                // Buat view bernama equipment yang mengacu ke equipments
                DB::statement("CREATE VIEW equipment AS SELECT * FROM equipments");
            } else {
                // Jika equipments tidak ada juga, buat tabel equipment
                Schema::create('equipment', function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->string('serial_number')->unique()->nullable();
                    $table->timestamp('installation_date')->default(DB::raw('CURRENT_TIMESTAMP'));
                    $table->text('description')->nullable();
                    $table->string('status')->default('active');
                    $table->string('manual_url')->nullable();
                    $table->string('specifications')->nullable();
                    $table->longText('qr_code')->nullable();
                    $table->string('location')->default('unknown');
                    $table->string('type')->nullable();
                    $table->string('priority')->nullable();
                    $table->timestamp('last_maintenance_date')->nullable();
                    $table->timestamp('next_maintenance_date')->nullable();
                    $table->string('barcode')->nullable();
                    $table->timestamps();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Jika yang dibuat adalah view, hapus view
        if (!Schema::hasTable('equipments') && Schema::hasTable('equipment')) {
            DB::statement("DROP VIEW IF EXISTS equipment");
        }
    }
};
