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
        Schema::table('lpm_indikators', function (Blueprint $table) {
            $table->foreign(['standar_id'])->references(['id'])->on('lpm_standars')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lpm_indikators', function (Blueprint $table) {
            $table->dropForeign('lpm_indikators_standar_id_foreign');
        });
    }
};
