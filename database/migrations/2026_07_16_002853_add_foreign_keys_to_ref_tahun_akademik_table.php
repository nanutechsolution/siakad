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
        Schema::table('ref_tahun_akademik', function (Blueprint $table) {
            $table->foreign(['activated_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['created_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['updated_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ref_tahun_akademik', function (Blueprint $table) {
            $table->dropForeign('ref_tahun_akademik_activated_by_foreign');
            $table->dropForeign('ref_tahun_akademik_created_by_foreign');
            $table->dropForeign('ref_tahun_akademik_updated_by_foreign');
        });
    }
};
