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
        Schema::create('ref_fakultas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode_fakultas', 10)->unique();
            $table->string('nama_fakultas', 100);
            $table->char('id_feeder', 36)->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_fakultas');
    }
};
