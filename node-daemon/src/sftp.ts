import fs from "node:fs";
import path from "node:path";
import crypto from "node:crypto";
import ssh2 from "ssh2";

const SSHServer: any = (ssh2 as any).Server;

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

export const safeServerRoot = (serversDir: string, serverId: string): string => {
  if (!/^[a-zA-Z0-9_-]+$/.test(serverId)) throw new Error("Invalid server id");
  const root = path.resolve(serversDir, serverId);
  const base = path.resolve(serversDir);
  if (!root.startsWith(`${base}${path.sep}`)) throw new Error("Invalid server root");
  fs.mkdirSync(root, { recursive: true, mode: 0o750 });
  return root;
};

export const jailedPath = (root: string, relative: string): string => {
  const clean = String(relative || ".").replace(/\\/g, "/");
  if (clean.includes("\0") || clean.startsWith("/")) throw new Error("Path escapes SFTP jail");
  const target = path.resolve(root, clean);
  if (target !== root && !target.startsWith(`${root}${path.sep}`)) throw new Error("Path escapes SFTP jail");
  let probe = target;
  while (!fs.existsSync(probe) && probe !== path.dirname(probe)) probe = path.dirname(probe);
  const realRoot = fs.realpathSync(root);
  const realProbe = fs.realpathSync(probe);
  if (realProbe !== realRoot && !realProbe.startsWith(`${realRoot}${path.sep}`)) throw new Error("Symlink escapes SFTP jail");
  if (fs.existsSync(target) && fs.lstatSync(target).isSymbolicLink()) throw new Error("Symlinks are not allowed");
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

const handleSftp = (stream: any, root: string): void => {
  const handles = new Map<string, { kind: "file" | "dir"; value: number | fs.Dir }>();
  const handle = (value: number | fs.Dir, kind: "file" | "dir"): Buffer => {
    const token = crypto.randomBytes(16).toString("hex");
    handles.set(token, { kind, value });
    return Buffer.from(token);
  };
  const get = (raw: Buffer): { kind: "file" | "dir"; value: number | fs.Dir } | null => handles.get(raw.toString()) || null;
  const close = (raw: Buffer): void => {
    const item = handles.get(raw.toString());
    if (!item) return;
    if (item.kind === "file") fs.closeSync(item.value as number); else (item.value as fs.Dir).closeSync();
    handles.delete(raw.toString());
  };
  const fail = (req: any, error: any): void => req.status(stream.STATUS_CODE.FAILURE, String(error?.message || "SFTP operation failed"));
  stream.on("REALPATH", (req: any) => { try { const target = jailedPath(root, req.path); req.name(0, safeRelative(root, target), ""); } catch (e) { fail(req, e); } });
  stream.on("STAT", (req: any) => { try { const stat = fs.lstatSync(jailedPath(root, req.path)); req.attrs(0, { mode: stat.mode, size: stat.size, uid: stat.uid, gid: stat.gid, atime: stat.atimeMs / 1000, mtime: stat.mtimeMs / 1000 }); } catch (e) { fail(req, e); } });
  stream.on("LSTAT", (req: any) => { try { const stat = fs.lstatSync(jailedPath(root, req.path)); req.attrs(0, { mode: stat.mode, size: stat.size, uid: stat.uid, gid: stat.gid, atime: stat.atimeMs / 1000, mtime: stat.mtimeMs / 1000 }); } catch (e) { fail(req, e); } });
  stream.on("OPEN", (req: any) => { try { const target = jailedPath(root, req.filename); const flags = req.flags & 2 ? "w+" : req.flags & 1 ? "r+" : "r"; req.handle(handle(fs.openSync(target, flags, 0o640), "file")); } catch (e) { fail(req, e); } });
  stream.on("READ", (req: any) => { try { const item = get(req.handle); if (!item || item.kind !== "file") throw new Error("Invalid file handle"); const buffer = Buffer.alloc(Math.min(req.length, 4 * 1024 * 1024)); const bytes = fs.readSync(item.value as number, buffer, 0, buffer.length, req.offset); req.data(buffer.subarray(0, bytes), 0); } catch (e) { fail(req, e); } });
  stream.on("WRITE", (req: any) => { try { const item = get(req.handle); if (!item || item.kind !== "file") throw new Error("Invalid file handle"); fs.writeSync(item.value as number, req.data, 0, req.data.length, req.offset); req.status(0); } catch (e) { fail(req, e); } });
  stream.on("CLOSE", (req: any) => { try { close(req.handle); req.status(0); } catch (e) { fail(req, e); } });
  stream.on("OPENDIR", (req: any) => { try { req.handle(handle(fs.opendirSync(jailedPath(root, req.path)), "dir")); } catch (e) { fail(req, e); } });
  stream.on("READDIR", (req: any) => { try { const item = get(req.handle); if (!item || item.kind !== "dir") throw new Error("Invalid directory handle"); const entry = (item.value as fs.Dir).readSync(); if (!entry) return req.end(); const target = path.join(root, safeRelative(root, path.join(root, entry.name))); const stat = fs.lstatSync(jailedPath(root, safeRelative(root, target))); req.file(0, { filename: entry.name, longname: entry.name, attrs: { mode: stat.mode, size: stat.size, uid: stat.uid, gid: stat.gid, atime: stat.atimeMs / 1000, mtime: stat.mtimeMs / 1000 } }); } catch (e) { fail(req, e); } });
  stream.on("REMOVE", (req: any) => { try { const target = jailedPath(root, req.path); if (fs.lstatSync(target).isDirectory()) throw new Error("Directory removal requires RMDIR"); fs.unlinkSync(target); req.status(0); } catch (e) { fail(req, e); } });
  stream.on("RMDIR", (req: any) => { try { fs.rmdirSync(jailedPath(root, req.path)); req.status(0); } catch (e) { fail(req, e); } });
  stream.on("MKDIR", (req: any) => { try { fs.mkdirSync(jailedPath(root, req.path), { recursive: false, mode: 0o750 }); req.status(0); } catch (e) { fail(req, e); } });
  stream.on("RENAME", (req: any) => { try { fs.renameSync(jailedPath(root, req.oldPath), jailedPath(root, req.newPath)); req.status(0); } catch (e) { fail(req, e); } });
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
      } catch { ctx.reject(); }
    }).on("ready", () => client.on("session", (accept: any) => {
      const session = accept();
      session.on("sftp", (acceptSftp: any) => { const stream = acceptSftp(); if (root) handleSftp(stream, root); else stream.end(); });
    }));
  });
  server.listen(config.port || 2022, config.host || "0.0.0.0");
  return server;
};
