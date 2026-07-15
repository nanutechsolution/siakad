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
        Schema::create('tagihan_mahasiswas_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('tagihan_id', 36);
            $table->unsignedBigInteger('komponen_biaya_id')->index('tagihan_mahasiswas_details_komponen_biaya_id_foreign');
            $table->string('nama_komponen_snapshot', 100);
            $table->decimal('nominal_dasar', 19);
            $table->decimal('nominal_diskon', 19)->default(0);
            $table->decimal('nominal_tagihan', 19)->nullable()->storedAs('(`nominal_dasar` - `nominal_diskon`)');
            $table->decimal('nominal_terbayar', 19)->default(0);
            $table->timestamps();

            $table->unique(['tagihan_id', 'komponen_biaya_id'], 'unik_tagihan_komponen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan_mahasiswas_details');
    }
};
