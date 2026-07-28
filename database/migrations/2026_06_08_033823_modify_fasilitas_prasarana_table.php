<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modifikasi tabel fasilitas_prasarana:
     *   HAPUS: kapasitas (integer), disabilitas (boolean)
     *   TAMBAH: jumlah (integer), keterangan (text)
     *
     * Struktur baru: id, prasarana_id, nama, jumlah, kondisi, keterangan
     */
    public function up(): void
    {
        Schema::table('fasilitas_prasarana', function (Blueprint $table) {
            $table->dropColumn(['kapasitas', 'disabilitas']);
            $table->integer('jumlah')->default(1)->after('nama');
            $table->text('keterangan')->nullable()->after('kondisi');
        });
    }

    public function down(): void
    {
        Schema::table('fasilitas_prasarana', function (Blueprint $table) {
            $table->dropColumn(['jumlah', 'keterangan']);
            $table->integer('kapasitas')->nullable()->after('nama');
            $table->boolean('disabilitas')->default(false)->after('kondisi');
        });
    }
};
