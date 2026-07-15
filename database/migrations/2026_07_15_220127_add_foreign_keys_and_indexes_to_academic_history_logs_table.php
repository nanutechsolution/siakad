<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_history_logs', function (Blueprint $table) {
            // Menambahkan Index komposit untuk mempercepat pencarian log per mahasiswa per semester
            $table->index(['mahasiswa_id', 'tahun_akademik_id'], 'idx_mhs_ta');

            // Menambahkan Foreign Key ke tabel mahasiswas
            $table->foreign('mahasiswa_id')
                ->references('id')
                ->on('mahasiswas')
                ->onDelete('cascade'); // Hapus log jika data mahasiswa dihapus

            // Menambahkan Foreign Key ke tabel ref_tahun_akademik
            $table->foreign('tahun_akademik_id')
                ->references('id')
                ->on('ref_tahun_akademik')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('academic_history_logs', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->dropForeign(['tahun_akademik_id']);
            $table->dropIndex('idx_mhs_ta');
        });
    }
};
