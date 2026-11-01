import fs from "node:fs";
import path from "node:path";
import crypto from "node:crypto";
import ssh2 from "ssh2";

const SSHServer: any = (ssh2 as any).Server;
const STATUS_CODE: any = (ssh2 as any).sftp.STATUS_CODE;

export const sftpOpenFlagsToNode = (flags: number): number => {
  const read = (flags & 0x00000001) !== 0;
  const write = (flags & 0x00000002) !== 0;
  if (!read && !write) throw new Error("SFTP open requires read or write permission");
  if (!write && (flags & (0x00000004 | 0x00000008 | 0x00000010 | 0x00000020))) {
    throw new Error("SFTP write flags require write permission");
  }
  let result = read && write ? fs.constants.O_RDWR : write ? fs.constants.O_WRONLY : fs.constants.O_RDONLY;
  if (flags & 0x00000004) result |= fs.constants.O_APPEND;
  if (flags & 0x00000008) result |= fs.constants.O_CREAT;
  if (flags & 0x00000010) result |= fs.constants.O_TRUNC;
  if (flags & 0x00000020) result |= fs.constants.O_EXCL;
  if (typeof fs.constants.O_NOFOLLOW === "number") result |= fs.constants.O_NOFOLLOW;
  return result;
};

export type SftpConfig = {
  host?: string;
  port?: number;
  hostKey: string;
  panelUrl: string;
  nodeId: string;
  credential: string;
  serversDir: string;
};

type AuthResult = { serverId: string };
type OpenHandle = { kind: "file"; value: number } | { kind: "dir"; value: fs.Dir; directoryPath: string };

export const safeServerRoot = (serversDir: string, serverId: string): string => {
  if (!/^[a-zA-Z0-9_-]+$/.test(serverId)) throw new Error("Invalid server id");
  const base = path.resolve(serversDir);
  fs.mkdirSync(base, { recursive: true, mode: 0o750 });
  const realBase = fs.realpathSync(base);
  const root = path.resolve(realBase, serverId);
  if (!root.startsWith(`${realBase}${path.sep}`)) throw new Error("Invalid server root");
  fs.mkdirSync(root, { recursive: true, mode: 0o750 });
  if (fs.lstatSync(root).isSymbolicLink()) throw new Error("Server root cannot be a symlink");
  return fs.realpathSync(root);
};

/**
 * Resolve a client path inside one server root. Absolute SFTP paths are treated
 * as virtual paths inside the jail, never as host filesystem paths. Symlinks
 * in every existing path component are rejected to prevent jail escapes.
 */
export const jailedPath = (root: string, relative: string): string => {
  const clean = String(relative || ".").replace(/\\/g, "/");
  if (clean.includes("\\0")) throw new Error("Path escapes SFTP jail");
  const canonicalRoot = fs.realpathSync(root);
  const virtualPath = clean.replace(/^\/+/, "");
  const target = path.resolve(canonicalRoot, virtualPath || ".");
  if (target !== canonicalRoot && !target.startsWith(`${canonicalRoot}${path.sep}`)) {
    throw new Error("Path escapes SFTP jail");
  }

  const rel = path.relative(canonicalRoot, target);
  let probe = canonicalRoot;
  for (const part of rel.split(path.sep).filter(Boolean)) {
    probe = path.join(probe, part);
    if (!fs.existsSync(probe)) break;
    if (fs.lstatSync(probe).isSymbolicLink()) throw new Error("Symlinks are not allowed inside the SFTP jail");
  }
  return target;
};

const safeRelative = (root: string, absolute: string): string => {
  const relative = path.relative(root, absolute).replace(/\\/g, "/");
  return relative === "" ? "." : relative;
};

const authenticate = async (config: SftpConfig, username: string, password: string): Promise<AuthResult | null> => {
  const response = await fetch(`${config.panelUrl.replace(/\/$/, "")}/api/node-agent/sftp-auth`, {
    method: "POST",
    headers: { "content-type": "application/json", authorization: `Bearer ${config.credential}` },
    body: JSON.stringify({ nodeId: config.nodeId, username, password }),
    signal: AbortSignal.timeout(8000),
  });
  if (!response.ok) return null;
  const body = await response.json() as { serverId?: string };
  return body.serverId && /^[a-zA-Z0-9_-]+$/.test(body.serverId) ? { serverId: body.serverId } : null;
};

const attrsFromStat = (stat: fs.Stats): Record<string, number> => ({
  mode: stat.mode,
  size: stat.size,
  uid: stat.uid,
  gid: stat.gid,
  atime: Math.floor(stat.atimeMs / 1000),
  mtime: Math.floor(stat.mtimeMs / 1000),
});

const statusForError = (error: unknown): number => {
  const code = (error as NodeJS.ErrnoException)?.code;
  if (code === "ENOENT" || code === "ENOTDIR") return STATUS_CODE.NO_SUCH_FILE;
  if (code === "EACCES" || code === "EPERM" || code === "ELOOP") return STATUS_CODE.PERMISSION_DENIED;
  return STATUS_CODE.FAILURE;
};

const handleSftp = (sftp: any, root: string): void => {
  const handles = new Map<string, OpenHandle>();
  const token = (): Buffer => crypto.randomBytes(24);
  const keyOf = (raw: Buffer): string => raw.toString("hex");
  const getHandle = (raw: Buffer): OpenHandle => {
    const item = handles.get(keyOf(raw));
    if (!item) throw new Error("Invalid SFTP handle");
    return item;
  };
  const fail = (requestId: number, error: unknown): void => {
    sftp.status(requestId, statusForError(error), String((error as Error)?.message || "SFTP operation failed"));
  };

  sftp.on("OPEN", (requestId: number, filename: string, flags: number, attrs: { mode?: number }) => {
    try {
      const target = jailedPath(root, filename);
      const descriptor = fs.openSync(target, sftpOpenFlagsToNode(flags), Number(attrs?.mode) || 0o640);
      const handle = token();
      handles.set(keyOf(handle), { kind: "file", value: descriptor });
      sftp.handle(requestId, handle);
    } catch (error) {
      fail(requestId, error);
    }
  });

  sftp.on("READ", (requestId: number, rawHandle: Buffer, offset: number, length: number) => {
    try {
      const item = getHandle(rawHandle);
      if (item.kind !== "file") throw new Error("Handle is not a file");
      const buffer = Buffer.alloc(Math.max(0, Math.min(Number(length) || 0, 4 * 1024 * 1024)));
      const bytesRead = fs.readSync(item.value, buffer, 0, buffer.length, Number(offset));
      if (bytesRead === 0) return sftp.status(requestId, STATUS_CODE.EOF);
      sftp.data(requestId, buffer.subarray(0, bytesRead));
    } catch (error) {
      fail(requestId, error);
    }
  });

  sftp.on("WRITE", (requestId: number, rawHandle: Buffer, offset: number, data: Buffer) => {
    try {
      const item = getHandle(rawHandle);
      if (item.kind !== "file") throw new Error("Handle is not a file");
      fs.writeSync(item.value, data, 0, data.length, Number(offset));
      sftp.status(requestId, STATUS_CODE.OK);
    } catch (error) {
      fail(requestId, error);
    }
  });

  sftp.on("FSTAT", (requestId: number, rawHandle: Buffer) => {
    try {
      const item = getHandle(rawHandle);
      if (item.kind !== "file") throw new Error("Handle is not a file");
      sftp.attrs(requestId, attrsFromStat(fs.fstatSync(item.value)));
    } catch (error) {
      fail(requestId, error);
    }
  });

  sftp.on("CLOSE", (requestId: number, rawHandle: Buffer) => {
    try {
      const key = keyOf(rawHandle);
      const item = getHandle(rawHandle);
      if (item.kind === "file") fs.closeSync(item.value);
      else item.value.closeSync();
      handles.delete(key);
      sftp.status(requestId, STATUS_CODE.OK);
    } catch (error) {
      fail(requestId, error);
    }
  });

  sftp.on("OPENDIR", (requestId: number, directory: string) => {
    try {
      const target = jailedPath(root, directory);
      const handle = token();
      handles.set(keyOf(handle), { kind: "dir", value: fs.opendirSync(target), directoryPath: target });
      sftp.handle(requestId, handle);
    } catch (error) {
      fail(requestId, error);
    }
  });

  sftp.on("READDIR", (requestId: number, rawHandle: Buffer) => {
    try {
      const item = getHandle(rawHandle);
      if (item.kind !== "dir") throw new Error("Handle is not a directory");
      const entry = item.value.readSync();
      if (!entry) return sftp.status(requestId, STATUS_CODE.EOF);
      const target = jailedPath(root, path.join(item.directoryPath, entry.name));
      const stat = fs.lstatSync(target);
      const name = entry.name;
      sftp.name(requestId, [{ filename: name, longname: name, attrs: attrsFromStat(stat) }]);
    } catch (error) {
      fail(requestId, error);
    }
  });

  const statPath = (requestId: number, requestedPath: string, follow: boolean): void => {
    try {
      const target = jailedPath(root, requestedPath);
      const stat = follow ? fs.statSync(target) : fs.lstatSync(target);
      sftp.attrs(requestId, attrsFromStat(stat));
    } catch (error) {
      fail(requestId, error);
    }
  };
  sftp.on("STAT", (requestId: number, requestedPath: string) => statPath(requestId, requestedPath, true));
  sftp.on("LSTAT", (requestId: number, requestedPath: string) => statPath(requestId, requestedPath, false));

  sftp.on("REALPATH", (requestId: number, requestedPath: string) => {
    try {
      const target = jailedPath(root, requestedPath);
      const relative = safeRelative(root, target);
      const virtualAbsolute = relative === "." ? "/" : `/${relative}`;
      sftp.name(requestId, [{ filename: virtualAbsolute, longname: virtualAbsolute }]);
    } catch (error) {
      fail(requestId, error);
    }
  });

  sftp.on("REMOVE", (requestId: number, requestedPath: string) => {
    try {
      const target = jailedPath(root, requestedPath);
      if (fs.lstatSync(target).isDirectory()) throw new Error("Directory removal requires RMDIR");
      fs.unlinkSync(target);
      sftp.status(requestId, STATUS_CODE.OK);
    } catch (error) {
      fail(requestId, error);
    }
  });

  sftp.on("RMDIR", (requestId: number, requestedPath: string) => {
    try {
      fs.rmdirSync(jailedPath(root, requestedPath));
      sftp.status(requestId, STATUS_CODE.OK);
    } catch (error) {
      fail(requestId, error);
    }
  });

  sftp.on("MKDIR", (requestId: number, requestedPath: string, attrs: { mode?: number }) => {
    try {
      fs.mkdirSync(jailedPath(root, requestedPath), { recursive: false, mode: Number(attrs?.mode) || 0o750 });
      sftp.status(requestId, STATUS_CODE.OK);
    } catch (error) {
      fail(requestId, error);
    }
  });

  sftp.on("RENAME", (requestId: number, oldPath: string, newPath: string) => {
    try {
      fs.renameSync(jailedPath(root, oldPath), jailedPath(root, newPath));
      sftp.status(requestId, STATUS_CODE.OK);
    } catch (error) {
      fail(requestId, error);
    }
  });

  sftp.on("close", () => {
    for (const item of handles.values()) {
      try {
        if (item.kind === "file") fs.closeSync(item.value);
        else item.value.closeSync();
      } catch {
        // The connection may close while a client is releasing a handle.
      }
    }
    handles.clear();
  });
};

export const startSftpServer = (config: SftpConfig): any => {
  const server = new SSHServer({ hostKeys: [fs.readFileSync(config.hostKey)], ident: "SNCK-Panel-SFTP" }, (client: any) => {
    let root: string | null = null;
    client.on("authentication", async (ctx: any) => {
      if (ctx.method !== "password") return ctx.reject(["password"]);
      try {
        const result = await authenticate(config, ctx.username, ctx.password);
        if (!result) return ctx.reject();
        root = safeServerRoot(config.serversDir, result.serverId);
        ctx.accept();
      } catch {
        ctx.reject();
      }
    }).on("ready", () => client.on("session", (accept: any) => {
      const session = accept();
      session.on("sftp", (acceptSftp: any) => {
        const channel = acceptSftp();
        if (root) handleSftp(channel, root);
        else channel.end();
      });
    }));
  });
  server.listen(config.port ?? 2022, config.host || "0.0.0.0");
  return server;
};
