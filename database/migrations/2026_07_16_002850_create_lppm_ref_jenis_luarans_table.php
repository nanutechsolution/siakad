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
        Schema::create('lppm_ref_jenis_luarans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode_luaran', 20)->unique();
            $table->string('nama_luaran', 150);
            $table->decimal('bobot_bkd', 5)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lppm_ref_jenis_luarans');
    }
};
