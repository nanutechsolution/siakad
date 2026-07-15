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
        Schema::create('keuangan_skema_tarif', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama_skema', 150);
            $table->integer('angkatan_id');
            $table->unsignedBigInteger('prodi_id')->index('keuangan_skema_tarif_prodi_id_foreign');
            $table->unsignedBigInteger('program_kelas_id')->index('keuangan_skema_tarif_program_kelas_id_foreign');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['angkatan_id', 'prodi_id', 'program_kelas_id'], 'unique_skema_tarif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuangan_skema_tarif');
    }
};
