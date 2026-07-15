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
        Schema::create('tagihan_mahasiswas', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('mahasiswa_id', 36)->index('tagihan_mahasiswas_mahasiswa_id_foreign');
            $table->unsignedBigInteger('tahun_akademik_id')->nullable()->index('tagihan_mahasiswas_tahun_akademik_id_foreign');
            $table->string('kode_transaksi', 60)->unique();
            $table->string('deskripsi');
            $table->decimal('total_tagihan', 19);
            $table->decimal('total_bayar', 19)->default(0);
            $table->decimal('sisa_tagihan', 19)->nullable()->virtualAs('(`total_tagihan` - `total_bayar`)');
            $table->enum('status_bayar', ['BELUM', 'CICIL', 'LUNAS'])->default('BELUM')->index();
            $table->char('created_by', 36)->nullable()->index('tagihan_mahasiswas_created_by_foreign');
            $table->date('tenggat_waktu')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan_mahasiswas');
    }
};
