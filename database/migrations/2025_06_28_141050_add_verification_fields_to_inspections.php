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
        Schema::table('inspections', function (Blueprint $table) {
            // Tambahkan field verifikasi
            $table->text('verification_notes')->nullable()->after('notes');
            $table->dateTime('verification_date')->nullable()->after('verification_notes');
            $table->unsignedBigInteger('verified_by')->nullable()->after('verification_date');

            // Ubah enum status untuk mencakup 'verified' dan 'rejected'
            DB::statement("ALTER TABLE inspections MODIFY COLUMN status ENUM('pending', 'completed', 'verified', 'rejected') DEFAULT 'pending'");

            // Tambahkan foreign key untuk verified_by
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            // Kembalikan enum status ke nilai aslinya
            DB::statement("ALTER TABLE inspections MODIFY COLUMN status ENUM('pending', 'completed') DEFAULT 'pending'");

            // Hapus field verifikasi
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['verification_notes', 'verification_date', 'verified_by']);
        });
    }
};
