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
        Schema::create('krs_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('krs_id', 36);
            $table->char('jadwal_kuliah_id', 36)->nullable()->index('krs_detail_jadwal_kuliah_id_foreign');
            $table->unsignedBigInteger('mata_kuliah_id')->nullable()->index('krs_detail_mata_kuliah_id_foreign');
            $table->string('kode_mk_snapshot', 20)->nullable();
            $table->string('nama_mk_snapshot', 150)->nullable();
            $table->integer('sks_snapshot')->nullable();
            $table->string('activity_type_snapshot', 20)->default('REGULAR');
            $table->unsignedBigInteger('ekuivalensi_id')->nullable()->index('krs_detail_ekuivalensi_id_foreign');
            $table->char('status_ambil', 1)->default('B');
            $table->decimal('nilai_angka', 5)->default(0);
            $table->string('nilai_huruf', 2)->nullable();
            $table->decimal('nilai_indeks', 3)->default(0);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->timestamps();

            $table->unique(['krs_id', 'jadwal_kuliah_id']);
            $table->unique(['krs_id', 'mata_kuliah_id'], 'krs_detail_prevent_double_mk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('krs_detail');
    }
};
