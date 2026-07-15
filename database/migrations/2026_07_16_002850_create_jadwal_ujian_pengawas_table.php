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
        Schema::create('jadwal_ujian_pengawas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('jadwal_ujian_id', 36);
            $table->unsignedBigInteger('person_id')->index('jadwal_ujian_pengawas_person_id_foreign');
            $table->string('peran', 20)->default('PENGAWAS');
            $table->timestamps();

            $table->unique(['jadwal_ujian_id', 'person_id'], 'jup_ujian_person_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_ujian_pengawas');
    }
};
