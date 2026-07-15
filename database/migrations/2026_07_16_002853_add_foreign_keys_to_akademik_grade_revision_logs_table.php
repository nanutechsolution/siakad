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
        Schema::table('akademik_grade_revision_logs', function (Blueprint $table) {
            $table->foreign(['executed_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['krs_detail_id'])->references(['id'])->on('krs_detail')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('akademik_grade_revision_logs', function (Blueprint $table) {
            $table->dropForeign('akademik_grade_revision_logs_executed_by_foreign');
            $table->dropForeign('akademik_grade_revision_logs_krs_detail_id_foreign');
        });
    }
};
