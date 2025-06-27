<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InspectionMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Menandai migrasi inspections sebagai selesai
        DB::table('migrations')->insert([
            'migration' => '2025_06_27_192102_create_inspections_table',
            'batch' => 13,
        ]);
    }
} 