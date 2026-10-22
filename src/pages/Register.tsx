import React, { useState } from "react";
import { Eye, EyeOff, LockKeyhole, UserRound } from "lucide-react";
import axios from "axios";
import { Link, useNavigate } from "react-router-dom";
import { useSettings } from "../context/SettingsContext";
import AuthExperience from "./AuthExperience";
import "./Login.css";

export default function Register() {
  const [username, setUsername] = useState(""); const [password, setPassword] = useState(""); const [confirmPassword, setConfirmPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false); const [showConfirmPassword, setShowConfirmPassword] = useState(false); const [error, setError] = useState(""); const [success, setSuccess] = useState(""); const [isLoading, setIsLoading] = useState(false);
  const { panelName, panelLogo, enableRegistration } = useSettings(); const navigate = useNavigate();
  const handleRegister = async (event: React.FormEvent) => {
    event.preventDefault(); setError(""); setSuccess(""); const cleanUsername = username.trim().toLowerCase();
    if (enableRegistration === false) { setError("User registration is currently disabled by the panel administrator."); return; }
    if (!/^[a-z0-9_.-]{3,32}$/.test(cleanUsername)) { setError("Username must be 3–32 characters using lowercase letters, numbers, dots, underscores, or hyphens."); return; }
    if (!password) { setError("Enter a password to continue."); return; }
    if (password !== confirmPassword) { setError("Passwords do not match."); return; }
    if (password.length < 8) { setError("Password must be at least 8 characters."); return; }
    setIsLoading(true);
    try { await axios.post("/api/auth/register", { username: cleanUsername, password, confirmPassword }, { timeout: 15000 }); setSuccess("Account created successfully. Redirecting to sign in..."); window.setTimeout(() => navigate("/login", { replace: true }), 1100); }
    catch (err: any) { setError(err.code === "ECONNABORTED" ? "The panel took too long to respond." : err.response?.data?.error || "Registration failed."); }
    finally { setIsLoading(false); }
  };
  return <AuthExperience mode="auth" panelName={panelName} panelLogo={panelLogo} eyebrow="Create your access" title="Create an account" description="Set up a secure account to start managing your infrastructure." footer={<>Already have an account? <Link to="/login">Sign in</Link></>}>
    {error && <div className="auth-alert" role="alert">{error}</div>}{success && <div className="auth-success" role="status">{success}</div>}
    <form onSubmit={handleRegister} className="auth-form" noValidate aria-busy={isLoading}>
      <label className="auth-field"><span>Username</span><div className="auth-input"><UserRound size={17} aria-hidden="true" /><input type="text" name="username" required autoComplete="username" placeholder="Choose a username" value={username} onChange={(event) => setUsername(event.target.value.toLowerCase())} /></div></label>
      <div className="auth-form-grid"><label className="auth-field"><span>Password</span><div className="auth-input"><LockKeyhole size={17} aria-hidden="true" /><input type={showPassword ? "text" : "password"} required minLength={8} autoComplete="new-password" placeholder="At least 8 characters" value={password} onChange={(event) => setPassword(event.target.value)} /><button type="button" className="auth-password-toggle" onClick={() => setShowPassword((visible) => !visible)} aria-label="Toggle password visibility">{showPassword ? <EyeOff size={16} /> : <Eye size={16} />}</button></div></label><label className="auth-field"><span>Confirm password</span><div className="auth-input"><LockKeyhole size={17} aria-hidden="true" /><input type={showConfirmPassword ? "text" : "password"} required minLength={8} autoComplete="new-password" placeholder="Repeat password" value={confirmPassword} onChange={(event) => setConfirmPassword(event.target.value)} /><button type="button" className="auth-password-toggle" onClick={() => setShowConfirmPassword((visible) => !visible)} aria-label="Toggle confirmation visibility">{showConfirmPassword ? <EyeOff size={16} /> : <Eye size={16} />}</button></div></label></div>
      <p className="auth-helper">Use at least 8 characters. Your password is never displayed or stored in plain text.</p><button type="submit" className="auth-submit" disabled={isLoading || !!success || enableRegistration === false}><span>{isLoading ? "Creating account..." : "Create account"}</span><span aria-hidden="true">→</span></button>
    </form>
  </AuthExperience>;
}
