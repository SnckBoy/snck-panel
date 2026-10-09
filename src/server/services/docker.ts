import Docker from "dockerode";
import fs from "fs-extra";
import path from "path";
let socketIo: any = null;
export const setSocketIO = (instance: any) => { socketIo = instance; };
const emitServerLog = (serverId: string, message: string) => {
  socketIo?.to(`server_${serverId}`).emit("log", message);
};
import { readJSON } from "./db.js";
import { randomSecret } from "./security.js";
import { nodeControl } from "./nodeClient.js";
import { nodeConnection } from "./nodeEndpoint.js";

const configuredSocketPath = process.env.DOCKER_SOCKET_PATH;
const configuredSocketExists = Boolean(configuredSocketPath && fs.existsSync(configuredSocketPath));

const getSocketPath = () => {
  if (process.platform === 'win32') return '//./pipe/docker_engine';
  if (configuredSocketExists && configuredSocketPath) {
    return configuredSocketPath;
  }
  if (fs.existsSync('/var/run/docker.sock')) return '/var/run/docker.sock';
  if (fs.existsSync('/run/docker.sock')) return '/run/docker.sock';
  return '/var/run/docker.sock';
};

export const isSandbox = false && !fs.existsSync('/var/run/docker.sock') &&
  !fs.existsSync('/run/docker.sock') &&
  !configuredSocketExists &&
  process.platform !== 'win32';

export const defaultDocker = new Docker({ socketPath: getSocketPath() });

export const getDocker = async (nodeId?: string) => {
  if (!nodeId || nodeId === "local") return defaultDocker;
  const nodes = await readJSON("nodes.json") || [];
  const node = nodes.find((n: any) => n.id === nodeId);
  if (node) {
    let host = node.ip;
    let protocol: "http" | "https" | "ssh" = "http";
    let port = node.port;

    if (!host.startsWith("http://") && !host.startsWith("https://") && port === 443) {
      protocol = "https";
    }

    if (host.startsWith("http://") || host.startsWith("https://")) {
      try {
        const url = new URL(host);
        protocol = (url.protocol.replace(':', '') === 'https' ? 'https' : 'http');
        host = url.hostname;
        if (url.port) port = parseInt(url.port);
        else port = protocol === "https" ? 443 : 80;
      } catch (e) {
        console.error("Invalid URL in node IP", host);
      }
    }
    return new Docker({
      protocol,
      host,
      port,
      headers: { 
        Authorization: "Bearer " + node.key,
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
      }
    });
  }
  return defaultDocker;
};

// Mock state for sandbox demo
export const mockState: Record<string, boolean> = {};

export const SUPPORTED_JAVA_VERSIONS = ["8", "11", "17", "21", "25"] as const;

export const getVersions = async (type: string = "PAPER") => {
  const normalizedType = type.toUpperCase();
  if (normalizedType === "VELOCITY") {
    return ["latest", "3.3.0-SNAPSHOT"];
  }
  if (normalizedType === "BUNGEECORD" || normalizedType === "WATERFALL") {
    return ["latest"];
  }
  
  return [
    "latest", "1.21.11", "1.21.10", "1.21.9", "1.21.8", "1.21.7", "1.21.6", "1.21.5", "1.21.4", "1.21.3", "1.21.1", "1.21", 
    "1.20.6", "1.20.5", "1.20.4", "1.20.2", "1.20.1", "1.20", 
    "1.19.4", "1.19.3", "1.19.2", "1.19.1", "1.19", 
    "1.18.2", "1.18.1", "1.18", "1.17.1", "1.17", "1.16.5", "1.16.4", "1.16.3", "1.16.2", "1.16.1", "1.15.2", "1.15.1", "1.15", 
    "1.14.4", "1.14.3", "1.14.2", "1.14.1", "1.14", "1.13.2", "1.13.1", "1.13", "1.12.2", "1.12.1", "1.12", "1.11.2", "1.10.2", 
    "1.9.4", "1.8.8", "1.7.10"
  ];
};

const remoteNode = async (nodeId?:string) => {
  if(!nodeId || nodeId==="local") return null;
  const nodes=await readJSON("nodes.json")||[]; const n=nodes.find((x:any)=>x.id===nodeId);
  if(!n) throw new Error("Node not found");
  if(n.disabled) throw new Error("Node is disabled");
  return nodeConnection(n);
};

export const createServerContainer = async (serverData: any, nodeId?: string) => {
  const remote = await remoteNode(nodeId || serverData.nodeId);
  if (remote) {
    const response: any = await nodeControl.createServer(remote, serverData);
    if (!response?.containerId || typeof response.containerId !== "string") {
      const error: any = new Error("Node returned no container ID. Check the node daemon version, Cloudflare Access policy, tunnel ingress, and node credential.");
      error.statusCode = 502;
      error.nodeResponseInvalid = true;
      throw error;
    }
    return response.containerId;
  }
  const docker = await getDocker(nodeId || serverData.nodeId);
  if (isSandbox) {
    mockState[serverData.id] = false;
    return "mock-container-id-" + serverData.id;
  }

  const serverType = String(serverData.type || "PAPER").toUpperCase();
  const isProxy = ["VELOCITY", "BUNGEECORD", "WATERFALL"].includes(serverType);
  const javaVersion = SUPPORTED_JAVA_VERSIONS.includes(String(serverData.javaVersion) as typeof SUPPORTED_JAVA_VERSIONS[number]) ? String(serverData.javaVersion) : "";
  const javaTag = javaVersion ? `:java${javaVersion}` : ":latest";
  const shortImage = isProxy ? "itzg/bungeecord:latest" : `itzg/minecraft-server${javaTag}`;
  const fullImage = isProxy ? "docker.io/itzg/bungeecord:latest" : `docker.io/itzg/minecraft-server${javaTag}`;

  const findImageId = async (): Promise<string | null> => {
    try {
      const images = await docker.listImages();
      const matched = images.find(img => 
        img.RepoTags && img.RepoTags.some(tag => tag.includes(shortImage) || tag.includes(fullImage))
      );
      if (matched) return matched.Id;
    } catch(e) {
      console.warn("Failed to list images:", e);
    }
    return null;
  };

  const pullImageStream = async (imgTag: string) => {
    console.log(`Pulling image ${imgTag}...`);
    const { exec } = require("child_process");
    const { promisify } = require("util");
    const execAsync = promisify(exec);
    const engine = "docker";
    
    try {
      console.log(`Executing: ${engine} pull ${imgTag}`);
      const { stdout, stderr } = await execAsync(`${engine} pull ${imgTag}`);
      console.log(`${engine} pull stdout:`, stdout);
      if (stderr) console.warn(`${engine} pull stderr:`, stderr);
    } catch (cliErr) {
      console.warn(`CLI pull failed for ${imgTag}: ${cliErr}. Trying Docker API fallback...`);
      await new Promise((resolve, reject) => {
        docker.pull(imgTag, (err: any, stream: any) => {
          if (err) return reject(err);
          docker.modem.followProgress(stream, (err: any, output: any) => {
            if (err) return reject(err);
            resolve(output);
          });
        });
      });
    }
  };

  const ensureImage = async (): Promise<string> => {
    let existingId = await findImageId();
    if (existingId) return existingId;

    try {
      await pullImageStream(shortImage);
      let idAfterShort = await findImageId();
      if (idAfterShort) return idAfterShort;
    } catch (e) {
      console.warn(`Failed to pull ${shortImage}...`, e);
    }

    console.warn(`Attempting fallback pull with ${fullImage}...`);
    await pullImageStream(fullImage);
    let idAfterFull = await findImageId();
    if (idAfterFull) return idAfterFull;

    return shortImage; // Fallback to string tag if we somehow couldn't find ID
  };

  const targetImage = await ensureImage();

  const serverDir = path.join(process.cwd(), ".data", "servers", serverData.id);
  await fs.ensureDir(serverDir);
  // Seed the workspace immediately so the file manager and Properties page are
  // useful before the image has finished its first boot cycle. The container may
  // later replace these defaults with its complete generated configuration.
  if (!isProxy) {
    const propertiesPath = path.join(serverDir, "server.properties");
    if (!(await fs.pathExists(propertiesPath))) {
      await fs.writeFile(propertiesPath, [
        `server-port=${serverData.port}`,
        "motd=A Minecraft Server",
        "online-mode=true",
        "pvp=true",
        "difficulty=easy",
        "gamemode=survival",
        "max-players=20",
        "view-distance=10",
        "allow-flight=false",
        "enable-command-block=false",
        "hardcore=false",
        "",
      ].join("\n"));
    }
    const eulaPath = path.join(serverDir, "eula.txt");
    if (!(await fs.pathExists(eulaPath))) await fs.writeFile(eulaPath, "# Generated by Snck\neula=true\n");
  }

  const envVars = [
    `TYPE=${serverType}`,
    `VERSION=${serverData.version}`,
    `MEMORY=${serverData.ram}G`,
    `INIT_MEMORY=128M`,
    `SERVER_PORT=${serverData.port}`,
  ];

  if (!isProxy) {
    envVars.push(
      `EULA=TRUE`,
      `ENABLE_RCON=true`,
      // A hardcoded "admin" RCON password meant any container reachable on the same
      // Docker network could issue console commands to every game server. Each
      // container now gets its own random password instead.
      `RCON_PASSWORD=${randomSecret(12)}`,
      `JVM_OPTS=-DPaper.IgnoreWorldDataVersion=true`,
      `JVM_DD_OPTS=Paper.IgnoreWorldDataVersion=true,paper.ignoreWorldDataVersion=true`
    );
  }

  const buildContainerOptions = (img: string) => ({
    Image: img,
    name: `shironex-server-${serverData.id}`,
    Tty: true,
    OpenStdin: true,
    StdinOnce: false,
    Env: envVars,
    ExposedPorts: {
      [`${serverData.port}/tcp`]: {}
    },
    HostConfig: {
      PortBindings: {
        [`${serverData.port}/tcp`]: [
          {
            HostPort: `${serverData.port}`
          }
        ]
      },
      Binds: [`${serverDir}:${isProxy ? '/server' : '/data'}`]
    }
  });

  let container;
  try {
    container = await docker.createContainer(buildContainerOptions(targetImage));
  } catch (err: any) {
    const errStr = String(err?.message || err);
    if (err?.statusCode === 404 || errStr.includes("404") || errStr.includes("no such image")) {
      const altImage = targetImage === shortImage ? fullImage : shortImage;
      console.log(`404 image error with ${targetImage}. Attempting fallback with ${altImage}...`);
      try {
        await pullImageStream(altImage);
        container = await docker.createContainer(buildContainerOptions(altImage));
      } catch (fallbackErr) {
        console.log(`Pulling ${targetImage} directly and retrying...`);
        await pullImageStream(targetImage);
        container = await docker.createContainer(buildContainerOptions(targetImage));
      }
    } else {
      throw err;
    }
  }

  return container.id;
};

export const startContainer = async (containerId: string, nodeId?: string) => {
  const remote = await remoteNode(nodeId);
  if (remote) { await nodeControl.start(remote, containerId); return; }
  const docker = await getDocker(nodeId);
  if (isSandbox) {
    const id = containerId.replace("mock-container-id-", "");
    mockState[id] = true;
    
    // In sandbox mode, mock the generation of server files that the docker container would normally do
    try {
      const servers = await readJSON("servers.json") || [];
      const server = servers.find((s: any) => s.id === id);
      if (server) {
        const serverDir = path.join(process.cwd(), ".data", "servers", id);
        await fs.ensureDir(serverDir);
        const type = (server.type || "PAPER").toUpperCase();
        
        if (["VELOCITY", "BUNGEECORD", "WATERFALL"].includes(type)) {
          const configName = type === "VELOCITY" ? "velocity.toml" : "config.yml";
          const configPath = path.join(serverDir, configName);
          if (!fs.existsSync(configPath)) {
            await fs.writeFile(configPath, "# Autogenerated proxy config in sandbox mode\n# Port: " + server.port + "\n");
          }
        } else {
          const propsPath = path.join(serverDir, "server.properties");
          if (!fs.existsSync(propsPath)) {
            await fs.writeFile(propsPath, "server-port=" + server.port + "\nmotd=A Minecraft Server\n");
          }
        }
      }
    } catch(e) {}
    
    emitServerLog(id, `[System] Server started (Sandbox Mode).\\r\\n`);
    return;
  }
  const container = docker.getContainer(containerId);
  await container.start();
};

export const stopContainer = async (containerId: string, nodeId?: string) => {
  const remote = await remoteNode(nodeId);
  if (remote) { await nodeControl.stop(remote, containerId); return; }
  const docker = await getDocker(nodeId);
  if (isSandbox) {
    const id = containerId.replace("mock-container-id-", "");
    mockState[id] = false;
    emitServerLog(id, `[System] Server stopped (Sandbox Mode).\\r\\n`);
    return;
  }
  const container = docker.getContainer(containerId);
  await container.stop();
};

export const restartContainer = async (containerId: string, nodeId?: string) => {
  const remote = await remoteNode(nodeId);
  if (remote) { await nodeControl.restart(remote, containerId); return; }
  const docker = await getDocker(nodeId);
  if (isSandbox) {
    const id = containerId.replace("mock-container-id-", "");
    mockState[id] = true;
    emitServerLog(id, `[System] Server restarted (Sandbox Mode).\\r\\n`);
    return;
  }
  const container = docker.getContainer(containerId);
  await container.restart();
};

export const deleteContainer = async (containerId: string, nodeId?: string) => {
  const remote = await remoteNode(nodeId);
  if (remote) { await nodeControl.delete(remote, containerId); return; }
  const docker = await getDocker(nodeId);
  if (isSandbox) {
    const id = containerId.replace("mock-container-id-", "");
    delete mockState[id];
    return;
  }
  const container = docker.getContainer(containerId);
  try {
    const info = await container.inspect();
    if (info.State.Running) {
      await container.stop();
    }
    await container.remove({ force: true });
  } catch (err) {
    console.error("Error deleting container", err);
  }
};

export const getContainerStatus = async (containerId: string, nodeId?: string) => {
  const remote = await remoteNode(nodeId);
  if (remote) {
    try {
      return await nodeControl.status(remote, containerId);
    } catch (error: any) {
      // A missing Docker runtime should leave the server visible as offline so
      // operators can open its page and read the actionable start error.
      if (error?.dockerUnavailable === true) return null;
      throw error;
    }
  }
  const docker = await getDocker(nodeId);
  if (isSandbox) {
    const id = containerId.replace("mock-container-id-", "");
    const isRunning = mockState[id] || false;
    return { State: { Running: isRunning, Status: isRunning ? "running" : "exited" } };
  }
  try {
    const container = docker.getContainer(containerId);
    const info = await container.inspect();
    return info;
  } catch (e) {
    return null;
  }
};

export const getContainerStats = async (containerId: string, nodeId?: string) => {
  const remote = await remoteNode(nodeId);
  if (remote) {
    try {
      return await nodeControl.statsServer(remote, containerId);
    } catch (error: any) {
      if (error?.dockerUnavailable === true) {
        return { available: false, dockerUnavailable: true, timestamp: Date.now(), cpu: null, ram: null, disk: null, networkRxBytes: null, networkTxBytes: null };
      }
      throw error;
    }
  }
  const docker = await getDocker(nodeId);
  if (isSandbox) {
    return {
      available: false,
      timestamp: Date.now(),
      cpu: null,
      ram: null,
      disk: null,
      networkRxBytes: null,
      networkTxBytes: null,
    };
  }
  try {
    const container = docker.getContainer(containerId);
    const info = await container.inspect();
    if (!info.State.Running) {
      return { available: true, timestamp: Date.now(), cpu: 0, ram: 0, disk: null, networkRxBytes: 0, networkTxBytes: 0 };
    }
    const statsResult: any = await container.stats({ stream: false });

    let cpuPercent: number | null = null;
    try {
      const cpuDelta = statsResult.cpu_stats.cpu_usage.total_usage - statsResult.precpu_stats.cpu_usage.total_usage;
      const systemDelta = statsResult.cpu_stats.system_cpu_usage - statsResult.precpu_stats.system_cpu_usage;
      if (systemDelta > 0.0 && cpuDelta >= 0.0) {
        const cpus = statsResult.cpu_stats.online_cpus || statsResult.cpu_stats.cpu_usage.percpu_usage?.length || 1;
        cpuPercent = (cpuDelta / systemDelta) * cpus * 100.0;
      }
    } catch {}

    let ramMB: number | null = null;
    try {
      const memoryStats = statsResult.memory_stats.stats || {};
      const cache = memoryStats.cache || memoryStats.inactive_file || memoryStats.total_inactive_file || 0;
      const usedMemory = Number(statsResult.memory_stats.usage) - Number(cache);
      if (Number.isFinite(usedMemory) && usedMemory >= 0) ramMB = usedMemory / 1024 / 1024;
    } catch {}

    let networkRxBytes = 0;
    let networkTxBytes = 0;
    let networkAvailable = false;
    try {
      for (const network of Object.values(statsResult.networks || {}) as any[]) {
        networkRxBytes += Number(network.rx_bytes) || 0;
        networkTxBytes += Number(network.tx_bytes) || 0;
        networkAvailable = true;
      }
    } catch {}

    return {
      available: true,
      timestamp: Date.now(),
      cpu: cpuPercent,
      ram: ramMB,
      disk: null,
      networkRxBytes: networkAvailable ? networkRxBytes : null,
      networkTxBytes: networkAvailable ? networkTxBytes : null,
    };
  } catch (e) {
    return { available: false, timestamp: Date.now(), cpu: null, ram: null, disk: null, networkRxBytes: null, networkTxBytes: null };
  }
};

export const getContainerLogs = async (containerId: string, nodeId?: string): Promise<string> => {
  const remote = await remoteNode(nodeId);
  if (remote) return await nodeControl.logs(remote, containerId);
  const docker = await getDocker(nodeId);
  if (isSandbox) return "[System] Sandbox mode. No historical logs available.\r\n";
  try {
    const container = docker.getContainer(containerId);
    
    // Convert Buffer log output to string safely. dockerode returns interleaved multiplexed streams if tty is false,
    // but we use tty: true in createServerContainer, so it's a raw stream buffer.
    const logsBuffer = await container.logs({ stdout: true, stderr: true, tail: 100 });
    return logsBuffer.toString('utf8');
  } catch (e) {
    return "";
  }
};

const activeStreams: Record<string, NodeJS.ReadWriteStream> = {};

export const attachContainerSocket = async (containerId: string, serverId: string, nodeId?: string) => {
  const remote = await remoteNode(nodeId);
  if (remote) return;
  const docker = await getDocker(nodeId);
  if (isSandbox) {
    return;
  }
  try {
    const container = docker.getContainer(containerId);
    if (!activeStreams[containerId]) {
      const stream = await container.attach({ stream: true, stdout: true, stderr: true, stdin: true });
      activeStreams[containerId] = stream;
      stream.on('data', (chunk) => {
        emitServerLog(serverId, chunk.toString());
      });
      stream.on('end', () => {
        delete activeStreams[containerId];
      });
    }
  } catch(e) {
    console.error("Attach error", e);
  }
};

export const sendContainerCommand = async (containerId: string, command: string, nodeId?: string) => {
  const remote = await remoteNode(nodeId);
  if (remote) { await nodeControl.command(remote, containerId, command); return; }
  const docker = await getDocker(nodeId);

  if (isSandbox) {
    // Handled by client local echo
    return;
  }
  if (activeStreams[containerId]) {
    activeStreams[containerId].write(command + "\n");
  } else {
    try {
      const container = docker.getContainer(containerId);
      const stream = await container.attach({ stream: true, stdout: true, stderr: true, stdin: true });
      activeStreams[containerId] = stream;
      stream.write(command + "\n");
      stream.on('data', (chunk) => {
        // Will be broadcasted due to existing or new attach
      });
    } catch(e) {
       console.error("Command error", e);
    }
  }
};
