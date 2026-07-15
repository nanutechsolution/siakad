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
        Schema::table('master_kurikulums', function (Blueprint $table) {
            $table->foreign(['prodi_id'])->references(['id'])->on('ref_prodi')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_kurikulums', function (Blueprint $table) {
            $table->dropForeign('master_kurikulums_prodi_id_foreign');
        });
    }
};
