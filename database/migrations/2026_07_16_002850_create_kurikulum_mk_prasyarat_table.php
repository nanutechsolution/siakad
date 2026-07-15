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
        Schema::create('kurikulum_mk_prasyarat', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('kurikulum_mk_id')->index('kurikulum_mk_prasyarat_kurikulum_mk_id_foreign');
            $table->string('min_nilai_huruf', 2)->default('D');
            $table->unsignedBigInteger('prasyarat_kurikulum_mk_id')->index('kurikulum_mk_prasyarat_prasyarat_kurikulum_mk_id_foreign');
            $table->decimal('min_nilai', 3)->default(2);
            $table->enum('logic_type', ['AND', 'OR'])->default('AND');

            $table->unique(['kurikulum_mk_id', 'prasyarat_kurikulum_mk_id'], 'unik_prasyarat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kurikulum_mk_prasyarat');
    }
};
