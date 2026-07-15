<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dosen_profile_change_requests', function (Blueprint $table) {
            $table->id();
            $table->char('dosen_id', 36);
            $table->string('field_name', 50); // kolom di ref_person: nama_lengkap, nik, dst
            $table->text('old_value')->nullable();
            $table->text('new_value');
            $table->text('reason')->nullable();
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->char('reviewed_by', 36)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_note')->nullable();
            $table->timestamps();

            $table->index(['dosen_id', 'status']);
            $table->foreign('dosen_id')
                ->references('id')->on('trx_dosen')
                ->cascadeOnDelete();
            $table->foreign('reviewed_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosen_profile_change_requests');
    }
};