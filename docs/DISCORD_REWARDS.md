# Discord Invite Rewards

Discord Invite Rewards adds a Discord gateway bot, OAuth identity linking, an invite/referral ledger, configurable reward plans, and a panel page under **Free Service → Discord Rewards**. Administrators and owners configure the integration; ordinary users see their own progress and claim history.

## Storage and process model

This feature uses the panel's current persistent JSON runtime store, `.data/discord_rewards.json`, and its serialized atomic JSON updates. It does not introduce a PostgreSQL runtime dependency. The production PM2 ecosystem is configured as one forked instance. Keep the panel at one active process: the invite event queue and claim reservation lock are process-local, not a distributed transaction. Do not run multiple panel replicas against this feature until its state and locking are moved to a shared transactional database.

There is no SQL/ORM migration: the inspected application currently persists its runtime records in JSON files, so the safe compatible schema is initialized and normalized in the dedicated rewards JSON store. Existing panel user, node, allocation, and server data files are not rewritten by this feature.

The rewards JSON file is written with mode `0600`. Bot tokens, OAuth client secrets, Discord snowflakes in invite snapshots, linked account identifiers, and member identifiers are encrypted with AES-256-GCM using key material derived from `NODE_ENCRYPTION_KEY`. The stable identifiers used for duplicate-credit prevention and inviter matching are keyed HMACs; the admin UI returns only the last four digits, not the full snowflake. Never rotate or lose `NODE_ENCRYPTION_KEY` without first planning a migration: previously encrypted values will no longer decrypt. Back up the `.env` key and panel data together, and keep both backups private.

OAuth access tokens are held only in memory for the callback and are not persisted. Account linking uses Discord's `identify` scope; the feature never requests a Discord password or user token. Users can unlink their account from their rewards page. Unlinking erases the reversible Discord identity mapping and inviter ciphertext while preserving historical earned credits and pseudonymous deduplication records. Linked-account display names remain in the private rewards store until the user unlinks or an administrator removes the account data through normal account-retention procedures.

The configurable retention policy is 30–3650 days (default 365). Cleanup runs daily while the panel process is up and can also be run by an administrator from **Activity & claims**. Aged unverified events are deleted. Aged credited/revoked events have reversible Discord IDs, visible suffixes, and invite codes erased while their keyed deduplication hashes and credit status are retained so historical progress and anti-duplicate protections continue to work. Claim and audit history remains available for administration. Back up the store before manually changing its JSON contents.

## Discord setup

1. Create an application and bot in the Discord Developer Portal.
2. Enable the privileged **Server Members Intent** on the Bot page. The gateway requests Guilds, Guild Members, and Guild Invites.
3. Invite the bot into the target server. Grant it **Manage Server** to read invite-use counts, and **View Channel**/**Send Messages** in any configured welcome or announcement channels. Avoid granting administrator permissions.
4. Under OAuth2, register the exact callback URI shown in the panel, normally `https://YOUR_PANEL_HOST/api/discord-rewards/oauth/callback`. Use HTTPS for public deployments.
5. In **Bot setup**, enter the guild ID, optional channel IDs, bot token, OAuth application/client ID and client secret, and the exact redirect URI. Secrets are write-only: saving a blank secret retains the existing encrypted value; the API never returns either secret.
6. Set the minimum Discord account age, whether inviter credit survives a member leaving, and the event retention interval. Test the connection, then save/enable the bot. A failed connection does not stop the panel server.
7. Configure reward plans under **Reward plans**. Each plan has an invite threshold, create/upgrade choices, RAM/CPU/disk/duration, eligible node IDs, per-user and global claim caps, and an enabled flag.

The first successful invite fetch is a baseline and never grants credit. An event is automatically attributed only when exactly one existing invite's use count increases by exactly one and that invite has a known inviter. Vanity invites, missing permissions, deleted/created invites during the event, simultaneous changes, and other ambiguous deltas are recorded as unverified rather than guessed. New invitees must not be bots and must pass the configured account-age check; younger accounts stay pending. A member is credited at most once globally. If an inviter links after a join, eligible recent events that were waiting only for account linking are matched and reconsidered.

Invite attribution is not proof of a genuine referral and cannot fully prevent self-referrals or other Discord-side abuse. Administrators can review events, but manual verification also cannot award the same pseudonymous member twice. If **Keep inviter credit if member leaves** is off, a leaving member's pending/verified event is revoked. Otherwise an already-earned credit remains.

## Safe reward fulfillment

The claim endpoint rechecks eligibility on the server; browser data is never trusted. A user must meet the invite threshold, have a linked account that is currently a member of the configured Discord guild, and have an eligible plan claim available. Create claims use the panel's existing server provisioning controller and a currently free allocation. Upgrade claims require a server owned by the claimant and use the existing resource-update controller.

Free Service must be enabled. Plan RAM, CPU, and disk must be finite, positive, and no greater than the current global Free Service ceilings. Create claims also enforce the per-user server cap and cannot exceed the configured default duration. Only enabled, non-maintenance, online nodes (or a ready local node) that are not Free-Service-locked are offered; plans can further restrict node IDs. Existing node-health, ownership, allocation, port, and resource checks remain authoritative. Claims have per-user idempotency keys, atomic single-process reservations, and a global quota that counts in-progress/reconciliation reservations. A timeout or uncertain provisioning result is held for administrator reconciliation rather than silently retried.

The **Activity & claims** tab provides masked referral events, sanitized diagnostics, invite resync, a manual retention run, paginated event/claim ledgers, and audited claim reconciliation. Release a pending claim only after confirming that no server or resource change was applied; otherwise confirm fulfillment to prevent duplicate provisioning.

## API overview

All API paths are mounted under `/api/discord-rewards`:

- User: `GET /me`, `GET /oauth/start`, `DELETE /account/link`, `GET /claim-options`, `POST /claims`
- OAuth callback: `GET /oauth/callback` (single-use state, limited request rate)
- Administrator: `GET /admin`, `PUT /admin/config`, `POST /admin/test-connection`, `POST /admin/sync`, `POST /admin/prune`, reward-plan CRUD, referral review, and claim reconciliation

User responses are scoped to the authenticated user's progress and claims. Admin configuration never includes cleartext secrets; only presence indicators are returned. Privileged routes require an admin or owner role and sensitive OAuth/claim/test routes are rate-limited.

## Development checks

```bash
npm test
npm run lint
npm run build
```

`npm test` runs theme tests and isolated Discord-rewards tests for invite-delta attribution, plan validation, node eligibility and RAM/CPU/disk capacity, ownership and upgrade ceilings, duplicate-credit checks, encrypted identifiers, retention cleanup, private file permissions, and serialized concurrent claim idempotency. These tests do not connect to Discord or provision servers. A live **Test connection** still requires a correctly configured Discord application, bot, guild, intent, and permissions.

## Production update

After merging the reviewed change, back up the panel data and `.env` together. From the installed panel directory, build the updated release and restart through the installed service helper (which chooses the available systemd or PM2/cron mode):

```bash
cd /opt/snck-panel
npm ci --include=dev --no-audit --no-fund
npm run lint
npm run build
sudo /usr/local/bin/snck-panel-service restart
```

For an installation in a different directory, substitute its configured `SNCK_PANEL_DIR`. Keep `NODE_ENCRYPTION_KEY` stable across updates and backups; losing it makes previously encrypted bot/OAuth credentials and linked identity mappings unreadable. The Discord worker starts after the panel HTTP listener and a Discord outage does not prevent panel startup.
