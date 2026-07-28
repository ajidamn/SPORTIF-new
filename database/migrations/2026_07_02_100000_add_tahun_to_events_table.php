<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->unsignedSmallInteger('tahun')->nullable()->after('nama')
                  ->comment('Tahun kegiatan (bisa beda dengan tahun pelaksanaan)');
            $table->index('tahun');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['tahun']);
            $table->dropColumn('tahun');
        });
    }
};
