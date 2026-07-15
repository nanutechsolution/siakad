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
        Schema::table('lpm_kuisioner_pertanyaan', function (Blueprint $table) {
            $table->foreign(['kelompok_id'])->references(['id'])->on('lpm_kuisioner_kelompok')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lpm_kuisioner_pertanyaan', function (Blueprint $table) {
            $table->dropForeign('lpm_kuisioner_pertanyaan_kelompok_id_foreign');
        });
    }
};
