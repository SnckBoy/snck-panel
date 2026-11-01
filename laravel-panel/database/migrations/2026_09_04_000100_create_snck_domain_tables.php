<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nodes', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('name', 128);
            $table->text('description')->nullable();
            $table->string('host', 255)->nullable();
            $table->string('fqdn', 255)->nullable()->index();
            $table->string('hostname', 255)->nullable();
            $table->string('public_ip', 64)->nullable();
            $table->string('internal_ip', 64)->nullable();
            $table->string('location', 128)->nullable();
            $table->string('visibility', 16)->default('public');
            $table->boolean('is_local')->default(false);
            $table->unsignedSmallInteger('public_port')->default(443);
            $table->unsignedSmallInteger('daemon_port')->default(6768);
            $table->unsignedSmallInteger('api_port')->default(6768);
            $table->unsignedSmallInteger('sftp_port')->default(2022);
            $table->string('protocol', 8)->default('https');
            $table->text('custom_ca_pem')->nullable();
            $table->boolean('behind_proxy')->default(false);
            $table->string('access_client_id', 255)->nullable();
            $table->text('access_client_secret_encrypted')->nullable();
            $table->text('credential_encrypted')->nullable();
            $table->string('credential_hash', 128)->nullable();
            $table->string('setup_token_hash', 128)->nullable();
            $table->timestamp('setup_token_expires_at')->nullable();
            $table->boolean('setup_token_used')->default(false);
            $table->string('daemon_version', 64)->nullable();
            $table->string('status', 32)->default('offline')->index();
            $table->timestamp('last_heartbeat_at')->nullable()->index();
            $table->jsonb('stats')->nullable();
            $table->jsonb('last_health_check')->nullable();
            $table->boolean('docker_available')->default(false);
            $table->boolean('disabled')->default(false)->index();
            $table->boolean('maintenance')->default(false)->index();
            $table->boolean('free_service_locked')->default(false);
            $table->unsignedBigInteger('memory_limit_mb')->nullable();
            $table->unsignedBigInteger('disk_limit_mb')->nullable();
            $table->unsignedInteger('cpu_limit_percent')->nullable();
            $table->decimal('memory_overallocate', 8, 2)->default(0);
            $table->decimal('disk_overallocate', 8, 2)->default(0);
            $table->string('server_directory', 1024)->default('/var/lib/snck/servers');
            $table->string('docker_host', 1024)->default('/var/run/docker.sock');
            $table->string('cloudflare_zone_id', 128)->nullable();
            $table->string('cloudflare_record_id', 128)->nullable();
            $table->jsonb('legacy_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('servers', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('identifier', 64)->unique();
            $table->string('owner_id', 64)->index();
            $table->string('node_id', 64)->index();
            $table->string('name', 128);
            $table->text('description')->nullable();
            $table->string('container_id', 128)->nullable()->index();
            $table->string('image', 255)->default('itzg/minecraft-server:latest');
            $table->string('type', 32)->nullable();
            $table->string('version', 64)->nullable();
            $table->string('java_version', 16)->nullable();
            $table->string('status', 32)->default('offline')->index();
            $table->unsignedBigInteger('memory_limit_mb')->default(1024);
            $table->decimal('ram_gb', 12, 3)->nullable();
            $table->unsignedInteger('cpu_limit_percent')->default(100);
            $table->decimal('cpu_percent', 12, 3)->nullable();
            $table->unsignedBigInteger('disk_limit_mb')->default(10240);
            $table->decimal('disk_gb', 12, 3)->nullable();
            $table->unsignedSmallInteger('port')->nullable();
            $table->string('ip_alias', 255)->nullable();
            $table->string('theme', 64)->nullable();
            $table->string('runtime', 32)->default('docker');
            $table->text('startup_command')->nullable();
            $table->jsonb('environment_json')->nullable();
            $table->boolean('suspended')->default(false)->index();
            $table->timestamp('suspended_until')->nullable();
            $table->jsonb('discord_reward_suspension')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->string('root_path', 1024)->nullable();
            $table->jsonb('legacy_payload')->nullable();
            $table->timestamps();
            $table->foreign('owner_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('node_id')->references('id')->on('nodes')->restrictOnDelete();
        });

        Schema::create('allocations', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('node_id', 64)->index();
            $table->string('ip', 64);
            $table->unsignedTinyInteger('ip_version')->nullable();
            $table->unsignedSmallInteger('port');
            $table->unsignedSmallInteger('port_start')->nullable();
            $table->unsignedSmallInteger('port_end')->nullable();
            $table->string('alias', 255)->nullable();
            $table->string('assigned_server_id', 64)->nullable()->index();
            $table->boolean('is_primary')->default(false);
            $table->text('notes')->nullable();
            $table->jsonb('legacy_payload')->nullable();
            $table->timestamps();
            $table->unique(['node_id', 'ip', 'port']);
            $table->foreign('node_id')->references('id')->on('nodes')->cascadeOnDelete();
            $table->foreign('assigned_server_id')->references('id')->on('servers')->nullOnDelete();
        });

        Schema::create('server_subusers', function (Blueprint $table) {
            $table->string('server_id', 64);
            $table->string('user_id', 64);
            $table->string('added_by', 64)->nullable();
            $table->jsonb('permissions')->default('[]');
            $table->timestamps();
            $table->primary(['server_id', 'user_id']);
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('added_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('backups', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('server_id', 64)->index();
            $table->string('filename', 255);
            $table->text('path')->nullable();
            $table->string('node_backup_id', 255)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('checksum', 128)->nullable();
            $table->string('status', 32)->default('pending')->index();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->jsonb('legacy_payload')->nullable();
            $table->timestamps();
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 128)->primary();
            $table->jsonb('value')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();
        });

        Schema::create('api_keys', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('user_id', 64)->index();
            $table->string('label', 100);
            $table->string('key_hash', 128)->unique();
            $table->jsonb('scopes')->default('[]');
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->jsonb('legacy_payload')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('sftp_users', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('server_id', 64)->unique();
            $table->string('username', 128)->unique();
            $table->string('password_hash', 255);
            $table->jsonb('legacy_payload')->nullable();
            $table->timestamps();
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
        });

        Schema::create('cloudflare_accounts', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('account_id', 128)->nullable()->index();
            $table->string('name', 128)->nullable();
            $table->text('api_token_encrypted')->nullable();
            $table->text('legacy_payload_encrypted')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('themes', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('name', 128);
            $table->string('version', 32)->default('1.0.0');
            $table->string('author', 128)->nullable();
            $table->text('description')->nullable();
            $table->boolean('enabled')->default(true);
            $table->boolean('built_in')->default(false);
            $table->boolean('protected')->default(false);
            $table->jsonb('config');
            $table->jsonb('layout');
            $table->jsonb('legacy_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('discord_reward_state', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->jsonb('payload');
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('user_id', 64)->index();
            $table->string('type', 64);
            $table->string('title', 255);
            $table->text('message');
            $table->string('dedupe_key', 255)->nullable()->index();
            $table->timestamp('read_at')->nullable();
            $table->jsonb('legacy_payload')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('server_transfers', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('server_id', 64)->index();
            $table->string('source_node_id', 64);
            $table->string('destination_node_id', 64);
            $table->string('status', 32)->default('pending')->index();
            $table->text('error')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->jsonb('legacy_payload')->nullable();
            $table->timestamps();
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
            $table->foreign('source_node_id')->references('id')->on('nodes')->restrictOnDelete();
            $table->foreign('destination_node_id')->references('id')->on('nodes')->restrictOnDelete();
        });

        // Exact source JSON rows are retained for audit/recovery and for fields
        // that have not yet been ported. The new application does not read or
        // dual-write this table.
        Schema::create('legacy_json_records', function (Blueprint $table) {
            $table->id();
            $table->string('source_file', 255);
            $table->string('record_key', 255);
            $table->jsonb('payload');
            $table->char('sha256', 64);
            $table->timestamps();
            $table->unique(['source_file', 'record_key']);
        });

        Schema::create('legacy_import_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('source_sha256', 64)->index();
            $table->string('source_label', 255);
            $table->string('backup_path', 1024)->nullable();
            $table->jsonb('counts');
            $table->timestamp('completed_at');
            $table->timestamps();
        });

        Schema::create('audit_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_id', 64)->nullable()->index();
            $table->string('action', 128);
            $table->string('resource_type', 64)->nullable();
            $table->string('resource_id', 128)->nullable();
            $table->string('result', 32)->default('success');
            $table->string('request_id', 128)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->index(['resource_type', 'resource_id']);
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('legacy_import_runs');
        Schema::dropIfExists('legacy_json_records');
        Schema::dropIfExists('server_transfers');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('discord_reward_state');
        Schema::dropIfExists('themes');
        Schema::dropIfExists('cloudflare_accounts');
        Schema::dropIfExists('sftp_users');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('backups');
        Schema::dropIfExists('server_subusers');
        Schema::dropIfExists('allocations');
        Schema::dropIfExists('servers');
        Schema::dropIfExists('nodes');
    }
};
