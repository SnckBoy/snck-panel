import express from "express";
import crypto from "crypto";
import dns from "dns/promises";
import tls from "tls";
import Docker from "dockerode";
import {readJSON,writeJSON} from "../services/db.js";
import {requireAdmin} from "../middleware/auth.js";
import {audit,encryptSecret,hashSecret,randomSecret,rateLimit,decryptSecret} from "../services/security.js";
import {createDns} from "../services/cloudflare.js";
import {nodeControl} from "../services/nodeClient.js";
import {nodeConnection, nodeUrlParts} from "../services/nodeEndpoint.js";
const router=express.Router();
// Safe, read-only node inventory for regular users. Never expose credentials,
// private nodes, daemon connection secrets, or mutation endpoints here.
router.get("/public", async (_req, res) => {
 try {
  const nodes = await readJSON("nodes.json") || [];
  const now = Date.now();
  res.json(nodes.filter((n:any) => n.visibility !== "private").map((n:any) => ({
   id: String(n.id), name: String(n.name || "Node"), description: String(n.description || ""),
   location: String(n.location || ""), status: nodeStatus(n, now),
   memory: Number(n.memory || 0), disk: Number(n.disk || 0), cpu: Number(n.cpu || 0),
   freeServiceLocked: Boolean(n.freeServiceLocked),
   lastHeartbeat: n.lastHeartbeat || null
  })));
 } catch { res.status(500).json({error:"Could not load public node inventory"}); }
});
router.use(requireAdmin,rateLimit());
const file="nodes.json", setupFile="node_setup_tokens.json";
// Nodes are created explicitly by an administrator. Panel-only installs must not
// manufacture a misleading local node before a daemon has been installed and registered.

const sanitize=(n:any)=>{const {credential,credentialHash,cloudflareAccessClientSecret,...safe}=n;return safe};
const HEARTBEAT_TIMEOUT_MS = Math.max(30000, Number(process.env.NODE_HEARTBEAT_TIMEOUT_MS || 45000));
const localDockerCheck=async(n:any)=>{const socketPath=String(n?.dockerHost||"/var/run/docker.sock");const docker=new Docker({socketPath});await docker.ping();return {docker:true,system:{status:"ready",socket:socketPath}}};
const nodeStatus=(n:any, now=Date.now())=>{
 const lastHeartbeat = n.lastHeartbeat ? Date.parse(n.lastHeartbeat) : NaN;
 if (n.maintenance) return "MAINTENANCE";
 if (n.restartingAt && Number.isFinite(Date.parse(n.restartingAt)) && now - Date.parse(n.restartingAt) < 90000) return "RESTARTING";
 if (n.installing) return "INSTALLING";
 if (n.error) return "ERROR";
 if (n.disabled) return "DISABLED";
 if (n.isLocal && n.localReady === true) return "ONLINE";
 if (n.isLocal && !n.credentialHash) return "SETUP_REQUIRED";
 return Number.isFinite(lastHeartbeat) && now - lastHeartbeat <= HEARTBEAT_TIMEOUT_MS ? "ONLINE" : "OFFLINE";
};
const withNodeStatus=(n:any, now=Date.now())=>({...sanitize(n),status:nodeStatus(n,now),lastHeartbeat:n.lastHeartbeat||null,heartbeatAgeMs:n.lastHeartbeat&&Number.isFinite(Date.parse(n.lastHeartbeat))?Math.max(0,now-Date.parse(n.lastHeartbeat)):null});

async function publicNodes(){
 const nodes=await readJSON(file)||[]; const now=Date.now();
 return nodes.map((n:any)=>withNodeStatus(n,now));
}
router.get("/",async(req,res)=>res.json(await publicNodes()));
router.post("/local",async(req,res)=>{
 try{
  const nodes=await readJSON(file)||[];
  const port=Number(req.body?.port)||8080;
  const dockerHost=String(req.body?.dockerHost||"/var/run/docker.sock");
  if(!Number.isInteger(port)||port<1||port>65535)return res.status(400).json({error:"Local node port must be a valid TCP port"});
  const existing=nodes.find((x:any)=>x.id==="local");
  const node=existing||{id:"local",createdAt:new Date().toISOString()};
  node.name=String(req.body?.name||node.name||"Local Node").trim();
  node.description=String(req.body?.description||node.description||"Panel host");
  node.hostname="127.0.0.1";
  node.fqdn="127.0.0.1";
  node.publicIp="127.0.0.1";
  node.apiPort=port;
  node.sftpPort=Number(req.body?.sftpPort||node.sftpPort||2022);
  node.tls=false;
  node.behindProxy=false;
  node.isLocal=true;
  node.disabled=false;
  node.maintenance=false;
  node.dockerHost=dockerHost;
  if(!existing)nodes.push(node);
  try{
   const runtime=await localDockerCheck(node);
   node.localReady=true;
   node.lastHeartbeat=new Date().toISOString();
   node.lastStats=runtime;
   node.error=null;
   await writeJSON(file,nodes);
   await audit(existing?"node.local.reused":"node.local.created",req,{nodeId:"local"});
   return res.status(existing?200:201).json({...sanitize(node),status:"ONLINE",local:true,reused:Boolean(existing),runtime,ready:true});
  }catch(e:any){
   node.localReady=false;
   node.lastHeartbeat=null;
   node.lastStats={docker:false,system:{status:"unavailable",socket:dockerHost}};
   node.error=String(e?.message||e);
   await writeJSON(file,nodes);
   await audit(existing?"node.local.updated_unavailable":"node.local.created_unavailable",req,{nodeId:"local"});
   return res.status(existing?200:201).json({...sanitize(node),status:"ERROR",local:true,reused:Boolean(existing),ready:false,dockerUnavailable:true,details:node.error,hint:"The local node record was saved, but Docker is unavailable. Start Docker Engine and make /var/run/docker.sock accessible, then click Test health."});
  }
 }catch(e:any){
  return res.status(500).json({error:"Local node could not be saved",details:String(e?.message||e),hint:"Check the panel data directory permissions and panel logs."});
 }
});
router.get("/:id",async(req,res)=>{const n=(await readJSON(file)||[]).find((x:any)=>x.id===req.params.id);if(!n)return res.status(404).json({error:"Node not found"});res.json(withNodeStatus(n))});
router.post("/",async(req,res)=>{
 const b=req.body || {};
 const hostname=String(b.hostname || b.fqdn || b.publicIp || "").trim();
 const apiPort=Number(b.apiPort || 8080), sftpPort=Number(b.sftpPort || 2022);
 const numberField=(value:any, fallback=0) => { const n=Number(value ?? fallback); return Number.isFinite(n) ? n : NaN; };
 const memory=numberField(b.memory), disk=numberField(b.disk), cpu=numberField(b.cpu, 100), memoryOverallocate=numberField(b.memoryOverallocate), diskOverallocate=numberField(b.diskOverallocate);
 if(!String(b.name || "").trim() || !hostname || !Number.isInteger(apiPort) || apiPort < 1 || apiPort > 65535 || !Number.isInteger(sftpPort) || sftpPort < 1 || sftpPort > 65535) return res.status(400).json({error:"name, hostname, daemon port, and SFTP port are required and must be valid"});
 if([memory,disk,cpu,memoryOverallocate,diskOverallocate].some((n)=>!Number.isFinite(n)) || memory < 0 || disk < 0 || cpu < 0 || memoryOverallocate < -1 || diskOverallocate < -1) return res.status(400).json({error:"Resource limits and over-allocation values are invalid"});
 const nodes=await readJSON(file)||[];const id=crypto.randomUUID();const setupToken=randomSecret(32);
 const node={id,name:String(b.name).trim(),description:b.description||"",hostname,fqdn:b.fqdn||hostname,publicIp:b.publicIp||"",internalIp:b.internalIp||"",apiPort,sftpPort,location:b.location||"",visibility:b.visibility === "private" ? "private" : "public",tls:b.tls!==false,behindProxy:Boolean(b.behindProxy),memory,disk,cpu,memoryOverallocate,diskOverallocate,serverDirectory:b.serverDirectory||"/var/lib/shironex/servers",dockerHost:b.dockerHost||"/var/run/docker.sock",disabled:false,cloudflareZoneId:b.cloudflareZoneId||null,cloudflareRecordId:null,cloudflareAccessClientId:String(b.cloudflareAccessClientId||"").trim()||null,cloudflareAccessClientSecret:b.cloudflareAccessClientSecret?encryptSecret(String(b.cloudflareAccessClientSecret)):null,credential:encryptSecret(randomSecret(48)),credentialHash:"",createdAt:new Date().toISOString(),lastHeartbeat:null,daemonVersion:null};
 node.credentialHash=hashSecret(decryptSecret(node.credential));
 nodes.push(node);await writeJSON(file,nodes);
 let dnsWarning:any=null;
 if(b.createCloudflareDns && b.cloudflareZoneId){
   try{const rec=await createDns(String(b.cloudflareZoneId),{type:"A",name:b.fqdn||b.hostname,content:b.publicIp,ttl:300,proxied:false});node.cloudflareRecordId=rec.id;await writeJSON(file,nodes);}
   catch(e:any){dnsWarning=e.message}
 }
 const tokens=await readJSON(setupFile)||[];tokens.push({hash:hashSecret(setupToken),nodeId:id,expiresAt:Date.now()+15*60*1000,used:false});await writeJSON(setupFile,tokens);
 await audit("node.created",req,{nodeId:id,name:node.name});
 res.status(201).json({...sanitize(node),setupToken,dnsWarning});
});
router.post("/:id/rotate",async(req,res)=>{const nodes=await readJSON(file)||[];const n=nodes.find((x:any)=>x.id===req.params.id);if(!n)return res.status(404).json({error:"Node not found"});const secret=randomSecret(48);n.credential=encryptSecret(secret);n.credentialHash=hashSecret(secret);await writeJSON(file,nodes);await audit("node.credentials.rotated",req,{nodeId:n.id});res.json({success:true})});
router.patch("/:id",async(req,res)=>{const nodes=await readJSON(file)||[];const n=nodes.find((x:any)=>x.id===req.params.id);if(!n)return res.status(404).json({error:"Node not found"});const candidate={...n,...req.body};const hostname=String(candidate.hostname||candidate.fqdn||candidate.publicIp||"").trim();const apiPort=Number(candidate.apiPort),sftpPort=Number(candidate.sftpPort||2022);const numberField=(value:any,fallback=0)=>{const number=Number(value??fallback);return Number.isFinite(number)?number:NaN};const memory=numberField(candidate.memory),disk=numberField(candidate.disk),cpu=numberField(candidate.cpu),memoryOverallocate=numberField(candidate.memoryOverallocate),diskOverallocate=numberField(candidate.diskOverallocate);if(!String(candidate.name||"").trim()||!hostname||!Number.isInteger(apiPort)||apiPort<1||apiPort>65535||!Number.isInteger(sftpPort)||sftpPort<1||sftpPort>65535)return res.status(400).json({error:"name, hostname, daemon port, and SFTP port are required and must be valid"});if([memory,disk,cpu,memoryOverallocate,diskOverallocate].some((value)=>!Number.isFinite(value))||memory<0||disk<0||cpu<0||memoryOverallocate<-1||diskOverallocate<-1)return res.status(400).json({error:"Resource limits and over-allocation values are invalid"});const editable=["name","description","hostname","fqdn","publicIp","internalIp","location","visibility","tls","behindProxy","serverDirectory","dockerHost","freeServiceLocked"];for(const key of editable)if(req.body[key]!==undefined)n[key]=key==="name"?String(req.body[key]).trim():req.body[key];n.hostname=hostname;n.apiPort=apiPort;n.sftpPort=sftpPort;n.memory=memory;n.disk=disk;n.cpu=cpu;n.memoryOverallocate=memoryOverallocate;n.diskOverallocate=diskOverallocate;if(req.body.cloudflareAccessClientId!==undefined)n.cloudflareAccessClientId=String(req.body.cloudflareAccessClientId||"").trim()||null;if(req.body.cloudflareAccessClientSecret!==undefined)n.cloudflareAccessClientSecret=req.body.cloudflareAccessClientSecret?encryptSecret(String(req.body.cloudflareAccessClientSecret)):null;await writeJSON(file,nodes);await audit("node.updated",req,{nodeId:n.id});res.json(sanitize(n))});
router.post("/:id/enable",async(req,res)=>{const nodes=await readJSON(file)||[];const n=nodes.find((x:any)=>x.id===req.params.id);if(!n)return res.status(404).json({error:"Node not found"});n.disabled=false;await writeJSON(file,nodes);await audit("node.enabled",req,{nodeId:n.id});res.json({success:true,status:nodeStatus(n)})});
router.post("/:id/disable",async(req,res)=>{const nodes=await readJSON(file)||[];const n=nodes.find((x:any)=>x.id===req.params.id);if(!n)return res.status(404).json({error:"Node not found"});n.disabled=true;await writeJSON(file,nodes);await audit("node.disabled",req,{nodeId:n.id});res.json({success:true,status:nodeStatus(n)})});
router.post("/:id/maintenance",async(req,res)=>{const nodes=await readJSON(file)||[];const n=nodes.find((x:any)=>x.id===req.params.id);if(!n)return res.status(404).json({error:"Node not found"});n.maintenance=true;await writeJSON(file,nodes);await audit("node.maintenance.enabled",req,{nodeId:n.id});res.json({success:true,status:nodeStatus(n)})});
router.delete("/:id/maintenance",async(req,res)=>{const nodes=await readJSON(file)||[];const n=nodes.find((x:any)=>x.id===req.params.id);if(!n)return res.status(404).json({error:"Node not found"});n.maintenance=false;await writeJSON(file,nodes);await audit("node.maintenance.disabled",req,{nodeId:n.id});res.json({success:true,status:nodeStatus(n)})});
router.delete("/:id",async(req,res)=>{const nodes=await readJSON(file)||[];const n=nodes.find((x:any)=>x.id===req.params.id);if(!n)return res.status(404).json({error:"Node not found"});const servers=await readJSON("servers.json")||[];if(servers.some((s:any)=>s.nodeId===n.id))return res.status(409).json({error:"Cannot delete a node with assigned servers"});await writeJSON(file,nodes.filter((x:any)=>x.id!==n.id));await audit("node.deleted",req,{nodeId:n.id});res.json({success:true})});
 router.post("/:id/test",async(req,res)=>{try{const nodes=await readJSON(file)||[];const n=nodes.find((x:any)=>x.id===req.params.id);if(!n)return res.status(404).json({error:"Node not found"});const connection=nodeConnection(n);const {url,hostname,port}=nodeUrlParts(connection.baseUrl);const result=await nodeControl.health(connection);res.json({success:true,health:result,endpoint:{protocol:url.protocol.replace(":",""),hostname,port}})}catch(e:any){res.status(Number(e?.statusCode||502)).json({success:false,error:e.message||"Node health check failed",nodeUnavailable:e?.nodeUnavailable===true,timeout:e?.timeout===true,hint:"Verify the node FQDN, Cloudflare Tunnel or Zero Trust ingress, TLS mode, daemon port, and firewall."})}});
 router.post("/:id/reconnect",async(req,res)=>{try{const nodes=await readJSON(file)||[];const n=nodes.find((x:any)=>x.id===req.params.id);if(!n)return res.status(404).json({error:"Node not found"});if(n.disabled)return res.status(409).json({error:"Node is disabled"});const connection=nodeConnection(n);const health:any=await nodeControl.health(connection);n.lastHeartbeat=new Date().toISOString();n.lastStats=health;n.error=null;await writeJSON(file,nodes);await audit("node.reconnected",req,{nodeId:n.id});res.json({success:true,status:nodeStatus(n),health,endpoint:connection.baseUrl})}catch(e:any){res.status(Number(e?.statusCode||502)).json({success:false,error:e.message||"Node reconnect failed",nodeUnavailable:e?.nodeUnavailable===true,timeout:e?.timeout===true,hint:"Verify the node FQDN, Cloudflare Tunnel or Zero Trust ingress, TLS mode, daemon port, and firewall."})}});
 router.post("/:id/restart",async(req,res)=>{try{const nodes=await readJSON(file)||[];const n=nodes.find((x:any)=>x.id===req.params.id);if(!n)return res.status(404).json({error:"Node not found"});if(n.disabled)return res.status(409).json({error:"Node is disabled"});const connection=nodeConnection(n);const result:any=await nodeControl.restartDaemon(connection);n.lastHeartbeat=null;n.lastStats=null;n.error=null;n.restartingAt=new Date().toISOString();await writeJSON(file,nodes);await audit("node.restart_requested",req,{nodeId:n.id});res.status(202).json({success:true,restarting:true,message:result?.message||"Node daemon restart accepted",status:"RESTARTING"})}catch(e:any){res.status(Number(e?.statusCode||503)).json({success:false,error:e.message||"Node daemon restart failed",nodeUnavailable:e?.nodeUnavailable===true,timeout:e?.timeout===true,hint:"Verify the node FQDN, Cloudflare Tunnel or Zero Trust ingress, TLS mode, daemon port, and firewall."})}});
router.get("/:id/stats",async(req,res)=>{const nodes=await readJSON(file)||[];const n=nodes.find((x:any)=>x.id===req.params.id);if(!n)return res.status(404).json({error:"Node not found"});if(n.disabled)return res.status(409).json({error:"Node disabled"});res.json({...n.lastStats,status:nodeStatus(n),lastHeartbeat:n.lastHeartbeat||null,heartbeatAgeMs:n.lastHeartbeat&&Number.isFinite(Date.parse(n.lastHeartbeat))?Math.max(0,Date.now()-Date.parse(n.lastHeartbeat)):null});});
router.get("/:id/health",async(req,res)=>{const nodes=await readJSON(file)||[];const n=nodes.find((x:any)=>x.id===req.params.id);if(!n)return res.status(404).json({error:"Node not found"});const checks:any={cloudflare:{status:"not_checked"},dns:{status:"not_checked"},node:{status:"offline"},docker:{status:"unknown"},system:{status:"unknown"}};if(n.isLocal){try{const runtime=await localDockerCheck(n);n.localReady=true;n.lastHeartbeat=new Date().toISOString();n.lastStats=runtime;n.error=null;await writeJSON(file,nodes);return res.json({...checks,node:{status:"ok",endpoint:{protocol:"local",hostname:"127.0.0.1",port:n.apiPort}},docker:{status:"ok",ready:true},system:runtime.system});}catch(e:any){n.localReady=false;n.error=String(e?.message||e);await writeJSON(file,nodes);return res.json({...checks,node:{status:"error",error:n.error},docker:{status:"error",error:n.error},system:{status:"error"}})}}try{
 const connection=nodeConnection(n); const {url,hostname,port}=nodeUrlParts(connection.baseUrl); const resolved=await dns.lookup(hostname); checks.dns={status:"ok",address:resolved.address};
 if(url.protocol === "https:"){await new Promise((resolve,reject)=>{const socket=tls.connect({host:hostname,port,servername:hostname,rejectUnauthorized:n.tlsVerify !== false},()=>{socket.end();resolve(true)});socket.on("error",reject);setTimeout(()=>{socket.destroy();reject(new Error("TLS timeout"))},5000)});checks.node.tls="valid"}
 const started=Date.now();const h=await nodeControl.health(connection);checks.node={status:"ok",latencyMs:Date.now()-started,tls:checks.node.tls,endpoint:{protocol:url.protocol.replace(":",""),hostname,port},...h};checks.docker=h.docker;checks.system=h.system;
 }catch(e:any){const message=String(e?.message||"Node health check failed");const code=String(e?.code||"");const timeout=e?.timeout===true||code==="ETIMEDOUT"||/timeout/i.test(message);const nodeUnavailable=e?.nodeUnavailable===true||/ECONNREFUSED|ENOTFOUND|EHOSTUNREACH|ENETUNREACH|ECONNRESET/i.test(`${code} ${message}`);checks.node={status:"error",error:message,nodeUnavailable,timeout,hint:"Verify the node FQDN, DNS, Cloudflare Tunnel or Zero Trust ingress, TLS mode, daemon port, and firewall."};checks.dns=checks.dns.status==="not_checked"?{status:"error",error:message}:checks.dns}res.json(checks)});
export default router;
