import express from "express";
import crypto from "node:crypto";
import os from "node:os";
import fs from "node:fs";
import path from "node:path";
import { Client, GatewayIntentBits, PermissionFlagsBits, ChannelType } from "discord.js";
import type { Request, Response } from "express";
import { requireAuth, requireAdmin } from "../middleware/auth.js";
import { readJSON } from "../services/db.js";
import { audit, encryptSecret, decryptSecret, rateLimit } from "../services/security.js";
import { createServer, updateResources } from "../controllers/servers.js";
import { checkFreeCapacity, isEligibleFreeNode, isSnowflake, rewardProgress, validateOwnedServerUpgrade, validateRewardPlan } from "../services/discordRewardsCore.js";
import { nodeControl } from "../services/nodeClient.js";
import { nodeConnection } from "../services/nodeEndpoint.js";
import { parseWholeNumber } from "../../shared/discordRewardsConfig.js";
import { appendDiscordDiagnostic, createOAuthState, getDiscordClient, getDiscordRuntime, hashDiscordId, oauthStateHash, protectInviteSnapshots, pruneDiscordRewardData, readRewardState, startDiscordRewards, updateRewardState, type RewardClaim, type RewardState } from "../services/discordRewards.js";

const router = express.Router();
const safePublicMessage = (error: unknown) => String((error as any)?.message || "Discord request failed").replace(/(?:Bot\s+)?[A-Za-z\d._-]{20,}\.[A-Za-z\d._-]{5,}\.[A-Za-z\d._-]{20,}/g, "[redacted]").replace(/\s+/g, " ").slice(0, 220);
const requireSnowflake = (value: unknown, optional = false) => optional && value === "" ? true : isSnowflake(value);
const panelCallback = (req: Request, redirectUri: string) => redirectUri || `${String(process.env.PANEL_PUBLIC_URL || process.env.PANEL_URL || `${req.protocol}://${req.get("host")}`).replace(/\/$/, "")}/api/discord-rewards/oauth/callback`;
const configForClient = (state: RewardState) => ({ enabled: state.config.enabled, configured: Boolean(state.config.botTokenEncrypted && state.config.guildId), hasBotToken: Boolean(state.config.botTokenEncrypted), guildId: state.config.guildId, announcementChannelId: state.config.announcementChannelId, oauthClientId: state.config.oauthClientId, hasOAuthClientSecret: Boolean(state.config.oauthClientSecretEncrypted), oauthRedirectUri: state.config.oauthRedirectUri, minimumAccountAgeDays: state.config.minimumAccountAgeDays, retainCreditOnLeave: state.config.retainCreditOnLeave, dataRetentionDays: state.config.dataRetentionDays });
const rateSensitive = rateLimit(10, 60_000);
const provisionLocks = new Map<string, Promise<void>>();

async function withProvisionLock<T>(key: string, work: () => Promise<T>): Promise<T> {
  const previous = provisionLocks.get(key) || Promise.resolve();
  let release!: () => void;
  const current = new Promise<void>((resolve) => { release = resolve; });
  const tail = previous.then(() => current);
  provisionLocks.set(key, tail);
  await previous.catch(() => undefined);
  try { return await work(); }
  finally { release(); if (provisionLocks.get(key) === tail) provisionLocks.delete(key); }
}

function localCapacityStats(node: any) {
  const candidates = [String(node.serverDirectory || ""), path.resolve(process.cwd(), ".data/servers")].filter(Boolean);
  for (const directory of candidates) {
    try { if (fs.statSync(directory).isDirectory()) { const stat = fs.statfsSync(directory); return { memory: { free: os.freemem() }, disk: { free: stat.bavail * stat.bsize } }; } }
    catch { /* try the panel's local server-data directory */ }
  }
  return { memory: { free: os.freemem() }, disk: { free: NaN } };
}

async function oauthCallback(req: Request, res: Response) {
  const stateValue = String(req.query.state || "");
  if (!stateValue || stateValue.length > 200) return res.redirect("/free-service/discord?discordLink=failed");
  let matchedUserId: string | null = null;
  const hash = oauthStateHash(stateValue);
  await updateRewardState((state) => {
    const now = Date.now();
    const record = state.oauthStates.find((candidate) => candidate.hash === hash && candidate.expiresAt > now);
    state.oauthStates = state.oauthStates.filter((candidate) => candidate.hash !== hash && candidate.expiresAt > now);
    if (record) matchedUserId = record.userId;
  });
  if (!matchedUserId || !req.query.code) return res.redirect("/free-service/discord?discordLink=failed");
  try {
    const config = (await readRewardState()).config;
    if (!config.oauthClientId || !config.oauthClientSecretEncrypted) throw new Error("Discord account linking is not configured");
    const callback = panelCallback(req, config.oauthRedirectUri);
    const tokenResponse = await fetch("https://discord.com/api/oauth2/token", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({ client_id: config.oauthClientId, client_secret: decryptSecret(config.oauthClientSecretEncrypted), grant_type: "authorization_code", code: String(req.query.code), redirect_uri: callback }), signal: AbortSignal.timeout(12_000) });
    if (!tokenResponse.ok) throw new Error("Discord OAuth exchange failed");
    const tokenData: any = await tokenResponse.json();
    if (typeof tokenData.access_token !== "string") throw new Error("Discord OAuth did not return an access token");
    const identityResponse = await fetch("https://discord.com/api/v10/users/@me", { headers: { Authorization: `Bearer ${tokenData.access_token}` }, signal: AbortSignal.timeout(12_000) });
    if (!identityResponse.ok) throw new Error("Discord identity lookup failed");
    const identity: any = await identityResponse.json();
    if (!isSnowflake(identity.id)) throw new Error("Discord returned an invalid identity");
    const users: any[] = await readJSON("users.json") || [];
    if (!users.some((user) => user.id === matchedUserId)) throw new Error("Panel account no longer exists");
    let linkConflict = false;
    const { encryptSecret } = await import("../services/security.js");
    const identityHash = hashDiscordId(identity.id);
    await updateRewardState((current) => {
      if (Object.entries(current.linkedAccounts).some(([userId, account]) => userId !== matchedUserId && account.discordIdHash === identityHash)) { linkConflict = true; return; }
      const username = String(identity.global_name || identity.username || "Discord member").slice(0, 80);
      current.linkedAccounts[matchedUserId!] = { discordIdHash: identityHash, discordIdEncrypted: encryptSecret(identity.id), discordIdSuffix: identity.id.slice(-4), username, linkedAt: new Date().toISOString() };
      for (const event of current.events.filter((row) => row.inviterIdHash === identityHash && !row.inviterPanelUserId && row.status === "unverified" && row.reason === "inviter_has_not_linked_panel_account")) {
        if (event.leftAt && !current.config.retainCreditOnLeave) continue;
        const ageDays = event.accountCreatedAt ? (Date.now() - Date.parse(event.accountCreatedAt)) / 86_400_000 : -1;
        event.inviterPanelUserId = matchedUserId!;
        if (ageDays >= Number(current.config.minimumAccountAgeDays || 0) && !current.events.some((other) => other.id !== event.id && other.memberIdHash === event.memberIdHash && other.everCredited)) { event.status = "verified"; event.reason = "inviter_linked_after_join"; event.everCredited = true; }
        else if (ageDays < Number(current.config.minimumAccountAgeDays || 0)) { event.status = "pending"; event.reason = "minimum_account_age_not_met"; }
      }
    });
    if (linkConflict) throw new Error("This Discord account is already linked to another panel user");
    await audit("discord.account_linked", { user: { id: matchedUserId }, ip: req.ip }, { discordUserIdSuffix: identity.id.slice(-4) });
    return res.redirect("/free-service/discord?discordLink=success");
  } catch (error) {
    await appendDiscordDiagnostic("warning", "oauth_link_failed", safePublicMessage(error));
    return res.redirect("/free-service/discord?discordLink=failed");
  }
}
router.get("/oauth/callback", rateSensitive, oauthCallback);
router.use(requireAuth);

router.get("/me", async (req, res) => {
  const user = (req as any).user;
  const state = await readRewardState();
  const profile = state.linkedAccounts[user.id];
  const linked = Boolean(profile?.discordIdEncrypted);
  const verified = state.events.filter((event) => event.inviterPanelUserId === user.id && event.status === "verified").length;
  const pending = state.events.filter((event) => event.inviterPanelUserId === user.id && event.status === "pending").length;
  const plans = state.plans.filter((plan: any) => plan.enabled).sort((a: any, b: any) => a.sortOrder - b.sortOrder).map((plan: any) => {
    const claims = state.claims.filter((claim) => claim.panelUserId === user.id && claim.planId === plan.id);
    const success = claims.filter((claim) => claim.status === "claimed").length;
    const progress = rewardProgress(verified, plan.requiredInvites);
    let unavailableReason: string | null = null;
    if (!linked) unavailableReason = "Link your Discord account first.";
    else if (success >= plan.maxClaimsPerUser) unavailableReason = "You have reached the claim limit for this plan.";
    else if (plan.maxTotalClaims !== null && state.claims.filter((claim) => claim.planId === plan.id && claim.status === "claimed").length >= plan.maxTotalClaims) unavailableReason = "This plan has no claims remaining.";
    else if (verified < plan.requiredInvites) unavailableReason = `Need ${progress.remaining} more verified invite(s).`;
    return { ...plan, progress, available: !unavailableReason, unavailableReason, claims: claims.map((claim) => ({ id: claim.id, mode: claim.mode, status: claim.status, targetId: claim.targetId, createdAt: claim.createdAt, error: claim.error })) };
  });
  res.json({ linked, discordUsername: profile?.username || null, discordIdMasked: profile?.discordIdSuffix ? `…${profile.discordIdSuffix}` : null, verifiedInvites: verified, pendingInvites: pending, plans, claims: state.claims.filter((claim) => claim.panelUserId === user.id).sort((a, b) => b.createdAt.localeCompare(a.createdAt)).slice(0, 50).map(({ id, planId, mode, status, targetId, createdAt, error }) => ({ id, planId, mode, status, targetId, createdAt, error })) });
});

router.delete("/account/link", async (req, res) => {
  const userId = String((req as any).user.id);
  let found = false;
  await updateRewardState((state) => {
    found = Boolean(state.linkedAccounts[userId]);
    delete state.linkedAccounts[userId];
    for (const event of state.events.filter((row) => row.inviterPanelUserId === userId)) { event.inviterIdEncrypted = null; event.inviterIdSuffix = null; }
  });
  if (found) await audit("discord.account_unlinked", req, { linkedReferralsPreserved: true });
  return res.json({ success: true, linked: false, historicalCreditsPreserved: true });
});

router.get("/oauth/start", rateSensitive, async (req, res) => {
  const state = await readRewardState();
  const config = state.config;
  if (!config.oauthClientId || !config.oauthClientSecretEncrypted) return res.status(409).json({ error: "Discord account linking is not configured yet. Ask a panel administrator." });
  const redirectUri = panelCallback(req, config.oauthRedirectUri);
  const value = createOAuthState();
  await updateRewardState((current) => { current.oauthStates = current.oauthStates.filter((entry) => entry.expiresAt > Date.now()).slice(-200); current.oauthStates.push({ hash: oauthStateHash(value), userId: (req as any).user.id, expiresAt: Date.now() + 10 * 60_000 }); });
  const url = new URL("https://discord.com/oauth2/authorize");
  url.search = new URLSearchParams({ client_id: config.oauthClientId, redirect_uri: redirectUri, response_type: "code", scope: "identify", state: value }).toString();
  res.json({ url: url.toString() });
});

router.get("/claim-options", async (req, res) => {
  const planId = String(req.query.planId || "");
  const state = await readRewardState();
  const plan = state.plans.find((candidate: any) => candidate.id === planId && candidate.enabled);
  if (!plan) return res.status(404).json({ error: "Reward plan not found" });
  const userId = (req as any).user.id;
  const linkedAccount = state.linkedAccounts[userId];
  if (!linkedAccount?.discordIdEncrypted) return res.status(409).json({ error: "Link your Discord account before claiming rewards." });
  const discordClient = getDiscordClient();
  if (!discordClient || !discordClient.isReady()) return res.status(503).json({ error: "Discord invite tracking is currently unavailable. Your invite credits are safe; try again when the bot is connected." });
  let member;
  try { const guild = await discordClient.guilds.fetch(state.config.guildId); member = await guild.members.fetch(decryptSecret(linkedAccount.discordIdEncrypted)); }
  catch { return res.status(403).json({ error: "Your linked Discord account must be a member of the configured rewards server." }); }
  const servers: any[] = await readJSON("servers.json") || [];
  const ownedServers = servers.filter((server) => server.owner === userId).map((server) => ({ id: server.id, name: server.name, ram: Number(server.ram), cpu: Number(server.cpu), disk: Number(server.disk), nodeId: String(server.nodeId || "local"), status: server.status }));
  const nodeRows: any[] = await readJSON("nodes.json") || [];
  const nodes = nodeRows.filter((node) => node.visibility !== "private");
  const allocations: any[] = await readJSON("allocations.json") || [];
  const availableAllocations = allocations.filter((allocation) => !allocation.assignedServerId);
  const publicNodes = nodes.map((node) => {
    const eligibility = isEligibleFreeNode(node);
    const allowedByPlan = !plan.allowedNodeIds.length || plan.allowedNodeIds.includes(String(node.id));
    const capacity = checkFreeCapacity(node, servers, { ram: Number(plan.ram), cpu: Number(plan.cpu), disk: Number(plan.disk) }, node.isLocal ? localCapacityStats(node) : node.lastStats);
    const freeAllocations = availableAllocations.filter((allocation) => String(allocation.nodeId) === String(node.id));
    const available = eligibility.eligible && allowedByPlan && capacity.available && freeAllocations.length > 0;
    const reason = !eligibility.eligible ? eligibility.reason : !allowedByPlan ? "This node is not included in this reward plan." : !capacity.available ? capacity.reason : !freeAllocations.length ? "No unassigned allocation is available on this node." : "Available.";
    return { id: String(node.id), name: String(node.name || "Node"), status: eligibility.eligible ? "ONLINE" : node.maintenance ? "MAINTENANCE" : node.disabled ? "DISABLED" : "UNAVAILABLE", available, reason, allocations: available ? freeAllocations.map((a) => ({ id: a.id, label: `${a.ip || a.ipAddress || node.publicIp || node.hostname || node.name}:${a.portStart}–${a.portEnd}` })) : [] };
  });
  return res.json({ modes: { create: Boolean(plan.allowCreate), upgrade: Boolean(plan.allowUpgrade) }, servers: plan.allowUpgrade ? ownedServers : [], nodes: plan.allowCreate ? publicNodes : [], linkedDiscordMember: Boolean(member) });
});

function callController(handler: Function, req: Request, params: Record<string, string>, body: any): Promise<{ status: number; data: any }> {
  return new Promise((resolve, reject) => {
    let status = 200;
    const fakeRequest = { user: (req as any).user, params, body, ip: req.ip, headers: req.headers } as any;
    const fakeResponse = { status(code: number) { status = code; return this; }, json(data: any) { resolve({ status, data }); return this; } } as any;
    Promise.resolve(handler(fakeRequest, fakeResponse)).catch(reject);
  });
}

router.post("/claims", rateLimit(5, 60_000), async (req, res) => {
  const userId = String((req as any).user.id);
  const planId = String(req.body?.planId || "");
  const mode = req.body?.mode;
  const idem = String(req.body?.idempotencyKey || "");
  if (!/^[A-Za-z0-9_-]{12,100}$/.test(idem)) return res.status(400).json({ error: "A valid idempotency key is required." });
  const reservation: { claim: RewardClaim | null; error: string | null; status: number } = { claim: null, error: null, status: 400 };
  await updateRewardState((state) => {
    const prior = state.claims.find((row) => row.panelUserId === userId && row.idempotencyKey === idem);
    if (prior) { reservation.claim = prior; return; }
    const plan = state.plans.find((candidate: any) => candidate.id === planId && candidate.enabled);
    if (!plan) { reservation.error = "Reward plan is unavailable."; reservation.status = 404; return; }
    if (mode !== "create" && mode !== "upgrade") { reservation.error = "Select a supported claim option."; return; }
    if (mode === "create" && !plan.allowCreate || mode === "upgrade" && !plan.allowUpgrade) { reservation.error = "This reward plan does not allow that claim option."; reservation.status = 403; return; }
    const verified = state.events.filter((event) => event.inviterPanelUserId === userId && event.status === "verified").length;
    const priorClaims = state.claims.filter((row) => row.panelUserId === userId && row.planId === planId);
    const successful = priorClaims.filter((row) => row.status === "claimed").length;
    const total = state.claims.filter((row) => row.planId === planId && ["claimed", "processing", "needs_review"].includes(row.status)).length;
    if (priorClaims.some((row) => row.status === "processing" || row.status === "needs_review")) { reservation.error = "A previous redemption is still being reconciled. Contact an administrator; do not submit another claim."; reservation.status = 409; return; }
    if (successful >= plan.maxClaimsPerUser || plan.maxTotalClaims !== null && total >= plan.maxTotalClaims) { reservation.error = "The claim limit for this reward has been reached."; reservation.status = 409; return; }
    if (verified < plan.requiredInvites) { reservation.error = "Not enough verified invite credits for this reward."; reservation.status = 403; return; }
    const id = crypto.randomUUID();
    reservation.claim = { id, idempotencyKey: idem, panelUserId: userId, planId, mode, status: "processing", targetId: null, createdAt: new Date().toISOString(), updatedAt: new Date().toISOString(), error: null, resources: { ram: Number(plan.ram), cpu: Number(plan.cpu), disk: Number(plan.disk) } };
    state.claims.push(reservation.claim);
  });
  if (reservation.error) return res.status(reservation.status).json({ error: reservation.error });
  const claim = reservation.claim as RewardClaim | null;
  if (!claim) return res.status(500).json({ error: "Could not reserve the reward claim." });
  if (claim.status === "claimed") return res.json({ success: true, claim });
  if (claim.status !== "processing") return res.status(409).json({ error: "This request is already being processed or requires administrator review.", claimId: claim.id });

  const user = (req as any).user;
  const state = await readRewardState();
  const plan = state.plans.find((candidate: any) => candidate.id === claim!.planId);
    const settings: any = await readJSON("settings.json") || {};
    const free = settings.freeService || {};
    let provisionResult: { status: number; data: any } | null = null;
  let targetId: string | null = null;
  try {
    if (!free.enabled) throw new Error("Free Service is disabled. Ask the panel owner to enable it before reward redemption.");
    const ceilings = { ram: Number(free.ram), cpu: Number(free.cpu), disk: Number(free.disk) };
    if (Object.values(ceilings).some((value) => !Number.isFinite(value) || value <= 0)) throw new Error("The panel Free Service RAM/CPU/disk ceilings are missing or invalid; an administrator must configure them before rewards can be redeemed.");
    if (claim.resources.ram > ceilings.ram || claim.resources.cpu > ceilings.cpu || claim.resources.disk > ceilings.disk) throw new Error("Reward resources exceed the current Free Service limits. Ask an administrator to adjust the plan.");
    if (mode === "create") {
      const maxServers = Math.max(1, Number(free.maxServers) || 1);
      const ownedCount = (await readJSON("servers.json") || []).filter((server: any) => server.owner === userId).length;
      if (ownedCount >= maxServers) throw new Error(`Free Service allows at most ${maxServers} server(s) per user. Delete an existing server or change the configured limit before claiming.`);
      const defaultDuration = Math.max(0, Number(free.durationHours) || 0);
      if (Number(plan.durationHours) > 0 && defaultDuration > 0 && Number(plan.durationHours) > defaultDuration) throw new Error("Reward duration exceeds the current Free Service duration limit.");
    }
    const profile = state.linkedAccounts[userId];
    const discordClient = getDiscordClient();
    if (!profile?.discordIdEncrypted || !discordClient?.isReady()) throw new Error("Discord account link or bot connection is unavailable.");
    const guild = await discordClient.guilds.fetch(state.config.guildId);
    if (!profile?.discordIdEncrypted) throw new Error("Link your Discord account before claiming rewards.");
    await guild.members.fetch(decryptSecret(profile.discordIdEncrypted));
    if (mode === "create") {
      const nodeId = String(req.body?.nodeId || "");
      const allocationId = String(req.body?.allocationId || "");
      await withProvisionLock(`user:${userId}`, async () => withProvisionLock(`node:${nodeId}`, async () => {
        const latestSettings: any = await readJSON("settings.json") || {};
        const latestFree = latestSettings.freeService || {};
        if (!latestFree.enabled) throw new Error("Free Service was disabled before provisioning.");
        const latestCeilings = { ram: Number(latestFree.ram), cpu: Number(latestFree.cpu), disk: Number(latestFree.disk) };
        if (Object.values(latestCeilings).some((value) => !Number.isFinite(value) || value <= 0) || claim.resources.ram > latestCeilings.ram || claim.resources.cpu > latestCeilings.cpu || claim.resources.disk > latestCeilings.disk) throw new Error("Reward exceeds the current Free Service resource ceilings.");
        const maxServers = Math.max(1, Number(latestFree.maxServers) || 1);
        const latestServers: any[] = await readJSON("servers.json") || [];
        if (latestServers.filter((server) => server.owner === userId).length >= maxServers) throw new Error(`Free Service allows at most ${maxServers} server(s) per user.`);
        const maxDuration = Math.max(0, Number(latestFree.durationHours) || 0);
        if (Number(plan.durationHours) > 0 && maxDuration > 0 && Number(plan.durationHours) > maxDuration) throw new Error("Reward duration exceeds the current Free Service duration limit.");
        const nodes: any[] = await readJSON("nodes.json") || [];
        const node = nodes.find((candidate) => String(candidate.id) === nodeId);
        const eligibility = isEligibleFreeNode(node);
        if (!eligibility.eligible) throw new Error(eligibility.reason);
        if (plan.allowedNodeIds.length && !plan.allowedNodeIds.includes(nodeId)) throw new Error("Selected node is not permitted by this reward plan.");
        let physicalStats: any;
        try { physicalStats = node.isLocal ? localCapacityStats(node) : await nodeControl.health(nodeConnection(node)); }
        catch { throw new Error("Selected node health/capacity could not be verified. Try again after it reports healthy telemetry."); }
        const capacity = checkFreeCapacity(node, latestServers, claim.resources, physicalStats);
        if (!capacity.available) throw new Error(capacity.reason);
        const latestAllocations: any[] = await readJSON("allocations.json") || [];
        const allocation = latestAllocations.find((candidate) => String(candidate.id) === allocationId && String(candidate.nodeId) === nodeId && !candidate.assignedServerId);
        if (!allocation) throw new Error("Selected allocation is no longer available. Refresh the node list and try again.");
        let port = Number(allocation.portStart);
        while (port <= Number(allocation.portEnd) && latestServers.some((server) => String(server.nodeId || "local") === nodeId && Number(server.port) === port)) port++;
        if (port > Number(allocation.portEnd)) throw new Error("No free port remains in the selected allocation.");
        const name = typeof req.body?.serverName === "string" ? req.body.serverName.trim().slice(0, 80) : "";
        const durationHours = Number(plan.durationHours) > 0 ? Number(plan.durationHours) : Number(latestFree.durationHours) > 0 ? Number(latestFree.durationHours) : "default";
        const payload = { name: name || `${String(user.username || "Member").slice(0, 55)} Reward Server`, ram: claim.resources.ram, cpu: claim.resources.cpu, disk: claim.resources.disk, port, type: "PAPER", version: "1.21.1", lifetimeHours: durationHours, nodeId, allocationId };
        provisionResult = await callController(createServer, req, {}, payload);
        if (provisionResult.status < 200 || provisionResult.status >= 300) throw new Error(String(provisionResult.data?.error || "Existing server provisioning pipeline rejected the reward."));
        targetId = String(provisionResult.data.id);
      }));
    } else {
      const serverId = String(req.body?.serverId || "");
      const server: any = (await readJSON("servers.json") || []).find((candidate: any) => candidate.id === serverId);
      const upgradeEligibility = validateOwnedServerUpgrade(server, userId, claim.resources);
      if (!upgradeEligibility.eligible) throw new Error(upgradeEligibility.reason);
      provisionResult = await callController(updateResources, req, { id: serverId }, claim.resources);
      if (provisionResult.status < 200 || provisionResult.status >= 300) throw new Error(String(provisionResult.data?.error || "Existing resource update pipeline rejected the reward."));
      targetId = serverId;
    }
    await updateRewardState((current) => { const row = current.claims.find((candidate) => candidate.id === claim!.id); if (row?.status === "processing") { row.status = "claimed"; row.targetId = targetId; row.updatedAt = new Date().toISOString(); } });
    const complete = (await readRewardState()).claims.find((row) => row.id === claim!.id);
    try { await audit("discord.reward_claimed", req, { claimId: claim.id, planId: plan.id, mode, targetId, resources: claim.resources }); }
    catch (auditError) { console.error("Reward was fulfilled but its audit record could not be written.", safePublicMessage(auditError)); }
    return res.status(201).json({ success: true, claim: complete, server: mode === "create" ? provisionResult?.data : undefined });
  } catch (error) {
    try {
      const recorded = (await readRewardState()).claims.find((row) => row.id === claim!.id);
      if (recorded?.status === "claimed") return res.status(201).json({ success: true, claim: recorded, server: mode === "create" ? provisionResult?.data : undefined });
    } catch { /* continue with the safe reserved/uncertain response below */ }
    const safe = safePublicMessage(error);
    // Leave uncertain outcomes reserved for operator review rather than risk creating/upgrade twice.
    const safeToRetry = !provisionResult || (provisionResult.status >= 400 && provisionResult.status < 500);
    await updateRewardState((current) => { const row = current.claims.find((candidate) => candidate.id === claim!.id); if (row?.status === "processing") { row.status = safeToRetry ? "failed" : "needs_review"; row.error = safe; row.updatedAt = new Date().toISOString(); } });
    await appendDiscordDiagnostic("error", "reward_claim_failed", safe);
    await audit("discord.reward_claim_needs_review", req, { claimId: claim.id, planId: plan?.id, mode, reason: safe });
    return res.status(safeToRetry ? 409 : 409).json({ error: safe, claimId: claim.id, status: safeToRetry ? "failed" : "needs_review", hint: safeToRetry ? "No successful provisioning was recorded. Correct the issue and submit a new claim." : "The claim is held to prevent duplicate provisioning. Ask an administrator to reconcile it before retrying." });
  }
});

router.use(requireAdmin, rateSensitive);
router.get("/admin", async (req, res) => {
  const state = await readRewardState();
  const offset = Math.max(0, Math.min(1_000_000, Number.parseInt(String(req.query.offset || "0"), 10) || 0));
  const limit = Math.max(1, Math.min(100, Number.parseInt(String(req.query.limit || "50"), 10) || 50));
  const events = [...state.events].sort((a, b) => b.joinedAt.localeCompare(a.joinedAt));
  const claims = [...state.claims].sort((a, b) => b.createdAt.localeCompare(a.createdAt));
  const logs = [...state.diagnostics].sort((a, b) => b.at.localeCompare(a.at));
  const safeEvents = events.slice(offset, offset + limit).map((event) => ({ id: event.id, guildId: event.guildId, memberId: event.memberIdSuffix ? `…${event.memberIdSuffix}` : "anonymized", inviterId: event.inviterIdSuffix ? `…${event.inviterIdSuffix}` : null, inviterPanelUserId: event.inviterPanelUserId, inviteCode: event.inviteCode, status: event.status, reason: event.reason, joinedAt: event.joinedAt, leftAt: event.leftAt }));
  return res.json({ config: configForClient(state), runtime: getDiscordRuntime(), plans: state.plans, stats: { verified: state.events.filter((row) => row.status === "verified").length, pending: state.events.filter((row) => row.status === "pending").length, unverified: state.events.filter((row) => row.status === "unverified").length, claims: state.claims.filter((row) => row.status === "claimed").length }, events: safeEvents, eventsTotal: events.length, claims: claims.slice(offset, offset + limit), claimsTotal: claims.length, diagnostics: logs.slice(0, 100) });
});

router.post("/admin/prune", async (req, res) => {
  await pruneDiscordRewardData();
  await audit("discord.data_pruned", req, { dataRetentionDays: (await readRewardState()).config.dataRetentionDays });
  return res.json({ success: true, message: "Expired unverified records were removed and old credited records were anonymized." });
});

router.put("/admin/config", async (req, res) => {
  const body = req.body || {};
  const previousConfig = (await readRewardState()).config;
  if (typeof body.enabled !== "boolean" || !requireSnowflake(body.guildId) || !requireSnowflake(body.announcementChannelId || "", true)) return res.status(400).json({ error: "Enabled must be boolean and Discord guild and announcement channel IDs must be 17–20 digit snowflakes." });
  const oauthClientId = String(body.oauthClientId || "").trim();
  const oauthRedirectUri = String(body.oauthRedirectUri || previousConfig.oauthRedirectUri || "").trim();
  if (oauthClientId && !isSnowflake(oauthClientId)) return res.status(400).json({ error: "Discord OAuth Client ID must be a valid Discord application ID." });
  if (oauthRedirectUri) {
    try { const url = new URL(oauthRedirectUri); if (!(["https:"].includes(url.protocol) || url.protocol === "http:" && ["localhost", "127.0.0.1"].includes(url.hostname)) || url.username || url.password) throw new Error(); }
    catch { return res.status(400).json({ error: "OAuth redirect must be HTTPS, except localhost during development." }); }
  }
  const minimumAccountAgeDays = parseWholeNumber(body.minimumAccountAgeDays ?? 0, 0, 3650);
  const dataRetentionDays = parseWholeNumber(body.dataRetentionDays ?? previousConfig.dataRetentionDays ?? 365, 30, 3650);
  if (minimumAccountAgeDays === null || typeof body.retainCreditOnLeave !== "boolean" || dataRetentionDays === null) return res.status(400).json({ error: "Account age must be a whole number from 0–3650 days, member-leave policy must be boolean, and event retention must be a whole number from 30–3650 days." });
  const botToken = typeof body.botToken === "string" ? body.botToken.trim() : "";
  const oauthSecret = typeof body.oauthClientSecret === "string" ? body.oauthClientSecret.trim() : "";
  if (botToken && (botToken.length < 40 || botToken.length > 200)) return res.status(400).json({ error: "Bot token has an invalid length." });
  if (oauthSecret && (oauthSecret.length < 10 || oauthSecret.length > 300)) return res.status(400).json({ error: "OAuth client secret has an invalid length." });
  if (body.enabled && !botToken && !previousConfig.botTokenEncrypted) return res.status(400).json({ error: "Enter the bot token before enabling the bot." });
  if (body.enabled && !(oauthClientId || previousConfig.oauthClientId)) return res.status(400).json({ error: "Configure the OAuth application ID so users can link their Discord account." });
  if (body.enabled && !(oauthSecret || previousConfig.oauthClientSecretEncrypted)) return res.status(400).json({ error: "Configure the OAuth application client secret so users can link their Discord account." });
  if (body.enabled && !oauthRedirectUri) return res.status(400).json({ error: "Set the exact HTTPS OAuth callback URL registered in the Discord Developer Portal." });
  let encryptedBot: string | null = null, encryptedOAuth: string | null = null;
  try { if (botToken) encryptedBot = encryptSecret(botToken); if (oauthSecret) encryptedOAuth = encryptSecret(oauthSecret); }
  catch { return res.status(500).json({ error: "Server encryption is unavailable. Check NODE_ENCRYPTION_KEY; no secret was saved." }); }
  await updateRewardState((state) => {
    state.config = { ...state.config, enabled: body.enabled, guildId: String(body.guildId), announcementChannelId: String(body.announcementChannelId || ""), oauthClientId: oauthClientId || state.config.oauthClientId, oauthRedirectUri, minimumAccountAgeDays, retainCreditOnLeave: body.retainCreditOnLeave, dataRetentionDays, botTokenEncrypted: encryptedBot || state.config.botTokenEncrypted, oauthClientSecretEncrypted: encryptedOAuth || state.config.oauthClientSecretEncrypted };
  });
  await audit("discord.config_updated", req, { enabled: body.enabled, guildId: body.guildId, announcementChannelConfigured: Boolean(body.announcementChannelId), oauthClientConfigured: Boolean(oauthClientId || (await readRewardState()).config.oauthClientId), tokenRotated: Boolean(botToken), oauthSecretRotated: Boolean(oauthSecret) });
  const saved = await readRewardState();
  if (saved.config.enabled) { await (await import("../services/discordRewards.js")).stopDiscordRewards(); await startDiscordRewards(); }
  else await (await import("../services/discordRewards.js")).stopDiscordRewards();
  return res.json({ config: configForClient(saved), runtime: getDiscordRuntime() });
});

router.post("/admin/test-connection", rateLimit(3, 60_000), async (req, res) => {
  const state = await readRewardState();
  const tokenInput = typeof req.body?.botToken === "string" ? req.body.botToken.trim() : "";
  const token = tokenInput || (state.config.botTokenEncrypted ? decryptSecret(state.config.botTokenEncrypted) : "");
  const guildId = String(req.body?.guildId || state.config.guildId);
  const announcementChannelId = String(req.body?.announcementChannelId ?? state.config.announcementChannelId);
  if (!token || !isSnowflake(guildId)) return res.status(400).json({ error: "A bot token and valid guild ID are required for testing." });
  const client = new Client({ intents: [GatewayIntentBits.Guilds, GatewayIntentBits.GuildMembers, GatewayIntentBits.GuildInvites] });
  try {
    await Promise.race([client.login(token), new Promise((_, reject) => setTimeout(() => reject(new Error("Discord login timed out")), 15_000))]);
    if (!client.isReady()) await Promise.race([new Promise<void>((resolve) => client.once("clientReady", () => resolve())), new Promise((_, reject) => setTimeout(() => reject(new Error("Discord gateway did not become ready")), 15_000))]);
    const guild = await client.guilds.fetch(guildId);
    await guild.invites.fetch();
    const me = await guild.members.fetch(client.user!.id);
    const required = [PermissionFlagsBits.ManageGuild];
    const missing: string[] = [];
    if (!me.permissions.has(required)) missing.push("Manage Server (read guild invites)");
    for (const [channelId, label] of [[announcementChannelId, "announcement"]] as const) {
      if (!channelId) continue;
      if (!isSnowflake(channelId)) return res.status(400).json({ error: `The ${label} channel ID must be a valid Discord snowflake.` });
      const channel = await guild.channels.fetch(channelId);
      if (!channel || channel.guildId !== guildId || channel.type === ChannelType.GuildCategory || !channel.isTextBased()) { missing.push(`${label} channel is not a text channel in this guild`); continue; }
      const permissions = channel.permissionsFor(me);
      if (!permissions?.has(PermissionFlagsBits.ViewChannel)) missing.push(`View Channel in ${label} channel`);
      if (!permissions?.has(PermissionFlagsBits.SendMessages)) missing.push(`Send Messages in ${label} channel`);
    }
    return res.json({ ok: missing.length === 0, bot: client.user?.tag || "Bot", guild: guild.name, guildId, inviteRead: true, gatewayIntents: ["Guilds", "GuildMembers (privileged)", "GuildInvites"], missingPermissions: missing, message: missing.length ? "The bot can connect, but requires additional permissions." : "Connection, target guild, invite access, and configured channels verified." });
  } catch (error) {
    const safe = safePublicMessage(error);
    await appendDiscordDiagnostic("warning", "connection_test_failed", safe);
    return res.status(502).json({ ok: false, error: "Discord connection test failed. Verify the token, guild ID, bot membership, gateway intents, and Manage Server permission.", diagnostic: safe });
  } finally { client.removeAllListeners(); client.destroy(); }
});

router.post("/admin/sync", async (req, res) => {
  const client = getDiscordClient();
  const state = await readRewardState();
  if (!client?.isReady()) return res.status(503).json({ error: "Discord bot is not connected." });
  try {
    const guild = await client.guilds.fetch(state.config.guildId);
    const invites = await guild.invites.fetch();
    const snapshots = [...invites.values()].map((invite) => ({ code: invite.code, uses: Number(invite.uses || 0), inviterId: invite.inviterId || null }));
    await updateRewardState((current) => { current.inviteSnapshots[state.config.guildId] = protectInviteSnapshots(snapshots); });
    await audit("discord.invites_synced", req, { guildId: state.config.guildId, inviteCount: snapshots.length });
    return res.json({ success: true, inviteCount: snapshots.length, syncedAt: new Date().toISOString() });
  } catch { return res.status(502).json({ error: "Could not read guild invites. Check Manage Server permission." }); }
});

router.post("/admin/plans", async (req, res) => {
  const plan = validateRewardPlan({ ...req.body, id: String(req.body?.id || "").toLowerCase() });
  if (!plan) return res.status(400).json({ error: "Invalid reward plan. Check its ID, invite threshold, resource caps, claim limits, node list, and supported reward choices." });
  let duplicate = false;
  await updateRewardState((current) => { if (current.plans.some((row: any) => row.id === plan.id)) duplicate = true; else current.plans.push(plan); });
  if (duplicate) return res.status(409).json({ error: "A reward plan with this ID already exists." });
  await audit("discord.reward_plan_created", req, { planId: plan.id, requiredInvites: plan.requiredInvites, allowedModes: [plan.allowCreate && "create", plan.allowUpgrade && "upgrade"].filter(Boolean) });
  return res.status(201).json({ plan });
});
router.put("/admin/plans/:id", async (req, res) => {
  const state = await readRewardState();
  if (!state.plans.some((row: any) => row.id === req.params.id)) return res.status(404).json({ error: "Reward plan not found." });
  const plan = validateRewardPlan({ ...req.body, id: req.params.id });
  if (!plan) return res.status(400).json({ error: "Invalid reward plan." });
  await updateRewardState((current) => { current.plans = current.plans.map((row: any) => row.id === plan.id ? plan : row); });
  await audit("discord.reward_plan_updated", req, { planId: plan.id, enabled: plan.enabled });
  return res.json({ plan });
});
router.delete("/admin/plans/:id", async (req, res) => {
  const state = await readRewardState();
  const plan = state.plans.find((row: any) => row.id === req.params.id);
  if (!plan) return res.status(404).json({ error: "Reward plan not found." });
  if (state.claims.some((claim) => claim.planId === plan.id && ["processing", "needs_review"].includes(claim.status))) return res.status(409).json({ error: "This plan has a claim requiring reconciliation; disable it instead of deleting." });
  await updateRewardState((current) => { current.plans = current.plans.filter((row: any) => row.id !== req.params.id); });
  await audit("discord.reward_plan_deleted", req, { planId: plan.id });
  return res.json({ success: true });
});

router.post("/admin/events/:id/review", async (req, res) => {
  const status = req.body?.status;
  if (status !== "verified" && status !== "unverified" && status !== "revoked") return res.status(400).json({ error: "Status must be verified, unverified, or revoked." });
  let found = false;
  let duplicateCredit = false;
  await updateRewardState((state) => {
    const event = state.events.find((row) => row.id === req.params.id);
    if (!event) return;
    if (status === "verified" && state.events.some((row) => row.id !== event.id && row.memberIdHash === event.memberIdHash && row.everCredited)) { duplicateCredit = true; return; }
    found = true; event.status = status; event.reason = `admin_review_${status}`;
    if (status === "verified") event.everCredited = true;
  });
  if (duplicateCredit) return res.status(409).json({ error: "This Discord member already has a verified referral credit; duplicate attribution is blocked." });
  if (!found) return res.status(404).json({ error: "Referral event not found." });
  await audit("discord.referral_reviewed", req, { eventId: req.params.id, status });
  return res.json({ success: true });
});

router.post("/admin/claims/:id/reconcile", async (req, res) => {
  const action = req.body?.action;
  if (!(["mark_claimed", "release_failed"] as string[]).includes(action)) return res.status(400).json({ error: "Action must be mark_claimed or release_failed." });
  let found: RewardClaim | undefined;
  await updateRewardState((state) => {
    const claim = state.claims.find((row) => row.id === req.params.id);
    if (!claim || !["needs_review", "processing"].includes(claim.status)) return;
    if (action === "mark_claimed") { claim.status = "claimed"; claim.targetId = String(req.body.targetId || claim.targetId || "").slice(0, 160) || null; }
    else { claim.status = "failed"; claim.error = "Administrator released after verifying no provisioning occurred."; }
    claim.updatedAt = new Date().toISOString(); found = claim;
  });
  if (!found) return res.status(404).json({ error: "No claim requiring reconciliation was found." });
  await audit("discord.reward_claim_reconciled", req, { claimId: found.id, action, targetId: found.targetId });
  return res.json({ success: true, claim: found });
});

export default router;
