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
        Schema::table('keuangan_general_ledgers', function (Blueprint $table) {
            $table->foreign(['mahasiswa_id'])->references(['id'])->on('mahasiswas')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keuangan_general_ledgers', function (Blueprint $table) {
            $table->dropForeign('keuangan_general_ledgers_mahasiswa_id_foreign');
        });
    }
};
