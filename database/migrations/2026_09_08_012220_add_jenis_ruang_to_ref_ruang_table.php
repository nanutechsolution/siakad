<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ref_ruang', function (Blueprint $table) {
            $table->string('jenis_ruang', 50)->default('TEORI')->after('nama_ruang')
                ->comment('TEORI, LABORATORIUM, STUDIO');
            $table->unsignedBigInteger('prodi_id')->nullable()->after('radius_meter')
                ->comment('Isi jika ruangan ini eksklusif milik prodi tertentu');

            $table->foreign('prodi_id')->references('id')->on('ref_prodi')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('ref_ruang', function (Blueprint $table) {
            $table->dropForeign(['prodi_id']);
            $table->dropColumn(['jenis_ruang', 'prodi_id']);
        });
    }
};
