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
        Schema::table('dispensasi_akademiks', function (Blueprint $table) {
            $table->foreign(['created_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['disetujui_oleh'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['mahasiswa_id'])->references(['id'])->on('mahasiswas')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispensasi_akademiks', function (Blueprint $table) {
            $table->dropForeign('dispensasi_akademiks_created_by_foreign');
            $table->dropForeign('dispensasi_akademiks_disetujui_oleh_foreign');
            $table->dropForeign('dispensasi_akademiks_mahasiswa_id_foreign');
        });
    }
};
