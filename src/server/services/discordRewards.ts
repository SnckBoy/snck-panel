import { Client, Events, GatewayIntentBits, type Guild, type Invite, type GuildMember } from "discord.js";
import crypto from "node:crypto";
import { readJSON, updateJSON } from "./db.js";
import { decryptSecret, encryptSecret } from "./security.js";
import { stopContainer } from "./docker.js";
import { attributeInvite, hasSuccessfulCreditForMember, type InviteSnapshot } from "./discordRewardsCore.js";

export type DiscordRewardsConfig = {
  enabled: boolean; botTokenEncrypted: string | null; guildId: string; announcementChannelId: string;
  oauthClientId: string; oauthClientSecretEncrypted: string | null;
  oauthRedirectUri: string; minimumAccountAgeDays: number; retainCreditOnLeave: boolean; dataRetentionDays: number;
};
export type ReferralEvent = {
  id: string; guildId: string; memberIdHash: string; memberIdEncrypted: string; memberIdSuffix: string; inviterIdHash: string | null; inviterIdEncrypted: string | null; inviterIdSuffix: string | null; inviterPanelUserId: string | null;
  inviteCode: string | null; status: "verified" | "pending" | "unverified" | "revoked";
  reason: string; joinedAt: string; leftAt: string | null; accountCreatedAt: string | null; everCredited: boolean;
};
export type RewardClaim = { id: string; idempotencyKey: string; panelUserId: string; planId: string; mode: "create" | "upgrade"; status: "processing" | "claimed" | "suspended" | "failed" | "needs_review"; targetId: string | null; createdAt: string; updatedAt: string; error: string | null; resources: { ram: number; cpu: number; disk: number } };
export type StoredInviteSnapshot = { code: string; uses: number; inviterIdEncrypted: string | null };
export type RewardState = {
  config: DiscordRewardsConfig; plans: any[]; inviteSnapshots: Record<string, StoredInviteSnapshot[]>;
  events: ReferralEvent[]; claims: RewardClaim[]; diagnostics: Array<{ id: string; at: string; level: "info" | "warning" | "error"; code: string; message: string }>;
  oauthStates: Array<{ hash: string; userId: string; expiresAt: number }>;
  notifiedPlans: Record<string, string[]>;
  linkedAccounts: Record<string, { discordIdHash: string; discordIdEncrypted: string; discordIdSuffix: string; username: string; linkedAt: string }>;
};

export const defaultDiscordRewardsState = (): RewardState => ({
  config: { enabled: false, botTokenEncrypted: null, guildId: "", announcementChannelId: "", oauthClientId: "", oauthClientSecretEncrypted: null, oauthRedirectUri: "", minimumAccountAgeDays: 0, retainCreditOnLeave: true, dataRetentionDays: 365 },
  plans: [], inviteSnapshots: {}, events: [], claims: [], diagnostics: [], oauthStates: [], notifiedPlans: {}, linkedAccounts: {},
});
const file = "discord_rewards.json";
function normalizeConfig(stored: any) {
  const config = { ...(stored || {}) };
  delete config.welcomeChannelId;
  return { ...defaultDiscordRewardsState().config, ...config, retainCreditOnLeave: false };
}
const runtime: { status: string; message: string; connectedAt: string | null; lastSyncAt: string | null; guildName: string | null; client: Client | null; starting: boolean; stopped: boolean } = { status: "not_configured", message: "Configure the bot and guild to start invite tracking.", connectedAt: null, lastSyncAt: null, guildName: null, client: null, starting: false, stopped: false };
const guildLocks = new Map<string, Promise<void>>();
let workerGeneration = 0;
let verificationTimer: NodeJS.Timeout | null = null;
let retentionTimer: NodeJS.Timeout | null = null;

export async function reconcileReferralRewardServers(panelUserId: string, stop = stopContainer) {
  const state = await readRewardState();
  const actions: Array<{ claimId: string; targetId: string; suspend: boolean }> = [];
  for (const plan of state.plans) {
    const required = Math.max(1, Math.floor(Number(plan.requiredInvites) || 1));
    const activeInvites = state.events.filter((event) => event.inviterPanelUserId === panelUserId && event.guildId === state.config.guildId && event.status === "verified").length;
    const earned = Math.floor(activeInvites / required);
    const claims = state.claims.filter((claim) => claim.panelUserId === panelUserId && claim.planId === plan.id && (claim.status === "claimed" || claim.status === "suspended")).sort((a, b) => a.createdAt.localeCompare(b.createdAt));
    claims.forEach((claim, index) => {
      const suspend = index >= earned;
      if (claim.targetId && (suspend && claim.status === "claimed" || !suspend && claim.status === "suspended")) actions.push({ claimId: claim.id, targetId: claim.targetId, suspend });
    });
  }
  const byTarget = new Map<string, typeof actions>();
  for (const action of actions) byTarget.set(action.targetId, [...(byTarget.get(action.targetId) || []), action]);
  const affected: Array<{ action: { claimId: string; targetId: string; suspend: boolean }; server: any }> = [];
  await updateJSON<any[]>("servers.json", (current) => current.map((server: any) => {
    const targetActions = byTarget.get(String(server.id));
    if (!targetActions?.length) return server;
    const marker = server.discordRewardSuspension;
    const claimIds = new Set<string>(Array.isArray(marker?.claimIds) ? marker.claimIds : marker?.claimId ? [marker.claimId] : []);
    for (const action of targetActions) action.suspend ? claimIds.add(action.claimId) : claimIds.delete(action.claimId);
    if (claimIds.size) {
      const changed = targetActions.some((action) => action.suspend && !(Array.isArray(marker?.claimIds) ? marker.claimIds : marker?.claimId ? [marker.claimId] : []).includes(action.claimId));
      server.discordRewardSuspension = { claimId: [...claimIds].at(-1), claimIds: [...claimIds], suspendedAt: marker?.suspendedAt || new Date().toISOString(), priorSuspended: marker ? Boolean(marker.priorSuspended) : Boolean(server.suspended), stopPending: marker ? Boolean(marker.stopPending) : true, reason: "The inviter no longer has enough active Discord referrals for this reward." };
      if (changed) server.discordRewardSuspension.stopPending = marker?.stopPending || !marker;
      server.suspended = true;
      if (changed) affected.push({ action: targetActions.find((action) => action.suspend)!, server: { ...server } });
    } else if (marker) {
      server.suspended = Boolean(marker.priorSuspended);
      delete server.discordRewardSuspension;
      affected.push({ action: targetActions.find((action) => !action.suspend)!, server: { ...server } });
    }
    return server;
  }), []);
  const pendingStops = (await readJSON("servers.json") || []).filter((server: any) => server.discordRewardSuspension?.stopPending && server.containerId);
  for (const server of pendingStops) {
    try {
      await stop(server.containerId, server.nodeId);
      await updateJSON<any[]>("servers.json", (current) => current.map((row: any) => row.id === server.id && row.discordRewardSuspension?.claimId === server.discordRewardSuspension.claimId ? { ...row, discordRewardSuspension: { ...row.discordRewardSuspension, stopPending: false } } : row), []);
    } catch (error: any) {
      if (/not running|already stopped|is stopped/i.test(String(error?.message || ""))) {
        await updateJSON<any[]>("servers.json", (current) => current.map((row: any) => row.id === server.id && row.discordRewardSuspension?.claimId === server.discordRewardSuspension.claimId ? { ...row, discordRewardSuspension: { ...row.discordRewardSuspension, stopPending: false } } : row), []);
      } else await appendDiscordDiagnostic("error", "reward_server_stop_failed", `Reward server …${String(server.id).slice(-8)} was suspended but could not be stopped: ${String(error?.message || error).slice(0, 180)}`);
    }
  }
  await updateRewardState((current) => {
    for (const action of actions) {
      const claim = current.claims.find((candidate) => candidate.id === action.claimId);
      if (!claim) continue;
      if (action.suspend && claim.status === "claimed") { claim.status = "suspended"; claim.error = "Suspended because a credited Discord member left the rewards server. Invite a new eligible member to restore this reward."; claim.updatedAt = new Date().toISOString(); }
      else if (!action.suspend && claim.status === "suspended") { claim.status = "claimed"; claim.error = null; claim.updatedAt = new Date().toISOString(); }
    }
  });
  return { suspended: affected.filter((entry) => entry.action.suspend).length, restored: affected.filter((entry) => !entry.action.suspend).length };
}

export async function reconcileDepartedRewardMembers() {
  await updateRewardState((state) => {
    for (const event of state.events) if (event.leftAt && event.everCredited && event.status === "verified") { event.status = "revoked"; event.reason = "member_left_server_credit_revoked"; }
  });
  const state = await readRewardState();
  for (const userId of new Set(state.claims.map((claim) => claim.panelUserId))) await reconcileReferralRewardServers(userId);
}

export function hashDiscordId(id: string) {
  const key = process.env.NODE_ENCRYPTION_KEY;
  if (!key) throw new Error("NODE_ENCRYPTION_KEY is required for Discord identifier protection");
  return crypto.createHmac("sha256", key).update(id).digest("hex");
}

export function protectInviteSnapshots(invites: InviteSnapshot[]): StoredInviteSnapshot[] {
  return invites.map((invite) => ({ code: invite.code, uses: invite.uses, inviterIdEncrypted: invite.inviterId ? encryptSecret(invite.inviterId) : null }));
}

function revealInviteSnapshots(invites: StoredInviteSnapshot[]): InviteSnapshot[] {
  return invites.map((invite) => ({ code: invite.code, uses: invite.uses, inviterId: invite.inviterIdEncrypted ? decryptSecret(invite.inviterIdEncrypted) : null }));
}

export async function pruneDiscordRewardData() {
  await updateRewardState((state) => {
    const days = Math.min(3650, Math.max(30, Number(state.config.dataRetentionDays) || 365));
    const cutoff = Date.now() - days * 86_400_000;
    state.events = state.events.filter((event) => {
      if (Date.parse(event.joinedAt) >= cutoff) return true;
      if (event.everCredited || event.status === "revoked") {
        event.memberIdEncrypted = ""; event.memberIdSuffix = ""; event.inviterIdEncrypted = null; event.inviterIdSuffix = null; event.inviteCode = null;
        event.reason = "historical_credit_anonymized_by_retention_policy";
        return true;
      }
      return false;
    });
    state.oauthStates = state.oauthStates.filter((entry) => entry.expiresAt > Date.now());
  });
}

export const readRewardState = async (): Promise<RewardState> => {
  const state = await readJSON(file);
  return { ...defaultDiscordRewardsState(), ...(state || {}), config: normalizeConfig(state?.config), plans: state?.plans || [], events: state?.events || [], claims: state?.claims || [], diagnostics: state?.diagnostics || [], oauthStates: state?.oauthStates || [], inviteSnapshots: state?.inviteSnapshots || {}, notifiedPlans: state?.notifiedPlans || {}, linkedAccounts: state?.linkedAccounts || {} };
};
export const updateRewardState = <T>(updater: (state: RewardState) => T | Promise<T>) => updateJSON<RewardState>(file, async (state) => {
  const merged = { ...defaultDiscordRewardsState(), ...(state || {}), config: normalizeConfig(state?.config), linkedAccounts: state?.linkedAccounts || {} } as RewardState;
  const next = await updater(merged);
  return (next && typeof next === "object" && "config" in (next as any) ? next : merged) as RewardState;
}, defaultDiscordRewardsState());

export async function appendDiscordDiagnostic(level: "info" | "warning" | "error", code: string, message: string) {
  const safeMessage = String(message).replace(/(?:Bot\s+)?[A-Za-z\d._-]{20,}\.[A-Za-z\d._-]{5,}\.[A-Za-z\d._-]{20,}/g, "[redacted]").slice(0, 300);
  await updateRewardState((state) => { state.diagnostics.push({ id: crypto.randomUUID(), at: new Date().toISOString(), level, code: code.slice(0, 80), message: safeMessage }); state.diagnostics = state.diagnostics.slice(-2000); });
}
const toSnapshot = (invite: Invite): InviteSnapshot => ({ code: invite.code, uses: Number(invite.uses || 0), inviterId: invite.inviterId || null });
const serializeGuild = async (guild: Guild) => guild.invites.fetch().then((invites) => [...invites.values()].map(toSnapshot));
const byGuild = async <T>(guildId: string, work: () => Promise<T>): Promise<T> => {
  const previous = guildLocks.get(guildId) || Promise.resolve();
  let release!: () => void;
  const current = new Promise<void>((resolve) => { release = resolve; });
  const tail = previous.then(() => current);
  guildLocks.set(guildId, tail);
  await previous.catch(() => undefined);
  try { return await work(); } finally { release(); if (guildLocks.get(guildId) === tail) guildLocks.delete(guildId); }
};

async function trackJoin(member: GuildMember) {
  const guildId = member.guild.id;
  await byGuild(guildId, async () => {
    const state = await readRewardState();
    if (guildId !== state.config.guildId) return;
    const before = revealInviteSnapshots(state.inviteSnapshots[guildId] || []);
    let after: InviteSnapshot[];
    try { after = await serializeGuild(member.guild); }
    catch (error: any) {
      await updateRewardState((current) => { current.inviteSnapshots[guildId] = []; });
      await appendDiscordDiagnostic("warning", "invite_fetch_failed", "Could not fetch current guild invites; this join was recorded as unverified.");
      after = [];
    }
    const attribution = attributeInvite(before, after);
    const joinedAt = member.joinedAt?.toISOString() || new Date().toISOString();
    const memberIdHash = hashDiscordId(member.id);
    const inviterIdHash = attribution.inviterId ? hashDiscordId(attribution.inviterId) : null;
    const id = crypto.createHash("sha256").update(`${guildId}:${memberIdHash}:${joinedAt}`).digest("hex");
    const inviter = inviterIdHash ? Object.entries(state.linkedAccounts).find(([, account]) => account.discordIdHash === inviterIdHash)?.[0] : null;
    const accountCreatedAt = member.user.createdAt?.toISOString() || null;
    const createdTime = accountCreatedAt ? Date.parse(accountCreatedAt) : NaN;
    const ageDays = Number.isFinite(createdTime) ? (Date.now() - createdTime) / 86_400_000 : -1;
    const ageAllowed = ageDays >= Number(state.config.minimumAccountAgeDays || 0);
    let status: ReferralEvent["status"] = "unverified";
    let reason = attribution.reason;
    if (member.user.bot) reason = "bot_join_not_eligible";
    else if (attribution.verified && !inviter) reason = "inviter_has_not_linked_panel_account";
    else if (attribution.verified && hasSuccessfulCreditForMember(state.events, memberIdHash)) reason = "member_already_credited";
    else if (attribution.verified && ageAllowed) { status = "verified"; reason = "unique_member_join_attributed"; }
    else if (attribution.verified && !ageAllowed) { status = "pending"; reason = "minimum_account_age_not_met"; }
    const event: ReferralEvent = { id, guildId, memberIdHash, memberIdEncrypted: (await import("./security.js")).encryptSecret(member.id), memberIdSuffix: member.id.slice(-4), inviterIdHash, inviterIdEncrypted: attribution.inviterId ? (await import("./security.js")).encryptSecret(attribution.inviterId) : null, inviterIdSuffix: attribution.inviterId?.slice(-4) || null, inviterPanelUserId: inviter || null, inviteCode: attribution.inviteCode, status, reason, joinedAt, leftAt: null, accountCreatedAt, everCredited: status === "verified" };
    await updateRewardState((current) => {
      current.inviteSnapshots[guildId] = protectInviteSnapshots(after);
      if (!current.events.some((existing) => existing.id === id)) current.events.push(event);
    });
    if (status === "unverified") await appendDiscordDiagnostic("warning", "join_unverified", `Join …${member.id.slice(-4)} was not credited: ${reason}.`);
    else if (status === "verified" && inviter) { await reconcileReferralRewardServers(inviter); await notifyEligible(inviter, member.guild); }
  });
}

export async function handleTrackedMemberLeave(member: Pick<GuildMember, "id" | "guild">, stop = stopContainer) {
  let affectedInviter: string | null = null;
  await byGuild(member.guild.id, async () => {
    const state = await readRewardState();
    if (member.guild.id !== state.config.guildId) return;
    await updateRewardState((current) => {
      const memberHash = hashDiscordId(member.id);
      const matches = current.events.filter((event) => event.memberIdHash === memberHash && event.guildId === member.guild.id && !event.leftAt).sort((a, b) => b.joinedAt.localeCompare(a.joinedAt));
      const event = matches[0];
      if (!event) return;
      event.leftAt = new Date().toISOString();
      if (event.status === "verified" || event.status === "pending") { event.status = "revoked"; event.reason = "member_left_server_credit_revoked"; }
      affectedInviter = event.inviterPanelUserId;
    });
  });
  if (affectedInviter) await reconcileReferralRewardServers(affectedInviter, stop);
}

async function notifyEligible(panelUserId: string, guild: Guild) {
  const state = await readRewardState();
  const discordIdEncrypted = state.linkedAccounts[panelUserId]?.discordIdEncrypted;
  if (!discordIdEncrypted) return;
  const discordId = decryptSecret(discordIdEncrypted);
  const verified = state.events.filter((event) => event.guildId === guild.id && event.inviterPanelUserId === panelUserId && event.status === "verified").length;
  for (const plan of state.plans.filter((candidate: any) => candidate.enabled && verified >= candidate.requiredInvites)) {
    const already = (state.notifiedPlans[panelUserId] || []).includes(plan.id);
    if (already) continue;
    await updateRewardState((current) => { const ids = current.notifiedPlans[panelUserId] || []; if (!ids.includes(plan.id)) current.notifiedPlans[panelUserId] = [...ids, plan.id]; });
    const message = `You unlocked “${String(plan.name).slice(0, 64)}” in SNCK Panel. Open the Free Service → Discord Rewards page to review your options.`;
    try { const discordUser = await guild.client.users.fetch(discordId); await discordUser.send(message); }
    catch { await appendDiscordDiagnostic("info", "reward_dm_unavailable", "A reward-unlocked DM could not be delivered; the panel still shows the reward."); }
    const current = await readRewardState();
    if (current.config.announcementChannelId) {
      try { const channel = await guild.channels.fetch(current.config.announcementChannelId); if (channel?.isTextBased() && "send" in channel) await channel.send({ content: `A member unlocked an invite reward: **${String(plan.name).slice(0, 64)}**.`, allowedMentions: { parse: [] } }); }
      catch { await appendDiscordDiagnostic("warning", "announcement_send_failed", "Could not post to the configured reward announcement channel."); }
    }
  }
}

async function verifyPendingEvents() {
  const state = await readRewardState();
  if (!runtime.client || !state.config.guildId || Number(state.config.minimumAccountAgeDays) < 1) return;
  let guild = runtime.client.guilds.cache.get(state.config.guildId);
  if (!guild) return;
  const pending = state.events.filter((event) => event.status === "pending" && !event.leftAt && event.inviterPanelUserId);
  for (const event of pending) {
    try {
      const member = await guild.members.fetch(decryptSecret(event.memberIdEncrypted));
      const ageDays = (Date.now() - member.user.createdTimestamp) / 86_400_000;
      if (ageDays < Number(state.config.minimumAccountAgeDays)) continue;
      await updateRewardState((current) => {
        const target = current.events.find((row) => row.id === event.id);
        if (target?.status === "pending" && !target.leftAt && !hasSuccessfulCreditForMember(current.events.filter((row) => row.id !== event.id), target.memberIdHash)) { target.status = "verified"; target.reason = "minimum_account_age_satisfied"; target.everCredited = true; }
      });
      if (event.inviterPanelUserId) { await reconcileReferralRewardServers(event.inviterPanelUserId); await notifyEligible(event.inviterPanelUserId, guild); }
    } catch { /* member left or transient Discord failure; leave pending until next sync */ }
  }
}

async function syncInvites(client: Client, guildId: string) {
  const guild = await client.guilds.fetch(guildId);
  const invites = await serializeGuild(guild);
  await updateRewardState((state) => { state.inviteSnapshots[guildId] = protectInviteSnapshots(invites); });
  runtime.lastSyncAt = new Date().toISOString(); runtime.guildName = guild.name;
}

export async function stopDiscordRewards() {
  workerGeneration += 1;
  runtime.starting = false;
  if (verificationTimer) { clearInterval(verificationTimer); verificationTimer = null; }
  const client = runtime.client;
  runtime.client = null;
  runtime.status = "not_configured";
  runtime.message = "Invite bot is disabled.";
  if (client) { try { client.removeAllListeners(); await client.destroy(); } catch { /* no-op during shutdown */ } }
}

export async function shutdownDiscordRewards() {
  runtime.stopped = true;
  if (retentionTimer) { clearInterval(retentionTimer); retentionTimer = null; }
  await stopDiscordRewards();
}

export async function startDiscordRewards() {
  if (runtime.starting || runtime.stopped) return;
  const state = await readRewardState();
  if (!retentionTimer) {
    void pruneDiscordRewardData().catch(() => appendDiscordDiagnostic("warning", "retention_prune_failed", "Scheduled Discord data-retention cleanup failed."));
    retentionTimer = setInterval(() => { void pruneDiscordRewardData().catch(() => appendDiscordDiagnostic("warning", "retention_prune_failed", "Scheduled Discord data-retention cleanup failed.")); }, 24 * 60 * 60 * 1000);
    retentionTimer.unref();
  }
  if (!state.config.enabled || !state.config.botTokenEncrypted || !state.config.guildId) {
    runtime.status = state.config.enabled ? "invalid_configuration" : "not_configured";
    runtime.message = state.config.enabled ? "Bot token and target guild are required." : "Invite bot is disabled.";
    return;
  }
  runtime.starting = true;
  const generation = ++workerGeneration;
  if (runtime.client) { const old = runtime.client; runtime.client = null; old.removeAllListeners(); old.destroy(); }
  runtime.status = "connecting"; runtime.message = "Connecting to Discord…";
  let client: Client;
  try {
    const token = decryptSecret(state.config.botTokenEncrypted);
    client = new Client({ intents: [GatewayIntentBits.Guilds, GatewayIntentBits.GuildMembers, GatewayIntentBits.GuildInvites] });
  } catch {
    runtime.status = "invalid_configuration"; runtime.message = "Saved bot credentials cannot be decrypted. Re-enter the bot token and verify NODE_ENCRYPTION_KEY."; runtime.starting = false;
    await appendDiscordDiagnostic("error", "bot_secret_decryption_failed", runtime.message); return;
  }
  runtime.client = client;
  client.once(Events.ClientReady, async (readyClient) => {
    if (generation !== workerGeneration || runtime.client !== client) return;
    runtime.status = "connected"; runtime.connectedAt = new Date().toISOString(); runtime.message = "Gateway connected; syncing target guild invites.";
    try { await syncInvites(readyClient, state.config.guildId); await reconcileDepartedRewardMembers(); runtime.message = `Connected to ${runtime.guildName}; invite tracking is operational.`; await appendDiscordDiagnostic("info", "bot_connected", "Discord gateway connected, invite snapshot refreshed, and reward access reconciled."); }
    catch { runtime.status = "degraded"; runtime.message = "Bot connected, but guild/invite access failed. Check guild ID, Manage Server permission, and privileged intents."; await appendDiscordDiagnostic("error", "guild_sync_failed", runtime.message); }
    runtime.starting = false;
  });
  client.on(Events.GuildMemberAdd, (member) => { if (generation === workerGeneration) void trackJoin(member).catch(() => appendDiscordDiagnostic("error", "join_handler_failed", "Member join could not be safely processed; check bot permissions and diagnostics.")); });
  client.on(Events.GuildMemberRemove, (member) => { if (generation === workerGeneration) void handleTrackedMemberLeave(member).catch(() => appendDiscordDiagnostic("error", "leave_handler_failed", "Member departure could not be recorded.")); });
  client.on(Events.InviteCreate, (invite) => { if (generation === workerGeneration && invite.guild?.id === state.config.guildId) void syncInvites(client, state.config.guildId).catch(() => appendDiscordDiagnostic("warning", "invite_sync_failed", "Invite cache refresh failed after invite creation.")); });
  client.on(Events.InviteDelete, (invite) => { if (generation === workerGeneration && invite.guild?.id === state.config.guildId) void syncInvites(client, state.config.guildId).catch(() => appendDiscordDiagnostic("warning", "invite_sync_failed", "Invite cache refresh failed after invite deletion.")); });
  client.on(Events.Error, () => { if (generation === workerGeneration) { runtime.status = "degraded"; runtime.message = "Discord connection error; the library will attempt to reconnect."; void appendDiscordDiagnostic("warning", "gateway_error", runtime.message); } });
  if (verificationTimer) clearInterval(verificationTimer);
  verificationTimer = setInterval(() => { if (generation === workerGeneration) { void verifyPendingEvents(); void reconcileDepartedRewardMembers().catch(() => appendDiscordDiagnostic("error", "reward_access_reconcile_failed", "Could not reconcile reward-server suspensions; the next scheduled reconciliation will retry.")); } }, 15 * 60 * 1000);
  verificationTimer.unref();
  try { await client.login(decryptSecret(state.config.botTokenEncrypted)); }
  catch {
    if (generation !== workerGeneration || runtime.client !== client) { client.destroy(); return; }
    runtime.status = "disconnected"; runtime.message = "Discord login failed. Re-enter the token and confirm the bot is enabled."; runtime.starting = false; await appendDiscordDiagnostic("error", "bot_login_failed", runtime.message);
    if (runtime.client === client) { runtime.client = null; client.destroy(); }
  }
}

export function getDiscordRuntime() {
  const { status, message, connectedAt, lastSyncAt, guildName } = runtime;
  return { status, message, connectedAt, lastSyncAt, guildName, tracking: status === "connected", worker: "in-process-singleton" };
}
export function getDiscordClient() { return runtime.client; }
export function oauthStateHash(state: string) { return crypto.createHash("sha256").update(state).digest("hex"); }
export function createOAuthState() { return crypto.randomBytes(32).toString("base64url"); }
