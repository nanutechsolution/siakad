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
        Schema::table('trx_person_role', function (Blueprint $table) {
            $table->foreign(['person_id'])->references(['id'])->on('ref_person')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['role_id'])->references(['id'])->on('ref_person_role')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trx_person_role', function (Blueprint $table) {
            $table->dropForeign('trx_person_role_person_id_foreign');
            $table->dropForeign('trx_person_role_role_id_foreign');
        });
    }
};
