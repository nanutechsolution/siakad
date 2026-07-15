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
        Schema::create('dosen_profile_change_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('dosen_id', 36);
            $table->string('field_name', 50);
            $table->text('old_value')->nullable();
            $table->text('new_value');
            $table->text('reason')->nullable();
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->char('reviewed_by', 36)->nullable()->index('dosen_profile_change_requests_reviewed_by_foreign');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_note')->nullable();
            $table->timestamps();

            $table->index(['dosen_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosen_profile_change_requests');
    }
};
