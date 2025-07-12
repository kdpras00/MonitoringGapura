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
        Schema::create('equipment_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipment_id'); // Tanpa foreign key constraint
            $table->foreignId('reporter_id')->constrained('users');
            $table->text('description');
            $table->string('urgency_level')->default('medium'); // low, medium, high
            $table->string('status')->default('pending'); // pending, in-review, confirmed, rejected, resolved
            $table->string('image')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('reported_at');
            $table->timestamp('resolved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_reports');
    }
}; 