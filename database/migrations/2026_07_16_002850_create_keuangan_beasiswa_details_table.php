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
        Schema::create('keuangan_beasiswa_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('beasiswa_id');
            $table->unsignedBigInteger('komponen_biaya_id')->index('keuangan_beasiswa_details_komponen_biaya_id_foreign');
            $table->enum('tipe_diskon', ['PERSENTASE', 'NOMINAL']);
            $table->decimal('nilai_diskon', 15);
            $table->timestamps();

            $table->unique(['beasiswa_id', 'komponen_biaya_id'], 'unik_beasiswa_komponen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuangan_beasiswa_details');
    }
};
