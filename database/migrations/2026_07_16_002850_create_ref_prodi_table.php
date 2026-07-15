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
        Schema::create('ref_prodi', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('fakultas_id')->index('ref_prodi_fakultas_id_foreign');
            $table->string('kode_prodi_dikti', 10)->nullable()->index();
            $table->string('kode_prodi_internal', 10)->unique();
            $table->string('nama_prodi', 100);
            $table->boolean('is_paket')->default(true);
            $table->enum('jenjang', ['D3', 'D4', 'S1', 'S2', 'S3', 'PROFESI']);
            $table->string('gelar_lulusan', 50)->nullable();
            $table->string('format_nim')->nullable()->comment('Pattern: {THN}=24, {TAHUN}=2024, {KODE}=KodeProdi, {NO:4}=0001');
            $table->unsignedBigInteger('last_nim_seq');
            $table->char('id_feeder', 36)->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_prodi');
    }
};
