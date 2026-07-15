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
        Schema::table('dispensasi_akademik_logs', function (Blueprint $table) {
            $table->foreign(['dilakukan_oleh'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['dispensasi_id'])->references(['id'])->on('dispensasi_akademiks')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispensasi_akademik_logs', function (Blueprint $table) {
            $table->dropForeign('dispensasi_akademik_logs_dilakukan_oleh_foreign');
            $table->dropForeign('dispensasi_akademik_logs_dispensasi_id_foreign');
        });
    }
};
