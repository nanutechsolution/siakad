<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('keuangan_komponen_biaya', function (Blueprint $table) {
            $table->unique('kode_komponen', 'uk_kode_komponen');
        });
    }

    public function down(): void
    {
        Schema::table('keuangan_komponen_biaya', function (Blueprint $table) {
            $table->dropUnique('uk_kode_komponen');
        });
    }
};
