import React, { useEffect, useMemo, useState } from "react";
import axios from "axios";
import {
  AlertCircle,
  Box,
  CheckCircle2,
  Download,
  ExternalLink,
  Filter,
  Loader2,
  Puzzle,
  Search,
  Server,
  SlidersHorizontal,
  Star,
  Trash2,
  RefreshCw,
} from "lucide-react";
import { LoadingOverlay } from "./LoadingOverlay";

type Provider = "all" | "modrinth" | "hangar" | "spiget";
type SortMode = "downloads" | "name" | "updated" | "rating";

interface InstalledPlugin {
  filename: string;
  size: number;
}

interface MarketplacePlugin {
  id: string;
  provider: Exclude<Provider, "all">;
  kind: "plugin" | "mod" | "modpack";
  name: string;
  description: string;
  author: string;
  iconUrl: string | null;
  downloads: number;
  rating: number | null;
  latestVersion: string | null;
  gameVersions: string[];
  loaders: string[];
  platforms: string[];
  updatedAt: string | null;
  projectUrl: string;
  compatibility: "known" | "unknown";
}

const sourceLabels: Record<Provider, string> = {
  all: "All providers",
  modrinth: "Modrinth",
  hangar: "Hangar",
  spiget: "Spiget",
};

export default function PluginManager({ serverId }: { serverId: string }) {
  const [plugins, setPlugins] = useState<MarketplacePlugin[]>([]);
  const [loading, setLoading] = useState(false);
  const [isInstalling, setIsInstalling] = useState<string | null>(null);
  const [query, setQuery] = useState("");
  const [provider, setProvider] = useState<Provider>("all");
  const [sort, setSort] = useState<SortMode>("downloads");
  const [gameVersion, setGameVersion] = useState("");
  const [loader, setLoader] = useState("");
  const [error, setError] = useState("");
  const [notice, setNotice] = useState("");
  const [installed, setInstalled] = useState<InstalledPlugin[]>([]);
  const [installedLoading, setInstalledLoading] = useState(false);

  const searchPlugins = async (event?: React.FormEvent) => {
    event?.preventDefault();
    try {
      setLoading(true);
      setError("");
      setNotice("");
      const response = await axios.get<{ items: MarketplacePlugin[]; failedProviders?: string[]; featured?: boolean }>("/api/marketplace/search", {
        params: { q: query.trim(), kind: "plugin", provider, sort, gameVersion: gameVersion.trim(), loader: loader.trim(), limit: 40 },
      });
      setPlugins(response.data.items || []);
      if (response.data.failedProviders?.length) {
        setNotice("Some providers are temporarily unavailable: " + response.data.failedProviders.join(", ") + ". Showing available results.");
      }
    } catch (requestError: any) {
      setError(requestError.response?.data?.error || "Marketplace providers are temporarily unavailable.");
      setPlugins([]);
    } finally {
      setLoading(false);
    }
  };

  const loadInstalled = async () => {
    setInstalledLoading(true);
    try {
      const response = await axios.get<{ items: InstalledPlugin[] }>(`/api/servers/${serverId}/plugins/installed`);
      setInstalled(response.data.items || []);
    } catch (requestError: any) {
      setError(requestError.response?.data?.error || "Unable to read installed plugins.");
    } finally {
      setInstalledLoading(false);
    }
  };

  const removeInstalled = async (plugin: InstalledPlugin) => {
    if (!window.confirm(`Remove ${plugin.filename}? This deletes the plugin from the server.`)) return;
    try {
      await axios.delete(`/api/servers/${serverId}/plugins/${encodeURIComponent(plugin.filename)}`);
      await loadInstalled();
    } catch (requestError: any) {
      setError(requestError.response?.data?.error || "Unable to remove plugin.");
    }
  };

  useEffect(() => {
    const timer = window.setTimeout(() => void searchPlugins(), 350);
    return () => window.clearTimeout(timer);
  }, [query, provider, sort, gameVersion, loader]);

  useEffect(() => {
    void loadInstalled();
  }, [serverId]);

  const sortedPlugins = useMemo(() => {
    return [...plugins].sort((left, right) => {
      if (sort === "name") return left.name.localeCompare(right.name);
      if (sort === "rating") return (right.rating || 0) - (left.rating || 0);
      if (sort === "updated") return String(right.updatedAt || "").localeCompare(String(left.updatedAt || ""));
      return right.downloads - left.downloads;
    });
  }, [plugins, sort]);

  const handleInstall = async (plugin: MarketplacePlugin) => {
    if (!confirm(`Install ${plugin.name} on this server? Compatibility will be checked by the server before installation.`)) return;
    try {
      setIsInstalling(plugin.id);
      const response = await axios.post(`/api/servers/${serverId}/plugins/install`, {
        source: plugin.provider,
        pluginId: plugin.id,
        pluginName: plugin.name,
        gameVersion: gameVersion.trim() || undefined,
        loader: loader.trim() || undefined,
      });
      setNotice(response.data.message || `${plugin.name} installed successfully. Restart or reload the server to apply it.`);
      await loadInstalled();
    } catch (requestError: any) {
      setError(requestError.response?.data?.error || "Plugin installation failed.");
    } finally {
      setIsInstalling(null);
    }
  };

  return (
    <div className="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-8 text-foreground bg-transparent">
      <div className="max-w-6xl mx-auto space-y-5 md:space-y-7">
        <header className="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
          <div>
            <div className="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.22em] text-cyan-300/80"><Puzzle className="h-4 w-4" /> Marketplace</div>
            <h2 className="mt-2 text-2xl md:text-3xl font-black tracking-tight text-foreground">Plugin Marketplace</h2>
            <p className="mt-1 max-w-2xl text-sm text-muted-foreground">Search trusted public providers, review compatibility signals, and install plugins on this server without leaving the panel.</p>
          </div>
          <div className="inline-flex items-center gap-2 rounded-full border border-emerald-400/20 bg-emerald-400/5 px-3 py-1.5 text-xs text-emerald-300"><CheckCircle2 className="h-3.5 w-3.5" /> Official APIs only</div>
        </header>

        <section className="snx-console-surface rounded-2xl border border-border-subtle p-4 md:p-5">
          <form onSubmit={searchPlugins} className="grid gap-3 lg:grid-cols-[minmax(0,1fr)_auto_auto]">
            <label className="relative block">
              <span className="sr-only">Search plugins</span>
              <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <input value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Search plugins by name or capability" className="w-full rounded-xl border border-border bg-muted-subtle py-3 pl-10 pr-4 text-sm text-foreground outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/10" />
            </label>
            <button type="submit" className="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-cyan-400 px-5 text-sm font-bold text-slate-950 transition hover:bg-cyan-300 disabled:opacity-50" disabled={loading}>{loading ? <Loader2 className="h-4 w-4 animate-spin" /> : <Search className="h-4 w-4" />} Search</button>
            <select value={sort} onChange={(event) => setSort(event.target.value as SortMode)} className="min-h-11 rounded-xl border border-border bg-muted-subtle px-3 text-sm text-foreground outline-none"><option value="downloads">Most downloaded</option><option value="rating">Highest rated</option><option value="updated">Recently updated</option><option value="name">Name</option></select>
          </form>
          <div className="mt-4 grid gap-3 sm:grid-cols-3">
            <label className="text-xs text-muted-foreground"><span className="mb-1.5 flex items-center gap-1.5"><Filter className="h-3.5 w-3.5" /> Provider</span><select value={provider} onChange={(event) => setProvider(event.target.value as Provider)} className="w-full rounded-lg border border-border bg-muted px-3 py-2 text-sm text-foreground outline-none">{Object.keys(sourceLabels).map((key) => <option key={key} value={key}>{sourceLabels[key as Provider]}</option>)}</select></label>
            <label className="text-xs text-muted-foreground"><span className="mb-1.5 block">Minecraft version</span><input value={gameVersion} onChange={(event) => setGameVersion(event.target.value)} placeholder="e.g. 1.21.1" className="w-full rounded-lg border border-border bg-muted px-3 py-2 text-sm text-foreground outline-none" /></label>
            <label className="text-xs text-muted-foreground"><span className="mb-1.5 block">Platform / loader</span><input value={loader} onChange={(event) => setLoader(event.target.value)} placeholder="Paper, Purpur, Folia" className="w-full rounded-lg border border-border bg-muted px-3 py-2 text-sm text-foreground outline-none" /></label>
          </div>
        </section>

        {error && <div className="flex items-center gap-2 rounded-xl border border-amber-400/20 bg-amber-400/5 p-3 text-sm text-amber-200"><AlertCircle className="h-4 w-4 shrink-0" /> {error}</div>}
        {notice && <div className="flex items-center gap-2 rounded-xl border border-emerald-400/20 bg-emerald-400/5 p-3 text-sm text-emerald-200"><CheckCircle2 className="h-4 w-4 shrink-0" /> {notice}</div>}

        <section className="snx-console-surface rounded-2xl border border-border-subtle p-4 md:p-5">
          <div className="flex flex-wrap items-center justify-between gap-3"><div><h3 className="text-lg font-bold text-foreground">Installed Plugins</h3><p className="mt-1 text-xs text-muted-foreground">Files currently detected in this server’s plugins directory.</p></div><button type="button" onClick={() => void loadInstalled()} disabled={installedLoading} className="inline-flex min-h-10 items-center gap-2 rounded-lg border border-border px-3 text-xs font-semibold text-muted-foreground hover:bg-muted disabled:opacity-50"><RefreshCw className={`h-3.5 w-3.5 ${installedLoading ? "animate-spin" : ""}`} /> Refresh</button></div>
          {installed.length === 0 ? <p className="mt-4 rounded-xl border border-dashed border-border p-5 text-center text-sm text-muted-foreground">No plugin JAR files detected yet.</p> : <div className="mt-4 divide-y divide-border-subtle">{installed.map((plugin) => <div key={plugin.filename} className="flex flex-wrap items-center justify-between gap-3 py-3"><div className="min-w-0"><p className="truncate text-sm font-semibold text-foreground">{plugin.filename}</p><p className="text-xs text-muted-foreground">{(plugin.size / 1024 / 1024).toFixed(2)} MB · server filesystem</p></div><button type="button" onClick={() => void removeInstalled(plugin)} className="inline-flex min-h-10 items-center gap-2 rounded-lg border border-rose-400/25 px-3 text-xs font-semibold text-rose-200 hover:bg-rose-400/10"><Trash2 className="h-3.5 w-3.5" /> Remove</button></div>)}</div>}
        </section>
        {!loading && !error && sortedPlugins.length === 0 && <div className="rounded-2xl border border-dashed border-border p-12 text-center text-muted-foreground"><SlidersHorizontal className="mx-auto mb-3 h-7 w-7" /><p>No plugins found for this search.</p><p className="mt-1 text-xs">Try another provider, version, or search term.</p></div>}

        <section className="flex flex-wrap items-end justify-between gap-2"><div><h3 className="text-lg font-bold text-foreground">{query.trim() ? "Search Results" : "Popular Plugins"}</h3><p className="mt-1 text-xs text-muted-foreground">{query.trim() ? `Matches for “${query.trim()}”` : "Popular plugins are loaded automatically — no search required."}</p></div><span className="text-xs text-muted-foreground">{sortedPlugins.length} results</span></section>
        <div className="grid gap-4 xl:grid-cols-2">
          {sortedPlugins.map((plugin) => (
            <article key={`${plugin.provider}-${plugin.id}`} className="snx-console-surface group flex min-w-0 flex-col gap-4 rounded-2xl border border-border-subtle p-4 transition hover:border-cyan-400/30 md:p-5">
              <div className="flex min-w-0 items-start gap-3">
                <div className="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-border bg-muted text-cyan-300">{plugin.iconUrl ? <img src={plugin.iconUrl} alt="" className="h-full w-full object-cover" /> : <Box className="h-6 w-6" />}</div>
                <div className="min-w-0 flex-1"><div className="flex flex-wrap items-center gap-2"><h3 className="truncate text-base font-bold text-foreground">{plugin.name}</h3><span className="rounded-full border border-cyan-400/20 bg-cyan-400/5 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-cyan-300">{sourceLabels[plugin.provider]}</span></div><p className="mt-1 text-xs text-muted-foreground">by {plugin.author}</p></div>
                <a href={plugin.projectUrl} target="_blank" rel="noreferrer" className="rounded-lg p-2 text-muted-foreground transition hover:bg-muted hover:text-foreground" aria-label={`Open ${plugin.name} source page`}><ExternalLink className="h-4 w-4" /></a>
              </div>
              <p className="line-clamp-2 min-h-10 text-sm leading-5 text-muted-foreground">{plugin.description || "No description provided by the provider."}</p>
              <div className="flex flex-wrap gap-2 text-[11px] text-muted-foreground"><span className="inline-flex items-center gap-1"><Download className="h-3.5 w-3.5" /> {plugin.downloads.toLocaleString()}</span>{plugin.rating !== null && <span className="inline-flex items-center gap-1"><Star className="h-3.5 w-3.5 text-amber-300" /> {plugin.rating.toFixed(1)}</span>}<span className={plugin.compatibility === "known" ? "text-emerald-300" : "text-amber-300"}>{plugin.compatibility === "known" ? "Compatibility data available" : "Compatibility to verify"}</span>{plugin.platforms.slice(0, 3).map((platform) => <span key={platform} className="rounded-md bg-muted px-2 py-0.5">{platform}</span>)}</div>
              <div className="mt-auto flex items-center justify-between gap-3 border-t border-border-subtle pt-3"><span className="inline-flex items-center gap-1.5 text-xs text-muted-foreground"><Server className="h-3.5 w-3.5" /> Current server</span><button type="button" onClick={() => void handleInstall(plugin)} disabled={isInstalling !== null} className="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-cyan-400/30 bg-cyan-400/10 px-4 text-sm font-semibold text-cyan-200 transition hover:bg-cyan-400/20 disabled:cursor-wait disabled:opacity-50">{isInstalling === plugin.id ? <Loader2 className="h-4 w-4 animate-spin" /> : <Download className="h-4 w-4" />} {isInstalling === plugin.id ? "Installing" : "Install"}</button></div>
            </article>
          ))}
        </div>
      </div>
      {isInstalling !== null && <LoadingOverlay message="Validating and installing plugin..." />}
    </div>
  );
}
