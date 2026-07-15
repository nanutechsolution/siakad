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
        Schema::create('ref_tahun_akademik', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode_tahun', 5)->unique();
            $table->string('nama_tahun', 50);
            $table->integer('semester')->comment('1=Ganjil, 2=Genap, 3=Pendek');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->boolean('buka_krs')->default(false);
            $table->boolean('is_locked_krs')->default(false)->comment('Lock manual KRS');
            $table->boolean('buka_input_nilai')->default(false);
            $table->boolean('is_locked_nilai')->default(false)->comment('Lock manual input nilai');
            $table->string('feeder_semester_id')->nullable()->comment('ID semester feeder');
            $table->timestamp('last_sync_at')->nullable()->comment('Sinkronisasi feeder terakhir');
            $table->boolean('is_feeder_locked')->default(false)->comment('Lock sinkronisasi feeder');
            $table->json('config')->nullable()->comment('Konfigurasi tambahan');
            $table->char('created_by', 36)->nullable()->index('ref_tahun_akademik_created_by_foreign');
            $table->char('updated_by', 36)->nullable()->index('ref_tahun_akademik_updated_by_foreign');
            $table->char('activated_by', 36)->nullable()->index('ref_tahun_akademik_activated_by_foreign');
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
            $table->date('tgl_mulai_krs')->nullable();
            $table->date('tgl_selesai_krs')->nullable();
            $table->date('tgl_mulai_perkuliahan')->nullable()->comment('Tanggal mulai perkuliahan');
            $table->date('tgl_selesai_perkuliahan')->nullable()->comment('Tanggal selesai perkuliahan');
            $table->date('tgl_mulai_uts')->nullable()->comment('Tanggal mulai UTS');
            $table->date('tgl_selesai_uts')->nullable()->comment('Tanggal selesai UTS');
            $table->date('tgl_mulai_uas')->nullable()->comment('Tanggal mulai UAS');
            $table->date('tgl_selesai_uas')->nullable()->comment('Tanggal selesai UAS');
            $table->date('tgl_mulai_input_nilai')->nullable()->comment('Tanggal mulai input nilai');
            $table->date('tgl_selesai_input_nilai')->nullable()->comment('Batas akhir input nilai');
            $table->date('tgl_publish_nilai')->nullable()->index()->comment('Tanggal publish nilai/KHS');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_tahun_akademik');
    }
};
