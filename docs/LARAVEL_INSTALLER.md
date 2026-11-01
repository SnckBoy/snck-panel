# Laravel production installer

The Laravel migration is installed with `curl -fsSL https://raw.githubusercontent.com/SnckBoy/snck-panel/main/install.sh | sudo bash`. The root entry point fetches `scripts/install-laravel-panel.sh` from the selected branch; you can also run the inner installer directly with `sudo bash scripts/install-laravel-panel.sh`. The installer supports Ubuntu and Debian hosts, requires PHP 8.3 with `mbstring`, `openssl`, `pdo_pgsql`, and `zip`, Node.js 20+, Composer, and systemd. It installs Nginx, PHP-FPM, the PostgreSQL client, application dependencies, compiled assets, migrations, the queue worker, and a one-minute scheduler timer.

For a branch test, use `SNCK_BRANCH=php-migration-final` with the root entry point. Run the non-destructive preflight first:

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

The node installer now downloads the daemon source from the repository's `main` branch, obtains a trusted Let's Encrypt certificate for the node FQDN, generates an SSH host key, and writes `tlsKey`, `tlsCert`, `sftpHostKey`, and `sftpPort` into the private daemon config. The Infrastructure page creates a one-time install command using the node's bearer credential. Each password login is validated by Laravel at `/api/node-agent/sftp-auth` with the node bearer credential; the returned server ID determines the jail root. Absolute SFTP paths are interpreted as virtual paths inside the server jail, not host paths. The daemon rejects `..` traversal, symlink components, symlink entries, and cross-server access.

Before running the generated command, point the node FQDN to the VPS and allow inbound TCP 80 for certificate issuance/renewal, TCP 6768 for the HTTPS daemon, and TCP 2022 for SFTP. Keep SFTP restricted to trusted networks. Test heartbeat and file transfer with a real node before production. The panel stores only a password hash; the create/reset response is the only time the plaintext password is shown. The transport is implemented in `node-daemon/src/sftp.ts` and is covered by a real SSH client upload/download test, virtual-root checks, traversal/symlink tests, and Laravel node-authentication tests.
