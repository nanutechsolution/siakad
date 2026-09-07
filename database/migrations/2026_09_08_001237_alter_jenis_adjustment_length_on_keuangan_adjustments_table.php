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
        Schema::table('keuangan_adjustments', function (Blueprint $table) {
            $table->string('jenis_adjustment', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('keuangan_adjustments', function (Blueprint $table) {
            // Sesuaikan kembali panjang aslinya jika perlu rollback (misal: 20 atau 25)
            $table->string('jenis_adjustment', 25)->change();
        });
    }
};
