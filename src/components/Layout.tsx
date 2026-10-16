import React, { useState } from "react";
import { Sidebar } from "./Sidebar";
import { Menu, ChevronRight, Command, ShieldCheck } from "lucide-react";
import { useLocation, matchPath, Link } from "react-router-dom";
import GlobalSearchModal from "./GlobalSearchModal";
import NotificationsDropdown from "./NotificationsDropdown";
import { useSettings } from "../context/SettingsContext";

export default function Layout({ children }: { children: React.ReactNode }) {
  const [mobileOpen, setMobileOpen] = useState(false);
  const [isCollapsed, setIsCollapsed] = useState(false);
  const location = useLocation();
  const { themeConfig } = useSettings();
  const structure = themeConfig?.layout?.structure || {};

  const isServerView = matchPath("/servers/:id/*", location.pathname) && !matchPath("/servers/create", location.pathname);

  const getBreadcrumb = () => {
    const path = location.pathname;
    if (path === '/') return 'Overview';
    if (path === '/servers') return 'Servers';
    if (path === '/servers/create') return 'Deploy Server';
    if (path.startsWith('/servers/')) return 'Server Management';
    if (path === '/admin') return 'Admin control center';
    if (path === '/admin/servers') return 'Fleet';
    if (path === '/nodes') return 'Node infrastructure';
    if (path === '/allocations') return 'Network allocations';
    if (path === '/cloudflare') return 'Cloudflare';
    if (path === '/settings') return 'Settings';
    if (path === '/api-keys') return 'API Keys';
    return '';
  };

  if (isServerView) {
    return (
      <div className="snx-server-layout flex flex-col h-[100dvh] w-full bg-transparent text-foreground font-sans overflow-hidden selection:bg-indigo-500/30">
        <main className="flex-1 min-h-0 w-full relative z-10 overflow-hidden">
          {children}
        </main>
        <footer className="snx-brand-footer flex flex-shrink-0 items-center justify-center gap-3 sm:gap-5 flex-wrap border-t border-border-subtle px-3 sm:px-6 py-3 text-xs text-muted-foreground">
        <span className="flex items-center gap-2 font-semibold tracking-wide text-foreground"><span className="h-6 w-6 text-foreground"><svg viewBox="0 0 100 100" aria-hidden="true" className="h-full w-full" fill="currentColor"><path d="M49 94 37 68 9 48 Q3 43 8 38 Q11 35 17 38 L37 48 22 20 Q18 13 23 10 Q28 7 33 13 L49 36 61 20 67 37 82 10 Q87 4 92 9 Q96 13 91 20 L79 39 91 35 Q98 33 100 40 Q101 46 93 49 L72 54 51 94 Q50 97 49 94 Z M50 49 40 61 50 73 60 60 Z" fillRule="evenodd"/></svg></span><span>SNCK PANEL</span></span>
        <span className="hidden sm:inline text-border">|</span>
        <span><strong className="font-semibold text-foreground">Created by Clark</strong></span>
        <span className="hidden sm:inline text-border">|</span>
        <span>Discord: <strong className="font-medium text-foreground">root_dora</strong></span>
      </footer>
      </div>
    );
  }

  return (
    <div className={`snx-app-shell flex h-[100dvh] w-full bg-transparent text-foreground font-sans overflow-hidden selection:bg-indigo-500/30 ${structure.shell === "pterodactyl" ? "snx-app-shell--pterodactyl" : ""} ${structure.navigationMode === "topbar" ? "snx-app-shell--topbar" : ""}`}>
      <a className="snx-skip-link" href="#main-content">Skip to main content</a>
      {/* Mobile Sidebar Overlay */}
      {mobileOpen && (
        <div 
          className="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 md:hidden"
          onClick={() => setMobileOpen(false)}
        />
      )}
      
      {/* Sidebar Container */}
      <div className={`fixed inset-y-0 left-0 z-50 transform flex-shrink-0 ${mobileOpen ? 'translate-x-0' : '-translate-x-full'} md:relative md:translate-x-0 transition-transform duration-300 ease-in-out`}>
        <Sidebar onClose={() => setMobileOpen(false)} isCollapsed={isCollapsed} toggleCollapse={() => setIsCollapsed(!isCollapsed)} />
      </div>

      <div className="flex-1 flex flex-col min-w-0 h-full overflow-hidden relative bg-transparent">
        
        {/* Top Header */}
        <header className="snx-global-topbar h-16 flex items-center justify-between px-4 sm:px-6 relative z-10 flex-shrink-0">
          <div className="flex items-center gap-4">
            <button type="button" aria-label="Open navigation menu" onClick={() => setMobileOpen(true)} className="snx-topbar-icon md:hidden p-2 -ml-2 rounded-lg">
              <Menu size={20} aria-hidden="true" />
            </button>
            <button type="button" aria-label={isCollapsed ? "Expand navigation menu" : "Collapse navigation menu"} onClick={() => setIsCollapsed(!isCollapsed)} className="snx-topbar-icon hidden md:flex p-2 -ml-2 rounded-lg">
              <Menu size={20} aria-hidden="true" />
            </button>
            <div className="hidden sm:flex items-center gap-3 text-sm font-medium text-muted-foreground">
              <span className="snx-command-chip"><Command size={14} aria-hidden="true" /> <span>Quick command</span> <kbd>⌘ K</kbd></span>
              <ChevronRight size={14} aria-hidden="true" />
              <span className="text-foreground">{getBreadcrumb()}</span>
            </div>
          </div>
          
          <div className="flex items-center gap-2 sm:gap-4">
            <GlobalSearchModal />
            <NotificationsDropdown />
          </div>
        </header>
        
        {/* Main Content */}
        <main id="main-content" tabIndex={-1} className={`snx-app-main flex-1 w-full h-full relative z-0 overflow-x-hidden overflow-y-auto pb-safe custom-scrollbar`}>
          <div className="snx-page-container p-4 sm:p-6 md:p-8 max-w-7xl mx-auto w-full">
            {children}
          </div>
        </main>
        <footer className="snx-brand-footer flex flex-shrink-0 items-center justify-center gap-3 sm:gap-5 flex-wrap border-t border-border-subtle px-3 sm:px-6 py-3 text-xs text-muted-foreground">
        <span className="flex items-center gap-2 font-semibold tracking-wide text-foreground"><span className="h-6 w-6 text-foreground"><svg viewBox="0 0 100 100" aria-hidden="true" className="h-full w-full" fill="currentColor"><path d="M49 94 37 68 9 48 Q3 43 8 38 Q11 35 17 38 L37 48 22 20 Q18 13 23 10 Q28 7 33 13 L49 36 61 20 67 37 82 10 Q87 4 92 9 Q96 13 91 20 L79 39 91 35 Q98 33 100 40 Q101 46 93 49 L72 54 51 94 Q50 97 49 94 Z M50 49 40 61 50 73 60 60 Z" fillRule="evenodd"/></svg></span><span>SNCK PANEL</span></span>
        <span className="hidden sm:inline text-border">|</span>
        <span><strong className="font-semibold text-foreground">Created by Clark</strong></span>
        <span className="hidden sm:inline text-border">|</span>
        <span>Discord: <strong className="font-medium text-foreground">root_dora</strong></span>
      </footer>
      </div>
    </div>
  );
}
