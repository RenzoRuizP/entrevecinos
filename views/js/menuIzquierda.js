// views/js/menuIzquierda.js — navegación AJAX ÚNICA y definitiva (EV)
// SOLUCIÓN RAÍZ: Shell único (MenuPrincipal) + deep link (ev_goto) + parciales AJAX
// Mantiene tu lógica: timeout scripts, watchdog overlay, abort, refcount.

document.addEventListener("DOMContentLoaded", () => {
  "use strict";

  // Evita doble inicialización (causa típica de bugs raros)
  if (window.__EV_NAV_INIT__ === true) return;
  window.__EV_NAV_INIT__ = true;

  const BASE = (window.BASE_URL || "/entrevecinos").toString().replace(/\/+$/, "");
  const main = document.getElementById("contenido-principal");
  const sidebar = document.getElementById("sidebar");

  if (!main) {
    console.warn("[EV][NAV] Falta #contenido-principal. No se inicializa navegación AJAX.");
    return;
  }

  // ==========================
  // Backdrop (mobile)
  // ==========================
  let backdrop = document.getElementById("sidebar-backdrop");
  if (!backdrop) {
    backdrop = document.createElement("div");
    backdrop.id = "sidebar-backdrop";
    document.body.appendChild(backdrop);
  }

  function closeSidebarMobile() {
    if (!sidebar) return;
    sidebar.classList.remove("open");
    backdrop.classList.remove("show");
  }

  // ==========================
  // Overlay único EV (GLOBAL)
  // ==========================
  let overlayRefCount = 0;
  let overlayWatchdog = null;

  function ensureEvOverlay() {
    let ov = document.getElementById("ev-nav-overlay");
    if (ov) return ov;

    ov = document.createElement("div");
    ov.id = "ev-nav-overlay";
    ov.setAttribute("aria-hidden", "true");
    ov.style.cssText = `
      position: fixed; inset: 0;
      display: none;
      align-items: center; justify-content: center;
      background: rgba(255,255,255,0.65);
      backdrop-filter: blur(2px);
      z-index: 99999;
    `;

    const box = document.createElement("div");
    box.style.cssText = `
      display:flex; align-items:center; gap:10px;
      padding: 14px 18px;
      border-radius: 999px;
      background: rgba(255,255,255,0.92);
      border: 1px solid rgba(15,89,47,0.10);
      box-shadow: 0 18px 45px rgba(0,0,0,0.12), 0 6px 12px rgba(0,0,0,0.06);
      font-family: Poppins, system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
      color: #0F592F;
      font-weight: 600;
    `;

    const spinner = document.createElement("div");
    spinner.style.cssText = `
      width: 30px; height: 30px;
      border-radius: 50%;
      border: 4px solid rgba(22,163,74,0.18);
      border-top-color: rgba(15,89,47,0.95);
      animation: evspin .8s linear infinite;
    `;

    const txt = document.createElement("div");
    txt.textContent = "Cargando...";

    const style = document.createElement("style");
    style.textContent = `@keyframes evspin{to{transform:rotate(360deg)}}`;

    box.appendChild(spinner);
    box.appendChild(txt);
    ov.appendChild(style);
    ov.appendChild(box);
    document.body.appendChild(ov);

    return ov;
  }

  function showEvOverlay() {
    overlayRefCount = Math.max(0, overlayRefCount) + 1;
    ensureEvOverlay().style.display = "flex";

    if (overlayWatchdog) clearTimeout(overlayWatchdog);
    overlayWatchdog = setTimeout(() => {
      overlayRefCount = 0;
      const ov = document.getElementById("ev-nav-overlay");
      if (ov) ov.style.display = "none";
    }, 20000);
  }

  function hideEvOverlay(force = false) {
    if (force) overlayRefCount = 0;
    overlayRefCount = Math.max(0, overlayRefCount - 1);

    if (overlayRefCount === 0) {
      const ov = document.getElementById("ev-nav-overlay");
      if (ov) ov.style.display = "none";
      if (overlayWatchdog) {
        clearTimeout(overlayWatchdog);
        overlayWatchdog = null;
      }
    }
  }

  // ==========================
  // Apagado agresivo de loaders legacy
  // ==========================
  function killLegacyLoaders() {
    const selectors = [
      "#spinner-overlay", "#loading-overlay", "#loader-overlay", "#global-loader", "#ev-loading",
      ".spinner-overlay", ".loading-overlay", ".loader-overlay", ".global-loader",
      ".preloader", "#preloader", ".page-loader", "#page-loader",
      ".overlay-loading", "#overlay-loading",
      ".ajax-loading", "#ajax-loading"
    ];
    selectors.forEach((sel) => {
      document.querySelectorAll(sel).forEach((el) => {
        try { el.style.display = "none"; } catch (_) {}
        try { el.classList.add("d-none"); } catch (_) {}
        try { el.classList.remove("show"); } catch (_) {}
      });
    });

    document.querySelectorAll('[aria-busy="true"], [data-loading="true"], [data-loader="true"]').forEach((el) => {
      try { el.setAttribute("aria-busy", "false"); } catch (_) {}
      try { el.dataset.loading = "false"; } catch (_) {}
      try { el.style.display = "none"; } catch (_) {}
      try { el.classList.add("d-none"); } catch (_) {}
    });

    document.body.classList.remove("loading", "is-loading", "modal-open");
    document.documentElement.classList.remove("loading", "is-loading");
  }

  // ==========================
  // URL helpers
  // ==========================
  function buildUrl(href) {
    if (!href) return null;
    const r = href.toString().trim();
    if (!r || r === "#" || r.startsWith("#menu")) return null;

    if (/^https?:\/\//i.test(r)) return r;
    if (r.startsWith(BASE)) return r;
    if (r.startsWith("/")) return BASE + r;
    return BASE + "/" + r;
  }

  function addPartial(url) {
    const u = new URL(url, window.location.origin);
    if (!u.searchParams.has("partial")) u.searchParams.set("partial", "1");
    return u.pathname + "?" + u.searchParams.toString();
  }

  // ==========================
  // Scripts del parcial (timeout por script)
  // ==========================
  const LOADED = new Set(
    Array.from(document.scripts)
      .map(s => (s.src || "").trim())
      .filter(Boolean)
      .map(src => new URL(src, window.location.origin).href)
  );

  function runInline(code) {
    if (!code) return;
    try {
      // eslint-disable-next-line no-new-func
      new Function(code)();
    } catch (e) {
      console.error("[EV][NAV] Error en script inline:", e);
    }
  }

  function loadScriptWithTimeout(src, { signal, timeoutMs = 8000 } = {}) {
    return new Promise((resolve) => {
      if (!src) return resolve(false);

      const abs = new URL(src, window.location.origin).href;
      if (LOADED.has(abs)) return resolve(true);

      const s = document.createElement("script");
      s.src = abs;
      s.defer = true;

      let doneCalled = false;
      const done = (ok) => {
        if (doneCalled) return;
        doneCalled = true;
        try { s.onload = null; s.onerror = null; } catch (_) {}
        try { if (s.parentNode) s.parentNode.removeChild(s); } catch (_) {}
        if (ok) LOADED.add(abs);
        resolve(ok);
      };

      const t = setTimeout(() => {
        console.warn("[EV][NAV] Timeout cargando script:", abs);
        done(false);
      }, timeoutMs);

      s.onload = () => { clearTimeout(t); done(true); };
      s.onerror = () => { clearTimeout(t); done(false); };

      if (signal) {
        signal.addEventListener("abort", () => {
          clearTimeout(t);
          done(false);
        }, { once: true });
      }

      document.body.appendChild(s);
    });
  }

  async function processScripts(root, signal) {
    const scripts = Array.from(root.querySelectorAll("script"));
    if (!scripts.length) return;

    scripts.forEach(s => s.parentNode && s.parentNode.removeChild(s));

    const inline = scripts.filter(s => !s.src);
    const external = scripts.filter(s => !!s.src);

    inline.forEach(s => runInline(s.textContent || ""));

    for (const s of external) {
      // eslint-disable-next-line no-await-in-loop
      await loadScriptWithTimeout(s.src, { signal, timeoutMs: 8000 });
    }
  }

  // ==========================
  // Fetch con timeout + abort
  // ==========================
  let currentLoadId = 0;

  async function fetchWithTimeout(url, { timeoutMs = 15000, ...opts } = {}) {
    const ctrl = new AbortController();
    const id = setTimeout(() => ctrl.abort(), timeoutMs);
    try {
      const res = await fetch(url, { ...opts, signal: ctrl.signal });
      return { res, signal: ctrl.signal };
    } finally {
      clearTimeout(id);
    }
  }

  // ==========================
  // Carga AJAX
  // ==========================
  async function loadPage(url, { pushState = true } = {}) {
    const finalUrl = addPartial(url);
    const myId = ++currentLoadId;

    killLegacyLoaders();
    showEvOverlay();

    const localWatchdog = setTimeout(() => {
      if (myId === currentLoadId) {
        console.warn("[EV][NAV] Watchdog: forzando hide overlay por cuelgue.");
        hideEvOverlay(true);
        killLegacyLoaders();
      }
    }, 22000);

    try {
      const { res, signal } = await fetchWithTimeout(finalUrl, {
        timeoutMs: 15000,
        method: "GET",
        cache: "no-store",
        credentials: "include",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "X-Partial": "1",
          "Accept": "text/html"
        }
      });

      if (myId !== currentLoadId) return;

      const ct = (res.headers.get("content-type") || "").toLowerCase();
      const text = await res.text().catch(() => "");

      if (!res.ok) {
        main.innerHTML = `
          <div class="alert alert-danger border-0 shadow-sm rounded-4">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Error</div>
            <div>No se pudo cargar el contenido solicitado.</div>
            <div class="small text-muted mt-2">HTTP ${res.status}</div>
          </div>
        `;
        return;
      }

      if (ct.includes("application/json")) {
        main.innerHTML = `
          <div class="alert alert-danger border-0 shadow-sm rounded-4">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Error</div>
            <div>La vista devolvió JSON en lugar de HTML.</div>
            <div class="small text-muted mt-2">${finalUrl}</div>
          </div>
        `;
        return;
      }

      main.innerHTML = text;

      await processScripts(main, signal);

      document.dispatchEvent(new CustomEvent("ev:content-loaded", { detail: { url: finalUrl } }));

      if (pushState) {
        history.pushState({ url: finalUrl }, "", url);
      }

    } catch (e) {
      const isAbort = String(e && e.name).toLowerCase().includes("abort");
      main.innerHTML = `
        <div class="alert alert-danger border-0 shadow-sm rounded-4">
          <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Error</div>
          <div>${isAbort ? "La carga tardó demasiado y se canceló (timeout)." : "No se pudo cargar el contenido solicitado."}</div>
          <div class="small text-muted mt-2">${String(e?.message || e)}</div>
        </div>
      `;
      console.error("[EV][NAV] Error:", e);
    } finally {
      clearTimeout(localWatchdog);
      hideEvOverlay(true);
      killLegacyLoaders();
      closeSidebarMobile();
    }
  }

  // ==========================
  // Clicks del sidebar (delegación)
  // ==========================
  document.addEventListener("click", (e) => {
    const a = e.target.closest("a");
    if (!a) return;

    const inSidebar = a.closest("#sidebar") || a.closest(".main-sidebar") || a.closest(".ev-sidebar");
    if (!inSidebar) return;

    const href = a.getAttribute("href");
    const url = buildUrl(href);
    if (!url) return;

    if (/^https?:\/\//i.test(href || "")) return;

    e.preventDefault();
    loadPage(url, { pushState: true });
  }, true);

  // Back/forward
  window.addEventListener("popstate", (ev) => {
    const u = ev.state?.url;
    if (!u) return;
    const clean = u.replace(/(\?|&)partial=1\b/g, "").replace(/[?&]$/, "");
    loadPage(clean, { pushState: false });
  });

  // ==========================================================
  // SOLUCIÓN RAÍZ: Deep link desde F5
  // Router redirige a /MenuPrincipal?ev_goto=/ruta
  // y aquí cargamos ese módulo automáticamente via AJAX.
  // ==========================================================
  try {
    const qs = new URLSearchParams(window.location.search);
    const goto = qs.get("ev_goto");

    if (goto) {
      // Limpiar URL para que no se repita el goto en refresh del shell
      const cleanShellUrl = BASE + "/MenuPrincipal";
      window.history.replaceState({}, document.title, cleanShellUrl);

      const url = buildUrl(goto);
      if (url) {
        loadPage(url, { pushState: true });
      }
    }
  } catch (e) {
    console.warn("[EV][NAV] ev_goto no procesado:", e);
  }
});
