import express from "express";
import { getVersions, SUPPORTED_JAVA_VERSIONS } from "../services/docker.js";
import { requireAuth } from "../middleware/auth.js";
import os from "os";
import { exec } from "child_process";
import util from "util";
const execPromise = util.promisify(exec);
import { readJSON, writeJSON } from "../services/db.js";
import bcrypt from "bcryptjs";
import { isStrongPassword } from "../services/security.js";

const router = express.Router();

router.use(requireAuth);

router.get("/settings", async (_req, res) => {
  const settings = await readJSON("settings.json") || {};
  // Only expose non-secret presentation/service settings here.
  res.json({ panelName: settings.panelName, panelLogo: settings.panelLogo, theme: settings.theme,
    enablePlayit: settings.enablePlayit, enableTutorial: settings.enableTutorial,
    enableLoginAnimation: settings.enableLoginAnimation, enableRegistration: settings.enableRegistration,
    enableGoogleLogin: settings.enableGoogleLogin, freeService: settings.freeService || { enabled:false, ram:2, cpu:100, disk:5, maxServers:1, durationHours:168 } });
});

router.get("/versions", async (req, res) => {
  const type = (req.query.type as string) || "PAPER";
  const versions = await getVersions(type);
  res.json(versions);
});

router.get("/java-versions", (_req, res) => {
  res.json(SUPPORTED_JAVA_VERSIONS);
});

// Deprecated endpoint for backward compatibility
router.get("/paper-versions", async (req, res) => {
  const versions = await getVersions("PAPER");
  res.json(versions);
});

import { getDocker, isSandbox, mockState } from "../services/docker.js";

function getCpuUsage(): Promise<number> {
  return new Promise((resolve) => {
    const startCpus = os.cpus();
    setTimeout(() => {
      const endCpus = os.cpus();
      let totalIdle = 0, totalTick = 0;
      
      for (let i = 0, len = startCpus.length; i < len; i++) {
        const start = startCpus[i].times;
        const end = endCpus[i].times;
        
        const startTick = start.user + start.nice + start.sys + start.idle + start.irq;
        const endTick = end.user + end.nice + end.sys + end.idle + end.irq;
        
        const idle = end.idle - start.idle;
        const total = endTick - startTick;
        
        totalIdle += idle;
        totalTick += total;
      }
      
      const usage = 100 - ~~(100 * totalIdle / totalTick);
      resolve(usage);
    }, 100);
  });
}

router.get("/stats", async (req, res) => {
  let diskSpace = 0;
  try {
    const { stdout } = await execPromise("df -h /home");
    const lines = stdout.split("\n");
    if (lines.length > 1) {
      const parts = lines[1].trim().split(/\s+/);
      if (parts.length >= 5) {
        diskSpace = parseInt(parts[4].replace("%", "")) || 0;
      }
    }
  } catch (err) {}
  
  const totalMemory = os.totalmem();
  const freeMemory = os.freemem();
  
  let cpuUsage = await getCpuUsage();
  
  let activeContainers = 0;
  let totalContainers = 0;
  
  try {
    if (isSandbox) {
       totalContainers = Object.keys(mockState).length;
       activeContainers = Object.values(mockState).filter(v => v).length;
    } else {
       const docker = await getDocker();
       const containers = await docker.listContainers({ all: true });
       totalContainers = containers.length;
       activeContainers = containers.filter(c => c.State === 'running').length;
    }
  } catch (err) {
     // fallback
  }
  
  res.json({
    cpuUsage: cpuUsage,
    totalMemory,
    freeMemory,
    ramUsage: Math.round(((totalMemory - freeMemory) / totalMemory) * 100),
    diskUsage: diskSpace,
    activeContainers,
    totalContainers
  });
});

router.get("/users", async (req, res) => {
  const user = (req as any).user;
  if(user.role !== "admin" && user.role !== "owner") return res.status(403).json({ error: "Forbidden"});
  const users = await readJSON("users.json") || [];
  // never return passwords
  res.json(users.map((u: any) => ({ id: u.id, username: u.username, email: u.email || "", role: u.role || 'admin', isGoogleUser: !!u.googleId, createdAt: u.createdAt })));
});

router.post("/users", async (req, res) => {
  const user = (req as any).user;
  if(user.role !== "admin" && user.role !== "owner") return res.status(403).json({ error: "Forbidden"});
  const { username, password, role } = req.body;
  const cleanUsername = typeof username === "string" ? username.trim() : "";
  if (!cleanUsername || !password || !role) return res.status(400).json({ error: "Missing fields" });
  if (!/^[A-Za-z0-9_.-]{3,32}$/.test(cleanUsername)) return res.status(400).json({ error: "Invalid username" });
  if (!isStrongPassword(password)) return res.status(400).json({ error: "Password must be 8-256 characters" });
  if (role !== "user" && role !== "admin") return res.status(400).json({ error: "Only user or admin roles can be created here" });

  const users = await readJSON("users.json") || [];
  if (users.find((u: any) => u.username && u.username.toLowerCase() === cleanUsername.toLowerCase())) return res.status(400).json({ error: "Username taken" });

  const hashedPassword = await bcrypt.hash(password, 12);
  const newUserId = Date.now().toString();
  users.push({
    id: newUserId,
    username: cleanUsername,
    password: hashedPassword,
    role,
    createdAt: new Date().toISOString()
  });

  await writeJSON("users.json", users);
  res.json({ success: true, id: newUserId, username: cleanUsername, role });
});

router.put("/users/:id/role", async (req, res) => {
  const user = (req as any).user;
  if (user.role !== "owner") return res.status(403).json({ error: "Only the Owner can change user roles" });
  const requestedRole = String(req.body?.role || "").toLowerCase();
  if (requestedRole !== "user" && requestedRole !== "admin") return res.status(400).json({ error: "Role must be user or admin" });
  if (req.params.id === user.id) return res.status(400).json({ error: "The Owner account cannot be demoted" });
  const users = await readJSON("users.json") || [];
  const targetIndex = users.findIndex((candidate: any) => candidate.id === req.params.id);
  if (targetIndex === -1) return res.status(404).json({ error: "User not found" });
  if (users[targetIndex].role === "owner") return res.status(403).json({ error: "The Owner account cannot be changed" });
  users[targetIndex].role = requestedRole;
  await writeJSON("users.json", users);
  res.json({ success: true, id: users[targetIndex].id, role: requestedRole });
});

router.delete("/users/:id", async (req, res) => {
  const user = (req as any).user;
  if(user.role !== "admin" && user.role !== "owner") return res.status(403).json({ error: "Forbidden"});
  
  const users = await readJSON("users.json") || [];
  const target = users.find((u: any) => u.id === req.params.id);
  if (!target) return res.status(404).json({ error: "User not found" });
  if (target.id === user.id) return res.status(400).json({ error: "You cannot delete your own account" });
  if (target.role === "owner") return res.status(403).json({ error: "The Owner account cannot be deleted" });
  const remainingUsers = users.filter((u: any) => u.id !== req.params.id);
  await writeJSON("users.json", remainingUsers);
  res.json({ success: true });
});


router.put("/users/:id/password", async (req, res) => {
  const user = (req as any).user;
  if(user.role !== "admin" && user.role !== "owner") return res.status(403).json({ error: "Forbidden"});
  const { newPassword } = req.body;
  if (!isStrongPassword(newPassword)) {
    return res.status(400).json({ error: "Password must be 8-256 characters" });
  }
  
  const users = await readJSON("users.json") || [];
  const targetIndex = users.findIndex((u: any) => u.id === req.params.id);
  if (targetIndex === -1) return res.status(404).json({ error: "User not found" });
  
  if (users[targetIndex].id === "temp-admin") {
    return res.status(400).json({ error: "Cannot change password of default admin account." });
  }

  if (users[targetIndex].role === "owner" && user.role !== "owner") {
    return res.status(403).json({ error: "Only the Owner can change the Owner password" });
  }

  if (users[targetIndex].googleId || !users[targetIndex].password) {
    return res.status(400).json({ error: "Cannot change password for Google authenticated accounts." });
  }
  
  const bcrypt = await import("bcryptjs");
  const hashedPassword = await bcrypt.default.hash(newPassword, 12);
  users[targetIndex].password = hashedPassword;
  users[targetIndex].passwordVersion = (users[targetIndex].passwordVersion || 0) + 1;
  await writeJSON("users.json", users);
  res.json({ success: true });
});

router.put("/settings", async (req, res) => {
  const user = (req as any).user;
  if(user.role !== "admin" && user.role !== "owner") return res.status(403).json({ error: "Forbidden"});
  const { 
    panelName, panelLogo, panelBackgroundImage, panelBackgroundBlur, 
    enablePlayit, enableTutorial, enableLoginAnimation, enableRegistration, theme,
    appearance, accent, backgroundEffect, reducedMotion,
    enableGoogleLogin, firebaseApiKey, firebaseAuthDomain, firebaseProjectId,
    firebaseStorageBucket, firebaseMessagingSenderId, firebaseAppId, freeService
  } = req.body;
  const settings = await readJSON("settings.json") || {};
  if (panelName !== undefined) {
    settings.panelName = panelName || "Snck";
    try {
      const fs = await import("fs/promises");
      const path = await import("path");
      const targetPaths = [
        path.join(process.cwd(), "index.html"),
        path.join(process.cwd(), "dist", "index.html")
      ];
      for (const p of targetPaths) {
        try {
          let html = await fs.readFile(p, "utf-8");
          html = html.replace(/<title>.*<\/title>/i, `<title>${settings.panelName}</title>`);
          await fs.writeFile(p, html, "utf-8");
        } catch (e) {
          // Ignore if file doesn't exist
        }
      }
    } catch (err) {
      console.error("Error updating html title:", err);
    }
  }
  if (panelLogo !== undefined) settings.panelLogo = panelLogo;
  if (panelBackgroundImage !== undefined) settings.panelBackgroundImage = panelBackgroundImage;
  if (panelBackgroundBlur !== undefined) settings.panelBackgroundBlur = panelBackgroundBlur;
  if (enablePlayit !== undefined) settings.enablePlayit = enablePlayit;
  if (enableTutorial !== undefined) settings.enableTutorial = enableTutorial;
  if (enableLoginAnimation !== undefined) settings.enableLoginAnimation = enableLoginAnimation;
  if (enableRegistration !== undefined) settings.enableRegistration = enableRegistration;
  if (theme !== undefined && ["aurora", "midnight", "nebula", "cyber", "royal-purple", "ocean", "emerald", "crimson"].includes(theme)) settings.theme = theme;
  if (appearance !== undefined && ["dark", "light", "system"].includes(appearance)) settings.appearance = appearance;
  if (accent !== undefined && ["purple", "indigo", "blue", "cyan", "emerald", "pink", "red"].includes(accent)) settings.accent = accent;
  if (backgroundEffect !== undefined && ["none", "aurora", "animated-gradient", "grid", "nebula", "starfield"].includes(backgroundEffect)) settings.backgroundEffect = backgroundEffect;
  if (reducedMotion !== undefined) settings.reducedMotion = Boolean(reducedMotion);
  if (enableGoogleLogin !== undefined) settings.enableGoogleLogin = enableGoogleLogin;
  if (firebaseApiKey !== undefined) settings.firebaseApiKey = firebaseApiKey;
  if (firebaseAuthDomain !== undefined) settings.firebaseAuthDomain = firebaseAuthDomain;
  if (firebaseProjectId !== undefined) settings.firebaseProjectId = firebaseProjectId;
  if (firebaseStorageBucket !== undefined) settings.firebaseStorageBucket = firebaseStorageBucket;
  if (firebaseMessagingSenderId !== undefined) settings.firebaseMessagingSenderId = firebaseMessagingSenderId;
  if (firebaseAppId !== undefined) settings.firebaseAppId = firebaseAppId;
  if (freeService !== undefined) {
    if (user.role !== "owner") return res.status(403).json({ error: "Only the Owner can change free-service limits." });
    const enabled = Boolean(freeService?.enabled);
    const ram = Number(freeService?.ram);
    const cpu = Number(freeService?.cpu);
    const disk = Number(freeService?.disk);
    const maxServers = Number(freeService?.maxServers);
    const durationHours = Number(freeService?.durationHours);
    if (![ram,cpu,disk,maxServers,durationHours].every(Number.isFinite) || ram < 1 || ram > 256 || cpu < 1 || cpu > 6400 || disk < 1 || disk > 4096 || maxServers < 1 || maxServers > 100 || durationHours < 0 || durationHours > 87600) {
      return res.status(400).json({ error: "Invalid free-service limits. RAM 1–256 GB, CPU 1–6400%, disk 1–4096 GB, servers 1–100, duration 0–87600 hours." });
    }
    settings.freeService = { enabled, ram, cpu, disk, maxServers: Math.floor(maxServers), durationHours: Math.floor(durationHours) };
  }
  await writeJSON("settings.json", settings);
  req.app.get("io")?.emit("settings_updated");
  res.json({ success: true });
});

router.post("/update", async (req, res) => {
  const user = (req as any).user;
  if(user.role !== "admin" && user.role !== "owner") return res.status(403).json({ error: "Forbidden"});

  // Broadcast to all clients to refresh in a few seconds
  const io = req.app.get("io");
  if (io) {
    io.emit("system_update_started");
  }

  res.json({ success: true, message: "Update process started" });

  const { exec } = await import("child_process");
  setTimeout(() => {
    exec("bash update.sh", (error, stdout, stderr) => {
      console.log(`Update stdout: ${stdout}`);
      console.error(`Update stderr: ${stderr}`);
    });
  }, 1000);
});





export default router;
