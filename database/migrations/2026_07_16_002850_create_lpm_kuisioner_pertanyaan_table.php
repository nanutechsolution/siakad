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
        Schema::create('lpm_kuisioner_pertanyaan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('kelompok_id')->index('lpm_kuisioner_pertanyaan_kelompok_id_foreign');
            $table->text('bunyi_pertanyaan');
            $table->string('jenis_input', 50)->default('RATING_4')->comment('RATING_4, RATING_5, ESSAY, BOOLEAN');
            $table->boolean('is_required')->default(true);
            $table->integer('urutan')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpm_kuisioner_pertanyaan');
    }
};
