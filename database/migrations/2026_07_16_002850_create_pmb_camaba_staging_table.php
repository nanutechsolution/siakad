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
        Schema::create('pmb_camaba_staging', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('external_id')->unique()->comment('Nomor pendaftaran dari PMB');
            $table->json('payload')->comment('Raw payload dari PMB');
            $table->enum('status', ['pending', 'processing', 'processed', 'failed'])->default('pending')->index();
            $table->text('error_log')->nullable()->comment('Pesan error jika gagal diproses');
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->string('source')->default('PMB');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->char('mahasiswa_id', 36)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pmb_camaba_staging');
    }
};
