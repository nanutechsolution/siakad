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
        Schema::table('dosen_ketersediaans', function (Blueprint $table) {
            $table->boolean('allow_outside_operational_hours')
                ->default(false)
                ->after('jam_selesai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dosen_ketersediaans', function (Blueprint $table) {
            //
        });
    }
};
