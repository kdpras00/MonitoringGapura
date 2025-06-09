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
        Schema::table('spare_parts', function (Blueprint $table) {
            // Tambah kolom barcode jika belum ada
            if (!Schema::hasColumn('spare_parts', 'barcode')) {
                $table->string('barcode')->nullable()->after('part_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spare_parts', function (Blueprint $table) {
            if (Schema::hasColumn('spare_parts', 'barcode')) {
                $table->dropColumn('barcode');
            }
        });
    }
}; 