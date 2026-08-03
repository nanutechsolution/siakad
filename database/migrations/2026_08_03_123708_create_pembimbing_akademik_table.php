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
        Schema::create('pembimbing_akademik', function (Blueprint $table) {
            $table->id();

            /*
    |--------------------------------------------------------------------------
    | Relasi
    |--------------------------------------------------------------------------
    */

            $table->foreignId('kelas_id')
                ->nullable()
                ->constrained('kelas')
                ->restrictOnDelete();

            $table->char('mahasiswa_id', 36)
                ->nullable();

            $table->foreign('mahasiswa_id')
                ->references('id')
                ->on('mahasiswas')
                ->cascadeOnDelete();

            $table->char('dosen_id', 36);

            $table->foreign('dosen_id')
                ->references('id')
                ->on('trx_dosen')
                ->cascadeOnDelete();

            /*
    |--------------------------------------------------------------------------
    | Jenis Penugasan
    |--------------------------------------------------------------------------
    */

            $table->enum('jenis', [
                'DOSEN_WALI',
                'PEMBIMBING_PKL',
                'PEMBIMBING_MBKM',
                'PEMBIMBING_SKRIPSI',
                'PEMBIMBING_TESIS',
                'PEMBIMBING_DISERTASI',
                'PENGUJI_SKRIPSI',
            ])->default('DOSEN_WALI');

            $table->boolean('is_primary')->default(true);

            /*
    |--------------------------------------------------------------------------
    | Masa Berlaku
    |--------------------------------------------------------------------------
    */

            $table->foreignId('semester_mulai_id')
                ->constrained('ref_tahun_akademik')
                ->restrictOnDelete();

            $table->foreignId('semester_selesai_id')
                ->nullable()
                ->constrained('ref_tahun_akademik')
                ->nullOnDelete();

            $table->date('tanggal_mulai');

            $table->date('tanggal_selesai')
                ->nullable();

            /*
    |--------------------------------------------------------------------------
    | Administrasi
    |--------------------------------------------------------------------------
    */

            $table->string('nomor_sk')
                ->nullable();

            $table->date('tanggal_sk')
                ->nullable();

            $table->string('alasan')
                ->nullable();

            $table->text('keterangan')
                ->nullable();

            /*
    |--------------------------------------------------------------------------
    | User Audit
    |--------------------------------------------------------------------------
    */

            $table->char('created_by', 36)
                ->nullable();

            $table->char('updated_by', 36)
                ->nullable();

            $table->char('deleted_by', 36)
                ->nullable();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('deleted_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

            $table->enum('status', [
                'AKTIF',
                'SELESAI',
                'DIBATALKAN',
            ])->default('AKTIF');

            $table->timestamps();

            $table->softDeletes();

            /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

            $table->index('kelas_id');
            $table->index('mahasiswa_id');
            $table->index('dosen_id');
            $table->index('jenis');
            $table->index('status');

            $table->index([
                'semester_mulai_id',
                'semester_selesai_id',
            ]);

            $table->index([
                'mahasiswa_id',
                'jenis',
                'status',
            ]);

            $table->index([
                'kelas_id',
                'jenis',
                'status',
            ]);

            $table->index([
                'dosen_id',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembimbing_akademik');
    }
};
