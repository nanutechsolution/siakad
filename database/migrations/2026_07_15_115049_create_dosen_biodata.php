<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dosen_biodata', function (Blueprint $table) {
            $table->id();
            $table->char('dosen_id', 36);

            // Alamat & kontak tambahan
            $table->text('alamat_domisili')->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('no_hp_kantor', 20)->nullable();

            // Identitas akademik/riset — dipakai untuk portofolio & rekap kinerja
            $table->string('bidang_keahlian')->nullable();
            $table->text('minat_penelitian')->nullable();
            $table->string('sinta_id', 50)->nullable();
            $table->string('scopus_id', 50)->nullable();
            $table->string('orcid_id', 50)->nullable();
            $table->string('google_scholar_id', 100)->nullable();
            $table->string('h_index_scopus')->nullable();
            $table->string('h_index_scholar')->nullable();

            // Data pelengkap umum
            $table->string('agama', 20)->nullable();
            $table->string('status_pernikahan', 20)->nullable();

            $table->timestamps();

            $table->unique('dosen_id');
            $table->foreign('dosen_id')
                ->references('id')->on('trx_dosen')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosen_biodata');
    }
};