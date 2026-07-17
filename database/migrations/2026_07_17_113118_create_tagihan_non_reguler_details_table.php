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
        Schema::create('tagihan_non_reguler_details', function (Blueprint $table) {

            $table->id();

            $table->uuid('tagihan_id');


            $table->foreignId('komponen_biaya_id');


            /*
     |--------------------------------------------------------------------------
     | Snapshot
     |--------------------------------------------------------------------------
     | Supaya histori aman jika nama komponen berubah
     */
            $table->string('nama_komponen_snapshot', 100);


            $table->decimal('nominal_dasar', 19, 2);

            $table->decimal('nominal_diskon', 19, 2)
                ->default(0);


            $table->decimal('nominal_tagihan', 19, 2);


            $table->decimal('nominal_terbayar', 19, 2)
                ->default(0);


            $table->timestamps();


            $table->foreign('tagihan_id')
                ->references('id')
                ->on('tagihan_non_regulers')
                ->cascadeOnDelete();


            $table->foreign('komponen_biaya_id')
                ->references('id')
                ->on('keuangan_komponen_biaya')
                ->restrictOnDelete();


            $table->index('komponen_biaya_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan_non_reguler_details');
    }
};
