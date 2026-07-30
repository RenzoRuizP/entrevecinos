// views/js/atenderPublicacion.js
(function () {
  "use strict";

  const baseUrl = (window.EV?.baseUrl ?? window.BASE_URL ?? "").replace(/\/+$/, "");

  const BOOT_KEY = "EV_BOOT_ATENDER_PUBLICACION";
  if (window[BOOT_KEY]) return;
  window[BOOT_KEY] = true;

  const REENVIO_PREFIX = "REENVIO_CORRECCION|";

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

  function textoContable(cantidad, singular, plural) {
    const total = Math.max(0, Number(cantidad) || 0);
    return `${total} ${total === 1 ? singular : plural}`;
  }

  function escapeHtml(v) {
    return String(v ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
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

  function getTipoPublicacionKey(it) {
    const directo = String(it?.tipo_publicacion || "").trim().toLowerCase();

    if (directo === "servicio") return "servicio";
    if (directo === "producto") return "producto";

    const txt = String(
      it?.tipo_nombre ||
      it?.tipo ||
      it?.nombre_tipo ||
      ""
    ).trim().toLowerCase();

    if (txt.includes("servicio")) return "servicio";
    return "producto";
  }

  function tipoPublicacionLabel(it) {
    return getTipoPublicacionKey(it) === "servicio" ? "Servicio" : "Producto";
  }

  function tipoPublicacionBadgeClass(it) {
    return getTipoPublicacionKey(it) === "servicio"
      ? "ev-badge ev-badge-aprobada"
      : "ev-badge ev-badge-pendiente";
  }

  function getUltimaRevision(it) {
    if (!it) return null;
    if (it.ultima_revision && typeof it.ultima_revision === "object") {
      return {
        comentario: String(it.ultima_revision.comentario ?? "").trim(),
        estado_nuevo: Number(it.ultima_revision.estado_nuevo ?? NaN),
      };
    }
    return null;
  }

  function esComentarioReenvio(comentario) {
    const c = String(comentario ?? "").trim();
    return !!c && c.startsWith(REENVIO_PREFIX);
  }

  function limpiarComentarioSistema(comentario) {
    const c = String(comentario ?? "").trim();
    if (!c) return "";
    if (esComentarioReenvio(c)) {
      return c.replace(REENVIO_PREFIX, "").trim();
    }
    return c;
  }

  function getEstadoKey(it) {
    const vis = Number(it?.visible ?? NaN);
    const rev = getUltimaRevision(it);
    const comentario = String(rev?.comentario ?? "").trim();
    const estadoNuevo = Number(rev?.estado_nuevo ?? NaN);

    if (vis === 1 && comentario && comentario.startsWith(REENVIO_PREFIX)) {
      return "corregido";
    }

    if (vis === 1 && comentario) {
      if (Number.isFinite(estadoNuevo) && estadoNuevo === 1) return "observada";
      return "pendiente";
    }

    if (vis === 0) return "borrador";
    if (vis === 1) return "pendiente";
    if (vis === 2) return "aprobada";
    if (vis === 3) return "rechazada";
    if (vis === 4) return "anulada";

    return "pendiente";
  }

  function estadoLabel(it) {
    const k = getEstadoKey(it);
    if (k === "borrador") return "Borrador";
    if (k === "pendiente") return "Pendiente";
    if (k === "aprobada") return "Aprobada";
    if (k === "rechazada") return "Rechazada";
    if (k === "observada") return "Observada";
    if (k === "corregido") return "Corregido";
    if (k === "anulada") return "Anulada";
    return "Pendiente";
  }

  function badgeClass(it) {
    const k = getEstadoKey(it);

    if (k === "corregido") return "ev-badge ev-badge-pendiente";
    if (k === "observada") return "ev-badge ev-badge-pendiente";

    if (k === "borrador") return "ev-badge ev-badge-borrador";
    if (k === "pendiente") return "ev-badge ev-badge-pendiente";
    if (k === "aprobada") return "ev-badge ev-badge-aprobada";
    if (k === "rechazada") return "ev-badge ev-badge-rechazada";
    if (k === "anulada") return "ev-badge ev-badge-borrador";

    return "ev-badge ev-badge-pendiente";
  }

  function normalizarComentarioParaEnviar(accion, comentario) {
    const c = String(comentario ?? "").trim();
    if (!c) return c;

    if (accion === "observar") {
      return c;
    }

    if (accion === "rechazar") {
      return c;
    }

    return c;
  }

  function isComentarioValidoFor(accion, comentario) {
    const c = String(comentario ?? "").trim();
    if (accion === "rechazar" || accion === "observar") return c.length >= 3;
    return true;
  }

  function aplicarReglasModalPorEstadoKey(estadoKey) {
    const btnAprobar = document.getElementById("btnAprobar");
    const btnRechazar = document.getElementById("btnRechazar");
    const btnObservar = document.getElementById("btnObservar");
    const txt = document.getElementById("mComentario");

    const esRevisable = (estadoKey === "pendiente" || estadoKey === "observada" || estadoKey === "corregido");
    const esSoloLectura = !esRevisable;

    if (btnAprobar) btnAprobar.style.display = esRevisable ? "" : "none";
    if (btnRechazar) btnRechazar.style.display = esRevisable ? "" : "none";
    if (btnObservar) btnObservar.style.display = esRevisable ? "" : "none";

    if (txt) {
      txt.readOnly = esSoloLectura;
      if (esSoloLectura) {
        txt.setAttribute("placeholder", "Esta publicación está en modo lectura.");
      } else {
        txt.setAttribute("placeholder", "Ej. Hola, revisamos tu publicación y necesitamos que ajustes la imagen principal para que se vea con más claridad.");
      }
    }

    const modalEl = document.getElementById("modalPub");
    if (modalEl) {
      modalEl.dataset.evEstadoKey = estadoKey || "";
      modalEl.dataset.evRevisable = esRevisable ? "1" : "0";
    }
  }

  function initModule(root) {
    if (!root || root.dataset.evInitAp === "1") return;
    root.dataset.evInitAp = "1";

    let aborter = null;
    let page = 1;
    let size = 10;
    let estado = "pendiente";
    let q = "";

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

    function getModalEls() {
      const modalEl = document.getElementById("modalPub");
      return {
        modalEl,
        mTipoPublicacion: document.getElementById("mTipoPublicacion"),
        mTitulo: document.getElementById("mTitulo"),
        mPrecio: document.getElementById("mPrecio"),
        mEstadoBadge: document.getElementById("mEstadoBadge"),
        mUsuario: document.getElementById("mUsuario"),
        mEmail: document.getElementById("mEmail"),
        mDescripcion: document.getElementById("mDescripcion"),
        mComentario: document.getElementById("mComentario"),
        mUltimoComentario: document.getElementById("mUltimoComentario"),
        mGaleria: document.getElementById("mGaleria"),
        mNoImgs: document.getElementById("mNoImgs"),
        mCantidadImagenes: document.getElementById("mCantidadImagenes"),
      };
    }

    let modalInstance = null;
    let currentId = null;
    let lightboxUrls = [];
    let lightboxIndex = 0;
    let lightboxTrigger = null;
    let lightboxTouchStartX = null;

    function getLightboxEls() {
      return {
        shell: document.getElementById('evApLightbox'),
        image: document.getElementById('evApLightboxImage'),
        counter: document.getElementById('evApLightboxCounter'),
        prev: document.getElementById('evApLightboxPrev'),
        next: document.getElementById('evApLightboxNext'),
        close: document.getElementById('evApLightboxClose')
      };
    }

    function renderLightbox() {
      const { shell, image, counter, prev, next } = getLightboxEls();
      if (!shell || !image || lightboxUrls.length === 0) return;

      lightboxIndex = Math.max(0, Math.min(lightboxUrls.length - 1, lightboxIndex));
      image.src = lightboxUrls[lightboxIndex];
      image.alt = `Imagen ${lightboxIndex + 1} de ${lightboxUrls.length} de la publicación`;
      if (counter) counter.textContent = `${lightboxIndex + 1} de ${lightboxUrls.length}`;

      const multiple = lightboxUrls.length > 1;
      if (prev) prev.hidden = !multiple;
      if (next) next.hidden = !multiple;
    }

    function abrirLightbox(index, trigger = null) {
      const { shell, close } = getLightboxEls();
      if (!shell || lightboxUrls.length === 0) return;

      lightboxIndex = Math.max(0, Math.min(lightboxUrls.length - 1, Number(index) || 0));
      lightboxTrigger = trigger instanceof HTMLElement ? trigger : null;
      renderLightbox();

      shell.hidden = false;
      shell.setAttribute('aria-hidden', 'false');
      document.body.classList.add('ev-ap-lightbox-open');
      window.requestAnimationFrame(() => shell.classList.add('is-open'));
      window.setTimeout(() => close?.focus(), 30);
    }

    function cerrarLightbox() {
      const { shell, image } = getLightboxEls();
      if (!shell || shell.hidden) return;

      shell.classList.remove('is-open');
      shell.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('ev-ap-lightbox-open');

      window.setTimeout(() => {
        shell.hidden = true;
        if (image) image.removeAttribute('src');
        lightboxTrigger?.focus?.();
        lightboxTrigger = null;
      }, 170);
    }

    function moverLightbox(delta) {
      if (lightboxUrls.length <= 1) return;
      lightboxIndex = (lightboxIndex + delta + lightboxUrls.length) % lightboxUrls.length;
      renderLightbox();
    }

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
          <td colspan="5" class="text-center py-4 ev-empty">
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

        const estTxt = estadoLabel(it);
        const tipoPub = tipoPublicacionLabel(it);

        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td class="ev-cell-fecha" data-label="Fecha">${escapeHtml(fecha)}</td>
          <td class="ev-cell-publicacion" data-label="Publicación">
            <div class="ev-ap-publication-cell">
              <span class="${tipoPublicacionBadgeClass(it)}">${escapeHtml(tipoPub)}</span>
              <div class="ev-ap-publication-copy">
                <span class="ev-table-title" title="${escapeHtml(titulo)}">${escapeHtml(titulo)}</span>
                <span class="ev-ap-publication-price">${escapeHtml(precio)}</span>
              </div>
            </div>
          </td>
          <td class="ev-cell-usuario" data-label="Vecino">
            <div class="ev-ap-user-cell">
              <strong>${escapeHtml(usuarioNombre || "—")}</strong>
              <span>${escapeHtml(usuarioEmail || "Sin correo registrado")}</span>
            </div>
          </td>
          <td class="ev-cell-estado" data-label="Estado"><span class="${badgeClass(it)}">${escapeHtml(estTxt)}</span></td>
          <td class="text-end ev-cell-acciones" data-label="Acciones">
            <button type="button" class="btn btn-sm btn-outline-success js-revisar" data-id="${String(id)}">
              <i class="bi bi-eye me-1" aria-hidden="true"></i>Revisar
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
      const {
        mTipoPublicacion,
        mTitulo,
        mPrecio,
        mUsuario,
        mEmail,
        mDescripcion,
        mComentario,
        mUltimoComentario,
        mGaleria,
        mNoImgs,
        mCantidadImagenes,
        mEstadoBadge,
        modalEl
      } = getModalEls();

      if (mTipoPublicacion) mTipoPublicacion.textContent = "—";
      if (mTitulo) mTitulo.textContent = "—";
      if (mPrecio) mPrecio.textContent = "—";
      if (mUsuario) mUsuario.textContent = "—";
      if (mEmail) mEmail.textContent = "—";
      if (mDescripcion) mDescripcion.textContent = "—";
      if (mComentario) mComentario.value = "";
      if (mUltimoComentario) mUltimoComentario.textContent = "Sin mensaje registrado.";
      lightboxUrls = [];
      lightboxIndex = 0;
      cerrarLightbox();

      if (mGaleria) {
        mGaleria.innerHTML = "";
        mGaleria.dataset.count = "0";
      }
      if (mNoImgs) mNoImgs.style.display = "grid";
      if (mCantidadImagenes) mCantidadImagenes.textContent = textoContable(0, "imagen", "imágenes");

      if (mEstadoBadge) {
        mEstadoBadge.textContent = "pendiente";
        mEstadoBadge.className = "ev-badge ev-badge-pendiente";
      }

      currentId = null;

      if (modalEl) {
        modalEl.dataset.evEstadoKey = "";
        modalEl.dataset.evRevisable = "1";
      }

      aplicarReglasModalPorEstadoKey("pendiente");
    }

    function fillModal(it) {
      const {
        mTipoPublicacion,
        mTitulo,
        mPrecio,
        mUsuario,
        mEmail,
        mDescripcion,
        mComentario,
        mUltimoComentario,
        mGaleria,
        mNoImgs,
        mCantidadImagenes,
        mEstadoBadge
      } = getModalEls();

      const usuarioNombre = safeStr(it.usuario_nombre, "");
      const usuarioEmail = safeStr(it.usuario_email, "");

      if (mTipoPublicacion) mTipoPublicacion.textContent = tipoPublicacionLabel(it);
      if (mTitulo) mTitulo.textContent = safeStr(it.titulo);
      if (mPrecio) mPrecio.textContent = money(it.precio);
      if (mUsuario) mUsuario.textContent = (usuarioNombre || usuarioEmail) ? safeStr(usuarioNombre, "—") : "—";
      if (mEmail) mEmail.textContent = usuarioEmail ? usuarioEmail : "—";
      if (mDescripcion) mDescripcion.textContent = safeStr(it.descripcion, "—");

      const estTxt = estadoLabel(it);
      if (mEstadoBadge) {
        mEstadoBadge.textContent = estTxt.toLowerCase();
        mEstadoBadge.className = badgeClass(it);
      }

      const prev = String(it?.ultima_revision?.comentario ?? "").trim();
      const prevLimpio = limpiarComentarioSistema(prev);

      if (mComentario) {
        const estadoKey = getEstadoKey(it);

        if (estadoKey === "observada" || estadoKey === "rechazada") {
          mComentario.value = prevLimpio;
        } else {
          mComentario.value = "";
        }
      }

      if (mUltimoComentario) {
        if (prevLimpio) {
          mUltimoComentario.textContent = prevLimpio;
        } else {
          mUltimoComentario.textContent = "Sin mensaje registrado.";
        }
      }

      const k = getEstadoKey(it);
      aplicarReglasModalPorEstadoKey(k);

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

      if (mGaleria) {
        mGaleria.innerHTML = "";
        mGaleria.dataset.count = String(urls.length);
      }
      if (mCantidadImagenes) {
        mCantidadImagenes.textContent = textoContable(urls.length, "imagen", "imágenes");
      }

      if (urls.length > 0) {
        if (mNoImgs) mNoImgs.style.display = "none";

        lightboxUrls = urls.map((u) => (
          u.startsWith("http") ? u : (u.startsWith("/") ? (baseUrl + u) : (baseUrl + "/" + u))
        ));

        lightboxUrls.forEach((src, index) => {
          const figure = document.createElement("figure");
          figure.className = "ev-galeria-item ev-ap-galeria-item";

          const button = document.createElement("button");
          button.type = "button";
          button.className = "ev-ap-image-button";
          button.dataset.evApImageIndex = String(index);
          button.setAttribute("aria-label", `Ampliar imagen ${index + 1} de ${lightboxUrls.length}`);
          button.title = "Ver imagen ampliada";

          const img = document.createElement("img");
          img.src = src;
          img.alt = `Imagen ${index + 1} de la publicación`;
          img.loading = "lazy";
          img.decoding = "async";

          const zoom = document.createElement("span");
          zoom.className = "ev-ap-image-zoom";
          zoom.setAttribute("aria-hidden", "true");
          zoom.innerHTML = '<i class="bi bi-search"></i><small>Ver detalle</small>';

          button.append(img, zoom);
          figure.appendChild(button);
          if (mGaleria) mGaleria.appendChild(figure);
        });
      } else {
        if (mNoImgs) mNoImgs.style.display = "grid";
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
          if (res.status === 404 || data?.error === 'NOT_FOUND') {
            toastInfo('La publicación ya no está disponible. El listado se actualizará para retirar el registro.');
            listar();
            return;
          }
          toastError(data?.mensaje || data?.error || "No se pudo obtener el detalle.");
          return;
        }

        const it = data.item || {};
        fillModal(it);

        const mi = ensureModal();
        if (mi) mi.show();
      } catch (e) {
        if (e && e.name === "AbortError") return;
        toastError("Error de red al obtener detalle.");
      } finally {
        aborter = null;
      }
    }

    async function enviarRevision(accion) {
      if (!currentId) return;

      const modalEl = document.getElementById("modalPub");
      const revisable = modalEl && modalEl.dataset.evRevisable === "1";

      if (!revisable) {
        toastInfo("Esta publicación está en modo lectura y no admite acciones.");
        return;
      }

      const { mComentario } = getModalEls();
      const comentarioRaw = String(mComentario?.value ?? "").trim();
      const comentario = normalizarComentarioParaEnviar(accion, comentarioRaw);

      if (!isComentarioValidoFor(accion, comentario)) {
        toastInfo("Debes ingresar un mensaje claro para el vecino (mín. 3 caracteres) al observar o rechazar.");
        if (mComentario) mComentario.focus();
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
        if (mi) mi.hide();

        listar();
      } catch (e) {
        toastError("Error de red al registrar revisión.");
      }
    }

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
      elEstado.addEventListener("change", function () {
        estado = String(elEstado.value || "pendiente").toLowerCase();
        page = 1;
        listar();
      }, { passive: true });
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

    if (btnRefrescar) btnRefrescar.addEventListener("click", () => listar());
    if (elBtnPrev) elBtnPrev.addEventListener("click", () => { if (page > 1) { page--; listar(); } });
    if (elBtnNext) elBtnNext.addEventListener("click", () => { page++; listar(); });

    root.addEventListener("click", function (ev) {
      const btn = ev.target && ev.target.closest ? ev.target.closest(".js-revisar") : null;
      if (!btn) return;

      const id = btn.getAttribute("data-id");
      if (!id) return;

      abrirRevisar(id);
    });

    document.addEventListener("click", function (ev) {
      const t = ev.target;
      if (!t || !t.closest) return;

      const imageButton = t.closest('[data-ev-ap-image-index]');
      if (imageButton) {
        ev.preventDefault();
        abrirLightbox(Number(imageButton.dataset.evApImageIndex || 0), imageButton);
        return;
      }

      if (t.closest('#evApLightboxClose') || t.closest('[data-ev-ap-lightbox-close="1"]')) {
        ev.preventDefault();
        cerrarLightbox();
        return;
      }

      if (t.closest('#evApLightboxPrev')) {
        ev.preventDefault();
        moverLightbox(-1);
        return;
      }

      if (t.closest('#evApLightboxNext')) {
        ev.preventDefault();
        moverLightbox(1);
        return;
      }

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

    document.addEventListener('keydown', function (ev) {
      const shell = document.getElementById('evApLightbox');
      if (!shell || shell.hidden) return;

      if (ev.key === 'Escape') {
        ev.preventDefault();
        cerrarLightbox();
      } else if (ev.key === 'ArrowLeft') {
        ev.preventDefault();
        moverLightbox(-1);
      } else if (ev.key === 'ArrowRight') {
        ev.preventDefault();
        moverLightbox(1);
      }
    });

    const lightboxStage = document.querySelector('#evApLightbox .ev-ap-lightbox-stage');
    lightboxStage?.addEventListener('touchstart', (ev) => {
      lightboxTouchStartX = ev.touches?.[0]?.clientX ?? null;
    }, { passive: true });
    lightboxStage?.addEventListener('touchend', (ev) => {
      if (lightboxTouchStartX === null) return;
      const endX = ev.changedTouches?.[0]?.clientX ?? lightboxTouchStartX;
      const delta = endX - lightboxTouchStartX;
      lightboxTouchStartX = null;
      if (Math.abs(delta) < 45) return;
      moverLightbox(delta > 0 ? -1 : 1);
    }, { passive: true });

    document.getElementById('modalPub')?.addEventListener('hidden.bs.modal', cerrarLightbox);

    setActiveQuickButtons();
    listar();
  }

  function scanAndInit() {
    const root = document.querySelector(".ev-ap-page") || document;
    if (document.querySelector(".ev-ap-page")) initModule(root);
  }

  scanAndInit();

  const mo = new MutationObserver(function () { scanAndInit(); });
  mo.observe(document.documentElement, { childList: true, subtree: true });

  document.addEventListener("ev:partial-loaded", scanAndInit);
  document.addEventListener("ev:content-loaded", scanAndInit);
})();
