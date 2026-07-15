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
        Schema::table('lpm_iku_targets', function (Blueprint $table) {
            $table->foreign(['indikator_id'])->references(['id'])->on('lpm_indikators')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lpm_iku_targets', function (Blueprint $table) {
            $table->dropForeign('lpm_iku_targets_indikator_id_foreign');
        });
    }
};
