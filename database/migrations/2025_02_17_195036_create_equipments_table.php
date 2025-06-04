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
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('serial_number')->unique()->nullable();
            // Menggunakan timestamp untuk otomatis mengisi tanggal sekarang
            $table->timestamp('installation_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->string('manual_url')->nullable();
            $table->string('specifications')->nullable();
            $table->longText('qr_code')->nullable();
            $table->string('location')->default('unknown');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
