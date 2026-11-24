<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Increase the default Wings upload limit for newly created nodes.
     *
     * Existing node-specific upload limits are intentionally left unchanged.
     */
    public function up(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->unsignedInteger('upload_size')->default(10240)->change();
        });
    }

    /**
     * Restore the upstream default for newly created nodes.
     *
     * Existing node-specific upload limits are intentionally left unchanged.
     */
    public function down(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->unsignedInteger('upload_size')->default(100)->change();
        });
    }
};
