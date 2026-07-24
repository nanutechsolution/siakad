<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_ami_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('lpm_ami_checklists')->cascadeOnDelete();
            $table->text('pertanyaan');
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();

            $table->index(['checklist_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_ami_checklist_items');
    }
};
