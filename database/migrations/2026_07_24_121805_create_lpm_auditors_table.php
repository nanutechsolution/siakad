<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_auditors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('ref_person')->cascadeOnDelete();
            $table->string('no_sertifikat_auditor', 100)->nullable();
            $table->text('kompetensi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('person_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_auditors');
    }
};
