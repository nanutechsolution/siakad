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
        Schema::create('ref_dokumen_dosen', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode', 50)->unique()->comment('Kode unik tanpa spasi, cth: ktp, ijazah');
            $table->string('nama_dokumen', 150)->comment('Nama label dokumen untuk UI, cth: Scan Kartu Identitas (KTP)');
            $table->string('allowed_types', 100)->default('pdf,jpg,jpeg,png')->comment('Format file yang diizinkan dipisah koma');
            $table->integer('max_size_kb')->default(2048)->comment('Batas ukuran file maksimal dalam satuan KB');
            $table->boolean('is_active')->default(true)->comment('Status aktif dokumen yang harus diupload');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_dokumen_dosen');
    }
};
