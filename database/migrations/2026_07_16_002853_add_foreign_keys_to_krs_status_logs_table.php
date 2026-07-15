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
        Schema::table('krs_status_logs', function (Blueprint $table) {
            $table->foreign(['dilakukan_oleh'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['krs_id'])->references(['id'])->on('krs')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('krs_status_logs', function (Blueprint $table) {
            $table->dropForeign('krs_status_logs_dilakukan_oleh_foreign');
            $table->dropForeign('krs_status_logs_krs_id_foreign');
        });
    }
};
