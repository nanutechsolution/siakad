<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('migration_batches', function (Blueprint $table) {
            $table->id();
            $table->enum('source', ['EXCEL', 'CSV', 'NEO_DATABASE', 'NEO_API']);
            $table->enum('status', ['PROCESSING', 'COMPLETED', 'FAILED'])
                ->default('PROCESSING');
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->json('parameter_snapshot');
            $table->json('summary_snapshot')->nullable();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('total_berhasil')->default(0);
            $table->unsignedInteger('total_gagal')->default(0);
            $table->unsignedInteger('total_dilewati')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['status', 'created_at']);
            $table->index(['source', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('migration_batches');
    }
};