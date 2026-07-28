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
        Schema::table('orang', function (Blueprint $table) {
            $table->text('nik')->nullable()->change();
        });

        Schema::table('operators', function (Blueprint $table) {
            $table->text('nik')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orang', function (Blueprint $table) {
            $table->string('nik', 16)->nullable()->change();
        });

        Schema::table('operators', function (Blueprint $table) {
            $table->string('nik', 16)->nullable()->change();
        });
    }
};
