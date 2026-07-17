<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tagihan_mahasiswas', function (Blueprint $table) {
            $table->unsignedBigInteger('tahun_akademik_id')
                ->nullable(false)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('tagihan_mahasiswas', function (Blueprint $table) {
            $table->dropColumn('jenis_tagihan');

            $table->unsignedBigInteger('tahun_akademik_id')
                ->nullable()
                ->change();
        });
    }
};