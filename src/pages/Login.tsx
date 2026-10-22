import React, { useState } from "react";
import { Eye, EyeOff, LockKeyhole, Mail, UserRound } from "lucide-react";
import axios from "axios";
import { initializeApp, getApps, getApp } from "firebase/app";
import { getAuth, GoogleAuthProvider, signInWithPopup } from "firebase/auth";
import { Link, useNavigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import { useSettings } from "../context/SettingsContext";
import AuthExperience from "./AuthExperience";
import "./Login.css";

export default function Login() {
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState("");
  const [isLoading, setIsLoading] = useState(false);
  const { login } = useAuth();
  const { panelName, panelLogo, enableRegistration, enableGoogleLogin, firebaseApiKey, firebaseAuthDomain, firebaseProjectId, firebaseStorageBucket, firebaseMessagingSenderId, firebaseAppId } = useSettings();
  const navigate = useNavigate();

  const handleLogin = async (event: React.FormEvent) => {
    event.preventDefault();
    const cleanUsername = username.trim().toLowerCase();
    if (!cleanUsername || !password) { setError("Enter your username or email and password to continue."); return; }
    setIsLoading(true); setError("");
    try {
      const response = await axios.post("/api/auth/login", { username: cleanUsername, password }, { timeout: 15000 });
      if (!response.data?.token || !response.data?.user) throw new Error("The server returned an incomplete login response.");
      login(response.data.token, response.data.user); navigate("/", { replace: true });
    } catch (err: any) {
      setError(err.code === "ECONNABORTED" ? "The panel took too long to respond." : err.response?.data?.error || err.message || "Login failed. Check your credentials.");
    } finally { setIsLoading(false); }
  };

  const handleGoogleLogin = async () => {
    if (!firebaseApiKey || !firebaseProjectId) { setError("Google Login is not configured by the panel administrator yet."); return; }
    setIsLoading(true); setError("");
    try {
      const app = getApps().length === 0 ? initializeApp({ apiKey: firebaseApiKey, authDomain: firebaseAuthDomain, projectId: firebaseProjectId, storageBucket: firebaseStorageBucket, messagingSenderId: firebaseMessagingSenderId, appId: firebaseAppId }) : getApp();
      const result = await signInWithPopup(getAuth(app), new GoogleAuthProvider());
      const response = await axios.post("/api/auth/google", { idToken: await result.user.getIdToken() });
      login(response.data.token, response.data.user); navigate("/", { replace: true });
    } catch (err: any) {
      setError(err.code === "auth/popup-closed-by-user" ? "The Google Login window was closed." : err.response?.data?.error || err.message || "Google Authentication failed.");
    } finally { setIsLoading(false); }
  };

  return <AuthExperience panelName={panelName} panelLogo={panelLogo} eyebrow="Welcome back" title="Sign in to your panel" description={`Enter your credentials to continue to ${panelName}.`} footer={enableRegistration !== false ? <>New to {panelName}? <Link to="/register">Create an account</Link></> : undefined}>
    {error && <div className="auth-alert" role="alert">{error}</div>}
    <form onSubmit={handleLogin} className="auth-form" noValidate aria-busy={isLoading}>
      <label className="auth-field"><span>Username or Gmail</span><div className="auth-input"><UserRound size={17} aria-hidden="true" /><input type="text" name="username" required autoComplete="username" placeholder="username or you@gmail.com" value={username} onChange={(event) => setUsername(event.target.value)} /></div></label>
      <label className="auth-field"><span>Password</span><div className="auth-input"><LockKeyhole size={17} aria-hidden="true" /><input type={showPassword ? "text" : "password"} name="password" required autoComplete="current-password" placeholder="Your secure password" value={password} onChange={(event) => setPassword(event.target.value)} /><button type="button" className="auth-password-toggle" onClick={() => setShowPassword((visible) => !visible)} aria-label={showPassword ? "Hide password" : "Show password"}>{showPassword ? <EyeOff size={16} /> : <Eye size={16} />}</button></div></label>
      <button type="submit" className="auth-submit" disabled={isLoading}><span>{isLoading ? "Signing in..." : "Sign in"}</span><span aria-hidden="true">→</span></button>
    </form>
    {enableGoogleLogin && firebaseApiKey && firebaseProjectId && <><div className="auth-divider"><span>or continue with</span></div><button type="button" className="auth-provider" onClick={handleGoogleLogin} disabled={isLoading}><span className="auth-provider-mark">G</span> Continue with Google</button></>}
    <p className="auth-security"><Mail size={14} /> Sessions expire safely when you sign out.</p>
  </AuthExperience>;
}
