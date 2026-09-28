import path from 'node:path';

export class UnsafePathError extends Error {
  readonly code = 'UNSAFE_PATH';

  constructor(message = 'The requested path is outside the allowed server directory.') {
    super(message);
    this.name = 'UnsafePathError';
  }
}

function normalizeRelativeInput(input: string): string {
  if (typeof input !== 'string' || input.length === 0) {
    throw new UnsafePathError('A non-empty relative path is required.');
  }
  if (input.includes('\0')) {
    throw new UnsafePathError('Null bytes are not allowed.');
  }

  const normalized = input.replaceAll('\\', '/');
  if (normalized.startsWith('/') || /^[A-Za-z]:\//.test(normalized)) {
    throw new UnsafePathError('Absolute paths are not allowed.');
  }
  if (normalized.split('/').some((segment) => segment === '..')) {
    throw new UnsafePathError('Path traversal is not allowed.');
  }
  return normalized;
}

export function resolveServerPath(serverRoot: string, requestedPath = '.'): string {
  const root = path.resolve(serverRoot);
  const relative = requestedPath === '.' ? '.' : normalizeRelativeInput(requestedPath);
  const resolved = path.resolve(root, relative);
  const prefix = `${root}${path.sep}`;

  if (resolved !== root && !resolved.startsWith(prefix)) {
    throw new UnsafePathError();
  }
  return resolved;
}

export function assertSafeArchiveEntry(serverRoot: string, archiveEntry: string): string {
  return resolveServerPath(serverRoot, archiveEntry);
}

export async function resolveExistingServerPath(serverRoot: string, requestedPath: string): Promise<string> {
  const fs = await import('node:fs/promises');
  const root = await fs.realpath(serverRoot);
  const candidate = resolveServerPath(root, requestedPath);
  const actual = await fs.realpath(candidate);
  const prefix = `${root}${path.sep}`;

  if (actual !== root && !actual.startsWith(prefix)) {
    throw new UnsafePathError('The resolved path escapes the server directory.');
  }
  return actual;
}
