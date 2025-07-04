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
            // Ubah kolom checklist menjadi nullable jika belum
            if (Schema::hasColumn('maintenances', 'checklist')) {
                $table->json('checklist')->nullable()->change();
            }
            
            // Ubah kolom before_image menjadi nullable jika belum
            if (Schema::hasColumn('maintenances', 'before_image')) {
                $table->string('before_image')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            // Kembalikan kolom checklist menjadi tidak nullable
            if (Schema::hasColumn('maintenances', 'checklist')) {
                $table->json('checklist')->nullable(false)->change();
            }
            
            // Kembalikan kolom before_image menjadi tidak nullable
            if (Schema::hasColumn('maintenances', 'before_image')) {
                $table->string('before_image')->nullable(false)->change();
            }
        });
    }
};
