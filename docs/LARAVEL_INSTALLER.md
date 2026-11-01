# Laravel production installer

The Laravel migration is installed with `sudo bash scripts/install-laravel-panel.sh`. The installer supports Ubuntu and Debian hosts, requires PHP 8.3 with `mbstring`, `openssl`, `pdo_pgsql`, and `zip`, Node.js 20+, Composer, and systemd. It installs Nginx, PHP-FPM, the PostgreSQL client, application dependencies, compiled assets, migrations, the queue worker, and a one-minute scheduler timer.

Run the non-destructive preflight first:

```bash
sudo bash scripts/install-laravel-panel.sh --dry-run
```

The real install accepts `--app-dir`, `--repo`, and `--branch`. It backs up an existing application directory before replacement, preserves the existing `.env` when present, generates an application key only through Laravel, runs migrations with `--force`, validates Nginx configuration, and enables these units:

- `snck-panel-laravel.service`: Laravel database queue worker
- `snck-panel-laravel-scheduler.timer`: scheduler every minute
- `nginx.service` and `php8.3-fpm.service`

After Nginx and PHP-FPM are available, the first-run web installer is available at `/install` (also `/setup`). It creates the first Owner account exactly once. Configure PostgreSQL credentials in `.env` before running the installer; the web flow must never be used to collect database passwords over an untrusted connection.

Rollback is intentionally conservative: the CLI installer creates a timestamped sibling backup before replacing an existing application directory. Stop the queue worker, restore the backup directory, run `composer install --no-dev`, and restart the services after verifying the restored `.env` and database migration state. The installer does not delete database rows or automatically roll back migrations.
