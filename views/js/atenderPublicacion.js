// views/js/atenderPublicacion.js
(function () {
  "use strict";

  const baseUrl = (window.BASE_URL || "").replace(/\/+$/, "");
  if (!baseUrl) return;

  // =========================================================
  // Boot global (se carga 1 vez en el SHELL)
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
    return s ? s : "-";
  }

  function toastError(msg) {
    if (window.Swal) Swal.fire({ icon: "error", title: "Error", text: msg });
    else alert(msg);
  }

  function toastOk(msg) {
    if (window.Swal) Swal.fire({ icon: "success", title: "Listo", text: msg });
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

  function badgeClassFromVisible(visible) {
    const n = Number(visible);
    if (n === 0) return "ev-badge ev-badge-borrador";
    if (n === 1) return "ev-badge ev-badge-pendiente";
    if (n === 2) return "ev-badge ev-badge-aprobada";
    if (n === 3) return "ev-badge ev-badge-rechazada";
    return "ev-badge ev-badge-pendiente";
  }

  function isComentarioValidoFor(accion, comentario) {
    const c = String(comentario ?? "").trim();
    if (accion === "rechazar" || accion === "observar") return c.length >= 3;
    return true;
  }

  // =========================================================
  // Módulo (se instancia por cada parcial insertado)
  // =========================================================
  function initModule(root) {
    if (!root || root.dataset.evInitAp === "1") return;
    root.dataset.evInitAp = "1";

    let aborter = null;
    let page = 1;
    let size = 10;
    let estado = "pendiente";
    let q = "";

    // DOM tabla
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

    // DOM modal
    function getModalEls() {
      const modalEl = document.getElementById("modalPub");
      return {
        modalEl,
        mTitulo: document.getElementById("mTitulo"),
        mPrecio: document.getElementById("mPrecio"),
        mEstadoBadge: document.getElementById("mEstadoBadge"),
        mUsuario: document.getElementById("mUsuario"),
        mEmail: document.getElementById("mEmail"),
        mDescripcion: document.getElementById("mDescripcion"),
        mComentario: document.getElementById("mComentario"),
        mGaleria: document.getElementById("mGaleria"),
        mNoImgs: document.getElementById("mNoImgs"),
      };
    }

    let modalInstance = null;
    let currentId = null;
    let currentVisible = null;

    if (!elBody) return;

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
        const id = it.codigo_producto ?? "";
        const fecha = formatFecha(it.updated_at || it.created_at);
        const titulo = safeStr(it.titulo);
        const precio = money(it.precio);

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
            <button type="button" class="btn btn-sm btn-outline-success js-revisar" data-id="${String(id)}">
              Revisar
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

    function ensureModal() {
      const { modalEl } = getModalEls();
      if (!modalEl) return null;
      if (!modalInstance && window.bootstrap?.Modal) {
        modalInstance = new bootstrap.Modal(modalEl);
      }
      return modalInstance;
    }

    function clearModal() {
      const { mTitulo, mPrecio, mUsuario, mEmail, mDescripcion, mComentario, mGaleria, mNoImgs, mEstadoBadge } = getModalEls();

      if (mTitulo) mTitulo.textContent = "—";
      if (mPrecio) mPrecio.textContent = "—";
      if (mUsuario) mUsuario.textContent = "—";
      if (mEmail) mEmail.textContent = "—";
      if (mDescripcion) mDescripcion.textContent = "—";
      if (mComentario) mComentario.value = "";
      if (mGaleria) mGaleria.innerHTML = "";
      if (mNoImgs) mNoImgs.style.display = "block";
      if (mEstadoBadge) {
        mEstadoBadge.textContent = "pendiente";
        mEstadoBadge.className = "ev-badge ev-badge-pendiente";
      }
      currentId = null;
      currentVisible = null;
    }

    function fillModal(it) {
      const { mTitulo, mPrecio, mUsuario, mEmail, mDescripcion, mComentario, mGaleria, mNoImgs, mEstadoBadge } = getModalEls();

      const usuarioNombre = safeStr(it.usuario_nombre, "");
      const usuarioEmail = safeStr(it.usuario_email, "");

      if (mTitulo) mTitulo.textContent = safeStr(it.titulo);
      if (mPrecio) mPrecio.textContent = money(it.precio);
      if (mUsuario) mUsuario.textContent = (usuarioNombre || usuarioEmail) ? safeStr(usuarioNombre, "—") : "—";
      if (mEmail) mEmail.textContent = usuarioEmail ? usuarioEmail : "—";
      if (mDescripcion) mDescripcion.textContent = safeStr(it.descripcion, "—");

      const estTxt = estadoLabelFromVisible(it.visible);
      if (mEstadoBadge) {
        mEstadoBadge.textContent = estTxt.toLowerCase();
        mEstadoBadge.className = badgeClassFromVisible(it.visible);
      }

      // ✅ FIX: Mostrar comentario previo (última revisión) si existe
      const prev = it?.ultima_revision?.comentario;
      if (mComentario) {
        const c = String(prev ?? "").trim();
        mComentario.value = c; // si no hay, queda vacío
      }

      const imgs = Array.isArray(it.imagenes) ? it.imagenes : [];
      const urls = [];

      for (const img of imgs) {
        const ruta = String(img?.ruta ?? "").trim();
        if (ruta) urls.push(ruta);
      }

      if (urls.length === 0) {
        const portada = String(it.imagen_portada ?? "").trim();
        if (portada) urls.push(portada);
      }

      if (mGaleria) mGaleria.innerHTML = "";
      if (urls.length > 0) {
        if (mNoImgs) mNoImgs.style.display = "none";
        for (const u of urls) {
          const img = document.createElement("img");
          const src = u.startsWith("http") ? u : (u.startsWith("/") ? (baseUrl + u) : (baseUrl + "/" + u));
          img.src = src;
          img.alt = "Imagen";
          img.loading = "lazy";
          mGaleria && mGaleria.appendChild(img);
        }
      } else {
        if (mNoImgs) mNoImgs.style.display = "block";
      }
    }

    async function abrirRevisar(id) {
      cancelFetchPrevio();
      aborter = new AbortController();

      clearModal();
      currentId = id;

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
        currentVisible = Number(it.visible);

        fillModal(it);

        const mi = ensureModal();
        mi && mi.show();
      } catch (e) {
        if (e && e.name === "AbortError") return;
        toastError("Error de red al obtener detalle.");
      } finally {
        aborter = null;
      }
    }

    async function enviarRevision(accion) {
      if (!currentId) return;

      const { mComentario } = getModalEls();
      const comentario = String(mComentario?.value ?? "").trim();

      if (!isComentarioValidoFor(accion, comentario)) {
        toastInfo("Debes ingresar un comentario (mín. 3 caracteres) para Observar o Rechazar.");
        mComentario && mComentario.focus();
        return;
      }

      if (window.Swal) {
        const txt = accion === "aprobar"
          ? "¿Confirmas aprobar esta publicación?"
          : accion === "rechazar"
            ? "¿Confirmas rechazar esta publicación?"
            : "¿Confirmas observar esta publicación?";

        const r = await Swal.fire({
          icon: "question",
          title: "Confirmar",
          text: txt,
          showCancelButton: true,
          confirmButtonText: "Sí, continuar",
          cancelButtonText: "Cancelar",
          confirmButtonColor: "#EA7C12",
        });

        if (!r.isConfirmed) return;
      }

      try {
        const url = baseUrl + "/api/soporte/productos/" + encodeURIComponent(currentId) + "/revisar";
        const res = await fetch(url, {
          method: "POST",
          headers: { "Content-Type": "application/json", Accept: "application/json" },
          cache: "no-store",
          body: JSON.stringify({ accion, comentario }),
        });

        if (res.status === 401) {
          const j = await safeJson(res);
          toastInfo(j?.mensaje || "Sesión finalizada. Inicia sesión nuevamente.");
          window.location.href = baseUrl + "/login";
          return;
        }

        const data = await safeJson(res);
        if (!res.ok || data?.ok === false) {
          toastError(data?.mensaje || data?.error || "No se pudo registrar la revisión.");
          return;
        }

        toastOk(data?.mensaje || "Acción realizada.");

        const mi = ensureModal();
        mi && mi.hide();

        listar();
      } catch (e) {
        toastError("Error de red al registrar revisión.");
      }
    }

    // =========================
    // Eventos filtros / tabla
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

    // Delegación: Revisar (fila)
    root.addEventListener("click", function (ev) {
      const btn = ev.target && ev.target.closest ? ev.target.closest(".js-revisar") : null;
      if (!btn) return;
      const id = btn.getAttribute("data-id");
      if (!id) return;
      abrirRevisar(id);
    });

    // Delegación: botones del modal
    document.addEventListener("click", function (ev) {
      const t = ev.target;
      if (!t || !t.closest) return;

      if (t.closest("#btnAprobar")) {
        ev.preventDefault();
        enviarRevision("aprobar");
        return;
      }
      if (t.closest("#btnRechazar")) {
        ev.preventDefault();
        enviarRevision("rechazar");
        return;
      }
      if (t.closest("#btnObservar")) {
        ev.preventDefault();
        enviarRevision("observar");
        return;
      }
    }, true);

    // Init
    setActiveQuickButtons();
    listar();
  }

  // =========================================================
  // Observador: detecta cuando el parcial se inserta (AJAX)
  // =========================================================
  function scanAndInit() {
    const root = document.querySelector(".ev-ap-page") || document;
    if (document.querySelector(".ev-ap-page")) initModule(root);
  }

  scanAndInit();

  const mo = new MutationObserver(function () {
    scanAndInit();
  });
  mo.observe(document.documentElement, { childList: true, subtree: true });

  document.addEventListener("ev:partial-loaded", scanAndInit);
  document.addEventListener("ev:content-loaded", scanAndInit);
})();
