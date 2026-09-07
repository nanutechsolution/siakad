<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dosen_pengampus', function (Blueprint $table) {
            $table->unsignedBigInteger('ruang_id')->nullable()->after('kelas_id')
                ->comment('Opsional: Kunci jadwal MK ini di ruang tertentu');
            $table->foreign('ruang_id')->references('id')->on('ref_ruang')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('dosen_pengampus', function (Blueprint $table) {
            $table->dropForeign(['ruang_id']);
            $table->dropColumn('ruang_id');
        });
    }
};
