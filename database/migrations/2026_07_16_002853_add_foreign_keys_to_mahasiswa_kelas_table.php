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
        Schema::table('mahasiswa_kelas', function (Blueprint $table) {
            $table->foreign(['kelas_id'])->references(['id'])->on('kelas')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['mahasiswa_id'])->references(['id'])->on('mahasiswas')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswa_kelas', function (Blueprint $table) {
            $table->dropForeign('mahasiswa_kelas_kelas_id_foreign');
            $table->dropForeign('mahasiswa_kelas_mahasiswa_id_foreign');
        });
    }
};
