import { useEffect, useMemo, useState } from "react";
import type { FormEvent } from "react";
import axios from "axios";
import { Link } from "react-router-dom";
import { Activity, AlertTriangle, Box, CheckCircle2, Clock3, Database, KeyRound, RefreshCw, Search, Server, ShieldCheck, Users, Wifi } from "lucide-react";
import { useAuth } from "../context/AuthContext";
import AdminControls from "../components/AdminControls";
import AdminPlatformControls from "../components/AdminPlatformControls";

type UserRecord = { id: string; username: string; email?: string; role: string; createdAt?: string };
type ServerRecord = { id: string; name: string; status?: string; owner?: string; type?: string; suspended?: boolean };
const statusClass = (status?: string) => status === "online" ? "snx-admin-status snx-admin-status--good" : status === "starting" || status === "restarting" ? "snx-admin-status snx-admin-status--warn" : "snx-admin-status snx-admin-status--muted";

export default function AdminDashboard() {
  const { user, logout } = useAuth();
  const [users, setUsers] = useState<UserRecord[]>([]);
  const [servers, setServers] = useState<ServerRecord[]>([]);
  const [stats, setStats] = useState<any>(null);
  const [query, setQuery] = useState("");
  const [section, setSection] = useState("overview");
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [isUpdatingSystem, setIsUpdatingSystem] = useState(false);
  const [updateMessage, setUpdateMessage] = useState("");
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [role, setRole] = useState("user");
  const [isCreatingUser, setIsCreatingUser] = useState(false);
  const [editingUserId, setEditingUserId] = useState<string | null>(null);
  const [adminUserNewPassword, setAdminUserNewPassword] = useState("");

  const load = async () => {
    setLoading(true); setError("");
    try {
      const [serverResponse, statsResponse, usersResponse] = await Promise.all([
        axios.get("/api/servers"), axios.get("/api/system/stats"), axios.get("/api/system/users")
      ]);
      setServers(Array.isArray(serverResponse.data) ? serverResponse.data : []);
      setStats(statsResponse.data || {});
      setUsers(Array.isArray(usersResponse.data) ? usersResponse.data : []);
    } catch (e: any) { setError(e.response?.data?.error || "Control center data is temporarily unavailable."); }
    finally { setLoading(false); }
  };
  useEffect(() => { void load(); }, []);

  const refreshUsers = async () => {
    try { const res = await axios.get("/api/system/users"); setUsers(Array.isArray(res.data) ? res.data : []); }
    catch (e: any) { setError(e.response?.data?.error || "Could not refresh users."); }
  };
  const createUser = async (e: FormEvent) => {
    e.preventDefault();
    if (password.length < 8 || password.length > 256) { window.alert("Password must be 8–256 characters."); return; }
    setIsCreatingUser(true);
    try {
      await axios.post("/api/system/users", { username, password, role });
      setUsername(""); setPassword(""); await refreshUsers(); window.alert("User created successfully.");
    } catch (e: any) { window.alert(e.response?.data?.error || "Could not create user."); }
    finally { setIsCreatingUser(false); }
  };
  const changeUserPassword = async (id: string) => {
    if (adminUserNewPassword.length < 8 || adminUserNewPassword.length > 256) { window.alert("Password must be 8–256 characters."); return; }
    try {
      await axios.put(`/api/system/users/${id}/password`, { newPassword: adminUserNewPassword });
      setEditingUserId(null); setAdminUserNewPassword(""); window.alert("Password changed successfully.");
      if (user?.id === id) await logout?.(); else await refreshUsers();
    } catch (e: any) { window.alert(e.response?.data?.error || "Could not change password."); }
  };
  const changeUserRole = async (id: string, nextRole: string) => {
    try { await axios.put(`/api/system/users/${id}/role`, { role: nextRole }); await refreshUsers(); }
    catch (e: any) { window.alert(e.response?.data?.error || "Could not change role."); }
  };
  const deleteUser = async (id: string) => {
    try { await axios.delete(`/api/system/users/${id}`); await refreshUsers(); }
    catch (e: any) { window.alert(e.response?.data?.error || "Could not delete user."); }
  };
  const triggerSystemUpdate = async () => {
    if (isUpdatingSystem || !window.confirm("Update SNCK PANEL now? The panel may be briefly unavailable while it rebuilds.")) return;
    setIsUpdatingSystem(true); setUpdateMessage("");
    try { await axios.post("/api/system/update", {}, { timeout: 15000 }); setUpdateMessage("Update request sent. The panel may restart while the update finishes."); }
    catch (e: any) {
      if (e.code === "ECONNABORTED" || !e.response) setUpdateMessage("The connection ended while the update may be restarting the panel. Refresh after a short wait to verify.");
      else setUpdateMessage(e.response?.data?.error || "Update request failed. Check server logs.");
    } finally { setIsUpdatingSystem(false); }
  };

  const online = servers.filter(s => s.status === "online").length;
  const suspended = servers.filter(s => s.suspended).length;
  const filteredUsers = useMemo(() => users.filter(u => `${u.username} ${u.email || ""} ${u.role}`.toLowerCase().includes(query.toLowerCase())), [users, query]);
  const filteredServers = useMemo(() => servers.filter(s => `${s.name} ${s.id} ${s.type || ""}`.toLowerCase().includes(query.toLowerCase())), [servers, query]);
  if (user?.role !== "admin" && user?.role !== "owner") return null;

  return <div className="snx-admin-page">
    <header className="snx-admin-hero"><div><div className="snx-eyebrow"><ShieldCheck size={14}/> Restricted workspace / {user.role}</div><h1>Admin control center</h1><p>Operate your panel with a clear view of fleet health, access, and runtime activity.</p></div><button className="snx-admin-refresh" onClick={() => void load()} disabled={loading}><RefreshCw size={16} className={loading ? "snx-spin" : ""}/> Refresh data</button></header>
    <nav className="snx-admin-tabs" aria-label="Admin sections">{["overview","users","fleet","security","settings"].map(item=><button key={item} className={section===item?"is-active":""} onClick={()=>setSection(item)}>{item==="overview"?"Overview":item==="fleet"?"Fleet & nodes":item==="security"?"Security & audit":item[0].toUpperCase()+item.slice(1)}</button>)}</nav>
    {error && <div className="snx-admin-alert"><AlertTriangle size={18}/> {error}</div>}
    {section === "overview" && <>
      <section className="snx-admin-stat-grid" aria-label="Control center summary">
        <article><span><Server size={16}/> Servers</span><strong>{servers.length}</strong><small>{online} online · {suspended} suspended</small></article>
        <article><span><Users size={16}/> Users</span><strong>{users.length}</strong><small>Accounts across the panel</small></article>
        <article><span><Activity size={16}/> CPU load</span><strong>{Number(stats?.cpuUsage||0).toFixed(1)}%</strong><small>Host aggregate</small></article>
        <article><span><Database size={16}/> Runtime</span><strong>{stats?.activeContainers||0}</strong><small>Active containers</small></article>
      </section>
      <div className="snx-admin-grid">
        <section className="snx-admin-panel"><div className="snx-admin-panel-heading"><div><p className="snx-eyebrow">Live inventory</p><h2>Fleet health</h2></div><Wifi size={18}/></div><div className="snx-health-row"><CheckCircle2 size={22}/><div><strong>{online} instances online</strong><span>Runtime responses are being monitored.</span></div><b>{servers.length?Math.round(online/servers.length*100):0}%</b></div><div className="snx-health-row"><Clock3 size={22}/><div><strong>{servers.length-online} instances need attention</strong><span>Review offline or transitional workloads.</span></div><b className="is-warn">Review</b></div><div className="snx-health-row"><KeyRound size={22}/><div><strong>Access controls active</strong><span>Admin actions require server-side authorization.</span></div><b className="is-good">Protected</b></div></section>
        <section className="snx-admin-panel"><div className="snx-admin-panel-heading"><div><p className="snx-eyebrow">Operator shortcuts</p><h2>Quick actions</h2></div><Activity size={18}/></div><div className="snx-quick-grid"><button onClick={()=>setSection("users")}><Users size={18}/><span>Manage users</span><small>Roles and access</small></button><button onClick={()=>setSection("fleet")}><Box size={18}/><span>Review fleet</span><small>Servers and nodes</small></button><button onClick={()=>setSection("security")}><KeyRound size={18}/><span>Audit security</span><small>Keys and activity</small></button><button onClick={()=>setSection("settings")}><Database size={18}/><span>Panel settings</span><small>Platform features</small></button></div></section>
      </div>
    </>}
    {section === "users" && <><section className="snx-admin-panel snx-admin-table-panel"><div className="snx-admin-panel-heading"><div><p className="snx-eyebrow">Identity directory</p><h2>User Management</h2></div><label className="snx-admin-search"><Search size={16}/><input value={query} onChange={e=>setQuery(e.target.value)} placeholder="Filter users"/></label></div><div className="snx-admin-table">{filteredUsers.map(item=><div className="snx-admin-table-row" key={item.id}><div className="snx-avatar">{item.username[0]?.toUpperCase()}</div><div><strong>{item.username}</strong><span>{item.email||"No email"}</span></div><span className="snx-role-badge">{item.role}</span><small>{item.createdAt?new Date(item.createdAt).toLocaleDateString():"Active"}</small></div>)}{!filteredUsers.length&&<div className="snx-admin-empty">No users match this filter.</div>}</div></section>
      <AdminControls user={user} users={users} username={username} setUsername={setUsername} password={password} setPassword={setPassword} role={role} setRole={setRole} isCreatingUser={isCreatingUser} createUser={createUser} editingUserId={editingUserId} setEditingUserId={setEditingUserId} adminUserNewPassword={adminUserNewPassword} setAdminUserNewPassword={setAdminUserNewPassword} changeUserPassword={changeUserPassword} changeUserRole={changeUserRole} deleteUser={deleteUser}/></>}
    {section === "fleet" && <section className="snx-admin-panel snx-admin-table-panel"><div className="snx-admin-panel-heading"><div><p className="snx-eyebrow">Runtime inventory</p><h2>Fleet & nodes</h2></div><label className="snx-admin-search"><Search size={16}/><input value={query} onChange={e=>setQuery(e.target.value)} placeholder="Filter records"/></label></div><div className="snx-admin-table">{filteredServers.map(item=><div className="snx-admin-table-row" key={item.id}><div className="snx-server-icon"><Server size={16}/></div><div><strong>{item.name}</strong><span>{item.type||"Unknown runtime"} · {item.id}</span></div><span className={statusClass(item.status)}>{item.status||"unknown"}</span><small>{item.suspended?"Suspended":"Ready"}</small></div>)}{!filteredServers.length&&<div className="snx-admin-empty">No servers match this filter.</div>}</div></section>}
    {section === "security" && <section className="snx-admin-panel snx-admin-table-panel"><div className="snx-admin-panel-heading"><div><p className="snx-eyebrow">Governance</p><h2>Security & audit</h2></div></div><div className="snx-admin-settings-list"><div><KeyRound size={18}/><span><strong>API key governance</strong><small>Manage privileged API keys and review access.</small></span><Link to="/api-keys">Manage keys</Link></div><div><ShieldCheck size={18}/><span><strong>Account and role controls</strong><small>Manage users and administrator access.</small></span><button onClick={()=>setSection("users")}>Manage users</button></div></div></section>}
    {section === "settings" && <section className="snx-admin-panel snx-admin-table-panel"><div className="snx-admin-panel-heading"><div><p className="snx-eyebrow">Platform operations</p><h2>Panel settings</h2></div></div>
      <div className="snx-admin-settings-list"><div><RefreshCw size={18}/><span><strong>System Update</strong><small>Trigger an automatic Git pull and rebuild. The panel may be briefly unavailable during this process.</small></span><button onClick={()=>void triggerSystemUpdate()} disabled={isUpdatingSystem}>{isUpdatingSystem?"Updating…":"Update panel"}</button></div>{updateMessage&&<p role="status">{updateMessage}</p>}</div>
      <AdminPlatformControls/>
    </section>}
  </div>;
}
