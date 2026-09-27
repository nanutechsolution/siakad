<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regencies', function (Blueprint $table): void {
            $table->string('code', 6)->change();
        });

        Schema::table('districts', function (Blueprint $table): void {
            $table->string('code', 12)->change();
        });

        Schema::table('villages', function (Blueprint $table): void {
            $table->string('code', 20)->change();
        });
    }

    public function down(): void
    {
        Schema::table('regencies', function (Blueprint $table): void {
            $table->string('code', 4)->change();
        });

        Schema::table('districts', function (Blueprint $table): void {
            $table->string('code', 6)->change();
        });

        Schema::table('villages', function (Blueprint $table): void {
            $table->string('code', 10)->change();
        });
    }
};
