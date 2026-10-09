/**
 * @license
 * SPDX-License-Identifier: Apache-2.0
 */

import React, { lazy, Suspense, useState, useEffect } from "react";
import { BrowserRouter as Router, Routes, Route, Navigate, useLocation } from "react-router-dom";
import { AuthProvider, useAuth } from "./context/AuthContext";
const Login = lazy(() => import("./pages/Login"));
const Register = lazy(() => import("./pages/Register"));
const Setup = lazy(() => import("./pages/Setup"));
const Dashboard = lazy(() => import("./pages/Dashboard"));
const ServerList = lazy(() => import("./pages/ServerList"));
const CreateServer = lazy(() => import("./pages/CreateServer"));
const ServerView = lazy(() => import("./pages/ServerView"));
const SettingsPage = lazy(() => import("./pages/SettingsPage"));
const ApiKeysPage = lazy(() => import("./pages/ApiKeysPage"));
const AdminServers = lazy(() => import("./pages/AdminServers"));
const PlayitTunnel = lazy(() => import("./pages/PlayitTunnel"));
const Nodes = lazy(() => import("./pages/Nodes"));
const Allocations = lazy(() => import("./pages/Allocations"));
const Cloudflare = lazy(() => import("./pages/Cloudflare"));
import Layout from "./components/Layout";
import { motion, AnimatePresence, MotionConfig } from "framer-motion";
import { SettingsProvider, useSettings } from "./context/SettingsContext";
import { GlobalBackground } from "./components/GlobalBackground";
import { SystemUpdateListener } from "./components/SystemUpdateListener";
import { TutorialOverlay } from "./components/TutorialOverlay";
const CoreCheckpoint = lazy(() => import("./pages/CoreCheckpoint"));
const AdminDashboard = lazy(() => import("./pages/AdminDashboard"));

const ProtectedRoute = ({ children }: { children: React.ReactNode }) => {
  const { user, loading, setupRequired } = useAuth();
  if (loading || setupRequired === null) return (
    <div className="h-[100dvh] w-full flex items-center justify-center bg-transparent text-foreground">
      <motion.div
        animate={{ scale: [1, 1.2, 1], rotate: [0, 180, 360] }}
        transition={{ duration: 2, repeat: Infinity, ease: "linear" }}
        className="w-16 h-16 border-4 border-indigo-500 border-t-transparent rounded-full"
      />
    </div>
  );
  if (!user) return <Navigate to={setupRequired ? "/setup" : "/login"} replace />;
  return <Layout>{children}</Layout>;
};

const AdminOnlyRoute = ({ children }: { children: React.ReactNode }) => {
  const { user } = useAuth();
  if (user?.role !== "admin" && user?.role !== "owner") return <Navigate to="/" replace />;
  return <>{children}</>;
};

const PublicAuthRoute = ({ children }: { children: React.ReactNode }) => {
  const { setupRequired, loading } = useAuth();
  if (loading || setupRequired === null) return <div className="min-h-screen grid place-items-center bg-background text-foreground">Loading...</div>;
  if (setupRequired) return <Navigate to="/setup" replace />;
  return <>{children}</>;
};

const AnimatedRoutes = () => {
  const location = useLocation();
  return (
    <AnimatePresence mode="wait">
      <motion.div 
        key={location.pathname} 
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        exit={{ opacity: 0, y: -20 }}
        transition={{ duration: 0.3 }}
        className="h-full w-full flex flex-col"
      >
        <Suspense fallback={<div role="status" className="snx-route-loading"><span className="snx-live-dot" /> Loading workspace…</div>}>
        <Routes location={location}>
          <Route path="/setup" element={<Setup />} />
          <Route path="/core-checkpoint" element={<CoreCheckpoint />} />
          <Route path="/login" element={<PublicAuthRoute><Login /></PublicAuthRoute>} />
          <Route path="/register" element={<PublicAuthRoute><Register /></PublicAuthRoute>} />
          <Route path="/" element={<ProtectedRoute><Dashboard /></ProtectedRoute>} />
          <Route path="/nodes" element={<ProtectedRoute><AdminOnlyRoute><Nodes /></AdminOnlyRoute></ProtectedRoute>} />
          <Route path="/allocations" element={<ProtectedRoute><AdminOnlyRoute><Allocations /></AdminOnlyRoute></ProtectedRoute>} />
          <Route path="/cloudflare" element={<ProtectedRoute><AdminOnlyRoute><Cloudflare /></AdminOnlyRoute></ProtectedRoute>} />
          <Route path="/servers" element={<ProtectedRoute><ServerList /></ProtectedRoute>} />
          <Route path="/servers/create" element={<ProtectedRoute><CreateServer /></ProtectedRoute>} />
          <Route path="/servers/:id/*" element={<ProtectedRoute><ServerView /></ProtectedRoute>} />
          <Route path="/settings" element={<ProtectedRoute><SettingsPage /></ProtectedRoute>} />
          <Route path="/api-keys" element={<ProtectedRoute><AdminOnlyRoute><ApiKeysPage /></AdminOnlyRoute></ProtectedRoute>} />
          <Route path="/admin" element={<ProtectedRoute><AdminOnlyRoute><AdminDashboard /></AdminOnlyRoute></ProtectedRoute>} />
          <Route path="/admin/servers" element={<ProtectedRoute><AdminOnlyRoute><AdminServers /></AdminOnlyRoute></ProtectedRoute>} />
          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
        </Suspense>
      </motion.div>
    </AnimatePresence>
  );
};

const TutorialManager = () => {
  const { panelName, enableTutorial } = useSettings();
  const [showTutorial, setShowTutorial] = useState(false);
  const { user, loading } = useAuth();
  const location = useLocation();

  useEffect(() => {
    // If the feature is globally disabled, do not show tutorial
    if (enableTutorial === false) {
      setShowTutorial(false);
      return;
    }

    if (loading || !user || location.pathname === '/login' || location.pathname === '/setup') return;

    const isDev = process.env.NODE_ENV === 'development';
    const tutorialKey = isDev ? `tutorialShown_dev_${user.id}` : `tutorialShown_prod_${user.id}`;
    
    const tutorialShown = isDev 
      ? sessionStorage.getItem(tutorialKey) 
      : localStorage.getItem(tutorialKey);

    if (!tutorialShown) {
      setShowTutorial(true);
    }
  }, [user, loading, location.pathname, enableTutorial]);

  const handleTutorialComplete = () => {
    if (!user) return;
    const isDev = process.env.NODE_ENV === 'development';
    const tutorialKey = isDev ? `tutorialShown_dev_${user.id}` : `tutorialShown_prod_${user.id}`;
    
    if (isDev) {
      sessionStorage.setItem(tutorialKey, 'true');
    } else {
      localStorage.setItem(tutorialKey, 'true');
    }
    
    setShowTutorial(false);
  };

  if (!showTutorial) return null;

  return <TutorialOverlay onComplete={handleTutorialComplete} panelName={panelName} />;
};

function PanelExperience() {
  const { reducedMotion } = useSettings();
  return <MotionConfig reducedMotion={reducedMotion ? "always" : "user"}>
    <GlobalBackground />
    <AnimatedRoutes />
    <TutorialManager />
  </MotionConfig>;
}

export default function App() {
  return (
    <SettingsProvider>
      <AuthProvider>
        <SystemUpdateListener />
        <Router>
          <PanelExperience />
        </Router>
      </AuthProvider>
    </SettingsProvider>
  );
}
