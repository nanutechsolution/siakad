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
        Schema::table('kelas_dosen_wali', function (Blueprint $table) {

            $table->unique(
                ['kelas_id', 'is_primary'],
                'uniq_primary_wali_per_kelas'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas_dosen_wali', function (Blueprint $table) {
            //
        });
    }
};
