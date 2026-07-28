<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Prasarana (venue/facility)
        Schema::create('prasarana', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_id')->nullable()->constrained('jenis')->nullOnDelete();
            $table->foreignId('lokasi_id')->nullable()->constrained('kab_kota')->nullOnDelete();
            $table->string('nama');
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->enum('pengelola', ['Pemerintah', 'Swasta'])->default('Pemerintah');
            $table->string('narahubung')->nullable();
            $table->string('telp_narahubung', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->integer('kapasitas')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Fasilitas per Prasarana
        Schema::create('fasilitas_prasarana', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prasarana_id')->constrained('prasarana')->cascadeOnDelete();
            $table->string('nama');
            $table->integer('kapasitas')->nullable();
            $table->enum('kondisi', ['Baik', 'Rusak Ringan', 'Rusak Berat'])->default('Baik');
            $table->boolean('disabilitas')->default(false);
            $table->timestamps();
        });

        // Cabor yang tersedia per Prasarana
        Schema::create('cabor_prasarana', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prasarana_id')->constrained('prasarana')->cascadeOnDelete();
            $table->foreignId('cabor_id')->constrained('cabors')->cascadeOnDelete();
            $table->timestamps();
        });

        // Foto Prasarana
        Schema::create('foto_prasarana', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prasarana_id')->constrained('prasarana')->cascadeOnDelete();
            $table->string('foto');
            $table->string('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_prasarana');
        Schema::dropIfExists('cabor_prasarana');
        Schema::dropIfExists('fasilitas_prasarana');
        Schema::dropIfExists('prasarana');
    }
};
