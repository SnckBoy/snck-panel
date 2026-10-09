import { Link, useLocation } from "react-router-dom";
import type { ReactNode } from "react";
import { Server, LayoutDashboard, Plus, LogOut, X, Settings, Key, Activity, Box, Cloud, ShieldCheck, LifeBuoy } from "lucide-react";
import { useAuth } from "../context/AuthContext";
import { useSettings } from "../context/SettingsContext";
import { motion } from "framer-motion";

type SidebarLink = { name: string; path: string; icon: ReactNode };

export function Sidebar({ onClose, isCollapsed, toggleCollapse }: { onClose?: () => void; isCollapsed?: boolean; toggleCollapse?: () => void }) {
  const location = useLocation();
  const { user, logout } = useAuth();
  const { panelName, panelLogo } = useSettings();

  const canAdminister = user?.role === "admin" || user?.role === "owner";
  const primaryLinks: SidebarLink[] = [
    { name: "Overview", path: "/", icon: <LayoutDashboard size={19} /> },
    { name: "Nodes", path: "/nodes", icon: <Activity size={19} /> },
    { name: "Servers", path: "/servers", icon: <Server size={19} /> },
  ];
  const adminLinks: SidebarLink[] = canAdminister ? [
    { name: "Deploy", path: "/servers/create", icon: <Plus size={19} /> },
    { name: "Admin control", path: "/admin", icon: <ShieldCheck size={19} /> },
    { name: "Fleet", path: "/admin/servers", icon: <Box size={19} /> },
    { name: "API Keys", path: "/api-keys", icon: <Key size={19} /> },
    { name: "Cloudflare", path: "/cloudflare", icon: <Cloud size={19} /> },
  ] : [];
  const sections: { title: string; links: SidebarLink[] }[] = [
    { title: "Your server", links: primaryLinks },
    ...(adminLinks.length ? [{ title: "Admin", links: adminLinks }] : []),
    { title: "Config", links: [{ name: "Settings", path: "/settings", icon: <Settings size={19} /> }] },
  ];

  return (
    <div className={`snx-app-sidebar h-full flex flex-col transition-all duration-300 z-20 ${isCollapsed ? "w-20" : "w-64"}`}>
      <div className={`snx-app-sidebar-header h-16 flex items-center flex-shrink-0 relative ${isCollapsed ? "justify-center" : "px-5"}`}>
        {onClose && (
          <button onClick={onClose} aria-label="Close navigation" className="md:hidden flex items-center justify-center absolute top-4 right-3 p-2 text-muted-foreground hover:text-foreground hover:bg-muted rounded-xl transition-colors">
            <X size={19} />
          </button>
        )}
        <div className="flex items-center gap-3 min-w-0">
          {panelLogo ? (
            <img src={panelLogo} alt="Logo" className="w-8 h-8 rounded-xl object-cover flex-shrink-0" />
          ) : (
            <div className="flex items-center justify-center w-8 h-8 rounded-xl bg-[#24242d] flex-shrink-0 text-white">
              <Server className="w-4 h-4" />
            </div>
          )}
          {!isCollapsed && (
            <motion.h1 initial={{ opacity: 0, width: 0 }} animate={{ opacity: 1, width: "auto" }} className="text-lg font-bold tracking-tight truncate whitespace-nowrap text-foreground">
              {panelName}
            </motion.h1>
          )}
        </div>
      </div>

      <nav className="snx-app-nav flex-1 w-full px-3 pt-2 pb-4 overflow-y-auto custom-scrollbar">
        {sections.map((section) => (
          <div key={section.title} className="mb-2">
            {!isCollapsed && <p className="snx-sidebar-section-label">{section.title}</p>}
            <div className="space-y-1">
              {section.links.map((link) => {
                const isActive = location.pathname === link.path || (link.path !== "/" && location.pathname.startsWith(link.path));
                return (
                  <Link
                    key={link.path}
                    to={link.path}
                    onClick={onClose}
                    title={isCollapsed ? link.name : undefined}
                    className={`snx-app-nav-link relative flex items-center ${isCollapsed ? "justify-center" : "px-3"} py-3.5 rounded-[13px] transition-colors group overflow-hidden`}
                  >
                    {isActive && (
                      <motion.div
                        layoutId="activeTabSidebar"
                        className="snx-app-nav-active absolute inset-0 rounded-[13px]"
                        initial={false}
                        transition={{ type: "spring", stiffness: 300, damping: 30 }}
                      />
                    )}
                    <div className={`relative z-10 transition-colors duration-200 ${isActive ? "text-foreground" : "text-muted-foreground group-hover:text-foreground"}`}>
                      {link.icon}
                    </div>
                    {!isCollapsed && (
                      <span className={`ml-3 relative z-10 font-medium text-sm transition-colors duration-200 ${isActive ? "text-foreground" : "text-muted-foreground group-hover:text-foreground"}`}>
                        {link.name}
                      </span>
                    )}
                  </Link>
                );
              })}
            </div>
          </div>
        ))}
      </nav>

      <div className="snx-app-sidebar-footer w-full p-3 mt-auto bg-transparent">
        {!isCollapsed && (
          <div className="snx-sidebar-promo flex items-start gap-3 p-3 mb-3">
            <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/[0.05] text-muted-foreground">
              <LifeBuoy size={17} />
            </div>
            <div className="min-w-0">
              <p className="text-sm font-semibold text-foreground">Need a hand?</p>
              <p className="mt-1 text-xs leading-relaxed text-muted-foreground">Your servers and settings are available from this panel.</p>
            </div>
          </div>
        )}
        {isCollapsed ? (
          <button onClick={logout} title="Logout" aria-label="Logout" className="flex items-center justify-center w-full p-3 rounded-xl text-muted-foreground hover:bg-white/[0.04] hover:text-foreground transition-colors">
            <LogOut size={19} />
          </button>
        ) : (
          <div className="flex items-center gap-3 rounded-xl px-2 py-2">
            <div className="w-9 h-9 rounded-full bg-[#24242d] flex items-center justify-center text-foreground font-semibold text-sm flex-shrink-0">
              {user?.username?.[0]?.toUpperCase()}
            </div>
            <div className="min-w-0 flex-1">
              <p className="font-semibold text-foreground text-sm truncate">{user?.username}</p>
              <p className="text-xs text-muted-foreground truncate">{user?.email || user?.role || "Account"}</p>
            </div>
            <button onClick={logout} title="Logout" aria-label="Logout" className="p-2 rounded-xl text-muted-foreground hover:bg-white/[0.04] hover:text-foreground transition-colors flex-shrink-0">
              <LogOut size={17} />
            </button>
          </div>
        )}
      </div>
    </div>
  );
}
