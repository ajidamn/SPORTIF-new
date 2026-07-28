<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Master Jenis Ekstrakurikuler ───────────────────────
        Schema::create('jenis_ekstrakurikuler', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('kategori', [
                'olahraga', 
                'kepemimpinan', 
                'seni_budaya', 
                'akademik_sains', 
                'keagamaan'
            ])->default('olahraga');
            $table->foreignId('cabor_id')->nullable()->constrained('cabors')->nullOnDelete();
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Data Sekolah ──────────────────────────────────────
        Schema::create('sekolah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kab_kota_id')->constrained('kab_kota')->cascadeOnDelete();
            $table->string('nama_sekolah');
            $table->enum('jenis_sekolah', ['SMA', 'SMK', 'MA', 'SLB']);
            $table->enum('status_sekolah', ['Negeri', 'Swasta']);
            $table->string('narahubung')->nullable();
            $table->string('telepon', 20)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // ── Data Ekstrakurikuler per Sekolah ──────────────────
        Schema::create('ekstrakurikuler_sekolah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolah')->cascadeOnDelete();
            $table->foreignId('jenis_ekskul_id')->constrained('jenis_ekstrakurikuler')->cascadeOnDelete();
            $table->string('nama_pembina');
            $table->unsignedInteger('jumlah_anggota_putra')->default(0);
            $table->unsignedInteger('jumlah_anggota_putri')->default(0);
            $table->string('dokumen_jumlah_anggota')->nullable(); // file path
            $table->string('jadwal_pertemuan')->nullable(); // text bebas
            $table->enum('status_ekstrakurikuler', ['Aktif', 'Non-Aktif'])->default('Aktif');
            $table->string('narahubung')->nullable();
            $table->string('telepon', 20)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekstrakurikuler_sekolah');
        Schema::dropIfExists('sekolah');
        Schema::dropIfExists('jenis_ekstrakurikuler');
    }
};
