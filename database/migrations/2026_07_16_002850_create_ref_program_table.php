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
        Schema::create('ref_program', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama_program', 50);
            $table->string('kode_internal', 10)->unique();
            $table->string('id_jenis_kelas_feeder')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_program');
    }
};
