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
        Schema::table('kurikulum_mata_kuliah', function (Blueprint $table) {
            $table->foreign(['kurikulum_id'])->references(['id'])->on('master_kurikulums')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['mata_kuliah_id'])->references(['id'])->on('master_mata_kuliahs')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kurikulum_mata_kuliah', function (Blueprint $table) {
            $table->dropForeign('kurikulum_mata_kuliah_kurikulum_id_foreign');
            $table->dropForeign('kurikulum_mata_kuliah_mata_kuliah_id_foreign');
        });
    }
};
