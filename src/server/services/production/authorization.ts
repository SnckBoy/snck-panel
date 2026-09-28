import { errors } from "./errors.js";

export const PERMISSIONS = [
  "console.read", "console.command", "files.read", "files.write", "files.delete", "files.upload", "files.download",
  "server.start", "server.stop", "server.restart", "server.kill", "server.settings", "server.properties", "server.delete",
  "backups.read", "backups.create", "backups.restore", "backups.delete", "plugins.manage", "mods.manage", "players.manage",
  "sftp.manage", "subusers.manage", "worlds.manage", "activity.read", "server.migrate",
] as const;
export type Permission = typeof PERMISSIONS[number];

export type AccessPrincipal = { id: string; role?: string; permissions?: string[]; disabled?: boolean };
export type OwnedServer = { id: string; ownerId: string; suspended?: boolean; subUsers?: Array<{ userId: string; permissions: string[] }> };

export function requireActive(principal: AccessPrincipal | null | undefined) {
  if (!principal) throw errors.unauthenticated();
  if (principal.disabled) throw errors.forbidden("This account is disabled");
  return principal;
}

export function can(principal: AccessPrincipal, server: OwnedServer, permission: Permission) {
  requireActive(principal);
  if (principal.role === "admin" || principal.role === "administrator") return true;
  if (server.ownerId === principal.id) return !server.suspended;
  const membership = server.subUsers?.find((entry) => entry.userId === principal.id);
  return Boolean(membership?.permissions.includes(permission) && !server.suspended);
}

export function assertPermission(principal: AccessPrincipal, server: OwnedServer, permission: Permission) {
  if (!can(principal, server, permission)) throw errors.forbidden(`Missing permission: ${permission}`);
}

export function assertAdmin(principal: AccessPrincipal) {
  requireActive(principal);
  if (principal.role !== "admin" && principal.role !== "administrator") throw errors.forbidden("Administrator access required");
}
