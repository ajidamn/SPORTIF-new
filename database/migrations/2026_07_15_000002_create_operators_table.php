<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operators', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 16)->unique();
            $table->string('nama');
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('skala_id');
            $table->unsignedBigInteger('cabor_id')->nullable();
            $table->string('nip', 18)->nullable();
            $table->string('jabatan');
            $table->string('email')->nullable();
            $table->string('no_telp', 20)->nullable();
            $table->unsignedBigInteger('kabkota_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('skala_id')->references('id')->on('skala');
            $table->foreign('cabor_id')->references('id')->on('cabors');
            $table->foreign('kabkota_id')->references('id')->on('kab_kota');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operators');
    }
};
