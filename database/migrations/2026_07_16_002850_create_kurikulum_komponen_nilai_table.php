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
        Schema::create('kurikulum_komponen_nilai', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('kurikulum_id')->index('kurikulum_komponen_nilai_kurikulum_id_foreign');
            $table->unsignedBigInteger('komponen_id')->index('kurikulum_komponen_nilai_komponen_id_foreign');
            $table->decimal('bobot_persen', 5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kurikulum_komponen_nilai');
    }
};
