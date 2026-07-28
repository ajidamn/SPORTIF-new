<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel riwayat_event: rekam prestasi/keikutsertaan orang di suatu event.
     *
     * Relasi:
     *   - event_id   → events (event induk)
     *   - cabor_id   → cabors (nomor/cabang yang diikuti)
     *   - orang_id   → orang  (peserta utama: atlet/orang yang berprestasi)
     *   - pelatih_id → orang  (nullable, pelatih pendamping)
     *   - wasit_id   → orang  (nullable, wasit/juri)
     *
     * Validasi di aplikasi: orang_id WAJIB ditemukan di tabel orang sebelum bisa diproses.
     */
    public function up(): void
    {
        Schema::create('riwayat_event', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')
                  ->constrained('events')
                  ->cascadeOnDelete();

            $table->foreignId('cabor_id')
                  ->nullable()
                  ->constrained('cabors')
                  ->nullOnDelete();

            // Peserta utama — wajib ada di tabel orang
            $table->foreignId('orang_id')
                  ->constrained('orang')
                  ->cascadeOnDelete();

            // Pelatih pendamping (opsional)
            $table->foreignId('pelatih_id')
                  ->nullable()
                  ->constrained('orang')
                  ->nullOnDelete();

            // Wasit/Juri (opsional)
            $table->foreignId('wasit_id')
                  ->nullable()
                  ->constrained('orang')
                  ->nullOnDelete();

            // Kategori/nomor pertandingan: "-60kg Putra", "100m Gaya Bebas", dll
            $table->string('kategori')->nullable();

            // Hasil: "Juara 1", "Runner-up", "Semifinalis", dll
            $table->string('prestasi')->nullable();

            // Medali yang diraih (nullable untuk non-perlombaan)
            $table->enum('medali', ['emas', 'perak', 'perunggu', '-'])->nullable();

            $table->date('tanggal')->nullable();
            $table->text('keterangan')->nullable();

            $table->timestamps();

            // Index untuk query cepat per orang
            $table->index(['orang_id', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_event');
    }
};
