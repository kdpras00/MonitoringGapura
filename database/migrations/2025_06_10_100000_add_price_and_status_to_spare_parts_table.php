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
            // Tambah kolom price jika belum ada
            if (!Schema::hasColumn('spare_parts', 'price')) {
                $table->decimal('price', 15, 2)->default(0)->after('min_stock');
            }
            
            // Tambah kolom status jika belum ada
            if (!Schema::hasColumn('spare_parts', 'status')) {
                $table->string('status')->default('available')->after('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spare_parts', function (Blueprint $table) {
            if (Schema::hasColumn('spare_parts', 'price')) {
                $table->dropColumn('price');
            }
            if (Schema::hasColumn('spare_parts', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
}; 