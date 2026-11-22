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

Supported installer targets: Ubuntu 22.04/24.04 and Debian 11/12/13.

    curl -fsSL https://raw.githubusercontent.com/SnckBoy/snck-panel/feat/snck-pterodactyl-v1.15.2/install.sh | sudo bash

The installer bootstraps PHP 8.3 and extensions, Composer, Node.js 22, Yarn Classic, MariaDB, Redis, Nginx, PHP-FPM and Certbot. It creates a restricted database account, builds frontend assets, runs migrations and seeds the default templates, creates the first administrator, configures the queue worker and scheduler, and can request a Let's Encrypt certificate when you provide an HTTPS domain and email.

The installer is interactive for the Panel URL and first administrator. It preserves existing Nginx sites and stops early if a non-Nginx service already occupies a required web port. Before running it, allow inbound TCP 80; also allow TCP 443 when using HTTPS in your VPS firewall or cloud security group. For a domain with automatic HTTPS, set these before running it:

    curl -fsSL https://raw.githubusercontent.com/SnckBoy/snck-panel/feat/snck-pterodactyl-v1.15.2/install.sh | sudo env SNCK_APP_URL=https://panel.example.com SNCK_LETSENCRYPT_EMAIL=admin@example.com bash

For non-default paths or database credentials, see the environment variables printed by:

    bash install.sh --help

## Install a game node (Wings)

Create a node in the Panel first. Copy its numeric node ID and use the Auto-Deploy token from the node's Configuration page.

    curl -fsSL https://raw.githubusercontent.com/SnckBoy/snck-panel/feat/snck-pterodactyl-v1.15.2/node-install.sh | sudo bash -s -- --panel-url https://panel.example.com --node-id 1

The installer asks for the one-time token without echoing it, installs Docker, downloads the pinned Wings v1.13.5 binary, verifies its SHA-256 digest, configures Wings from the Panel, and creates a systemd service. Supported node OS: Ubuntu 22.04/24.04 and Debian 12/13. Make sure the node firewall permits the API, SFTP and game allocation ports configured for that node.

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
