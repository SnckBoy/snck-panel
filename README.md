# SNCK Panel

SNCK Panel is a game-server hosting control panel based on the upstream Pterodactyl Panel v1.15.2 codebase. It retains the upstream PHP/Laravel application and React client so its existing API, database schema, and Wings integration remain compatible.

## Architecture

- **Panel:** PHP 8.3+, Laravel, and a React/TypeScript browser client.
- **Node agent:** Pterodactyl Wings (Go), installed separately on each game node. The panel source archive does not include Wings.
- **Database:** MariaDB/MySQL.
- **Cache and queues:** Redis.
- **Frontend build:** Node.js 22.12+ and Yarn Classic.

## Upstream source and licence

This repository contains a modified copy of Pterodactyl Panel, pinned to commit `90b2aa87469da4fffd941b3c99f3c1ca48a01b78` (v1.15.2). The upstream MIT licence and copyright notices are preserved in [LICENSE.md](LICENSE.md). SNCK branding changes are kept separate from upstream internal namespaces, database keys, and API contracts to avoid breaking compatibility.

## Installation

The production installer is being adapted for SNCK. Do not use the previous Laravel-migration installer against this fork. Use the SNCK installer instructions after the import PR's CI and clean-machine installation checks are complete.

## Compatibility notes

The panel and Wings communicate through the Pterodactyl node API. The previous SNCK TypeScript node daemon is not a drop-in Wings replacement and must not be installed with this panel. Install the matching SNCK-packaged Wings binary on each node after the node installer is published.
