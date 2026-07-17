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
        Schema::create('tagihan_non_regulers', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->char('mahasiswa_id', 36);

            $table->string('kode_transaksi', 60)->unique();

            $table->string('deskripsi', 255);

            $table->decimal('total_tagihan', 19, 2);

            $table->decimal('total_bayar', 19, 2)
                ->default(0);

            $table->enum('status_bayar', [
                'BELUM',
                'CICIL',
                'LUNAS'
            ])
                ->default('BELUM');

            /*
     |--------------------------------------------------------------------------
     | Referensi proses akademik
     |--------------------------------------------------------------------------
     | Contoh:
     | PengajuanProposal
     | PengajuanSidang
     | Wisuda
     |
     */
            $table->string('referensi_type', 100)
                ->nullable();

            $table->uuid('referensi_id')
                ->nullable();


            $table->date('tenggat_waktu')
                ->nullable();


            $table->char('created_by', 36)
                ->nullable();


            $table->timestamps();

            $table->softDeletes();


            $table->foreign('mahasiswa_id')
                ->references('id')
                ->on('mahasiswas')
                ->cascadeOnDelete();


            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();


            $table->index([
                'mahasiswa_id',
                'status_bayar'
            ]);

            $table->index([
                'referensi_type',
                'referensi_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan_non_regulers');
    }
};
