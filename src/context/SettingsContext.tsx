import React, { createContext, useContext, useEffect, useMemo, useState } from "react";
import axios from "axios";
import { io } from "socket.io-client";

const VALID_APPEARANCES = new Set(["dark"]);
const VALID_THEMES = new Set(["aurora", "midnight", "nebula", "cyber", "royal-purple", "ocean", "emerald", "crimson"]);
const VALID_ACCENTS = new Set(["purple", "indigo", "blue", "cyan", "emerald", "pink", "red"]);
const VALID_BACKGROUND_EFFECTS = new Set(["none", "aurora", "animated-gradient", "grid", "nebula", "starfield"]);

const safeChoice = (value: unknown, allowed: Set<string>, fallback: string) =>
  typeof value === "string" && allowed.has(value) ? value : fallback;

const getSystemAppearance = () => "dark";

export const SettingsContext = createContext<any>(null);

export const SettingsProvider = ({ children }: { children: React.ReactNode }) => {
  const [panelName, setPanelName] = useState<string>("Snck");
  const [panelLogo, setPanelLogo] = useState<string>("");
  const [panelBackgroundImage, setPanelBackgroundImage] = useState<string>("");
  const [panelBackgroundBlur, setPanelBackgroundBlur] = useState<number>(10);
  const [enablePlayit, setEnablePlayit] = useState<boolean>(false);
  const [enableTutorial, setEnableTutorial] = useState<boolean>(true);
  const [enableLoginAnimation, setEnableLoginAnimation] = useState<boolean>(true);
  const [enableRegistration, setEnableRegistration] = useState<boolean>(true);
  const [theme, setTheme] = useState<string>("aurora");
  const [themeConfig, setThemeConfig] = useState<any>(null);
  const [appearance, setAppearance] = useState<string>("dark");

  const setDarkAppearance = () => setAppearance("dark");
  const [accent, setAccent] = useState<string>("purple");
  const [backgroundEffect, setBackgroundEffect] = useState<string>("aurora");
  const [reducedMotion, setReducedMotion] = useState<boolean>(false);
  const [enableGoogleLogin, setEnableGoogleLogin] = useState<boolean>(false);
  const [firebaseApiKey, setFirebaseApiKey] = useState<string>("");
  const [firebaseAuthDomain, setFirebaseAuthDomain] = useState<string>("");
  const [firebaseProjectId, setFirebaseProjectId] = useState<string>("");
  const [firebaseStorageBucket, setFirebaseStorageBucket] = useState<string>("");
  const [firebaseMessagingSenderId, setFirebaseMessagingSenderId] = useState<string>("");
  const [firebaseAppId, setFirebaseAppId] = useState<string>("");

  const applyVisualSettings = () => {
    if (typeof document === "undefined") return;

    const root = document.documentElement;
    const resolvedAppearance = "dark";
    const resolvedTheme = safeChoice(theme, VALID_THEMES, "aurora");
    const resolvedAccent = safeChoice(accent, VALID_ACCENTS, "purple");
    const resolvedBackground = safeChoice(backgroundEffect, VALID_BACKGROUND_EFFECTS, "aurora");

    root.dataset.theme = resolvedAppearance;
    root.dataset.themePreset = resolvedTheme;
    root.dataset.appearance = resolvedAppearance;
    root.dataset.accent = resolvedAccent;
    root.dataset.backgroundEffect = resolvedBackground;
    root.classList.toggle("reduce-motion", reducedMotion);
    root.style.setProperty("--accent-color", `var(--accent-${resolvedAccent})`);
    if (themeConfig && typeof themeConfig === "object") {
      const surfaceOpacity = Math.max(55, Math.min(100, Number(themeConfig.surfaceOpacity) || 100));
      const variables: Record<string, string> = {
        "--snx-primary": themeConfig.primary, "--snx-secondary": themeConfig.secondary,
        "--snx-accent": themeConfig.accent, "--snx-primary-soft": `color-mix(in srgb, ${themeConfig.primary} 16%, transparent)`,
        "--snx-primary-line": `color-mix(in srgb, ${themeConfig.primary} 48%, transparent)`, "--snx-bg": themeConfig.background,
        "--snx-bg-deep": `color-mix(in srgb, ${themeConfig.sidebar} ${surfaceOpacity}%, transparent)`, "--snx-surface": `color-mix(in srgb, ${themeConfig.surface} ${surfaceOpacity}%, transparent)`,
        "--snx-surface-solid": themeConfig.surface, "--snx-surface-raised": `color-mix(in srgb, ${themeConfig.navbar} ${surfaceOpacity}%, transparent)`,
        "--snx-border": themeConfig.border, "--snx-text": themeConfig.text,
        "--snx-text-muted": themeConfig.muted, "--snx-text-dim": themeConfig.muted,
        "--snx-success": themeConfig.success, "--snx-warning": themeConfig.warning,
        "--snx-danger": themeConfig.error, "--snx-radius": `${themeConfig.radius}px`,
        "--bg-background": themeConfig.background, "--bg-card": `color-mix(in srgb, ${themeConfig.surface} ${surfaceOpacity}%, transparent)`,
        "--text-foreground": themeConfig.text, "--text-muted-foreground": themeConfig.muted,
        "--border-border": themeConfig.border,
      };
      for (const [name, value] of Object.entries(variables)) if (typeof value === "string" && value) root.style.setProperty(name, value);
      const fonts: Record<string, string> = { system: "ui-sans-serif, system-ui, sans-serif", inter: "Inter, ui-sans-serif, system-ui, sans-serif", mono: "'JetBrains Mono', ui-monospace, monospace" };
      const shadows: Record<string, string> = { flat: "none", soft: "0 16px 38px rgba(0,0,0,.12), inset 0 1px rgba(255,255,255,.04)", glow: `0 12px 42px color-mix(in srgb, ${themeConfig.primary} 18%, transparent)` };
      const density: Record<string, string> = { compact: "0.86", comfortable: "1", spacious: "1.16" };
      root.style.setProperty("--snx-glass-blur", surfaceOpacity < 100 ? "14px" : "0px");
      root.style.setProperty("--snx-font-family", fonts[themeConfig.fontFamily] || fonts.inter);
      root.style.setProperty("--snx-card-shadow", shadows[themeConfig.elevation] || shadows.soft);
      root.style.setProperty("--snx-density", density[themeConfig.density] || density.comfortable);
      root.style.fontSize = `${16 * Math.max(0.85, Math.min(1.25, Number(themeConfig.fontScale) || 1))}px`;
      root.dataset.snckThemeId = String(themeConfig.id || "snck-default");
      const structure = themeConfig.layout?.structure || {};
      root.dataset.snxShell = structure.shell === "pterodactyl" ? "pterodactyl" : "snck";
      root.dataset.snxNavigationMode = structure.navigationMode === "topbar" ? "topbar" : "sidebar";
      root.dataset.snxSidebarMode = structure.sidebarMode === "compact" ? "compact" : "expanded";
      root.dataset.snxContentWidth = ["wide", "full"].includes(structure.contentWidth) ? structure.contentWidth : "standard";
      root.dataset.snxDashboardMode = structure.dashboardMode === "server-first" ? "server-first" : "overview";
      root.dataset.snxServerNavigationMode = structure.serverNavigationMode === "tabs" ? "tabs" : "sidebar";
      const widthByMode: Record<string, string> = { standard: "80rem", wide: "100rem", full: "100%" };
      root.style.setProperty("--snx-content-max-width", widthByMode[structure.contentWidth] || widthByMode.standard);
    }
    root.style.colorScheme = "dark";
  };

  const fetchSettings = async () => {
    try {
      const res = await axios.get("/api/settings");
      const settings = res.data || {};
      if (settings.activeTheme?.config) setThemeConfig({ ...settings.activeTheme.config, id: settings.activeTheme.id, layout: settings.activeTheme.layout });
      if (settings.panelName) setPanelName(settings.panelName);
      if (settings.panelLogo !== undefined) setPanelLogo(settings.panelLogo);
      if (settings.panelBackgroundImage !== undefined) setPanelBackgroundImage(settings.panelBackgroundImage);
      if (settings.panelBackgroundBlur !== undefined) setPanelBackgroundBlur(Number(settings.panelBackgroundBlur) || 0);
      if (settings.enablePlayit !== undefined) setEnablePlayit(Boolean(settings.enablePlayit));
      if (settings.enableTutorial !== undefined) setEnableTutorial(Boolean(settings.enableTutorial));
      if (settings.enableLoginAnimation !== undefined) setEnableLoginAnimation(Boolean(settings.enableLoginAnimation));
      if (settings.enableRegistration !== undefined) setEnableRegistration(Boolean(settings.enableRegistration));
      if (settings.theme !== undefined) setTheme(safeChoice(String(settings.theme), VALID_THEMES, "aurora"));
      if (settings.appearance !== undefined) setDarkAppearance();
      if (settings.accent !== undefined) setAccent(safeChoice(settings.accent, VALID_ACCENTS, "purple"));
      if (settings.backgroundEffect !== undefined) setBackgroundEffect(safeChoice(settings.backgroundEffect, VALID_BACKGROUND_EFFECTS, "aurora"));
      if (settings.reducedMotion !== undefined) setReducedMotion(Boolean(settings.reducedMotion));
      if (settings.enableGoogleLogin !== undefined) setEnableGoogleLogin(Boolean(settings.enableGoogleLogin));
      if (settings.firebaseApiKey !== undefined) setFirebaseApiKey(settings.firebaseApiKey);
      if (settings.firebaseAuthDomain !== undefined) setFirebaseAuthDomain(settings.firebaseAuthDomain);
      if (settings.firebaseProjectId !== undefined) setFirebaseProjectId(settings.firebaseProjectId);
      if (settings.firebaseStorageBucket !== undefined) setFirebaseStorageBucket(settings.firebaseStorageBucket);
      if (settings.firebaseMessagingSenderId !== undefined) setFirebaseMessagingSenderId(settings.firebaseMessagingSenderId);
      if (settings.firebaseAppId !== undefined) setFirebaseAppId(settings.firebaseAppId);
    } catch (error) {
      // The UI keeps safe defaults if the public settings endpoint is unavailable.
      console.warn("Unable to load Snck settings", error);
    }
  };

  useEffect(() => {
    fetchSettings();

    const mediaQuery = window.matchMedia?.("(prefers-color-scheme: light)");
    const handleSystemTheme = () => {
      if (appearance === "system") applyVisualSettings();
    };
    mediaQuery?.addEventListener?.("change", handleSystemTheme);

    // Must match the key AuthContext actually stores the JWT under
    // ("shironex_token"). This previously read a nonexistent "token" key, so
    // the settings socket never connected and live settings pushes never
    // reached already-open tabs until a full page reload.
    let socket: ReturnType<typeof io> | null = null;

    const connect = () => {
      socket?.disconnect();
      socket = null;
      const token = localStorage.getItem("shironex_token");
      if (!token) return;
      socket = io({ auth: { token } });
      socket.on("settings_updated", fetchSettings);
    };

    connect();
    // Reconnect immediately on login/logout within this tab instead of
    // requiring a full page reload (localStorage writes don't emit a
    // same-tab "storage" event).
    window.addEventListener("shironex-auth-changed", connect);

    return () => {
      socket?.disconnect();
      mediaQuery?.removeEventListener?.("change", handleSystemTheme);
      window.removeEventListener("shironex-auth-changed", connect);
    };
  }, []);

  useEffect(() => {
    applyVisualSettings();
  }, [theme, appearance, accent, backgroundEffect, reducedMotion, themeConfig]);

  useEffect(() => {
    if (panelName) document.title = panelName;
  }, [panelName]);

  const value = useMemo(() => ({
    panelName, setPanelName,
    panelLogo, setPanelLogo,
    panelBackgroundImage, setPanelBackgroundImage,
    panelBackgroundBlur, setPanelBackgroundBlur,
    enablePlayit, setEnablePlayit,
    enableTutorial, setEnableTutorial,
    enableLoginAnimation, setEnableLoginAnimation,
    enableRegistration, setEnableRegistration,
    theme, setTheme, themeConfig,
    appearance, setAppearance: setDarkAppearance,
    accent, setAccent,
    backgroundEffect, setBackgroundEffect,
    reducedMotion, setReducedMotion,
    enableGoogleLogin, setEnableGoogleLogin,
    firebaseApiKey, setFirebaseApiKey,
    firebaseAuthDomain, setFirebaseAuthDomain,
    firebaseProjectId, setFirebaseProjectId,
    firebaseStorageBucket, setFirebaseStorageBucket,
    firebaseMessagingSenderId, setFirebaseMessagingSenderId,
    firebaseAppId, setFirebaseAppId,
    fetchSettings,
  }), [
    panelName, panelLogo, panelBackgroundImage, panelBackgroundBlur,
    enablePlayit, enableTutorial, enableLoginAnimation, enableRegistration,
    theme, themeConfig, appearance, accent, backgroundEffect, reducedMotion,
    enableGoogleLogin, firebaseApiKey, firebaseAuthDomain, firebaseProjectId,
    firebaseStorageBucket, firebaseMessagingSenderId, firebaseAppId,
  ]);

  return <SettingsContext.Provider value={value}>{children}</SettingsContext.Provider>;
};

export const useSettings = () => useContext(SettingsContext);
