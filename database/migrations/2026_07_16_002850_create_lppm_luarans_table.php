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
        Schema::create('lppm_luarans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('dosen_id', 36)->index('lppm_luarans_dosen_id_foreign');
            $table->unsignedBigInteger('jenis_luaran_id')->index('lppm_luarans_jenis_luaran_id_foreign');
            $table->text('judul_luaran');
            $table->string('nama_penerbit_jurnal')->nullable();
            $table->string('tautan_url')->nullable();
            $table->year('tahun_terbit');
            $table->string('status_verifikasi', 30)->default('PENDING');
            $table->char('verified_by', 36)->nullable()->index('lppm_luarans_verified_by_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lppm_luarans');
    }
};
