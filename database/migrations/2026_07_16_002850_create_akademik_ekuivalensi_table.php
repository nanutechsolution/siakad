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
        Schema::create('akademik_ekuivalensi', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('prodi_id')->index('akademik_ekuivalensi_prodi_id_foreign');
            $table->unsignedBigInteger('mk_asal_id');
            $table->unsignedBigInteger('mk_tujuan_id')->index('akademik_ekuivalensi_mk_tujuan_id_foreign');
            $table->string('minimal_nilai_asal', 3)->default('C')->comment('Grade minimal dari MK Asal untuk syarat penyetaraan');
            $table->integer('sks_diakui')->nullable()->comment('Jumlah SKS yang akan diakui di transkrip baru');
            $table->string('group_identifier', 50)->nullable()->comment('ID grup jika beberapa MK Asal digabung menjadi satu MK Tujuan');
            $table->string('nomor_sk')->nullable();
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->char('created_by', 36)->nullable()->index('akademik_ekuivalensi_created_by_foreign');
            $table->timestamps();

            $table->unique(['mk_asal_id', 'mk_tujuan_id'], 'unique_ekuivalensi_pair');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akademik_ekuivalensi');
    }
};
