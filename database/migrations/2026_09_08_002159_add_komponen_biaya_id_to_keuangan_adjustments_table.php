<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('keuangan_adjustments', function (Blueprint $table) {
            $table->unsignedBigInteger('komponen_biaya_id')->nullable()->after('tagihan_id');
        });
    }

    public function down(): void
    {
        Schema::table('keuangan_adjustments', function (Blueprint $table) {
            $table->dropColumn('komponen_biaya_id');
        });
    }
};
