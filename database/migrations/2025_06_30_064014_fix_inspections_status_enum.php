<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Konversi ke VARCHAR dulu, lalu kembali ke ENUM untuk memperbaiki masalah
        DB::statement("ALTER TABLE inspections MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'");

        // Update data yang sudah ada dengan literal strings
        DB::table('inspections')
            ->where('status', '=', 'verified')
            ->orWhere('status', '=', 'rejected')
            ->orWhere('status', '=', 'completed')
            ->orWhere('status', '=', 'pending')
            ->update(['status' => DB::raw("status")]);

        // Buat ulang kolom enum dengan quotes
        DB::statement("ALTER TABLE inspections MODIFY COLUMN status ENUM('pending', 'completed', 'verified', 'rejected') NOT NULL DEFAULT 'pending'");

        // Cek definisi ulang tabel
        $results = DB::select("SHOW COLUMNS FROM inspections WHERE Field = 'status'");
        Log::info("Fixed inspections status enum definition", ['column_def' => $results]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu dibalik, karena seharusnya tipe data tetap sama
    }
};
