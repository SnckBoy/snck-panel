import assert from "node:assert/strict";
import crypto from "node:crypto";
import fs from "node:fs/promises";
import os from "node:os";
import path from "node:path";
import { fileURLToPath } from "node:url";

const originalCwd = process.cwd();
const tempDir = await fs.mkdtemp(path.join(os.tmpdir(), "snck-discord-rewards-test-"));
process.chdir(tempDir);
process.env.NODE_ENCRYPTION_KEY = "test-discord-rewards-encryption-key-only";
try {
  const sourceRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "../src/server/services");
  const configParser = await import(path.resolve(path.dirname(fileURLToPath(import.meta.url)), "../src/shared/discordRewardsConfig.ts"));
  const core = await import(path.join(sourceRoot, "discordRewardsCore.ts"));
  const security = await import(path.join(sourceRoot, "security.ts"));
  const rewards = await import(path.join(sourceRoot, "discordRewards.ts"));

  assert.deepEqual(core.attributeInvite([{ code: "first", uses: 3, inviterId: "10000000000000001" }], [{ code: "first", uses: 4, inviterId: "10000000000000001" }]), { inviterId: "10000000000000001", inviteCode: "first", verified: true, reason: "single_invite_use_delta" });
  assert.equal(core.attributeInvite([{ code: "a", uses: 0, inviterId: "10000000000000001" }, { code: "b", uses: 0, inviterId: "10000000000000002" }], [{ code: "a", uses: 1, inviterId: "10000000000000001" }, { code: "b", uses: 1, inviterId: "10000000000000002" }]).reason, "multiple_invites_changed");
  assert.equal(core.attributeInvite([{ code: "a", uses: 0, inviterId: "10000000000000001" }], [{ code: "a", uses: 2, inviterId: "10000000000000001" }]).reason, "multiple_uses_changed");
  assert.equal(core.attributeInvite([{ code: "a", uses: 4, inviterId: null }], [{ code: "a", uses: 3, inviterId: null }]).verified, false);
  assert.equal(core.isSnowflake("10000000000000001"), true);
  assert.equal(core.isSnowflake("not-a-discord-id"), false);
  assert.equal(configParser.parseWholeNumber("14", 0, 3650), 14);
  assert.equal(configParser.parseWholeNumber("365", 30, 3650), 365);
  assert.equal(configParser.parseWholeNumber("", 0, 3650), null);
  assert.equal(configParser.parseWholeNumber("abc", 0, 3650), null);
  assert.equal(configParser.parseWholeNumber("2.5", 0, 3650), null);
  assert.equal(configParser.parseWholeNumber("0", 30, 3650), null);
  assert.equal(configParser.parseWholeNumber("3651", 30, 3650), null);

  const validPlan = { id: "invite-reward-1", name: "Invite reward", description: "Milestone", requiredInvites: 4, allowCreate: true, allowUpgrade: true, ram: 2, cpu: 100, disk: 5, durationHours: 168, allowedNodeIds: ["node-a"], maxClaimsPerUser: 1, maxTotalClaims: 20, enabled: true, sortOrder: 0 };
  assert.deepEqual(core.validateRewardPlan(validPlan), validPlan);
  assert.equal(core.validateRewardPlan({ ...validPlan, requiredInvites: 0 }), null);
  assert.equal(core.validateRewardPlan({ ...validPlan, ram: Infinity }), null);
  assert.equal(core.validateRewardPlan({ ...validPlan, allowCreate: false, allowUpgrade: false }), null);
  assert.equal(core.validateRewardPlan({ ...validPlan, allowedNodeIds: ["node-a", "node-a"] }), null);
  assert.equal(core.validateRewardPlan({ ...validPlan, enabled: "true" }), null);

  const now = Date.now();
  assert.deepEqual(core.isEligibleFreeNode({ id: "remote", visibility: "public", lastHeartbeat: new Date(now - 2_000).toISOString() }, now).eligible, true);
  assert.equal(core.isEligibleFreeNode({ id: "locked", visibility: "public", freeServiceLocked: true, lastHeartbeat: new Date(now).toISOString() }, now).reason, "Node is locked for Free Service.");
  assert.equal(core.isEligibleFreeNode({ id: "stale", visibility: "public", lastHeartbeat: new Date(now - 90_000).toISOString() }, now).eligible, false);
  assert.equal(core.isEligibleFreeNode({ id: "disabled", visibility: "public", disabled: true, lastHeartbeat: new Date(now).toISOString() }, now).eligible, false);
  assert.equal(core.isEligibleFreeNode({ id: "private", visibility: "private", lastHeartbeat: new Date(now).toISOString() }, now).eligible, false);
  assert.equal(core.isEligibleFreeNode({ id: "local", isLocal: true, localReady: false }, now).eligible, false);
  assert.equal(core.isEligibleFreeNode({ id: "local", isLocal: true, localReady: true }, now).eligible, true);

  const capacityNode = { id: "capacity-node", memory: 8192, memoryOverallocate: 0, disk: 100, diskOverallocate: 0, cpu: 400 };
  const healthyPhysical = { memory: { free: 8 * 1024 ** 3 }, disk: { free: 100 * 1024 ** 3 } };
  assert.equal(core.checkFreeCapacity(capacityNode, [], { ram: 2, cpu: 100, disk: 5 }, healthyPhysical).available, true);
  assert.match(core.checkFreeCapacity(capacityNode, [{ nodeId: capacityNode.id, ram: 7, cpu: 100, disk: 5 }], { ram: 2, cpu: 100, disk: 5 }, healthyPhysical).reason, /configured node resource capacity is exhausted/i);
  assert.match(core.checkFreeCapacity(capacityNode, [], { ram: 2, cpu: 100, disk: 5 }, { memory: { free: 1 }, disk: { free: 100 * 1024 ** 3 } }).reason, /physical RAM or disk capacity is exhausted/i);
  assert.match(core.checkFreeCapacity({ ...capacityNode, memory: 0 }, [], { ram: 2, cpu: 100, disk: 5 }, healthyPhysical).reason, /must be configured/i);

  assert.deepEqual(core.rewardProgress(2, 4), { current: 2, required: 4, percent: 50, remaining: 2 });
  assert.equal(core.rewardProgress(9, 4).percent, 100);
  assert.equal(core.claimIsAvailable({ userClaims: 0, totalClaims: 0, maxPerUser: 1, maxTotal: 20, verifiedInvites: 4, requiredInvites: 4 }), true);
  assert.equal(core.claimIsAvailable({ userClaims: 0, totalClaims: 0, maxPerUser: 1, maxTotal: 20, verifiedInvites: 3, requiredInvites: 4 }), false);
  assert.equal(core.claimIsAvailable({ userClaims: 1, totalClaims: 1, maxPerUser: 1, maxTotal: 20, verifiedInvites: 10, requiredInvites: 4 }), false);
  assert.equal(core.hasSuccessfulCreditForMember([{ memberIdHash: "member-hash", everCredited: true }], "member-hash"), true);
  assert.equal(core.hasSuccessfulCreditForMember([{ memberIdHash: "member-hash", everCredited: false }], "member-hash"), false);
  const ownedServer = { id: "server-a", owner: "user-1", ram: 2, cpu: 100, disk: 5 };
  assert.equal(core.validateOwnedServerUpgrade(ownedServer, "user-1", { ram: 4, cpu: 200, disk: 10 }).eligible, true);
  assert.equal(core.validateOwnedServerUpgrade({ ...ownedServer, owner: "user-2" }, "user-1", { ram: 4, cpu: 200, disk: 10 }).eligible, false);
  assert.equal(core.validateOwnedServerUpgrade(ownedServer, "user-1", { ram: 4, cpu: 50, disk: 10 }).eligible, false);
  assert.equal(core.validateOwnedServerUpgrade(ownedServer, "user-1", { ram: 2, cpu: 100, disk: 5 }).eligible, false);

  const hashed = rewards.hashDiscordId("10000000000000001");
  assert.equal(hashed, rewards.hashDiscordId("10000000000000001"));
  assert.notEqual(hashed, rewards.hashDiscordId("10000000000000002"));
  const protectedInvite = rewards.protectInviteSnapshots([{ code: "safe-code", uses: 7, inviterId: "10000000000000001" }])[0];
  assert.equal(protectedInvite.code, "safe-code");
  assert.notEqual(protectedInvite.inviterIdEncrypted, "10000000000000001");
  assert.equal(security.decryptSecret(protectedInvite.inviterIdEncrypted), "10000000000000001");

  const memberHash = rewards.hashDiscordId("10000000000000003");
  const event = { id: crypto.randomUUID(), guildId: "10000000000000000", memberIdHash: memberHash, memberIdEncrypted: security.encryptSecret("10000000000000003"), memberIdSuffix: "0003", inviterIdHash: hashed, inviterIdEncrypted: security.encryptSecret("10000000000000001"), inviterIdSuffix: "0001", inviterPanelUserId: "user-1", inviteCode: "invite-code", status: "verified", reason: "unique_member_join_attributed", joinedAt: new Date(Date.now() - 400 * 86_400_000).toISOString(), leftAt: null, accountCreatedAt: null, everCredited: true };
  const expired = { ...event, id: crypto.randomUUID(), memberIdHash: "pending-hash", status: "unverified", reason: "ambiguous", everCredited: false };
  await rewards.updateRewardState((state: any) => { state.config.dataRetentionDays = 365; state.config.welcomeChannelId = "legacy-welcome-channel"; state.events.push(event, expired); state.oauthStates.push({ hash: "expired", userId: "user-1", expiresAt: Date.now() - 1 }); });
  assert.equal((await fs.stat(path.join(tempDir, ".data", "discord_rewards.json"))).mode & 0o777, 0o600);
  await rewards.pruneDiscordRewardData();
  const afterPrune = await rewards.readRewardState();
  assert.equal("welcomeChannelId" in afterPrune.config, false);
  const oldCredit = afterPrune.events.find((row: any) => row.id === event.id)!;
  assert.equal(oldCredit.memberIdEncrypted, "");
  assert.equal(oldCredit.inviterIdEncrypted, null);
  assert.equal(oldCredit.inviteCode, null);
  assert.equal(oldCredit.everCredited, true);
  assert.equal(afterPrune.events.some((row: any) => row.id === expired.id), false);
  assert.equal(afterPrune.oauthStates.length, 0);

  // updateRewardState is the same per-file atomic serializer used by claim
  // reservation. Simultaneous duplicate keys must produce exactly one record.
  let accepted = 0;
  await Promise.all(Array.from({ length: 40 }, () => rewards.updateRewardState((state: any) => {
    if (state.claims.some((claim: any) => claim.panelUserId === "user-1" && claim.idempotencyKey === "one-key-for-all-requests")) return;
    accepted += 1;
    state.claims.push({ id: crypto.randomUUID(), idempotencyKey: "one-key-for-all-requests", panelUserId: "user-1", planId: "invite-reward-1", mode: "create", status: "processing", targetId: null, createdAt: new Date().toISOString(), updatedAt: new Date().toISOString(), error: null, resources: { ram: 2, cpu: 100, disk: 5 } });
  })));
  const afterConcurrent = await rewards.readRewardState();
  assert.equal(accepted, 1);
  assert.equal(afterConcurrent.claims.filter((claim: any) => claim.idempotencyKey === "one-key-for-all-requests").length, 1);
  console.log("Discord rewards behavioral tests passed (attribution, validation, node filtering, anti-abuse, encrypted identifiers, retention, and atomic claim idempotency).");
} finally {
  process.chdir(originalCwd);
  await fs.rm(tempDir, { recursive: true, force: true });
}
