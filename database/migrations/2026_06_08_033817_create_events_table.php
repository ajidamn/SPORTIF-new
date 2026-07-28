<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel events: data kegiatan olahraga, kepemudaan, kepramukaan.
     * Tabel cabor_event: pivot event <-> cabor (multi cabor per event).
     *
     * Logika filter cabor:
     *   jenis_id=1 (Olahraga Prestasi)   → cabor tipe=olahraga_prestasi
     *   jenis_id=2 (Olahraga Masyarakat) → cabor tipe=olahraga_masyarakat
     *   jenis_id=3/4                     → tidak memerlukan cabor
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_id')
                  ->constrained('jenis')
                  ->cascadeOnDelete()
                  ->comment('Domain: Olahraga Prestasi, Olahraga Masyarakat, dll');
            $table->string('nama');
            $table->foreignId('skala_id')
                  ->nullable()
                  ->constrained('skala')
                  ->nullOnDelete()
                  ->comment('Daerah, Provinsi, Nasional, Internasional');
            $table->enum('jenis_event', [
                'single event',
                'multi event',
                'pelatihan',
                'perlombaan',
            ])->default('perlombaan');
            $table->string('penyelenggara');
            $table->string('lokasi_kegiatan')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->enum('status', ['aktif', 'selesai', 'dibatalkan'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();
        });

        // Pivot: cabor yang dipertandingkan di event ini
        Schema::create('cabor_event', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                  ->constrained('events')
                  ->cascadeOnDelete();
            $table->foreignId('cabor_id')
                  ->constrained('cabors')
                  ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['event_id', 'cabor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cabor_event');
        Schema::dropIfExists('events');
    }
};
