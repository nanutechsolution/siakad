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
        Schema::table('trx_pegawai', function (Blueprint $table) {
            $table->foreign(['person_id'])->references(['id'])->on('ref_person')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trx_pegawai', function (Blueprint $table) {
            $table->dropForeign('trx_pegawai_person_id_foreign');
        });
    }
};
