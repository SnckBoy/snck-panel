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


def replace_all(rel: str, old: str, new: str) -> None:
    value = read(rel)
    if old not in value:
        raise ValueError(f"Expected branding marker not found in {rel}: {old!r}")
    write(rel, value.replace(old, new))


def main() -> None:
    if not (ROOT / "LICENSE.md").is_file():
        raise FileNotFoundError("Upstream LICENSE.md is missing; refusing to import.")

    composer = json.loads(read("composer.json"))
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
    replace_all(
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
        """<div class="box
            @if($version->isLatestPanel())
                box-success
            @else
                box-danger
            @endif
        ">
            <div class="box-header with-border">
                <h3 class="box-title">System Information</h3>
            </div>
            <div class="box-body">
                @if ($version->isLatestPanel())
                    You are running Pterodactyl Panel version <code>{{ config('app.version') }}</code>. Your panel is up-to-date!
                @else
                    Your panel is <strong>not up-to-date!</strong> The latest version is <a href="https://github.com/Pterodactyl/Panel/releases/v{{ $version->getPanel() }}" target="_blank"><code>{{ $version->getPanel() }}</code></a> and you are currently running version <code>{{ config('app.version') }}</code>. You can find instructions on how to update your panel <a href="https://github.com/pterodactyl/panel/panel/1.0/updating.html">here</a>.
                @endif
            </div>
        </div>""",
        """<div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">SNCK Panel Information</h3>
            </div>
            <div class="box-body">
                SNCK Panel is based on Pterodactyl Panel v1.15.2. Follow the
                <a href="https://github.com/SnckBoy/snck-panel/releases">SNCK Panel releases</a>
                for project updates.
            </div>
        </div>""",
    )
    replace_once("resources/views/admin/index.blade.php", 'href="{{ $version->getDiscord() }}"', 'href="https://github.com/SnckBoy/snck-panel/issues"')
    replace_once("resources/views/admin/index.blade.php", "Get Help <small>(via Discord)</small>", "Get Help <small>(GitHub Issues)</small>")
    replace_once("resources/views/admin/index.blade.php", "https://pterodactyl.io", "https://github.com/SnckBoy/snck-panel#readme")
    replace_once("resources/views/admin/index.blade.php", 'href="{{ $version->getDonations() }}"', 'href="https://github.com/SnckBoy/snck-panel"')
    replace_once("resources/views/admin/nests/index.blade.php", "Eggs are a powerful feature of Pterodactyl Panel", "Eggs are a powerful feature of SNCK Panel")
    replace_once("resources/views/admin/nests/index.blade.php", "those provided by <code>support@pterodactyl.io</code>", "the original Egg authors")
    replace_once("resources/views/admin/nests/view.blade.php", "official option authored by <code>support@pterodactyl.io</code>", "official option authored by the upstream project")
    replace_once("resources/views/admin/settings/advanced.blade.php", "Configure advanced settings for Pterodactyl.", "Configure advanced settings for SNCK Panel.")
    replace_once("resources/views/admin/settings/index.blade.php", "Configure Pterodactyl to your liking.", "Configure SNCK Panel to your liking.")
    replace_once("resources/views/admin/settings/mail.blade.php", "Configure how Pterodactyl should handle sending emails.", "Configure how SNCK Panel should handle sending emails.")
    replace_once("resources/views/admin/servers/index.blade.php", "Pterodactyl Console", "SNCK Console")
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
    write("public/.gitignore", "/assets/*\\n!/assets/svgs/\\n!/assets/svgs/*.svg\\n")
    write("public/assets/svgs/snck.svg", svg)

    readme = """# SNCK Panel

SNCK Panel is a game-server hosting panel forked from Pterodactyl Panel v1.15.2. The Panel backend is PHP/Laravel, the browser UI is React/TypeScript, and game nodes run the separate Go-based Wings service.

## Features

- Users, authentication, two-factor authentication and administrator roles.
- Servers, Nests/Eggs, startup variables, Docker images and install scripts.
- Nodes, allocations, resource limits and server placement.
- Live console, power controls, resource graphs, file manager and SFTP.
- MySQL databases, scheduled tasks, backups and restore workflows.
- Subusers, permissions, API keys and application/client APIs.
- Admin settings, user/node/server management and activity logs.

## One-command installation

Panel: Ubuntu 22.04/24.04 and Debian 11/12/13.

    curl -fsSL https://raw.githubusercontent.com/SnckBoy/snck-panel/feat/snck-pterodactyl-v1.15.2/install.sh | sudo bash

Game node: Ubuntu 22.04/24.04 and Debian 12/13. Create a node in Admin first, then run:

    curl -fsSL https://raw.githubusercontent.com/SnckBoy/snck-panel/feat/snck-pterodactyl-v1.15.2/node-install.sh | sudo bash -s -- --panel-url https://panel.example.com --node-id 1

The node installer downloads Wings v1.13.5 and verifies the published SHA-256 digest before installation. It prompts for the node Auto-Deploy token.

## Architecture and compatibility

The Panel is PHP/Laravel with a React/TypeScript frontend. Wings is a separate Go service required on each game node. The old SNCK TypeScript daemon is not compatible with the Pterodactyl node API and must not be used with this fork.

## Upstream source and licence

This repository contains a modified copy of Pterodactyl Panel pinned to commit 90b2aa87469da4fffd941b3c99f3c1ca48a01b78 (v1.15.2). The original MIT licence and copyright notice are preserved verbatim in LICENSE.md. See SNCK_UPSTREAM.md for attribution. SNCK branding leaves upstream internal namespaces, database keys, API contracts and Wings paths intact for compatibility.

The upstream project is not affiliated with or endorsing SNCK Panel.
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
