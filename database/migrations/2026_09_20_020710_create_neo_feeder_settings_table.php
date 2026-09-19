<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('neo_feeder_settings', function (Blueprint $table) {
            $table->id();
            $table->string('url')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable();   // terenkripsi (cast encrypted)
            $table->text('token')->nullable();      // terenkripsi (cast encrypted)
            $table->boolean('verify_ssl')->default(true);
            $table->unsignedSmallInteger('timeout')->default(60);
            $table->unsignedSmallInteger('connect_timeout')->default(10);
            $table->timestamp('token_obtained_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('neo_feeder_settings');
    }
};
