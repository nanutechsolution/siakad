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
        Schema::create('lpm_ami_periodes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama_periode');
            $table->year('tahun')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->enum('status', ['DRAFT', 'ON-GOING', 'FINISHED'])->default('DRAFT');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpm_ami_periodes');
    }
};
