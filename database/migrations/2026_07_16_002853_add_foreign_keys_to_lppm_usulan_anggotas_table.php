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
        Schema::table('lppm_usulan_anggotas', function (Blueprint $table) {
            $table->foreign(['person_id'])->references(['id'])->on('ref_person')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['usulan_id'])->references(['id'])->on('lppm_usulans')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lppm_usulan_anggotas', function (Blueprint $table) {
            $table->dropForeign('lppm_usulan_anggotas_person_id_foreign');
            $table->dropForeign('lppm_usulan_anggotas_usulan_id_foreign');
        });
    }
};
