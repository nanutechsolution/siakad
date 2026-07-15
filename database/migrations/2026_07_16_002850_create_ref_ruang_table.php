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
        Schema::create('ref_ruang', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode_ruang', 20)->unique();
            $table->string('nama_ruang', 100);
            $table->integer('kapasitas')->default(40);
            $table->decimal('latitude', 10, 8)->nullable()->comment('Koordinat garis lintang ruangan');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Koordinat garis bujur ruangan');
            $table->integer('radius_meter')->default(50)->comment('Radius jangkauan absen dari titik koordinat');
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_ruang');
    }
};
