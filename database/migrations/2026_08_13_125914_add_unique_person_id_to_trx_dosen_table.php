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
        Schema::table('trx_dosen', function (Blueprint $table) {
            $table->unique(
                'person_id',
                'trx_dosen_person_id_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trx_dosen', function (Blueprint $table) {
            $table->dropUnique('trx_dosen_person_id_unique');
        });
    }
};
