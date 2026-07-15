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
        Schema::create('lpm_ami_discussions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('finding_id')->index('lpm_ami_discussions_finding_id_foreign');
            $table->char('user_id', 36)->index('lpm_ami_discussions_user_id_foreign');
            $table->text('message');
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpm_ami_discussions');
    }
};
