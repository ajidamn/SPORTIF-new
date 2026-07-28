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
        Schema::table('prasarana', function (Blueprint $table) {
            $table->string('kategori')->nullable()->after('nama');
            $table->string('standar')->default('Belum di Standarisasi')->after('kategori');
            // Ubah pengelola menjadi string untuk mengakomodasi opsi baru
            $table->string('pengelola')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prasarana', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'standar']);
            // Revert back to enum or simply leave as string (safer to leave as string to avoid data loss)
        });
    }
};
