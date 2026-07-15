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
        Schema::create('mahasiswas', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->unsignedBigInteger('person_id')->nullable()->index('mahasiswas_person_id_foreign');
            $table->string('nim', 20)->index('idx_mhs_nim');
            $table->integer('angkatan_id')->index('mahasiswas_angkatan_id_foreign');
            $table->unsignedBigInteger('prodi_id')->index('mahasiswas_prodi_id_foreign');
            $table->unsignedBigInteger('program_id')->nullable()->index('mahasiswas_program_id_foreign');
            $table->unsignedBigInteger('kurikulum_id')->nullable()->index('mahasiswas_kurikulum_id_foreign');
            $table->json('data_tambahan')->nullable();
            $table->char('id_pd_feeder', 36)->nullable()->index();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['nim']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswas');
    }
};
