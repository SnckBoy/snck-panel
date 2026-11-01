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

## SFTP transport

The node daemon now includes an SSH/SFTP listener. Configure `sftpHostKey` to an administrator-owned private host-key file, `sftpPort` (default `2022`), `panelUrl`, `nodeId`, `credential`, and the same `serverDirectory` used by the daemon. Each password login is validated by Laravel at `/api/node-agent/sftp-auth` with the node bearer credential; the returned server ID determines the jail root. The daemon rejects absolute paths, `..` traversal, symlink escapes, symlink entries, and cross-server access.

Expose the SFTP port only through a firewall or private network, use a stable host key, and test with an SFTP client after provisioning credentials in the server SFTP tab. The panel stores only a password hash; the create/reset response is the only time the plaintext password is shown. The transport is implemented in `node-daemon/src/sftp.ts` and is covered by traversal/symlink tests plus Laravel node-authentication tests.
