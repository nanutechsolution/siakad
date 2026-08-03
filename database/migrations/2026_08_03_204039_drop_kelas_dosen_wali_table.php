<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('kelas_dosen_wali');
    }

    public function down(): void
    {
        Schema::create('kelas_dosen_wali', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kelas_id')
                ->constrained('kelas');

            $table->char('dosen_id', 36);
            $table->foreign('dosen_id')
                ->references('id')
                ->on('trx_dosen')
                ->cascadeOnDelete();

            $table->boolean('is_primary')->default(true);

            $table->timestamps();

            $table->unique(['kelas_id', 'is_primary'], 'uniq_primary_wali_per_kelas');
        });
    }
};
