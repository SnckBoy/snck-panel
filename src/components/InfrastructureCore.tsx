import { useEffect, useMemo, useRef, useState } from "react";
import type { CSSProperties } from "react";
import { useSettings } from "../context/SettingsContext";
import type {
  BoxGeometry,
  CanvasTexture,
  Group,
  Mesh,
  MeshStandardMaterial,
  PerspectiveCamera,
  Scene,
  WebGLRenderer,
} from "three";

export type CoreHealth = "online" | "warning" | "offline";

export type CoreServer = {
  id: string | number;
  name?: string;
  status?: CoreHealth | string;
  load?: number;
};

type InfrastructureCoreProps = {
  servers: CoreServer[];
  size?: "hero" | "compact";
  label?: string;
  className?: string;
};

type ThreeRuntime = typeof import("three");

type BlockRecord = {
  id: string | number;
  mesh: Mesh<BoxGeometry, MeshStandardMaterial>;
  base: { x: number; y: number; z: number };
  phase: number;
  load: number;
};

const HEALTH_COLORS: Record<CoreHealth, number> = {
  online: 0x3ddc84,
  warning: 0xf5a623,
  offline: 0xf2545b,
};

const CORE_GLOW = 0x4de8f0;

function normalizeHealth(status?: string): CoreHealth {
  if (status === "online" || status === "running" || status === "healthy") return "online";
  if (status === "warning" || status === "degraded" || status === "starting") return "warning";
  return "offline";
}

function clamp(value: number, min = 0, max = 100) {
  return Math.min(max, Math.max(min, Number.isFinite(value) ? value : 0));
}

function getFallbackReason() {
  if (typeof window === "undefined" || typeof navigator === "undefined") return "server";
  const reducedMotion = window.matchMedia?.("(prefers-reduced-motion: reduce)").matches;
  const mobileOrCoarsePointer = window.matchMedia?.("(max-width: 820px), (pointer: coarse)").matches;
  const saveData = Boolean((navigator as Navigator & { connection?: { saveData?: boolean } }).connection?.saveData);
  const lowMemory = typeof (navigator as Navigator & { deviceMemory?: number }).deviceMemory === "number"
    && ((navigator as Navigator & { deviceMemory?: number }).deviceMemory ?? 8) <= 2;
  // Keep continuous WebGL rendering off phones/tablets; it can compete with scrolling.
  return reducedMotion ? "reduced-motion" : mobileOrCoarsePointer ? "mobile-low-power" : saveData ? "data-saver" : lowMemory ? "low-power" : "";
}

function createFallbackTexture(THREE: ThreeRuntime, color: number): CanvasTexture {
  const canvas = document.createElement("canvas");
  canvas.width = 32;
  canvas.height = 32;
  const context = canvas.getContext("2d");
  if (context) {
    context.fillStyle = `#${color.toString(16).padStart(6, "0")}`;
    context.fillRect(0, 0, 32, 32);
    context.fillStyle = "rgba(255,255,255,.18)";
    context.fillRect(0, 0, 32, 3);
    context.fillStyle = "rgba(0,0,0,.24)";
    context.fillRect(0, 29, 32, 3);
  }
  const texture = new THREE.CanvasTexture(canvas);
  texture.colorSpace = THREE.SRGBColorSpace;
  return texture;
}

export function InfrastructureCore({ servers, size = "hero", label = "Infrastructure Core", className = "" }: InfrastructureCoreProps) {
  const settings = useSettings();
  const canvasRef = useRef<HTMLCanvasElement | null>(null);
  const sceneHostRef = useRef<HTMLDivElement | null>(null);
  const [fallbackReason, setFallbackReason] = useState<string>(() => getFallbackReason());
  const [ready, setReady] = useState(false);

  const visibleServers = useMemo(() => servers.slice(0, 24), [servers]);
  const blockServers = useMemo(() => visibleServers.length ? visibleServers : [{ id: "empty", name: "No servers", status: "offline", load: 0 }], [visibleServers]);
  const serversRef = useRef(blockServers);
  serversRef.current = blockServers;
  // Telemetry changes update materials in-place; only topology changes rebuild WebGL.
  const topology = JSON.stringify(blockServers.map(server => server.id));
  const reducedMotion = Boolean(settings?.reducedMotion);
  useEffect(() => {
    const media = window.matchMedia("(prefers-reduced-motion: reduce)");
    const update = () => setFallbackReason(current => reducedMotion || media.matches ? "reduced-motion" : current === "reduced-motion" ? getFallbackReason() : current);
    update();
    media.addEventListener("change", update);
    return () => media.removeEventListener("change", update);
  }, [reducedMotion]);

  useEffect(() => {
    const host = sceneHostRef.current;
    const canvas = canvasRef.current;
    if (!host || !canvas || fallbackReason) return;

    setReady(false);
    let disposed = false;
    let animationFrame = 0;
    let renderer: WebGLRenderer | undefined;
    let scene: Scene | undefined;
    let camera: PerspectiveCamera | undefined;
    let root: Group | undefined;
    let sharedGeometry: BoxGeometry | undefined;
    let blocks: BlockRecord[] = [];
    let isVisible = true;
    let lastRenderTime = 0;
    let dragStartX = 0;
    let dragStartY = 0;
    let rotationStartX = 0;
    let rotationStartY = 0;
    let dragging = false;
    let bootStart = performance.now();

    const resize = () => {
      if (!renderer || !camera) return;
      const bounds = host.getBoundingClientRect();
      const width = Math.max(1, bounds.width);
      const height = Math.max(1, bounds.height);
      // A capped pixel ratio avoids rendering a large offscreen buffer on retina displays.
      renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, size === "hero" ? 1.25 : 1));
      renderer.setSize(width, height, false);
      camera.aspect = width / height;
      camera.updateProjectionMatrix();
    };

    const onPointerDown = (event: PointerEvent) => {
      if (!root) return;
      dragging = true;
      dragStartX = event.clientX;
      dragStartY = event.clientY;
      rotationStartX = root.rotation.y;
      rotationStartY = root.rotation.x;
      canvas.setPointerCapture?.(event.pointerId);
      canvas.classList.add("is-dragging");
    };

    const onPointerMove = (event: PointerEvent) => {
      if (!dragging || !root) return;
      root.rotation.y = rotationStartX + (event.clientX - dragStartX) * 0.008;
      root.rotation.x = Math.max(-0.75, Math.min(0.75, rotationStartY + (event.clientY - dragStartY) * 0.006));
    };

    const onPointerUp = (event: PointerEvent) => {
      dragging = false;
      canvas.releasePointerCapture?.(event.pointerId);
      canvas.classList.remove("is-dragging");
    };

    const onVisibilityChange = () => {
      if (document.hidden) {
        cancelAnimationFrame(animationFrame);
        animationFrame = 0;
      } else if (!disposed && isVisible && !animationFrame) {
        animationFrame = requestAnimationFrame(render);
      }
    };

    const render = (time: number) => {
      animationFrame = 0;
      if (disposed || !renderer || !scene || !camera || !root || document.hidden || !isVisible) return;

      // The reference animation is calm rather than a 60fps game loop. Keep interaction
      // responsive while capping idle GPU renders to roughly 30fps.
      const frameInterval = dragging ? 1000 / 60 : 1000 / 30;
      if (lastRenderTime && time - lastRenderTime < frameInterval) {
        animationFrame = requestAnimationFrame(render);
        return;
      }
      const delta = Math.min(50, lastRenderTime ? time - lastRenderTime : 16.67);
      lastRenderTime = time;
      if (!dragging) root.rotation.y += (size === "hero" ? 0.102 : 0.132) * (delta / 1000);
      const bootProgress = Math.min(1, (time - bootStart) / 1250);
        blocks.forEach((block, index) => {
          const blockProgress = Math.max(0, Math.min(1, (bootProgress - index * 0.035) / 0.45));
          block.mesh.scale.setScalar(Math.max(0.001, blockProgress));
          block.mesh.position.y = block.base.y + (1 - blockProgress) * 0.65;
          const current = serversRef.current.find(server => server.id === block.id);
          const color = HEALTH_COLORS[normalizeHealth(current?.status)];
          block.mesh.material.color.setHex(color);
          block.mesh.material.emissive.setHex(color);
          block.load = clamp(current?.load ?? 0);
          const pulse = 0.78 + Math.sin(time * 0.002 + block.phase) * 0.12 + block.load / 100 * 0.14;
          block.mesh.material.emissiveIntensity = pulse;
        });
      renderer.render(scene, camera);
      animationFrame = requestAnimationFrame(render);
    };

    const initialize = async () => {
      try {
        const THREE = await import("three");
        if (disposed) return;
        renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: false, powerPreference: "low-power", precision: "mediump", depth: true, stencil: false });
        renderer.outputColorSpace = THREE.SRGBColorSpace;
        renderer.toneMapping = THREE.ACESFilmicToneMapping;
        renderer.toneMappingExposure = 1.05;
        renderer.sortObjects = false;
        scene = new THREE.Scene();
        camera = new THREE.PerspectiveCamera(size === "hero" ? 28 : 32, 1, 0.1, 100);
        camera.position.set(0, 0.2, size === "hero" ? 9.5 : 7.5);
        root = new THREE.Group();
        scene.add(root);
        scene.add(new THREE.AmbientLight(0x9b93d8, 0.65));
        const keyLight = new THREE.DirectionalLight(CORE_GLOW, 2.1);
        keyLight.position.set(4, 5, 6);
        scene.add(keyLight);
        const rimLight = new THREE.PointLight(CORE_GLOW, 4, 14);
        rimLight.position.set(-3, 1, 3);
        scene.add(rimLight);

        sharedGeometry = new THREE.BoxGeometry(0.82, 0.82, 0.82);
        const columns = size === "hero" ? 5 : 3;
        const spacing = 1.05;
        serversRef.current.forEach((server, index) => {
          const health = normalizeHealth(server.status);
          const color = HEALTH_COLORS[health];
          const material = new THREE.MeshStandardMaterial({
            color,
            roughness: 0.34,
            metalness: 0.2,
            emissive: color,
            emissiveIntensity: 0.85,
            transparent: true,
          });
          const mesh = new THREE.Mesh(sharedGeometry, material);
          const layer = Math.floor(index / (columns * columns));
          const layerIndex = index % (columns * columns);
          const row = Math.floor(layerIndex / columns);
          const column = layerIndex % columns;
          const base = {
            x: (column - (columns - 1) / 2) * spacing,
            y: (row - (columns - 1) / 2) * spacing,
            z: (layer - 1) * spacing * 0.9,
          };
          mesh.position.set(base.x, base.y + 0.7, base.z);
          mesh.rotation.set(index * 0.17, index * 0.23, index * 0.11);
          root?.add(mesh);
          blocks.push({ id: server.id, mesh, base, phase: index * 0.67, load: clamp(server.load ?? (health === "online" ? 42 : health === "warning" ? 73 : 4)) });
        });
        resize();
        setReady(true);
        bootStart = performance.now();
        animationFrame = requestAnimationFrame(render);
      } catch {
        if (!disposed) setFallbackReason("webgl-unavailable");
      }
    };

    const observer = new ResizeObserver(resize);
    observer.observe(host);
    const visibilityObserver = typeof IntersectionObserver !== "undefined"
      ? new IntersectionObserver(([entry]) => {
        isVisible = Boolean(entry?.isIntersecting);
        if (isVisible && !document.hidden && !animationFrame) animationFrame = requestAnimationFrame(render);
      }, { threshold: 0.05 })
      : undefined;
    visibilityObserver?.observe(host);
    canvas.addEventListener("pointerdown", onPointerDown);
    canvas.addEventListener("pointermove", onPointerMove);
    canvas.addEventListener("pointerup", onPointerUp);
    canvas.addEventListener("pointercancel", onPointerUp);
    document.addEventListener("visibilitychange", onVisibilityChange);
    initialize();

    return () => {
      disposed = true;
      cancelAnimationFrame(animationFrame);
      observer.disconnect();
      visibilityObserver?.disconnect();
      canvas.removeEventListener("pointerdown", onPointerDown);
      canvas.removeEventListener("pointermove", onPointerMove);
      canvas.removeEventListener("pointerup", onPointerUp);
      canvas.removeEventListener("pointercancel", onPointerUp);
      document.removeEventListener("visibilitychange", onVisibilityChange);
      blocks.forEach(({ mesh }) => {
        mesh.material.map?.dispose();
        mesh.material.dispose();
      });
      sharedGeometry?.dispose();
      sharedGeometry = undefined;
      renderer?.dispose();
      renderer = undefined;
    };
  }, [topology, fallbackReason, size]);

  const displayBlocks = visibleServers.length ? visibleServers : [{ id: "empty", name: "No servers", status: "offline", load: 0 }];

  return (
    <section className={`snx-core snx-core--${size} ${ready ? "is-ready" : ""} ${fallbackReason ? "is-fallback" : ""} ${className}`} aria-label={label}>
      <div className="snx-core__header">
        <div>
          <span className="snx-eyebrow">THE CORE</span>
          <h2>{label}</h2>
        </div>
        <span className="snx-core__mode">{fallbackReason ? "2D SAFE MODE" : "WEBGL CORE"}</span>
      </div>
      <div ref={sceneHostRef} className="snx-core__stage">
        <div className="snx-core__halo" aria-hidden="true" />
        <canvas ref={canvasRef} className="snx-core__canvas" aria-hidden="true" />
        <div className="snx-core__fallback" aria-hidden={!fallbackReason}>
          {displayBlocks.map((server, index) => {
            const health = normalizeHealth(server.status);
            const load = clamp(server.load ?? 0);
            return <span key={server.id} className={`snx-core__fallback-block snx-core__fallback-block--${health}`} style={{ "--core-index": index, "--core-load": load } as CSSProperties} title={`${server.name ?? "Server"}: ${health}`} />;
          })}
        </div>
        <div className="snx-core__hint">{fallbackReason ? "Low-power view · one block per server" : "Drag to rotate · one block per server"}</div>
      </div>
      <div className="snx-core__legend" aria-label="Core health legend">
        <span><i className="snx-health-dot snx-health-dot--online" /> Online</span>
        <span><i className="snx-health-dot snx-health-dot--warning" /> Warning</span>
        <span><i className="snx-health-dot snx-health-dot--offline" /> Offline</span>
      </div>
    </section>
  );
}

export default InfrastructureCore;
