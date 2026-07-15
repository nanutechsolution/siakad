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
        Schema::create('ref_status_verifikasi_pembayaran', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('kode', 20)->unique();
            $table->string('nama', 50);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_final')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_status_verifikasi_pembayaran');
    }
};
