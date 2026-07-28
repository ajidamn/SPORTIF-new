<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom tipe ke tabel cabors.
     * Cabor dibagi 2: olahraga_prestasi (KONI) dan olahraga_masyarakat (KORMI).
     */
    public function up(): void
    {
        Schema::table('cabors', function (Blueprint $table) {
            $table->enum('tipe', ['olahraga_prestasi', 'olahraga_masyarakat'])
                  ->default('olahraga_prestasi')
                  ->after('nama')
                  ->comment('Jenis cabor: KONI (prestasi) atau KORMI (masyarakat)');
        });
    }

    public function down(): void
    {
        Schema::table('cabors', function (Blueprint $table) {
            $table->dropColumn('tipe');
        });
    }
};
