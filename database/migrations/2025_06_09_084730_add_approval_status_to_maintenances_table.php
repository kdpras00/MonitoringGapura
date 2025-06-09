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
            // Tambah kolom approval
            if (!Schema::hasColumn('maintenances', 'approval_status')) {
                $table->string('approval_status')->nullable()->default('pending')->comment('pending, approved, rejected');
            }
            if (!Schema::hasColumn('maintenances', 'approval_notes')) {
                $table->text('approval_notes')->nullable();
            }
            if (!Schema::hasColumn('maintenances', 'approved_by')) {
                $table->string('approved_by')->nullable();
            }
            if (!Schema::hasColumn('maintenances', 'approval_date')) {
                $table->timestamp('approval_date')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumn([
                'approval_status',
                'approval_notes',
                'approved_by',
                'approval_date',
            ]);
        });
    }
};
