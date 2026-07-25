<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_benchmark_institusis', function (Blueprint $table) {
            $table->id();
            $table->string('nama_institusi', 255);
            $table->string('jenis', 50)->nullable()->comment('mis. PTN, PTS, Internasional');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_benchmark_institusis');
    }
};
