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
        Schema::create('krs_detail_nilai', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('krs_detail_id')->index('krs_detail_nilai_krs_detail_id_foreign');
            $table->unsignedBigInteger('komponen_id')->index('krs_detail_nilai_komponen_id_foreign');
            $table->decimal('nilai_angka', 5)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('krs_detail_nilai');
    }
};
