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
        Schema::create('lppm_usulan_anggotas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('usulan_id', 36);
            $table->unsignedBigInteger('person_id')->index('lppm_usulan_anggotas_person_id_foreign');
            $table->string('peran_anggota', 50)->default('ANGGOTA');
            $table->timestamps();

            $table->unique(['usulan_id', 'person_id'], 'unik_anggota_usulan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lppm_usulan_anggotas');
    }
};
