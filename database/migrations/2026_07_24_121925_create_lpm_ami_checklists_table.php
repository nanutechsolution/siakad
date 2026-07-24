<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_ami_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standar_id')->constrained('lpm_standars')->cascadeOnDelete();
            $table->string('kriteria', 255);
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();

            $table->index(['standar_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_ami_checklists');
    }
};