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
        Schema::table('trx_person_gelar', function (Blueprint $table) {
            $table->foreign(['gelar_id'])->references(['id'])->on('ref_gelar')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['person_id'])->references(['id'])->on('ref_person')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trx_person_gelar', function (Blueprint $table) {
            $table->dropForeign('trx_person_gelar_gelar_id_foreign');
            $table->dropForeign('trx_person_gelar_person_id_foreign');
        });
    }
};
