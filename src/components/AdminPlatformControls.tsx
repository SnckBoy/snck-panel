import { useEffect, useState } from "react";
import axios from "axios";
import { initializeApp, deleteApp } from "firebase/app";
import { useSettings } from "../context/SettingsContext";
import { useAuth } from "../context/AuthContext";
import { CheckCircle2, AlertCircle, KeyRound, SlidersHorizontal, Gift } from "lucide-react";

export default function AdminPlatformControls() {
  const settings = useSettings();
  const { user } = useAuth();
  const [panelName, setPanelName] = useState(settings.panelName || "SNCK PANEL");
  const [playit, setPlayit] = useState(Boolean(settings.enablePlayit));
  const [tutorial, setTutorial] = useState(Boolean(settings.enableTutorial));
  const [loginAnimation, setLoginAnimation] = useState(Boolean(settings.enableLoginAnimation));
  const [registration, setRegistration] = useState(Boolean(settings.enableRegistration));
  const [theme, setTheme] = useState(settings.theme || "aurora");
  const [google, setGoogle] = useState(Boolean(settings.enableGoogleLogin));
  const [apiKey, setApiKey] = useState(settings.firebaseApiKey || "");
  const [authDomain, setAuthDomain] = useState(settings.firebaseAuthDomain || "");
  const [projectId, setProjectId] = useState(settings.firebaseProjectId || "");
  const [storageBucket, setStorageBucket] = useState(settings.firebaseStorageBucket || "");
  const [senderId, setSenderId] = useState(settings.firebaseMessagingSenderId || "");
  const [appId, setAppId] = useState(settings.firebaseAppId || "");
  const [freeEnabled, setFreeEnabled] = useState(false);
  const [freeRam, setFreeRam] = useState("2");
  const [freeCpu, setFreeCpu] = useState("100");
  const [freeDisk, setFreeDisk] = useState("5");
  const [freeMaxServers, setFreeMaxServers] = useState("1");
  const [freeDurationHours, setFreeDurationHours] = useState("168");
  const [busy, setBusy] = useState(false);
  const [message, setMessage] = useState<{text:string; ok:boolean} | null>(null);

  useEffect(() => {
    setPanelName(settings.panelName || "SNCK PANEL");
    setPlayit(Boolean(settings.enablePlayit)); setTutorial(Boolean(settings.enableTutorial));
    setLoginAnimation(Boolean(settings.enableLoginAnimation)); setRegistration(Boolean(settings.enableRegistration));
    setTheme(settings.theme || "aurora"); setGoogle(Boolean(settings.enableGoogleLogin));
    setApiKey(settings.firebaseApiKey || ""); setAuthDomain(settings.firebaseAuthDomain || "");
    setProjectId(settings.firebaseProjectId || ""); setStorageBucket(settings.firebaseStorageBucket || "");
    setSenderId(settings.firebaseMessagingSenderId || ""); setAppId(settings.firebaseAppId || "");
  }, [settings.panelName, settings.enablePlayit, settings.enableTutorial, settings.enableLoginAnimation, settings.enableRegistration, settings.theme, settings.enableGoogleLogin, settings.firebaseApiKey, settings.firebaseAuthDomain, settings.firebaseProjectId, settings.firebaseStorageBucket, settings.firebaseMessagingSenderId, settings.firebaseAppId]);

  const save = async (payload: Record<string, unknown>, success: string) => {
    setBusy(true); setMessage(null);
    try { await axios.put("/api/system/settings", payload); await settings.fetchSettings(); setMessage({text:success,ok:true}); }
    catch (e:any) { setMessage({text:e.response?.data?.error || "Could not save settings.",ok:false}); }
    finally { setBusy(false); }
  };
  const toggle = async (key: string, value: boolean, setter: (v:boolean)=>void, label:string) => {
    setter(value); await save({[key]:value}, label + " updated.");
  };
  const testFirebase = async () => {
    setMessage(null);
    if (!apiKey.trim() || !projectId.trim()) { setMessage({text:"Enter Firebase API key and project ID first.",ok:false}); return; }
    let app: any;
    try {
      app = initializeApp({apiKey,authDomain,projectId,storageBucket,messagingSenderId:senderId,appId}, "snck-admin-firebase-test-" + Date.now());
      await deleteApp(app);
      setMessage({text:"Firebase config object initialized successfully. This checks config format, not Google provider/domain setup.",ok:true});
    } catch(e:any) {
      if (app) { try { await deleteApp(app); } catch {} }
      setMessage({text:"Firebase config test failed: " + (e.message || String(e)),ok:false});
    }
  };
  const field = (label:string, value:string, set:(v:string)=>void, placeholder:string, type="text") => <label className="block text-sm"><span className="block text-muted-foreground mb-1.5">{label}</span><input type={type} value={value} onChange={e=>set(e.target.value)} placeholder={placeholder} className="w-full bg-muted border border-border rounded-xl px-3 py-2.5 text-foreground outline-none focus:border-indigo-500" /></label>;
  const switchRow = (title:string, description:string, value:boolean, change:(v:boolean)=>void) => <label className="flex items-start justify-between gap-4 p-4 rounded-xl bg-muted/40 border border-border-subtle cursor-pointer"><span><strong className="block text-sm text-foreground">{title}</strong><span className="block text-xs text-muted-foreground mt-1">{description}</span></span><input type="checkbox" className="mt-1 h-4 w-4 accent-emerald-500" checked={value} disabled={busy} onChange={e=>change(e.target.checked)} /></label>;

  return <div className="space-y-6 mt-6">
    <section className="bg-card border border-border-subtle rounded-2xl p-5 md:p-7 shadow-xl">
      <h2 className="text-xl font-bold mb-1 flex items-center gap-3 text-foreground"><SlidersHorizontal className="text-emerald-400" size={20}/> Platform Features</h2>
      <p className="text-sm text-muted-foreground mb-5">These controls save directly to the panel's system settings.</p>
      <form className="flex flex-col sm:flex-row gap-3 mb-5" onSubmit={e=>{e.preventDefault();void save({panelName:panelName.trim()}, "Panel name saved.");}}>
        <div className="flex-1">{field("Panel Name",panelName,setPanelName,"SNCK PANEL")}</div>
        <button disabled={busy || !panelName.trim()} className="self-end rounded-xl px-5 py-2.5 bg-indigo-600 text-white disabled:opacity-50">{busy?"Saving…":"Save name"}</button>
      </form>
      <div className="space-y-3">
        {switchRow("Playit Tunnel Integration","Allow users to expose local servers through playit.gg.",playit,v=>void toggle("enablePlayit",v,setPlayit,"Playit"))}
        {switchRow("Onboarding Tutorial","Show the guided tour to new users.",tutorial,v=>void toggle("enableTutorial",v,setTutorial,"Tutorial"))}
        {switchRow("Login Animation","Enable the login screen animation.",loginAnimation,v=>void toggle("enableLoginAnimation",v,setLoginAnimation,"Login animation"))}
        {switchRow("User Registration","Allow new users to register.",registration,v=>void toggle("enableRegistration",v,setRegistration,"Registration"))}
      </div>
      <div className="mt-4 flex flex-col sm:flex-row sm:items-center gap-3"><label className="text-sm text-muted-foreground" htmlFor="snck-admin-theme">Theme preset</label><select id="snck-admin-theme" value={theme} onChange={e=>setTheme(e.target.value)} className="bg-muted border border-border rounded-xl px-3 py-2.5 text-foreground">{["aurora","midnight","nebula","cyber","royal-purple","ocean","emerald","crimson"].map(t=><option key={t} value={t}>{t}</option>)}</select><button disabled={busy} onClick={()=>void save({theme},"Theme saved.")} className="rounded-xl px-4 py-2.5 bg-indigo-600 text-white disabled:opacity-50">Save theme</button></div>
    </section>
    <section className="bg-card border border-emerald-500/20 rounded-2xl p-5 md:p-7 shadow-xl">
      <h2 className="text-xl font-bold mb-1 flex items-center gap-3 text-foreground"><Gift className="text-emerald-400" size={20}/> Free Minecraft Service</h2>
      <p className="text-sm text-muted-foreground mb-5">Owner-configured limits for regular users creating free servers.</p>
      <label className="flex items-center justify-between gap-4 rounded-xl border border-border bg-muted/40 p-4 mb-5"><span><strong className="block text-sm text-foreground">Enable free server creation</strong><span className="block text-xs text-muted-foreground mt-1">Allow normal users to create servers within these limits.</span></span><input type="checkbox" className="h-4 w-4 accent-emerald-500" checked={freeEnabled} disabled={busy || user?.role !== "owner"} onChange={e=>setFreeEnabled(e.target.checked)} /></label>
      {freeEnabled && <div className="space-y-4">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          {field("RAM per server (GB)",freeRam,setFreeRam,"2","number")}
          {field("CPU limit (%)",freeCpu,setFreeCpu,"100","number")}
          {field("Disk per server (GB)",freeDisk,setFreeDisk,"5","number")}
          {field("Servers per user",freeMaxServers,setFreeMaxServers,"1","number")}
          {field("Default lifetime (hours)",freeDurationHours,setFreeDurationHours,"168 (7 days)","number")}
        </div>
        <p className="text-xs text-muted-foreground">Set duration to 0 for no default expiry. Users can still choose Permanent when creating a server.</p>
      </div>}
      <p className="mt-3 text-xs text-muted-foreground">{freeEnabled ? "Regular users can create servers within these limits and only on nodes not locked for free service." : "Free server creation is off. Limits stay saved and appear again when you enable the service."}</p>
      <button type="button" disabled={busy || user?.role !== "owner"} onClick={()=>void save({freeService:{enabled:freeEnabled,ram:Number(freeRam),cpu:Number(freeCpu),disk:Number(freeDisk),maxServers:Number(freeMaxServers),durationHours:Number(freeDurationHours)}}, "Free service settings saved.")} className="mt-5 rounded-xl px-5 py-2.5 bg-emerald-600 text-white disabled:opacity-50">{busy?"Saving…":"Save free service settings"}</button>
    </section>
    <section className="bg-card border border-border-subtle rounded-2xl p-5 md:p-7 shadow-xl">
      <h2 className="text-xl font-bold mb-1 flex items-center gap-3 text-foreground"><KeyRound className="text-amber-400" size={20}/> Google & Firebase Authentication</h2>
      <p className="text-sm text-muted-foreground mb-5">Configure Firebase web app credentials and enable Google sign-in. Also enable Google in Firebase Authentication and authorize your panel domain in Firebase Console.</p>
      <div className="mb-5">{switchRow("Enable Google Login","Turn on the panel's Google login setting.",google,v=>setGoogle(v))}</div>
      <form onSubmit={e=>{e.preventDefault();void save({enableGoogleLogin:google,firebaseApiKey:apiKey,firebaseAuthDomain:authDomain,firebaseProjectId:projectId,firebaseStorageBucket:storageBucket,firebaseMessagingSenderId:senderId,firebaseAppId:appId},"Firebase and Google login settings saved.");}} className="space-y-4">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">{field("Firebase API Key",apiKey,setApiKey,"AIza…")}{field("Auth Domain",authDomain,setAuthDomain,"project.firebaseapp.com")}{field("Project ID",projectId,setProjectId,"your-project-id")}{field("Storage Bucket",storageBucket,setStorageBucket,"project.appspot.com")}{field("Messaging Sender ID",senderId,setSenderId,"numeric sender ID")}{field("Firebase App ID",appId,setAppId,"1:…:web:…")}</div>
        <div className="flex flex-wrap gap-3"><button disabled={busy} type="submit" className="rounded-xl px-5 py-2.5 bg-indigo-600 text-white disabled:opacity-50">{busy?"Saving…":"Save Firebase settings"}</button><button type="button" onClick={()=>void testFirebase()} className="rounded-xl px-5 py-2.5 border border-border text-foreground">Test config</button><a className="rounded-xl px-5 py-2.5 border border-border text-amber-300" href="https://console.firebase.google.com" target="_blank" rel="noreferrer">Open Firebase Console ↗</a></div>
      </form>
    </section>
    {message && <div role="status" className={"rounded-xl border p-4 flex gap-3 items-start "+(message.ok?"border-emerald-500/30 bg-emerald-500/10 text-emerald-300":"border-red-500/30 bg-red-500/10 text-red-300")}>{message.ok?<CheckCircle2 size={18}/>:<AlertCircle size={18}/>}<span>{message.text}</span></div>}
  </div>;
}
