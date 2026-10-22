import { Request, Response } from "express";
import bcrypt from "bcryptjs";
import jwt from "jsonwebtoken";
import crypto from "crypto";
import { readJSON, writeJSON } from "../services/db.js";
import { getJwtSecret, isStrongPassword } from "../services/security.js";

const JWT_SECRET = getJwtSecret();
let ownerSetupInProgress = false;

const isValidEmail = (email: unknown): email is string =>
  typeof email === "string" && email.length <= 254 && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

const isValidUsername = (username: unknown): username is string =>
  typeof username === "string" && /^[a-z0-9_.-]{3,32}$/.test(username);

export const setupStatus = async (_req: Request, res: Response) => {
  const users = await readJSON("users.json") || [];
  res.json({ setupRequired: !Array.isArray(users) || users.length === 0 });
};

export const setupOwner = async (req: Request, res: Response) => {
  if (ownerSetupInProgress) {
    res.status(409).json({ error: "Owner setup is already in progress" });
    return;
  }

  ownerSetupInProgress = true;
  try {
    const users = await readJSON("users.json") || [];
    if (!Array.isArray(users)) {
      res.status(500).json({ error: "User database is invalid" });
      return;
    }
    if (users.length > 0) {
      res.status(409).json({ error: "Owner setup has already been completed" });
      return;
    }

    const { username, email, password, confirmPassword } = req.body || {};
    const cleanUsername = typeof username === "string" ? username.trim().toLowerCase() : "";
    const cleanEmail = typeof email === "string" ? email.trim().toLowerCase() : "";

    if (!isValidUsername(cleanUsername)) {
      res.status(400).json({ error: "Username must be 3-32 characters and contain only letters, numbers, dots, underscores, or hyphens" });
      return;
    }
    if (!isValidEmail(cleanEmail)) {
      res.status(400).json({ error: "A valid email address is required" });
      return;
    }
    if (!isStrongPassword(password)) {
      res.status(400).json({ error: "Password must be 8-256 characters" });
      return;
    }
    if (password !== confirmPassword) {
      res.status(400).json({ error: "Passwords do not match" });
      return;
    }

    const owner = {
      id: `user-${crypto.randomUUID()}`,
      username: cleanUsername,
      email: cleanEmail,
      password: await bcrypt.hash(password, 12),
      role: "owner",
      passwordVersion: 0,
      createdAt: new Date().toISOString()
    };
    await writeJSON("users.json", [owner]);
    res.status(201).json({ success: true, message: "Owner account created. Please sign in.", user: { id: owner.id, username: owner.username, email: owner.email, role: owner.role } });
  } finally {
    ownerSetupInProgress = false;
  }
};

export const register = async (req: Request, res: Response) => {
  const settings = await readJSON("settings.json") || {};
  if (settings.enableRegistration === false) {
    res.status(403).json({ error: "User registration is currently disabled by administrator." });
    return;
  }

  const { username, email, password, confirmPassword } = req.body;
  const cleanEmail = typeof email === "string" ? email.trim().toLowerCase() : "";

  if (!username || !password || !confirmPassword) {
    res.status(400).json({ error: "Username, password, and confirm password are required" });
    return;
  }

  const cleanUsername = typeof username === "string" ? username.trim().toLowerCase() : "";
  if (!isValidUsername(cleanUsername)) {
    res.status(400).json({ error: "Username must be 3-32 characters and use lowercase letters, numbers, dots, underscores, or hyphens" });
    return;
  }

  if (cleanEmail && !isValidEmail(cleanEmail)) {
    res.status(400).json({ error: "Please enter a valid email address" });
    return;
  }

  if (!isStrongPassword(password)) {
    res.status(400).json({ error: "Password must be 8-256 characters" });
    return;
  }

  if (password !== confirmPassword) {
    res.status(400).json({ error: "Passwords do not match" });
    return;
  }

  const users = await readJSON("users.json") || [];
  if (!Array.isArray(users) || users.length === 0) {
    res.status(403).json({ error: "First-run setup is required. Create the Owner account at /setup.", setupRequired: true });
    return;
  }
  const existingUser = users.find((u: any) => u.username.toLowerCase() === cleanUsername.toLowerCase());

  if (existingUser) {
    res.status(400).json({ error: "Username is already taken" });
    return;
  }

  const { writeJSON } = await import("../services/db.js");
  const hashedPassword = await bcrypt.hash(password, 12);
  
  const newUser = {
    id: `user-${crypto.randomUUID()}`,
    username: cleanUsername,
    password: hashedPassword,
    ...(cleanEmail ? { email: cleanEmail } : {}),
    role: "user",
    passwordVersion: 0
  };

  users.push(newUser);
  await writeJSON("users.json", users);

  res.status(201).json({
    message: "User registered successfully",
    user: { id: newUser.id, username: newUser.username, role: newUser.role }
  });
};

export const login = async (req: Request, res: Response) => {
  const { username, password } = req.body;

  if (!username || !password) {
    res.status(400).json({ error: "Username and password required" });
    return;
  }

  const users = await readJSON("users.json") || [];
  if (!Array.isArray(users) || users.length === 0) {
    res.status(403).json({ error: "First-run setup is required. Create the Owner account at /setup.", setupRequired: true });
    return;
  }

  // Dev auto-provisioning (creates a first admin account on empty installs) is only
  // ever active when explicitly opted into via DEV_AUTO_LOGIN=true, and only outside
  // production. It must NEVER be inferred from PORT, since that previously caused
  // real deployments (any run not launched through ecosystem.config.cjs, e.g. a bare
  // `node dist/server.cjs` or a misconfigured .env) to silently skip password
  // verification for every existing user.
  const devAutoLoginEnabled = process.env.DEV_AUTO_LOGIN === "true" && process.env.NODE_ENV !== "production";

  if (devAutoLoginEnabled) {
    let user = users.find((u: any) => u.username === username);

    if (!user) {
      const { writeJSON } = await import("../services/db.js");
      const hashedPassword = await bcrypt.hash(password, 12);
      user = {
        id: `dev-user-${crypto.randomUUID()}`,
        username,
        password: hashedPassword,
        role: "user",
        passwordVersion: 0
      };
      users.push(user);
      await writeJSON("users.json", users);
    } else {
      // Even in dev auto-login mode, an existing account must still authenticate
      // with its real password. Only brand-new usernames get auto-provisioned.
      const isMatch = await bcrypt.compare(password, user.password || "");
      if (!isMatch) {
        res.status(401).json({ error: "Invalid credentials" });
        return;
      }
    }

    const role = user.role || "user";
    const token = jwt.sign(
      { id: user.id, username: user.username, role, passwordVersion: user.passwordVersion || 0 },
      JWT_SECRET,
      { expiresIn: "7d" }
    );

    res.json({ token, user: { id: user.id, username: user.username, role } });
    return;
  }

  const loginIdentifier = String(username).trim().toLowerCase();
  const user = users.find((u: any) => (typeof u.username === "string" && u.username.toLowerCase() === loginIdentifier) || (typeof u.email === "string" && u.email.toLowerCase() === loginIdentifier));

  if (!user) {
    res.status(401).json({ error: "Invalid credentials" });
    return;
  }

  const isMatch = await bcrypt.compare(password, user.password);

  if (!isMatch) {
    res.status(401).json({ error: "Invalid credentials" });
    return;
  }

  const role = user.role || "user";
  const token = jwt.sign({ id: user.id, username: user.username, role, passwordVersion: user.passwordVersion || 0 }, JWT_SECRET, { expiresIn: "7d" });

  res.json({ token, user: { id: user.id, username: user.username, role } });
};

export const logout = (req: Request, res: Response) => {
  res.json({ message: "Logged out" });
};

export const getMe = async (req: Request, res: Response) => {
  const reqUser = (req as any).user;
  if (reqUser && reqUser.id !== "temp-admin") {
    const users = await readJSON("users.json") || [];
    const dbUser = users.find((u: any) => u.id === reqUser.id);
    if (dbUser) {
      return res.json({
        user: {
          ...reqUser,
          googleId: dbUser.googleId || null,
          isGoogleUser: !!(dbUser.googleId || !dbUser.password)
        }
      });
    }
  }
  res.json({ user: reqUser });
};

export const getUsers = async (req: Request, res: Response) => {
  const users = await readJSON("users.json") || [];
  res.json(users.map((u: any) => ({ id: u.id, username: u.username, role: u.role, isGoogleUser: !!u.googleId })));
};

export const changeUsername = async (req: Request, res: Response) => {
  const reqUser = (req as any).user;
  const { newUsername } = req.body;

  if (!newUsername || typeof newUsername !== "string" || newUsername.trim().length < 3) {
    return res.status(400).json({ error: "Username must be at least 3 characters long." });
  }

  const cleanUsername = newUsername.trim();

  if (reqUser.id === "temp-admin") {
    return res.status(400).json({ error: "Cannot change username of default admin account." });
  }

  const users = await readJSON("users.json") || [];
  const userIndex = users.findIndex((u: any) => u.id === reqUser.id);

  if (userIndex === -1) {
    return res.status(404).json({ error: "User not found" });
  }

  if (!users[userIndex].googleId) {
    return res.status(400).json({ error: "Username change is only available for Google authenticated accounts." });
  }

  const existingUser = users.find((u: any) => u.id !== reqUser.id && u.username && u.username.toLowerCase() === cleanUsername.toLowerCase());
  if (existingUser) {
    return res.status(400).json({ error: `Username '${cleanUsername}' is already taken.` });
  }

  users[userIndex].username = cleanUsername;
  await writeJSON("users.json", users);

  res.json({ success: true, username: cleanUsername });
};

export const changePassword = async (req: Request, res: Response) => {
  const reqUser = (req as any).user;
  const { oldPassword, newPassword } = req.body;
  
  if (reqUser.id === "temp-admin") {
    return res.status(400).json({ error: "Cannot change password of default admin account. Create a new admin user instead." });
  }

  const users = await readJSON("users.json") || [];
  const userIndex = users.findIndex((u: any) => u.id === reqUser.id);
  
  if (userIndex === -1) {
    return res.status(404).json({ error: "User not found" });
  }

  if (users[userIndex].googleId || !users[userIndex].password) {
    return res.status(400).json({ error: "Password change is disabled for Google Auth accounts." });
  }

  if (!newPassword || newPassword.length < 8) {
    return res.status(400).json({ error: "New password must be at least 8 characters" });
  }
  
  const isMatch = await bcrypt.compare(oldPassword || "", users[userIndex].password);
  if (!isMatch) {
    return res.status(401).json({ error: "Incorrect old password" });
  }

  // Use dynamic import for writeJSON since it's in another file
  const { writeJSON } = await import("../services/db.js");
  const hashedPassword = await bcrypt.hash(newPassword, 10);
  
  users[userIndex].password = hashedPassword;
  users[userIndex].passwordVersion = (users[userIndex].passwordVersion || 0) + 1;
  await writeJSON("users.json", users);
  
  res.json({ success: true });
};

export const googleLogin = async (req: Request, res: Response) => {
  const { idToken, email, googleId, name, photoURL } = req.body;

  const settings = await readJSON("settings.json") || {};
  if (settings.enableGoogleLogin === false) {
    res.status(403).json({ error: "Google Login is disabled on this panel." });
    return;
  }
  if (!settings.firebaseProjectId || typeof idToken !== "string" || !idToken) {
    res.status(400).json({ error: "Google Login is not fully configured or the identity token is missing." });
    return;
  }

  let verifiedIdentity: any;
  try {
    const verification = await fetch(`https://oauth2.googleapis.com/tokeninfo?id_token=${encodeURIComponent(idToken)}`);
    if (!verification.ok) throw new Error("Google token rejected");
    verifiedIdentity = await verification.json();
  } catch {
    res.status(401).json({ error: "Google identity verification failed." });
    return;
  }

  const verifiedEmail = typeof verifiedIdentity.email === "string" ? verifiedIdentity.email.toLowerCase() : "";
  const verifiedGoogleId = typeof verifiedIdentity.sub === "string" ? verifiedIdentity.sub : "";
  const audience = typeof verifiedIdentity.aud === "string" ? verifiedIdentity.aud : "";
  if (!verifiedEmail || verifiedIdentity.email_verified !== "true" || !verifiedGoogleId || audience !== settings.firebaseProjectId) {
    res.status(401).json({ error: "Google identity token is invalid for this panel." });
    return;
  }
  if ((email && email.toLowerCase() !== verifiedEmail) || (googleId && googleId !== verifiedGoogleId)) {
    res.status(401).json({ error: "Google identity did not match the supplied account." });
    return;
  }

  const cleanEmail = verifiedEmail;
  const cleanGoogleId = verifiedGoogleId;

  // Derive username from Gmail (e.g. jishnumondal32@gmail.com -> jishnumondal32)
  const emailPrefix = cleanEmail.split("@")[0].replace(/[^a-zA-Z0-9_.]/g, "");
  const baseUsername = emailPrefix || "user";

  const users = await readJSON("users.json") || [];
  if (!Array.isArray(users) || users.length === 0) {
    res.status(403).json({ error: "First-run setup is required. Create the Owner account at /setup.", setupRequired: true });
    return;
  }
  let user = users.find((u: any) => (u.email && u.email.toLowerCase() === cleanEmail) || (u.googleId && u.googleId === cleanGoogleId) || (u.username && u.username.toLowerCase() === baseUsername.toLowerCase()));

  if (!user) {
    const role = "user";

    const { writeJSON } = await import("../services/db.js");
    user = {
      id: `google-user-${crypto.randomUUID()}`,
      username: baseUsername,
      email: cleanEmail,
      googleId: cleanGoogleId,
      role,
      avatar: photoURL || "",
      passwordVersion: 0,
      createdAt: new Date().toISOString()
    };
    users.push(user);
    await writeJSON("users.json", users);
  } else {
    // Link email & googleId if missing
    let updated = false;
    if (!user.email) { user.email = cleanEmail; updated = true; }
    if (!user.googleId) { user.googleId = cleanGoogleId; updated = true; }
    if (photoURL && !user.avatar) { user.avatar = photoURL; updated = true; }
    if (updated) {
      const { writeJSON } = await import("../services/db.js");
      await writeJSON("users.json", users);
    }
  }

  const role = user.role || "user";
  const token = jwt.sign(
    { id: user.id, username: user.username, role, passwordVersion: user.passwordVersion || 0 },
    JWT_SECRET,
    { expiresIn: "7d" }
  );

  res.json({ 
    token, 
    user: { 
      id: user.id, 
      username: user.username, 
      role, 
      email: user.email, 
      avatar: user.avatar,
      googleId: user.googleId,
      isGoogleUser: true 
    } 
  });
};
