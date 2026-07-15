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
        Schema::create('lppm_skemas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tahun_akademik_id')->index('lppm_skemas_tahun_akademik_id_foreign');
            $table->unsignedBigInteger('jenis_skema_id')->index('lppm_skemas_jenis_skema_id_foreign');
            $table->string('nama_skema');
            $table->decimal('maksimal_dana', 19)->default(0);
            $table->date('tgl_mulai_daftar')->nullable();
            $table->date('tgl_tutup_daftar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lppm_skemas');
    }
};
