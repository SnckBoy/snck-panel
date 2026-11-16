#!/usr/bin/env python3
"""Conservative SNCK branding for the pinned Pterodactyl Panel source.

Internal PHP namespaces, database keys, API contracts, Wings paths, and event
names stay unchanged for compatibility. LICENSE.md remains verbatim.
"""
from __future__ import annotations

import json
import sys
from pathlib import Path

ROOT = Path(sys.argv[1]).resolve() if len(sys.argv) > 1 else Path.cwd()
REPO_URL = "https://github.com/SnckBoy/snck-panel"


def read(rel: str) -> str:
    path = ROOT / rel
    if not path.is_file():
        raise FileNotFoundError(f"Required upstream file is missing: {rel}")
    return path.read_text(encoding="utf-8")


def write(rel: str, value: str) -> None:
    path = ROOT / rel
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(value, encoding="utf-8")


def replace_once(rel: str, old: str, new: str) -> None:
    value = read(rel)
    if old not in value:
        raise ValueError(f"Expected branding marker not found in {rel}: {old!r}")
    write(rel, value.replace(old, new, 1))


def main() -> None:
    if not (ROOT / "LICENSE.md").is_file():
        raise FileNotFoundError("Upstream LICENSE.md is missing; refusing to import.")

    composer = json.loads(read("composer.json"))
    composer["name"] = "snck/panel"
    composer["description"] = "SNCK game server hosting panel, based on Pterodactyl Panel."
    write("composer.json", json.dumps(composer, indent=4, ensure_ascii=False) + "\n")

    package = json.loads(read("package.json"))
    package["name"] = "snck-panel"
    write("package.json", json.dumps(package, indent=4, ensure_ascii=False) + "\n")

    replace_once("config/app.php", "env('APP_NAME', 'Pterodactyl')", "env('APP_NAME', 'SNCK Panel')")
    replace_once(".env.example", 'MAIL_FROM_NAME="Pterodactyl Panel"', 'MAIL_FROM_NAME="SNCK Panel"')
    replace_once(
        "resources/scripts/components/auth/LoginFormContainer.tsx",
        "/assets/svgs/pterodactyl.svg",
        "/assets/svgs/snck.svg",
    )
    replace_once(
        "resources/scripts/components/auth/LoginFormContainer.tsx",
        "href={'https://pterodactyl.io'}",
        f"href={{'{REPO_URL}'}}",
    )
    replace_once(
        "resources/scripts/components/auth/LoginFormContainer.tsx",
        "Pterodactyl Software",
        "SNCK Panel",
    )
    replace_once(
        "resources/scripts/components/elements/PageContentBlock.tsx",
        "href={'https://pterodactyl.io'}",
        f"href={{'{REPO_URL}'}}",
    )
    replace_once(
        "resources/scripts/components/elements/PageContentBlock.tsx",
        "Pterodactyl&reg;",
        "SNCK Panel",
    )
    replace_once(
        "resources/views/layouts/admin.blade.php",
        "config('app.name', 'Pterodactyl')",
        "config('app.name', 'SNCK Panel')",
    )
    replace_once(
        "resources/views/layouts/admin.blade.php",
        "Pterodactyl Software",
        "SNCK Panel · based on Pterodactyl",
    )
    replace_once(
        "resources/views/templates/wrapper.blade.php",
        "config('app.name', 'Pterodactyl')",
        "config('app.name', 'SNCK Panel')",
    )
    replace_once(
        "resources/views/admin/index.blade.php",
        "You are running Pterodactyl Panel version",
        "You are running SNCK Panel version",
    )
    replace_once("resources/views/admin/index.blade.php", "https://pterodactyl.io", REPO_URL)
    replace_once("resources/views/admin/index.blade.php", "https://github.com/pterodactyl/panel", REPO_URL)
    replace_once(
        "resources/scripts/components/server/console/Console.tsx",
        "container@pterodactyl~",
        "container@snck~",
    )

    svg = """<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 96" role="img" aria-labelledby="title">
  <title id="title">SNCK Panel</title>
  <defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#a78bfa"/><stop offset="1" stop-color="#38bdf8"/></linearGradient></defs>
  <rect x="4" y="4" width="88" height="88" rx="24" fill="url(#g)"/>
  <path d="M54 17 29 52h18l-5 27 25-36H49z" fill="#fff"/>
  <text x="112" y="64" fill="#fff" font-family="Inter,Arial,sans-serif" font-size="48" font-weight="750" letter-spacing="-2">SNCK</text>
</svg>
"""
    write("public/assets/svgs/snck.svg", svg)

    readme = """# SNCK Panel

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
"""
    write("README.md", readme)

    upstream_note = """# Upstream attribution

SNCK Panel is a modified fork of Pterodactyl Panel.

- Upstream project: https://github.com/pterodactyl/panel
- Imported source commit: `90b2aa87469da4fffd941b3c99f3c1ca48a01b78`
- Upstream release: v1.15.2
- Licence: MIT; see [LICENSE.md](LICENSE.md).

The original copyright and permission notice is retained verbatim. SNCK branding changes do not imply endorsement by the upstream authors.
"""
    write("SNCK_UPSTREAM.md", upstream_note)
    print("SNCK branding applied; upstream licence and compatibility identifiers preserved.")


if __name__ == "__main__":
    main()
