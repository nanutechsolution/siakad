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
        Schema::create('payment_policies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tahun_akademik_id')->index('payment_policies_tahun_akademik_id_foreign');
            $table->string('nama');
            $table->unsignedBigInteger('prodi_id')->nullable()->index('payment_policies_prodi_id_foreign');
            $table->unsignedBigInteger('program_kelas_id')->nullable()->index('payment_policies_program_kelas_id_foreign');
            $table->string('angkatan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_policies');
    }
};
