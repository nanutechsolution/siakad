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
        Schema::table('kurikulum_komponen_nilai', function (Blueprint $table) {
            $table->foreign(['komponen_id'])->references(['id'])->on('ref_komponen_nilai')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['kurikulum_id'])->references(['id'])->on('master_kurikulums')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kurikulum_komponen_nilai', function (Blueprint $table) {
            $table->dropForeign('kurikulum_komponen_nilai_komponen_id_foreign');
            $table->dropForeign('kurikulum_komponen_nilai_kurikulum_id_foreign');
        });
    }
};
