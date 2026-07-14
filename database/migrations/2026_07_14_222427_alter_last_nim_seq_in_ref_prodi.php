<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ref_prodi', function (Blueprint $table) {
            // Mengubah menjadi BIGINT agar tidak pernah overflow
            $table->unsignedBigInteger('last_nim_seq')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ref_prodi', function (Blueprint $table) {
            //
        });
    }
};
