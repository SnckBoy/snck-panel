# SNCK Panel

SNCK Panel is a game-server hosting control panel forked from the upstream Pterodactyl Panel v1.15.2 source. It keeps the upstream PHP/Laravel backend, React/TypeScript web client, database schema, and node API so the established hosting workflows remain compatible. SNCK adds its own product branding and a translucent purple/blue glass visual theme.

> **Status:** draft release candidate. Automated CI passes on PHP 8.3 with MariaDB 11.4 and MySQL 8.4, and the Composer security audit is clean. A fresh VPS installation and real Wings/Minecraft node lifecycle test are still pending; do not treat this as production-verified yet.

## Features included in the upstream panel

- User accounts, authentication, two-factor authentication, and administrator roles.
- Server creation and lifecycle management.
- Nests and Eggs for server templates, startup variables, Docker images, and install scripts.
- Nodes, allocations, resource limits, and server placement.
- Live console, power controls, resource graphs, and server activity.
- File manager, archive operations, file editing, and SFTP.
- MySQL database provisioning and management.
- Scheduled tasks and task actions.
- Server backups and restore workflows.
- Subusers, permissions, API keys, and application/client APIs.
- Admin settings, user management, node management, server management, and audit/activity views.

Availability of a feature depends on the node being correctly provisioned and connected to the Panel.

## Architecture

| Component | Technology | Role |
| --- | --- | --- |
| Panel backend | PHP 8.3+, Laravel | Authentication, admin, API, database, scheduling and server orchestration |
| Web client | React, TypeScript | Responsive browser interface and live server controls |
| Node agent | Wings (Go) | Docker-based game server runtime, console, files and SFTP |
| Database | MariaDB/MySQL | Panel data |
| Cache and queues | Redis | Sessions, caching and background jobs |
| Web server | Nginx + PHP-FPM | Serves the Panel |

The panel source ZIP does not contain Wings. The previous SNCK TypeScript daemon is not compatible with the Pterodactyl node API and must not be used with this fork.

## One-command Panel installation

Supported installer targets: Ubuntu 22.04/24.04 and Debian 11/12/13. Local Wings provisioning follows the official Wings support matrix and also accepts Ubuntu 20.04.

    curl -fsSL https://raw.githubusercontent.com/SnckBoy/snck-panel/feat/snck-control-center-v2/menu.sh | sudo bash

The installer bootstraps PHP 8.3 and extensions, Composer, Node.js 22, Yarn Classic, MariaDB, Redis, Nginx, PHP-FPM and Certbot. It creates a restricted database account, builds frontend assets, runs migrations and seeds the default templates, creates the first administrator, configures the queue worker and scheduler, and can request a Let's Encrypt certificate when you provide an HTTPS domain and email.

The interactive SNCK Control Center menu provides fresh installation, safe update (with database and environment backup), repair, diagnostics, backup, Wings installation, service restart, and reversible disable options. Fresh installation is interactive for the Panel URL and first administrator. It preserves existing Nginx sites and stops early if a non-Nginx service already occupies a required web port. Before running it, allow inbound TCP 80; also allow TCP 443 when using HTTPS in your VPS firewall or cloud security group. For a domain with automatic HTTPS, set these before running it:

    curl -fsSL https://raw.githubusercontent.com/SnckBoy/snck-panel/feat/snck-control-center-v2/install.sh | sudo env SNCK_APP_URL=https://panel.example.com SNCK_LETSENCRYPT_EMAIL=admin@example.com bash

For non-default paths or database credentials, see the environment variables printed by:

    bash install.sh --help

## Create a local node with one click

On the Panel host, open **Admin → Nodes** and select **Create Local Node**. SNCK detects the host capacity, checks the API and SFTP ports, creates the node record with Panel-generated credentials, installs Docker if needed, downloads and checksum-verifies the pinned Wings binary when one is not already installed, and starts Wings in the background. The action does not overwrite an existing Wings configuration.

Requirements and network notes:

- New nodes default to a 10 GiB (10,240 MiB) per-file Wings upload limit. Fresh installs configure Nginx for 11 GiB request bodies and PHP-FPM for 10 GiB file uploads / 11 GiB POST bodies. This leaves room for multipart overhead.
- Existing node-specific upload limits are preserved by the migration. Increase them from the node settings only when you intend to change an existing node. Large uploads still require enough free disk space, bandwidth, and client timeout tolerance.

- Run the Panel on Ubuntu 20.04/22.04/24.04 or Debian 11/12/13 with systemd.
- HTTP is accepted only when the Panel is opened through a private LAN or Tailscale address. Public hostnames must use HTTPS with a valid Let's Encrypt certificate for the same hostname.
- TCP 8080 (Wings API) and 2022 (SFTP) must be reachable from the clients that use the node. Allow them in the VPS firewall and cloud security group where applicable. The installer cannot change AWS or another cloud provider's security-group rules.
- The setup runs asynchronously. The node is online only after Wings starts and an authenticated `/api/system` response succeeds. The panel health check keeps daemon credentials server-side; a failed setup is logged to `/var/log/snck-local-node-NODE_ID.log` and `journalctl` under the generated `snck-local-node-setup-*` unit.
- An existing `/etc/pterodactyl/config.yml` or occupied node ports 8080/2022 cause the action to stop rather than overwrite or interfere with an existing daemon.

If setup fails, inspect it without printing the node token:

    systemctl status "snck-local-node-setup-NODE_ID-*" --no-pager
    journalctl -u "snck-local-node-setup-NODE_ID-*" -n 120 --no-pager
    cat "/var/log/snck-local-node-NODE_ID.log"
    systemctl status docker wings --no-pager
    ss -ltnp | grep -E ':(8080|2022)\b'

The staged configuration remains under `/var/lib/snck-local-node/` after a failed bootstrap so an administrator can diagnose the failure. Do not paste that file into tickets or chat. Fix Docker, DNS/certificates, firewall rules, or the conflicting service, then remove the failed panel node record only after confirming it has no servers or allocations and retry from **Admin → Nodes**.

The SNCK installer and updater install a root-owned bootstrap helper and grant the `www-data` account permission to invoke only that helper with no command-line arguments. The helper receives the node configuration over standard input and performs the privileged installation as a systemd unit.

## Install a game node (Wings)

Create a node in the Panel first. Copy its numeric node ID and use the Auto-Deploy token from the node's Configuration page.

    curl -fsSL https://raw.githubusercontent.com/SnckBoy/snck-panel/feat/snck-control-center-v2/node-install.sh | sudo bash -s -- --panel-url https://panel.example.com --node-id 1

The installer asks for the one-time token without echoing it, installs Docker, downloads the pinned Wings v1.13.5 binary, verifies its SHA-256 digest, configures Wings from the Panel, and creates a systemd service. Supported node OS: Ubuntu 20.04/22.04/24.04 and Debian 11/12/13. Make sure the node firewall permits the API, SFTP and game allocation ports configured for that node.

## Development and validation

Requirements:

- PHP 8.3 and Composer 2.
- MariaDB/MySQL for integration tests. Redis is required for production cache, sessions and queues; the CI test profile uses isolated array cache and synchronous queues.
- Node.js 22.12+ and Yarn Classic 1.22.
- Docker on a node host for Wings integration.

Build and test:

    composer install
    cp .env.ci .env
    vendor/bin/phpunit --bootstrap vendor/autoload.php tests/Unit
    vendor/bin/phpunit tests/Integration
    composer audit

    npm install --global yarn@1.22.22
    yarn install --frozen-lockfile
    yarn tsc
    yarn lint
    yarn build:production

## Upstream source and licence

This repository contains a modified copy of Pterodactyl Panel, pinned to commit 90b2aa87469da4fffd941b3c99f3c1ca48a01b78 (v1.15.2). The original MIT licence and copyright notice are preserved verbatim in LICENSE.md, with additional attribution in SNCK_UPSTREAM.md. SNCK branding changes deliberately leave internal namespaces, database keys, event names, API contracts and Wings paths intact to avoid breaking compatibility.

The upstream project is not affiliated with or endorsing SNCK Panel.
