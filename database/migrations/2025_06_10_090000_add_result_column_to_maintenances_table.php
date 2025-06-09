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
            // Tambah kolom result jika belum ada
            if (!Schema::hasColumn('maintenances', 'result')) {
                $table->string('result')->nullable()->after('status')->comment('good, partial, failed');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            if (Schema::hasColumn('maintenances', 'result')) {
                $table->dropColumn('result');
            }
        });
    }
}; 