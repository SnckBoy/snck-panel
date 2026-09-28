import path from "path";

/**
 * Resolve a user-controlled relative path inside a server root.
 *
 * This intentionally rejects absolute paths and null bytes before resolving.
 * The returned path is guaranteed to remain below serverRoot. Callers that
 * operate on symlinks should additionally use realpath/lstat checks before
 * following an existing symlink.
 */
export function resolveServerPath(serverRoot: string, userPath = "."): string {
  if (typeof userPath !== "string") {
    throw new Error("Invalid path");
  }
  if (userPath.includes("\0")) {
    throw new Error("Invalid path");
  }

  const normalizedInput = userPath.replace(/\\/g, "/");
  if (path.posix.isAbsolute(normalizedInput) || path.win32.isAbsolute(userPath)) {
    throw new Error("Absolute paths are not allowed");
  }

  const root = path.resolve(serverRoot);
  const resolved = path.resolve(root, normalizedInput);
  const relative = path.relative(root, resolved);

  if (relative === "" || (!relative.startsWith(`..${path.sep}`) && relative !== ".." && !path.isAbsolute(relative))) {
    return resolved;
  }

  throw new Error("Path escapes server directory");
}

export function assertSafeArchiveEntry(entryName: string): string {
  if (typeof entryName !== "string" || entryName.includes("\0")) {
    throw new Error("Invalid archive entry");
  }

  const normalized = entryName.replace(/\\/g, "/");
  if (normalized.startsWith("/") || path.posix.isAbsolute(normalized)) {
    throw new Error("Absolute archive paths are not allowed");
  }

  const segments = normalized.split("/");
  if (segments.some((segment) => segment === "..")) {
    throw new Error("Archive traversal is not allowed");
  }

  return normalized;
}
