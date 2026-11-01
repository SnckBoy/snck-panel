<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('name', 128);
            $table->string('username', 64)->nullable()->unique();
            $table->string('email', 255)->nullable()->unique();
            $table->string('role', 16)->default('user')->index();
            $table->boolean('disabled')->default(false)->index();
            $table->string('google_id', 128)->nullable()->unique();
            $table->unsignedInteger('password_version')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->jsonb('legacy_payload')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // The owner role is a singleton in this panel. The transactional setup
        // lock below serializes bootstrap requests; this index is the DB-level
        // invariant that also protects against other write paths.
        DB::statement("CREATE UNIQUE INDEX users_single_owner_unique ON users (role) WHERE role = 'owner'");

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id', 64)->nullable()->index();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('setup_state', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->primary();
            $table->string('owner_user_id', 64)->nullable()->unique();
            $table->timestamp('completed_at')->nullable();
            $table->foreign('owner_user_id')->references('id')->on('users')->nullOnDelete();
        });
        DB::table('setup_state')->insert(['id' => 1]);
    }

    public function down(): void
    {
        Schema::dropIfExists('setup_state');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        DB::statement('DROP INDEX IF EXISTS users_single_owner_unique');
        Schema::dropIfExists('users');
    }
};
