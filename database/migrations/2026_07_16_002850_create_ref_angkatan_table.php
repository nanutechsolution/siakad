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
        Schema::create('ref_angkatan', function (Blueprint $table) {
            $table->integer('id_tahun')->primary();
            $table->integer('batas_tahun_studi')->nullable();
            $table->boolean('is_active_pmb')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_angkatan');
    }
};
