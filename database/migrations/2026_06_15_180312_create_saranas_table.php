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
        Schema::create('sarana', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kab_kota_id')->nullable();
            $table->unsignedBigInteger('jenis_id')->nullable();
            $table->string('kode_inventaris', 100)->nullable()->comment('Nomor registrasi aset/BMD');
            $table->string('nama_barang', 255);
            $table->text('spesifikasi')->nullable();
            $table->enum('kondisi', ['baik','rusak_ringan','rusak_berat','butuh_perbaikan','dalam_perbaikan','tidak_layak'])->default('baik');
            $table->enum('status', ['tersedia','dipakai','dipinjam','dipelihara','hilang','rusak_total','dijual','dimusnahkan'])->default('tersedia');
            $table->string('foto_barang', 255)->nullable();
            $table->unsignedBigInteger('cabor_id')->nullable()->comment('Hanya diisi jika sarana olahraga');
            $table->enum('posisi_aset', ['prasarana', 'internal_dinas'])->default('internal_dinas')->comment('Flagging posisi barang');
            $table->unsignedBigInteger('lokasi_barang')->nullable()->comment('Diisi ID Prasarana jika posisi_aset = prasarana');
            $table->text('keterangan_lokasi')->nullable()->comment('Wajib diisi nama ruangan/gudang jika posisi_aset = internal_dinas');
            $table->unsignedInteger('jumlah')->default(1);
            $table->string('satuan', 50)->default('buah');
            $table->year('tahun_pengadaan')->nullable();
            $table->string('sumber_dana', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sarana');
    }
};
