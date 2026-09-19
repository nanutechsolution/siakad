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
        Schema::table('pembayaran_mahasiswas', function (Blueprint $table) {
            $table->string('file_hash', 64)
                ->nullable()
                ->index()
                ->after('bukti_bayar_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_mahasiswas', function (Blueprint $table) {
            $table->dropColumn('file_hash');
        });
    }
};
