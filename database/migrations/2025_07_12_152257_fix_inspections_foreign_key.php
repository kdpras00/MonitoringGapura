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
        // Hapus foreign key yang mungkin sudah ada
        Schema::table('inspections', function (Blueprint $table) {
            // Hapus foreign key jika ada
            $foreignKeys = $this->getForeignKeyNames('inspections', 'equipment_id');
            foreach ($foreignKeys as $foreignKey) {
                $table->dropForeign($foreignKey);
            }
            
            // Ubah tipe kolom equipment_id menjadi unsignedBigInteger jika bukan
            if (Schema::hasColumn('inspections', 'equipment_id')) {
                // Cek tipe data saat ini
                $columnType = DB::connection()->getDoctrineColumn('inspections', 'equipment_id')->getType()->getName();
                
                if ($columnType !== 'bigint') {
                    DB::statement('ALTER TABLE `inspections` MODIFY `equipment_id` BIGINT UNSIGNED NOT NULL');
                }
            }
        });
        
        // Tambahkan foreign key yang benar
        Schema::table('inspections', function (Blueprint $table) {
            if (Schema::hasTable('equipment')) {
                // Tambahkan foreign key ke tabel equipment jika ada
                $table->foreign('equipment_id')->references('id')->on('equipment')->onDelete('cascade');
            } else if (Schema::hasTable('equipments')) {
                // Jika tidak, tambahkan foreign key ke tabel equipments
                $table->foreign('equipment_id')->references('id')->on('equipments')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak ada yang perlu di-rollback
    }
    
    /**
     * Dapatkan nama foreign key yang terkait dengan kolom
     */
    protected function getForeignKeyNames($table, $column)
    {
        $foreignKeys = [];
        
        try {
            // Dapatkan semua foreign keys untuk tabel
            $schema = DB::connection()->getDoctrineSchemaManager();
            $tableDetails = $schema->listTableDetails($table);
            
            foreach ($tableDetails->getForeignKeys() as $name => $key) {
                if (in_array($column, $key->getLocalColumns())) {
                    $foreignKeys[] = $name;
                }
            }
        } catch (\Exception $e) {
            // Abaikan jika ada error
        }
        
        return $foreignKeys;
    }
};
