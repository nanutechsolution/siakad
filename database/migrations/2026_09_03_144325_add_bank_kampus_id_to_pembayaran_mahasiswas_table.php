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
            // Kita set nullable() karena pembayaran via Midtrans/VA mungkin tidak pakai bank kampus internal
            $table->foreignId('bank_kampus_id')
                ->nullable()
                ->after('metode_pembayaran')
                ->constrained('bank_kampuses')
                ->nullOnDelete(); // Jika bank dihapus, data histori pembayaran tidak error
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_mahasiswas', function (Blueprint $table) {
            // Drop foreign key dulu, baru drop kolomnya
            $table->dropForeign(['bank_kampus_id']);
            $table->dropColumn('bank_kampus_id');
        });
    }
};
