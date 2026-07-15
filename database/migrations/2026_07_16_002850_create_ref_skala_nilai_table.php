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
        Schema::create('ref_skala_nilai', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('huruf', 2);
            $table->decimal('bobot_indeks', 3);
            $table->decimal('nilai_min', 6);
            $table->decimal('nilai_max', 6);
            $table->boolean('is_lulus')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_skala_nilai');
    }
};
