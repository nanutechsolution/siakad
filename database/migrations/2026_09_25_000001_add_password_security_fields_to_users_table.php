<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('must_change_password')->default(false)->after('is_active');
            $table->unsignedTinyInteger('failed_login_attempts')->default(0)->after('last_login_ip');
            $table->timestamp('locked_at')->nullable()->after('failed_login_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'must_change_password',
                'failed_login_attempts',
                'locked_at',
            ]);
        });
    }
};
