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
        Schema::create('ref_jabatan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode_jabatan', 30)->unique();
            $table->string('nama_jabatan', 100);
            $table->enum('jenis', ['STRUKTURAL', 'FUNGSIONAL']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_jabatan');
    }
};
