<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Log MENTAH setiap notifikasi webhook Midtrans yang masuk, apa pun
 * hasilnya (diproses, ditolak signature, order_id tidak dikenal, dst).
 * Append-only, tidak pernah diupdate/dihapus — satu-satunya cara
 * menelusuri "Midtrans bilang apa persisnya" kalau ada sengketa nominal
 * atau status di kemudian hari.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('midtrans_gateway_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_id', 100)->nullable();
            $table->string('transaction_status', 50)->nullable();
            $table->json('payload');
            $table->timestamp('created_at')->useCurrent();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('midtrans_gateway_logs');
    }
};
