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
        Schema::table('payment_policies', function (Blueprint $table) {
            $table->foreign(['prodi_id'])->references(['id'])->on('ref_prodi')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['program_kelas_id'])->references(['id'])->on('ref_program')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['tahun_akademik_id'])->references(['id'])->on('ref_tahun_akademik')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_policies', function (Blueprint $table) {
            $table->dropForeign('payment_policies_prodi_id_foreign');
            $table->dropForeign('payment_policies_program_kelas_id_foreign');
            $table->dropForeign('payment_policies_tahun_akademik_id_foreign');
        });
    }
};
