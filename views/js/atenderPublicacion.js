// views/js/atenderPublicacion.js
(function () {
  "use strict";

  const baseUrl = (window.BASE_URL || "").replace(/\/+$/, "");
  if (!baseUrl) return;

  // =========================================================
  // Boot global (se carga 1 vez en el SHELL)
  // - Re-inicializa el módulo cuando el parcial aparece en DOM
  // =========================================================
  const BOOT_KEY = "EV_BOOT_ATENDER_PUBLICACION";
  if (window[BOOT_KEY]) return;
  window[BOOT_KEY] = true;

  // =========================================================
  // Utils
  // =========================================================
  function $(sel, root) {
    return (root || document).querySelector(sel);
  }

  function qs(v) {
    return encodeURIComponent(String(v ?? ""));
  }

  function safeStr(v, def = "-") {
    const s = String(v ?? "").trim();
    return s ? s : def;
  }

  function money(v) {
    const n = Number(v);
    if (Number.isFinite(n)) return "S/ " + n.toFixed(2);
    return safeStr(v, "-");
  }

  function formatFecha(v) {
    const s = String(v ?? "").trim();
    return s ? s : "-"; // "YYYY-MM-DD HH:mm:ss"
  }

  function toastError(msg) {
    if (window.Swal) Swal.fire({ icon: "error", title: "Error", text: msg });
    else alert(msg);
  }

  function toastInfo(msg) {
    if (window.Swal) Swal.fire({ icon: "info", title: "Info", text: msg });
    else alert(msg);
  }

  async function safeJson(res) {
    try {
      return await res.json();
    } catch (_) {
      return null;
    }
  }

  function estadoLabelFromVisible(visible) {
    const n = Number(visible);
    if (n === 0) return "Borrador";
    if (n === 1) return "Pendiente";
    if (n === 2) return "Aprobada";
    if (n === 3) return "Rechazada";

    const s = String(visible ?? "").toLowerCase();
    if (s === "borrador") return "Borrador";
    if (s === "pendiente") return "Pendiente";
    if (s === "aprobada") return "Aprobada";
    if (s === "rechazada") return "Rechazada";
    return safeStr(visible, "-");
  }

  // =========================================================
  // Módulo (se instancia por cada parcial insertado)
  // =========================================================
  function initModule(root) {
    // Evitar doble init en el mismo parcial
    if (!root || root.dataset.evInitAp === "1") return;
    root.dataset.evInitAp = "1";

    // Estado del módulo
    let aborter = null;
    let page = 1;
    let size = 10;
    let estado = "pendiente"; // pendiente|aprobada|rechazada|borrador|todas
    let q = "";

    // DOM (IDs reales de AtenderPublicacionView.php)
    const elForm = $("#formFiltros", root);
    const elEstado = $("#fEstado", root);
    const elTexto = $("#fTexto", root);

    const elBody = $("#tbodyItems", root);
    const elLblMeta = $("#lblMeta", root);
    const elLblFooterLeft = $("#lblFooterLeft", root);
    const elLblPagina = $("#lblPagina", root);
    const elBtnPrev = $("#btnPrev", root);
    const elBtnNext = $("#btnNext", root);

    const elLblPendientes = $("#lblPendientes", root);

    const btnVerPend = $("#btnVerPendientes", root);
    const btnVerApr = $("#btnVerAprobadas", root);
    const btnVerRech = $("#btnVerRechazadas", root);
    const btnVerBor = $("#btnVerBorradores", root);

    const btnRefrescar = $("#btnRefrescar", root);

    // Si no existe el tbody, no es esta vista.
    if (!elBody) return;

    // Tomar valor inicial del select si existe
    if (elEstado && elEstado.value) estado = String(elEstado.value).toLowerCase();

    function cancelFetchPrevio() {
      if (aborter) {
        aborter.abort();
        aborter = null;
      }
    }

    function setActiveQuickButtons() {
      const map = [
        [btnVerPend, "pendiente"],
        [btnVerApr, "aprobada"],
        [btnVerRech, "rechazada"],
        [btnVerBor, "borrador"],
      ];
      map.forEach(([el, st]) => {
        if (!el) return;
        el.classList.toggle("active", estado === st);
        el.setAttribute("aria-pressed", estado === st ? "true" : "false");
      });
    }

    function renderEmptyRow() {
      elBody.innerHTML = `
        <tr>
          <td colspan="6" class="text-center py-4 ev-empty">
            <div class="ev-empty-wrap">
              <i class="bi bi-inbox ev-empty-ico"></i>
              <div class="ev-empty-text">No hay publicaciones para los filtros seleccionados.</div>
            </div>
          </td>
        </tr>
      `;
    }

    function render(items) {
      elBody.innerHTML = "";

      if (!items || items.length === 0) {
        renderEmptyRow();
        return;
      }

      for (const it of items) {
        // campos reales del response
        const id = it.codigo_producto ?? "";
        const fecha = formatFecha(it.updated_at || it.created_at);
        const titulo = safeStr(it.titulo);
        const precio = money(it.precio);

        // OJO: tu response trae usuario_nombre / usuario_email
        const usuarioNombre = safeStr(it.usuario_nombre, "");
        const usuarioEmail = safeStr(it.usuario_email, "");
        const usuario =
          (usuarioNombre || usuarioEmail)
            ? `${usuarioNombre}${usuarioEmail ? " (" + usuarioEmail + ")" : ""}`.trim()
            : "-";

        const est = estadoLabelFromVisible(it.visible);

        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${fecha}</td>
          <td>${titulo}</td>
          <td class="text-end">${precio}</td>
          <td>${usuario}</td>
          <td>${est}</td>
          <td class="text-end">
            <button type="button" class="btn btn-sm btn-outline-success js-observar" data-id="${String(id)}">
              Observar
            </button>
          </td>
        `;
        elBody.appendChild(tr);
      }
    }

    function renderMeta(total) {
      const t = Number(total || 0);
      const from = t === 0 ? 0 : (page - 1) * size + 1;
      const to = Math.min(page * size, t);

      if (elLblMeta) elLblMeta.textContent = `Mostrando ${t} registros`;
      if (elLblFooterLeft) elLblFooterLeft.textContent = `Mostrando ${from} a ${to} de ${t}`;
      if (elLblPagina) elLblPagina.textContent = String(page);

      const hasPrev = page > 1;
      const hasNext = to < t;

      if (elBtnPrev) elBtnPrev.disabled = !hasPrev;
      if (elBtnNext) elBtnNext.disabled = !hasNext;
    }

    function renderCounts(counts) {
      if (!counts) return;
      if (elLblPendientes) elLblPendientes.textContent = String(Number(counts.pendientes || 0));
    }

    function getApiUrl() {
      // Según tu modelo ProductoSoporte: estado + q + page + size
      return (
        baseUrl +
        "/api/soporte/productos" +
        "?estado=" + qs(estado) +
        "&q=" + qs(q) +
        "&page=" + qs(page) +
        "&size=" + qs(size)
      );
    }

    async function listar() {
      cancelFetchPrevio();
      aborter = new AbortController();

      try {
        const url = getApiUrl();
        const res = await fetch(url, {
          method: "GET",
          headers: { Accept: "application/json" },
          signal: aborter.signal,
          cache: "no-store",
        });

        if (res.status === 401) {
          const j = await safeJson(res);
          toastInfo(j?.mensaje || "Sesión finalizada. Inicia sesión nuevamente.");
          window.location.href = baseUrl + "/login";
          return;
        }

        const data = await safeJson(res);
        if (!res.ok || !data || data.ok === false) {
          toastError(data?.mensaje || data?.error || "No se pudo listar.");
          renderEmptyRow();
          renderMeta(0);
          return;
        }

        const items = Array.isArray(data.items) ? data.items : [];
        const total = Number(data.total || 0);

        setActiveQuickButtons();
        renderCounts(data.counts);

        render(items);
        renderMeta(total);
      } catch (e) {
        if (e && e.name === "AbortError") return;
        toastError("Error de red al listar.");
        renderEmptyRow();
        renderMeta(0);
      } finally {
        aborter = null;
      }
    }

    async function abrirDetalle(id) {
      cancelFetchPrevio();
      aborter = new AbortController();

      try {
        const url = baseUrl + "/api/soporte/productos/" + encodeURIComponent(id);
        const res = await fetch(url, {
          method: "GET",
          headers: { Accept: "application/json" },
          signal: aborter.signal,
          cache: "no-store",
        });

        if (res.status === 401) {
          const j = await safeJson(res);
          toastInfo(j?.mensaje || "Sesión finalizada. Inicia sesión nuevamente.");
          window.location.href = baseUrl + "/login";
          return;
        }

        const data = await safeJson(res);
        if (!res.ok || data?.ok === false) {
          toastError(data?.mensaje || data?.error || "No se pudo obtener el detalle.");
          return;
        }

        const it = data.item || {};
        const usuario =
          (it.usuario_nombre || it.usuario_email)
            ? `${safeStr(it.usuario_nombre, "")}${it.usuario_email ? " (" + safeStr(it.usuario_email, "") + ")" : ""}`.trim()
            : "-";

        if (window.Swal) {
          Swal.fire({
            title: it.titulo || "Detalle",
            html: `
              <div style="text-align:left">
                <div><b>Precio:</b> ${money(it.precio)}</div>
                <div><b>Usuario:</b> ${usuario}</div>
                <div><b>Estado:</b> ${estadoLabelFromVisible(it.visible)}</div>
                <div style="margin-top:10px"><b>Descripción:</b><br>${safeStr(it.descripcion, "-")}</div>
              </div>
            `,
            confirmButtonText: "Cerrar",
            confirmButtonColor: "#EA7C12",
          });
        }
      } catch (e) {
        if (e && e.name === "AbortError") return;
        toastError("Error de red al obtener detalle.");
      } finally {
        aborter = null;
      }
    }

    // =========================
    // Eventos (solo 1 vez por parcial)
    // =========================
    if (elForm) {
      elForm.addEventListener("submit", function (ev) {
        ev.preventDefault();
        estado = String(elEstado?.value || "pendiente").toLowerCase();
        q = String(elTexto?.value || "").trim();
        page = 1;
        listar();
      });
    }

    if (elEstado) {
      elEstado.addEventListener(
        "change",
        function () {
          estado = String(elEstado.value || "pendiente").toLowerCase();
          page = 1;
          listar();
        },
        { passive: true }
      );
    }

    if (elTexto) {
      elTexto.addEventListener("keydown", function (ev) {
        if (ev.key === "Enter") {
          ev.preventDefault();
          estado = String(elEstado?.value || "pendiente").toLowerCase();
          q = String(elTexto.value || "").trim();
          page = 1;
          listar();
        }
      });
    }

    const quickMap = [
      [btnVerPend, "pendiente"],
      [btnVerApr, "aprobada"],
      [btnVerRech, "rechazada"],
      [btnVerBor, "borrador"],
    ];
    quickMap.forEach(([btn, st]) => {
      if (!btn) return;
      btn.addEventListener("click", function (ev) {
        ev.preventDefault();
        estado = st;
        if (elEstado) elEstado.value = st;
        page = 1;
        listar();
      });
    });

    if (btnRefrescar) {
      btnRefrescar.addEventListener("click", function () {
        listar();
      });
    }

    if (elBtnPrev) {
      elBtnPrev.addEventListener("click", function () {
        if (page > 1) {
          page--;
          listar();
        }
      });
    }

    if (elBtnNext) {
      elBtnNext.addEventListener("click", function () {
        page++;
        listar();
      });
    }

    // Delegación observar (dentro del parcial)
    root.addEventListener("click", function (ev) {
      const btn = ev.target && ev.target.closest ? ev.target.closest(".js-observar") : null;
      if (!btn) return;
      const id = btn.getAttribute("data-id");
      if (!id) return;
      abrirDetalle(id);
    });

    // Init del módulo (cuando el parcial existe)
    setActiveQuickButtons();
    listar();
  }

  // =========================================================
  // Observador: detecta cuando el parcial se inserta (AJAX)
  // =========================================================
  function scanAndInit() {
    // Tu vista tiene wrapper: .ev-ap-page
    const root = document.querySelector(".ev-ap-page") || document;
    // Si el parcial aún no está, no hace nada.
    if (document.querySelector(".ev-ap-page")) initModule(root);
  }

  // 1) intento inmediato
  scanAndInit();

  // 2) reintentos por si el parcial llega después
  const mo = new MutationObserver(function () {
    scanAndInit();
  });
  mo.observe(document.documentElement, { childList: true, subtree: true });

  // 3) por si tu loader dispara eventos (no rompe si no existen)
  document.addEventListener("ev:partial-loaded", scanAndInit);
  document.addEventListener("ev:content-loaded", scanAndInit);
})();
