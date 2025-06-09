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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('email')->unique();
        });
        
        // Isi username untuk user yang sudah ada menggunakan nama depan email
        DB::table('users')->select('id', 'email')->whereNull('username')->get()
            ->each(function ($user) {
                $username = explode('@', $user->email)[0];
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['username' => $username]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
