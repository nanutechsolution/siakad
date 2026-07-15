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
        Schema::create('master_kurikulums', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('prodi_id')->index('master_kurikulums_prodi_id_foreign');
            $table->string('nama_kurikulum', 100);
            $table->integer('tahun_mulai');
            $table->string('id_semester_mulai', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('mode_krs', ['PAKET', 'BEBAS'])->default('PAKET')->comment('PAKET: MK ditentukan kurikulum via kelas, GATE SKS berbasis IPS di-skip. BEBAS: mahasiswa pilih sendiri, tunduk GATE SKS Maksimal berbasis IPS.');
            $table->integer('jumlah_sks_lulus')->default(144)->comment('Total SKS minimal untuk lulus');
            $table->integer('jumlah_sks_wajib')->default(0);
            $table->integer('jumlah_sks_pilihan')->default(0);
            $table->timestamps();
            $table->string('no_sk_kurikulum', 100)->nullable();
            $table->date('tgl_sk_kurikulum')->nullable();
            $table->string('id_kurikulum_feeder', 36)->nullable();
            $table->text('keterangan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_kurikulums');
    }
};
