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
        Schema::table('tagihan_mahasiswas', function (Blueprint $table) {
            $table->foreign(['created_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['mahasiswa_id'])->references(['id'])->on('mahasiswas')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['tahun_akademik_id'])->references(['id'])->on('ref_tahun_akademik')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tagihan_mahasiswas', function (Blueprint $table) {
            $table->dropForeign('tagihan_mahasiswas_created_by_foreign');
            $table->dropForeign('tagihan_mahasiswas_mahasiswa_id_foreign');
            $table->dropForeign('tagihan_mahasiswas_tahun_akademik_id_foreign');
        });
    }
};
