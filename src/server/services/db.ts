import fs from "fs-extra";
import path from "path";

const DATA_DIR = path.resolve(process.cwd(), ".data");
const writeLocks = new Map<string, Promise<void>>();
const updateLocks = new Map<string, Promise<unknown>>();
const dataPath = (filename: string) => {
  const filePath = path.resolve(DATA_DIR, String(filename));
  if (filePath !== DATA_DIR && !filePath.startsWith(`${DATA_DIR}${path.sep}`)) throw new Error("Invalid data filename");
  return filePath;
};

export const readJSON = async (filename: string) => {
  const filePath = dataPath(filename);
  try {
    return await fs.readJson(filePath);
  } catch (err: any) {
    // A missing file is an empty optional store; malformed JSON and I/O errors
    // must surface instead of silently replacing real data with defaults.
    if (err?.code === "ENOENT") return null;
    throw new Error(`Failed to read data file "${filename}": ${err?.message || String(err)}`);
  }
};

export const writeJSON = async (filename: string, data: any) => {
  const filePath = dataPath(filename);
  const previous = writeLocks.get(filePath) || Promise.resolve();
  const current = previous.catch(() => undefined).then(async () => {
    await fs.ensureDir(path.dirname(filePath));
    const tempPath = `${filePath}.${process.pid}.${Date.now()}.tmp`;
    try {
      await fs.writeJson(tempPath, data, { spaces: 2 });
      await fs.rename(tempPath, filePath);
    } finally {
      if (await fs.pathExists(tempPath)) await fs.remove(tempPath);
    }
  });
  writeLocks.set(filePath, current);
  try {
    await current;
  } finally {
    if (writeLocks.get(filePath) === current) writeLocks.delete(filePath);
  }
};


/**
 * Serialize read-modify-write updates within this Node.js process.
 * Multi-process/multi-host deployments require a transactional shared database.
 */
export const updateJSON = async <T = any>(
  filename: string,
  updater: (current: T) => T | Promise<T>,
  fallback: T,
): Promise<T> => {
  const filePath = dataPath(filename);
  const previous = updateLocks.get(filePath) || Promise.resolve();
  const current = previous.catch(() => undefined).then(async () => {
    const stored = await readJSON(filename);
    const next = await updater((stored === null ? fallback : stored) as T);
    await writeJSON(filename, next);
    return next;
  });
  updateLocks.set(filePath, current);
  try {
    return await current as T;
  } finally {
    if (updateLocks.get(filePath) === current) updateLocks.delete(filePath);
  }
};
