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
        Schema::create('payment_policy_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('payment_policy_id')->index('payment_policy_details_payment_policy_id_foreign');
            $table->unsignedBigInteger('komponen_biaya_id')->index('payment_policy_details_komponen_biaya_id_foreign');
            $table->decimal('minimal_persen', 5)->default(100);
            $table->decimal('minimal_nominal', 15)->nullable();
            $table->boolean('wajib')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_policy_details');
    }
};
