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
        Schema::create('lpm_indikators', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('standar_id')->index('lpm_indikators_standar_id_foreign');
            $table->string('kode_indikator', 20)->nullable();
            $table->string('nama_indikator');
            $table->string('satuan', 50)->nullable()->comment('%, Orang, Dokumen, dll');
            $table->text('deskripsi')->nullable();
            $table->string('slug')->unique();
            $table->decimal('bobot', 5)->default(0);
            $table->boolean('is_iku')->default(true);
            $table->boolean('is_active')->default(true);
            $table->string('sumber_data_siakad')->nullable();
            $table->string('calculation_method')->nullable();
            $table->json('calculation_params')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpm_indikators');
    }
};
