<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Organisasi (KONI, Pengprov, OKP, Kwarda, dll.)
        Schema::create('organisasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_id')->constrained('jenis')->cascadeOnDelete();
            $table->string('nama');
            $table->text('alamat')->nullable();
            $table->string('telp', 20)->nullable();
            $table->string('narahubung')->nullable();
            $table->string('email')->nullable();
            $table->string('sk_pendirian')->nullable();
            $table->date('tgl_sk_pendirian')->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->enum('status', ['Aktif', 'Non-Aktif'])->default('Aktif');
            $table->foreignId('skala_id')->nullable()->constrained('skala')->nullOnDelete();
            $table->foreignId('kab_kota_id')->nullable()->constrained('kab_kota')->nullOnDelete();
            $table->string('logo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisasi');
    }
};
