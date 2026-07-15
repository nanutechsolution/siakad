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
        Schema::table('academic_history_logs', function (Blueprint $table) {
            $table->foreign(['mahasiswa_id'])->references(['id'])->on('mahasiswas')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['tahun_akademik_id'])->references(['id'])->on('ref_tahun_akademik')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_history_logs', function (Blueprint $table) {
            $table->dropForeign('academic_history_logs_mahasiswa_id_foreign');
            $table->dropForeign('academic_history_logs_tahun_akademik_id_foreign');
        });
    }
};
