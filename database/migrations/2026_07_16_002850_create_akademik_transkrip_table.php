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
        Schema::create('akademik_transkrip', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('mahasiswa_id', 36);
            $table->unsignedBigInteger('mata_kuliah_id')->index('akademik_transkrip_mata_kuliah_id_foreign');
            $table->unsignedBigInteger('krs_detail_id')->index('akademik_transkrip_krs_detail_id_foreign');
            $table->integer('sks_diakui');
            $table->decimal('nilai_angka_final', 5);
            $table->string('nilai_huruf_final', 2);
            $table->decimal('nilai_indeks_final', 3);
            $table->boolean('is_konversi')->default(false);
            $table->timestamps();

            $table->unique(['mahasiswa_id', 'mata_kuliah_id'], 'unik_transkrip_mhs_mk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akademik_transkrip');
    }
};
