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
        Schema::create('jadwal_komponen_nilai', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('jadwal_kuliah_id', 36);
            $table->unsignedBigInteger('komponen_id')->index('jadwal_komponen_nilai_komponen_id_foreign');
            $table->decimal('bobot_persen', 5);
            $table->timestamps();

            $table->unique(['jadwal_kuliah_id', 'komponen_id'], 'jkn_jadwal_komponen_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_komponen_nilai');
    }
};
