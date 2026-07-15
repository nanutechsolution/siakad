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
        Schema::create('ref_aturan_sks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('min_ips', 4);
            $table->decimal('max_ips', 4);
            $table->integer('max_sks');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_aturan_sks');
    }
};
