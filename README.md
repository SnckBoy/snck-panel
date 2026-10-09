# SNCK PANEL

SNCK PANEL is a self-hosted web control panel for managing Minecraft servers and remote Docker nodes.

## One-command installer

On a fresh Ubuntu 20.04+ or supported Debian VPS with a root/sudo account, run:

```bash
curl -fsSL https://raw.githubusercontent.com/SnckBoy/snck-panel/main/install.sh | sudo bash
```

This opens the **SNCK PANEL CONTROL CENTER** menu. It does not silently install on launch.

Menu options:

1. Install SNCK PANEL
2. Update SNCK PANEL
3. Repair / reinstall application files
4. Status and health check
5. Restart panel
6. View panel and installer logs
7. Create a data/config backup
8. System information
9. Uninstall panel (requires typing `UNINSTALL`)
0. Exit

Supported OS targets in the installer are Ubuntu 20.04+ and Debian 11/12/13 on `amd64` or `arm64`. A fresh install requires internet access to package repositories and GitHub. The installer installs Node.js when needed, Docker, builds the application, configures a systemd service, and checks `/health` before reporting success.

## Access and operations

Default application port: `6767`.

After installation, open:

```text
http://YOUR_SERVER_IP:6767
```

Use the server's firewall and a reverse proxy with HTTPS for public deployments. Do not expose a panel or node daemon to the public internet without reviewing firewall rules and TLS configuration.

Useful commands:

```bash
sudo systemctl status snck-panel --no-pager
sudo systemctl restart snck-panel
sudo journalctl -u snck-panel -n 100 --no-pager
curl -fsS http://127.0.0.1:6767/health
```

Run the same one-command installer again to open the menu for updates, repair, diagnostics, backups, or removal. Backups are stored under `/var/backups/snck-panel`. Uninstall creates a final backup of `.data` and `.env` before removing the application directory; it does not delete Docker images or containers.

## Runtime data and database note

The current application reads and writes JSON files under `/opt/snck-panel/.data`. The installer therefore provisions this JSON store and does **not** claim to switch the application to PostgreSQL. The repository contains PostgreSQL schema/migration work, but that is not the active runtime storage path. Do not treat a PostgreSQL migration as complete until all application routes and services have been moved to and tested against the database.

## Repository checks

GitHub Actions runs dependency installation, TypeScript checking, the production build, node-daemon build, and shell syntax checks on the configured branches. A green CI run is required before treating a particular commit as verified. The installer itself also runs TypeScript validation and the production build before restarting the service.

## Security basics

- Use a strong unique admin password and keep `.env` private.
- Use HTTPS for public deployments and a firewall to restrict access.
- Keep Ubuntu, Docker, Node.js dependencies, and this repository updated.
- Keep backups outside the application directory.
- Never expose the Docker socket directly to untrusted users.
- Review node daemon TLS and network exposure before connecting remote nodes.
