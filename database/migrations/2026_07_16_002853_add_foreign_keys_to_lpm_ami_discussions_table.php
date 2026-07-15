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
        Schema::table('lpm_ami_discussions', function (Blueprint $table) {
            $table->foreign(['finding_id'])->references(['id'])->on('lpm_ami_findings')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lpm_ami_discussions', function (Blueprint $table) {
            $table->dropForeign('lpm_ami_discussions_finding_id_foreign');
            $table->dropForeign('lpm_ami_discussions_user_id_foreign');
        });
    }
};
