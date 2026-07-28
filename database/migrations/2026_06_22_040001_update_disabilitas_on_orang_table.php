<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orang', function (Blueprint $table) {
            $table->renameColumn('difabel', 'disabilitas');
        });

        Schema::table('orang', function (Blueprint $table) {
            $table->enum('jenis_disabilitas', [
                'fisik',
                'intelektual',
                'mental',
                'sensorik_netra',
                'sensorik_rungu',
                'ganda',
            ])->nullable()->after('disabilitas');
        });
    }

    public function down(): void
    {
        Schema::table('orang', function (Blueprint $table) {
            $table->dropColumn('jenis_disabilitas');
        });

        Schema::table('orang', function (Blueprint $table) {
            $table->renameColumn('disabilitas', 'difabel');
        });
    }
};
