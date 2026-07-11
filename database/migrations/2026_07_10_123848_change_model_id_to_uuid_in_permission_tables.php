<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE model_has_roles
            MODIFY COLUMN model_id CHAR(36) NOT NULL
        ");

        DB::statement("
            ALTER TABLE model_has_permissions
            MODIFY COLUMN model_id CHAR(36) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE model_has_roles
            MODIFY COLUMN model_id BIGINT UNSIGNED NOT NULL
        ");

        DB::statement("
            ALTER TABLE model_has_permissions
            MODIFY COLUMN model_id BIGINT UNSIGNED NOT NULL
        ");
    }
};
