export type InviteSnapshot = { code: string; uses: number; inviterId: string | null };
export type InviteAttribution = { inviterId: string | null; inviteCode: string | null; verified: boolean; reason: string };

export function attributeInvite(before: InviteSnapshot[], after: InviteSnapshot[]): InviteAttribution {
  const oldUses = new Map(before.map((invite) => [invite.code, Number(invite.uses) || 0]));
  const changed: Array<{ invite: InviteSnapshot; delta: number }> = [];
  for (const invite of after) {
    const delta = (Number(invite.uses) || 0) - (oldUses.get(invite.code) ?? 0);
    if (delta > 0) changed.push({ invite, delta });
  }
  // A newly created invite or concurrent use-count changes make attribution uncertain.
  if (changed.length !== 1 || changed[0].delta !== 1 || !changed[0].invite.inviterId) {
    return { inviterId: null, inviteCode: null, verified: false, reason: changed.length > 1 ? "multiple_invites_changed" : changed.length === 1 && changed[0].delta > 1 ? "multiple_uses_changed" : "invite_use_not_identifiable" };
  }
  return { inviterId: changed[0].invite.inviterId, inviteCode: changed[0].invite.code, verified: true, reason: "single_invite_use_delta" };
}

export function isSnowflake(value: unknown): value is string {
  return typeof value === "string" && /^\d{17,20}$/.test(value);
}

export type RewardPlan = {
  id: string; name: string; description: string; requiredInvites: number;
  allowCreate: boolean; allowUpgrade: boolean; ram: number; cpu: number; disk: number;
  durationHours: number; allowedNodeIds: string[]; maxClaimsPerUser: number;
  maxTotalClaims: number | null; enabled: boolean; sortOrder: number;
};

export function validateRewardPlan(input: any): RewardPlan | null {
  if (!input || typeof input !== "object" || Array.isArray(input)) return null;
  const id = typeof input.id === "string" ? input.id : "";
  const name = typeof input.name === "string" ? input.name.trim() : "";
  const description = typeof input.description === "string" ? input.description.trim() : "";
  const requiredInvites = Number(input.requiredInvites);
  const ram = Number(input.ram), cpu = Number(input.cpu), disk = Number(input.disk);
  const durationHours = Number(input.durationHours ?? 0);
  const maxClaimsPerUser = Number(input.maxClaimsPerUser ?? 1);
  const maxTotalClaims = input.maxTotalClaims === "" || input.maxTotalClaims === null || input.maxTotalClaims === undefined ? null : Number(input.maxTotalClaims);
  const sortOrder = Number(input.sortOrder ?? 0);
  if (!/^[a-z0-9][a-z0-9-]{1,47}$/.test(id) || name.length < 2 || name.length > 64 || description.length > 300) return null;
  if (!Number.isInteger(requiredInvites) || requiredInvites < 1 || requiredInvites > 5000) return null;
  if (typeof input.allowCreate !== "boolean" || typeof input.allowUpgrade !== "boolean" || (!input.allowCreate && !input.allowUpgrade)) return null;
  if (![ram, cpu, disk].every(Number.isFinite) || ram <= 0 || ram > 1024 || cpu <= 0 || cpu > 10000 || disk <= 0 || disk > 1048576) return null;
  if (!Number.isFinite(durationHours) || durationHours < 0 || durationHours > 87600) return null;
  if (!Number.isInteger(maxClaimsPerUser) || maxClaimsPerUser < 1 || maxClaimsPerUser > 50) return null;
  if (maxTotalClaims !== null && (!Number.isInteger(maxTotalClaims) || maxTotalClaims < 1 || maxTotalClaims > 1000000)) return null;
  if (!Number.isInteger(sortOrder) || sortOrder < 0 || sortOrder > 100000) return null;
  if (!Array.isArray(input.allowedNodeIds) || input.allowedNodeIds.length > 200 || input.allowedNodeIds.some((id: unknown) => typeof id !== "string" || id.length > 120) || new Set(input.allowedNodeIds).size !== input.allowedNodeIds.length) return null;
  if (typeof input.enabled !== "boolean") return null;
  return { id, name, description, requiredInvites, allowCreate: input.allowCreate, allowUpgrade: input.allowUpgrade, ram, cpu, disk, durationHours, allowedNodeIds: [...input.allowedNodeIds], maxClaimsPerUser, maxTotalClaims, enabled: input.enabled, sortOrder };
}

export function isEligibleFreeNode(node: any, now = Date.now(), heartbeatTimeoutMs = 45_000): { eligible: boolean; reason: string } {
  if (!node || node.visibility === "private") return { eligible: false, reason: "Node is not publicly available for Free Service." };
  if (node.freeServiceLocked) return { eligible: false, reason: "Node is locked for Free Service." };
  if (node.disabled) return { eligible: false, reason: "Node is disabled." };
  if (node.maintenance) return { eligible: false, reason: "Node is in maintenance." };
  if (node.error || node.installing || node.restartingAt) return { eligible: false, reason: "Node is not healthy yet." };
  if (node.isLocal) return node.localReady ? { eligible: true, reason: "Available." } : { eligible: false, reason: "Local node is not ready." };
  const heartbeat = node.lastHeartbeat ? Date.parse(node.lastHeartbeat) : NaN;
  if (!Number.isFinite(heartbeat) || now - heartbeat > heartbeatTimeoutMs) return { eligible: false, reason: "Node heartbeat is offline or stale." };
  return { eligible: true, reason: "Available." };
}

export function checkFreeCapacity(node: any, servers: any[], requested: { ram: number; cpu: number; disk: number }, physicalStats: any): { available: boolean; reason: string } {
  if (!node || !Array.isArray(servers)) return { available: false, reason: "Node capacity data is unavailable." };
  const ramMb = Number(node.memory), cpuLimit = Number(node.cpu), diskGb = Number(node.disk);
  if (![ramMb, cpuLimit, diskGb].every((value) => Number.isFinite(value) && value > 0)) return { available: false, reason: "Node RAM, CPU, and disk capacity must be configured by an administrator." };
  const nodeId = String(node.id);
  const assigned = servers.filter((server) => String(server.nodeId || "local") === nodeId && server.status !== "deleted");
  const reservedRamMb = assigned.reduce((total, server) => total + Math.max(0, Number(server.ram) || 0) * 1024, 0);
  const reservedCpu = assigned.reduce((total, server) => total + Math.max(0, Number(server.cpu) || 0), 0);
  const reservedDiskGb = assigned.reduce((total, server) => total + Math.max(0, Number(server.disk) || 0), 0);
  const ramPercent = Math.max(0, Number(node.memoryOverallocate) || 0);
  const diskPercent = Math.max(0, Number(node.diskOverallocate) || 0);
  const effectiveRamMb = ramMb * (1 + ramPercent / 100);
  const effectiveDiskGb = diskGb * (1 + diskPercent / 100);
  if (reservedRamMb + requested.ram * 1024 > effectiveRamMb || reservedCpu + requested.cpu > cpuLimit || reservedDiskGb + requested.disk > effectiveDiskGb) return { available: false, reason: "Configured node resource capacity is exhausted for this reward." };
  const freeRamBytes = Number(physicalStats?.memory?.free), freeDiskBytes = Number(physicalStats?.disk?.free);
  if (!Number.isFinite(freeRamBytes) || !Number.isFinite(freeDiskBytes)) return { available: false, reason: "Current physical RAM/disk telemetry is unavailable." };
  if (freeRamBytes < requested.ram * 1024 ** 3 || freeDiskBytes < requested.disk * 1024 ** 3) return { available: false, reason: "Current physical RAM or disk capacity is exhausted." };
  return { available: true, reason: "Capacity available." };
}

export function rewardProgress(verifiedInvites: number, requiredInvites: number): { current: number; required: number; percent: number; remaining: number } {
  const current = Math.max(0, Math.floor(Number(verifiedInvites) || 0));
  const required = Math.max(1, Math.floor(Number(requiredInvites) || 1));
  return { current, required, percent: Math.min(100, Math.floor(current / required * 100)), remaining: Math.max(0, required - current) };
}

export function hasSuccessfulCreditForMember(events: any[], memberId: string): boolean {
  return events.some((event) => (event.memberIdHash || event.memberId) === memberId && event.everCredited === true);
}

export function claimIsAvailable(args: { userClaims: number; totalClaims: number; maxPerUser: number; maxTotal: number | null; verifiedInvites: number; requiredInvites: number }): boolean {
  return args.userClaims < args.maxPerUser && (args.maxTotal === null || args.totalClaims < args.maxTotal) && args.verifiedInvites >= args.requiredInvites;
}

export function rewardCredits(verifiedInvites: number, requiredInvites: number, claimed = 0, reserved = 0) {
  const threshold = Math.max(1, Math.floor(Number(requiredInvites) || 1));
  const inviteCount = Math.max(0, Math.floor(Number(verifiedInvites) || 0));
  const earned = Math.floor(inviteCount / threshold);
  const spent = Math.min(earned, Math.max(0, Math.floor(Number(claimed) || 0)));
  const held = Math.min(Math.max(0, earned - spent), Math.max(0, Math.floor(Number(reserved) || 0)));
  return { earned, spent, reserved: held, available: Math.max(0, earned - spent - held), progress: inviteCount % threshold, required: threshold };
}

export function validateOwnedServerUpgrade(server: any, userId: string, requested: { ram: number; cpu: number; disk: number }): { eligible: boolean; reason: string } {
  if (!server || String(server.owner) !== userId) return { eligible: false, reason: "Only a server owned by your authenticated panel account can be upgraded." };
  const current = { ram: Number(server.ram), cpu: Number(server.cpu), disk: Number(server.disk) };
  if (Object.values(current).some((value) => !Number.isFinite(value) || value <= 0)) return { eligible: false, reason: "The target server resource limits are invalid." };
  if (requested.ram < current.ram || requested.cpu < current.cpu || requested.disk < current.disk) return { eligible: false, reason: "This plan would not increase all target server resource limits." };
  if (requested.ram === current.ram && requested.cpu === current.cpu && requested.disk === current.disk) return { eligible: false, reason: "The server already has these resources." };
  return { eligible: true, reason: "Eligible." };
}
