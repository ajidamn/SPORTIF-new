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
        Schema::table('sarana', function (Blueprint $table) {
            $table->foreign('kab_kota_id')->references('id')->on('kab_kota')->nullOnDelete();
            $table->foreign('cabor_id')->references('id')->on('cabors')->nullOnDelete();
            $table->foreign('jenis_id')->references('id')->on('jenis')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sarana', function (Blueprint $table) {
            $table->dropForeign(['kab_kota_id']);
            $table->dropForeign(['cabor_id']);
            $table->dropForeign(['jenis_id']);
        });
    }
};
