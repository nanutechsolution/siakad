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
        Schema::create('keuangan_detail_tarif', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('skema_tarif_id')->index('keuangan_detail_tarif_skema_tarif_id_foreign');
            $table->unsignedBigInteger('komponen_biaya_id')->index('keuangan_detail_tarif_komponen_biaya_id_foreign');
            $table->decimal('nominal', 19)->default(0);
            $table->integer('berlaku_semester')->nullable();
            $table->enum('penerapan', ['FLAT', 'ONCE'])->default('FLAT')->comment('FLAT: Tiap Semester, ONCE: Sekali Saja');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuangan_detail_tarif');
    }
};
