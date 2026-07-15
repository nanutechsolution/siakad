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
        Schema::table('payment_policy_details', function (Blueprint $table) {
            $table->foreign(['komponen_biaya_id'])->references(['id'])->on('keuangan_komponen_biaya')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['payment_policy_id'])->references(['id'])->on('payment_policies')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_policy_details', function (Blueprint $table) {
            $table->dropForeign('payment_policy_details_komponen_biaya_id_foreign');
            $table->dropForeign('payment_policy_details_payment_policy_id_foreign');
        });
    }
};
