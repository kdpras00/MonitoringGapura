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
        // Alih-alih menghapus dan membuat ulang, kita tambahkan kolom yang mungkin belum ada
        Schema::table('maintenances', function (Blueprint $table) {
            // Cek apakah kolom attachments belum ada
            if (!Schema::hasColumn('maintenances', 'attachments')) {
                $table->json('attachments')->nullable();
            }
            
            // Pastikan kolom actual_date bisa nullable
            if (Schema::hasColumn('maintenances', 'actual_date')) {
                // Ubah tipe data dan nullable status menggunakan DB statement
                DB::statement('ALTER TABLE `maintenances` MODIFY `actual_date` datetime NULL');
            } else {
                // Jika kolom belum ada, tambahkan
                $table->dateTime('actual_date')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus kolom yang ditambahkan jika perlu rollback
        Schema::table('maintenances', function (Blueprint $table) {
            if (Schema::hasColumn('maintenances', 'attachments')) {
                $table->dropColumn('attachments');
            }
        });
    }
};
