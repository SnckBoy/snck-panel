import express from "express";
import { login, logout, getMe, getUsers, changePassword, changeUsername, register, googleLogin, setupStatus, setupOwner } from "../controllers/auth.js";
import { requireAuth, requireAdmin } from "../middleware/auth.js";
import { rateLimit } from "../services/security.js";

const router = express.Router();

// Apply separate, conservative per-IP/path limits to credential and account-creation
// endpoints. This is process-local; multi-instance deployments need a shared store.
const authRateLimit = rateLimit(10, 15 * 60_000);
const registrationRateLimit = rateLimit(5, 60 * 60_000);

router.get("/setup-status", setupStatus);
router.post("/setup", registrationRateLimit, setupOwner);
router.post("/register", registrationRateLimit, register);
router.post("/login", authRateLimit, login);
router.post("/google", authRateLimit, googleLogin);
router.post("/logout", logout);
router.get("/me", requireAuth, getMe);
router.get("/users", requireAdmin, getUsers);
router.put("/password", requireAuth, authRateLimit, changePassword);
router.put("/username", requireAuth, authRateLimit, changeUsername);

export default router;
