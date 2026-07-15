<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_change_requests', function (Blueprint $table) {
            $table->id();
            $table->char('mahasiswa_id', 36);
            $table->string('field_name', 50); // nama_lengkap, nik, tanggal_lahir, dst (kolom di ref_person)
            $table->text('old_value')->nullable();
            $table->text('new_value');
            $table->text('reason')->nullable(); // alasan mahasiswa mengajukan perubahan
            $table->string('attachment_path')->nullable(); // bukti dokumen: KTP/KK/akta
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->char('reviewed_by', 36)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_note')->nullable();
            $table->timestamps();

            $table->index(['mahasiswa_id', 'status']);
            $table->foreign('mahasiswa_id')
                ->references('id')->on('mahasiswas')
                ->cascadeOnDelete();
            $table->foreign('reviewed_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_change_requests');
    }
};
