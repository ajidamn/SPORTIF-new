<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jenis (domain): Olahraga Prestasi, Olahraga Masyarakat, Kepemudaan, Kepramukaan
        Schema::create('jenis', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->timestamps();
        });

        // Peran per jenis: Atlet, Pelatih, Wasit/Juri, etc.
        Schema::create('peran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_id')->constrained('jenis')->cascadeOnDelete();
            $table->string('nama');
            $table->timestamps();
        });

        // Skala: Daerah, Provinsi, Nasional, Internasional
        Schema::create('skala', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->timestamps();
        });

        // Kab/Kota Jawa Timur
        Schema::create('kab_kota', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 10);
            $table->enum('type', ['kabupaten', 'kota']);
            $table->string('logo')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });

        // Cabang Olahraga
        Schema::create('cabors', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nama_pengprov')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Nomor Tanding per Cabor
        Schema::create('nomor_tanding', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabor_id')->constrained('cabors')->cascadeOnDelete();
            $table->string('nama');
            $table->enum('kategori', ['Tim', 'Individu'])->default('Individu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nomor_tanding');
        Schema::dropIfExists('cabors');
        Schema::dropIfExists('kab_kota');
        Schema::dropIfExists('skala');
        Schema::dropIfExists('peran');
        Schema::dropIfExists('jenis');
    }
};
