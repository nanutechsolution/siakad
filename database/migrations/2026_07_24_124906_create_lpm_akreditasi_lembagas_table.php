<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_akreditasi_lembagas', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->string('nama', 150);
            $table->enum('jenis', ['INSTITUSI', 'PRODI'])->default('PRODI');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_akreditasi_lembagas');
    }
};
