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
        Schema::table('events', function (Blueprint $table) {
            // Tracking pembuat event
            $table->foreignId('created_by')
                  ->nullable()
                  ->after('disabilitas')
                  ->constrained('users')
                  ->nullOnDelete();

            // Approval workflow
            $table->enum('approval_status', ['approved', 'pending', 'rejected'])
                  ->default('approved')
                  ->after('created_by')
                  ->comment('pending jika kab/kota buat event skala >= Provinsi');

            // Opsional (nullable)
            $table->string('dokumen_pendukung')->nullable()->after('lokasi_kegiatan')
                  ->comment('File SK/proposal/surat tugas');
            $table->unsignedInteger('kapasitas_peserta')->nullable()->after('dokumen_pendukung')
                  ->comment('Batas maks peserta, null = tanpa batas');
        });

        // Unique index UPPER(nama) + tahun + jenis_id + kab_kota_id untuk mencegah duplikasi
        // MySQL tidak support functional index secara langsung,
        // jadi kita gunakan generated column + unique index
        Schema::table('events', function (Blueprint $table) {
            $table->string('nama_upper')->nullable()->after('nama')
                  ->storedAs('UPPER(nama)')
                  ->comment('Generated column untuk duplicate check');
                  
            $table->unique(['nama_upper', 'tahun', 'jenis_id', 'kab_kota_id'], 'events_unique_name_year_jenis_kab');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique('events_unique_name_year_jenis_kab');
            $table->dropColumn([
                'created_by', 
                'approval_status', 
                'dokumen_pendukung', 
                'kapasitas_peserta', 
                'nama_upper'
            ]);
        });
    }
};
