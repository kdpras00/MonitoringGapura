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
        Schema::table('equipment_reports', function (Blueprint $table) {
            // Mengubah nama kolom menggunakan cara alternatif
            $table->string('issue_description')->after('reporter_id');
            $table->string('issue_image')->nullable()->after('issue_description');
            $table->string('priority')->default('kuning')->after('issue_image');

            // Menambahkan kolom baru
            $table->unsignedBigInteger('approver_id')->nullable()->after('reporter_id');
            $table->unsignedBigInteger('maintenance_id')->nullable()->after('approver_id');
            $table->timestamp('approved_at')->nullable()->after('reported_at');
            $table->text('rejection_reason')->nullable()->after('approved_at');
            
            // Menghapus kolom yang tidak diperlukan
            $table->dropColumn(['description', 'image', 'urgency_level', 'location', 'resolved_at']);
            
            // Menambahkan foreign key
            $table->foreign('approver_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('maintenance_id')->references('id')->on('maintenances')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_reports', function (Blueprint $table) {
            // Menghapus foreign key
            $table->dropForeign(['approver_id']);
            $table->dropForeign(['maintenance_id']);
            
            // Menghapus kolom baru
            $table->dropColumn(['approver_id', 'maintenance_id', 'approved_at', 'rejection_reason', 'issue_description', 'issue_image', 'priority']);
            
            // Menambahkan kembali kolom yang dihapus
            $table->text('description');
            $table->string('image')->nullable();
            $table->string('urgency_level')->default('medium');
            $table->string('location')->nullable();
            $table->timestamp('resolved_at')->nullable();
        });
    }
}; 