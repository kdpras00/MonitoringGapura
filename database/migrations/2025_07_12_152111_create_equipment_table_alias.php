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
        // Cek apakah tabel equipment sudah ada
        if (!Schema::hasTable('equipment')) {
            // Buat tabel equipment dengan struktur yang sama dengan equipments
            Schema::create('equipment', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('serial_number')->unique()->nullable();
                $table->timestamp('installation_date')->nullable();
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
            
            // Jika tabel equipments sudah ada, salin datanya ke tabel equipment
            if (Schema::hasTable('equipments')) {
                $equipments = DB::table('equipments')->get();
                
                foreach ($equipments as $equipment) {
                    $data = (array) $equipment;
                    DB::table('equipment')->insert($data);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
