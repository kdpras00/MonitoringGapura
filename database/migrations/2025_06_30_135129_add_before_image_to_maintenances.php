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
        Schema::table('maintenances', function (Blueprint $table) {
            // Tambahkan kolom before_image jika belum ada
            if (!Schema::hasColumn('maintenances', 'before_image')) {
                $table->string('before_image')->nullable()->after('notes');
            }
            
            // Tambahkan kolom after_image jika belum ada
            if (!Schema::hasColumn('maintenances', 'after_image')) {
                $table->string('after_image')->nullable()->after('before_image');
            }
            
            // Tambahkan kolom checklist jika belum ada
            if (!Schema::hasColumn('maintenances', 'checklist')) {
                $table->json('checklist')->nullable()->after('after_image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            // Hapus kolom-kolom yang ditambahkan jika ada
            if (Schema::hasColumn('maintenances', 'before_image')) {
                $table->dropColumn('before_image');
            }
            
            if (Schema::hasColumn('maintenances', 'after_image')) {
                $table->dropColumn('after_image');
            }
            
            if (Schema::hasColumn('maintenances', 'checklist')) {
                $table->dropColumn('checklist');
            }
        });
    }
};
