<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengurus_organisasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisasi_id')->constrained('organisasi')->cascadeOnDelete();
            $table->foreignId('ketua_id')->nullable()->constrained('orang')->nullOnDelete();
            $table->foreignId('sekretaris_id')->nullable()->constrained('orang')->nullOnDelete();
            $table->foreignId('bendahara_id')->nullable()->constrained('orang')->nullOnDelete();
            $table->integer('jumlah_anggota')->nullable();
            $table->string('sk_kepengurusan')->nullable();
            $table->date('tgl_awal')->nullable();
            $table->date('tgl_akhir')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengurus_organisasi');
    }
};
