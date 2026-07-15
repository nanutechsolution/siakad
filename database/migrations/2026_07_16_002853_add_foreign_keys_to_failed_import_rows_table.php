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
        Schema::table('failed_import_rows', function (Blueprint $table) {
            $table->foreign(['import_id'])->references(['id'])->on('imports')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('failed_import_rows', function (Blueprint $table) {
            $table->dropForeign('failed_import_rows_import_id_foreign');
        });
    }
};
