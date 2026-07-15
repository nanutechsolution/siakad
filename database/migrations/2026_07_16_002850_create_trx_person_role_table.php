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
        Schema::create('trx_person_role', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('person_id')->index('trx_person_role_person_id_foreign');
            $table->unsignedBigInteger('role_id')->index('trx_person_role_role_id_foreign');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trx_person_role');
    }
};
