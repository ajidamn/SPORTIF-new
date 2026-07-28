<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Data Orang — unified person entity
        Schema::create('orang', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 16)->nullable();
            $table->string('nama');
            $table->date('tgl_lahir')->nullable();
            $table->string('telp', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->boolean('difabel')->default(false);
            $table->string('foto')->nullable();
            $table->decimal('tinggi', 5, 2)->nullable()->comment('cm');
            $table->decimal('berat', 5, 2)->nullable()->comment('kg');
            $table->enum('gol_darah', ['A', 'B', 'AB', 'O'])->nullable();
            $table->foreignId('domisili_id')->nullable()->constrained('kab_kota')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Orang Status — multi-role per orang (atlet+pelatih, etc.)
        Schema::create('orang_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orang_id')->constrained('orang')->cascadeOnDelete();
            $table->foreignId('jenis_id')->constrained('jenis')->cascadeOnDelete();
            $table->foreignId('peran_id')->constrained('peran')->cascadeOnDelete();
            $table->foreignId('cabor_id')->nullable()->constrained('cabors')->nullOnDelete();
            $table->foreignId('organisasi_id')->nullable()->constrained('organisasi')->nullOnDelete();
            $table->string('id_sitenor')->nullable();
            $table->string('sertifikat_profesi')->nullable();
            $table->foreignId('skala_id')->nullable()->constrained('skala')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orang_status');
        Schema::dropIfExists('orang');
    }
};
