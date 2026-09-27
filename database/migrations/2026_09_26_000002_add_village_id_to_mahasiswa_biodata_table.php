<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswa_biodata', function (Blueprint $table): void {
            $table->foreignId('village_id')
                ->nullable()
                ->after('kode_pos')
                ->constrained('villages')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::table('mahasiswa_biodata', function (Blueprint $table): void {
                $table->dropColumn('village_id');
            });

            return;
        }

        Schema::table('mahasiswa_biodata', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('village_id');
        });
    }
};
