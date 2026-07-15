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
        Schema::create('trx_dosen', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->unsignedBigInteger('person_id')->index('trx_dosen_person_id_foreign');
            $table->unsignedBigInteger('prodi_id')->index('trx_dosen_prodi_id_foreign');
            $table->string('jenis_dosen', 20)->default('TETAP');
            $table->string('asal_institusi')->nullable();
            $table->string('nidn', 50)->nullable()->index('idx_dosen_nidn');
            $table->string('nuptk', 50)->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->json('data_tambahan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['nidn']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trx_dosen');
    }
};
