// views/js/datosPersonales.js
// Mi perfil - Wizard 3 pasos EV + residencia + cambio clave
// Punto 5: compatible con foto de perfil clickeable desde topbar y Mi perfil.
(function () {
  "use strict";

  const baseURL = (window.BASE_URL || "/entrevecinos").replace(/\/$/, "");

  // Endpoints
  const API_CONDOMINIOS = `${baseURL}/condominios`;
  const API_URBANIZACIONES = `${baseURL}/urbanizaciones`;

  const API_UB_DEPTOS = `${baseURL}/ubigeo/departamentos`;
  const API_UB_PROVS  = (depId) => `${baseURL}/ubigeo/departamentos/${depId}/provincias`;
  const API_UB_DISTS  = (provId) => `${baseURL}/ubigeo/provincias/${provId}/distritos`;

  // Guardado por secciones
  const API_ACTUALIZAR_TELEFONO = `${baseURL}/api/usuario/actualizar-telefono`;
  const API_SOLICITAR_CAMBIO = `${baseURL}/api/usuario/solicitar-cambio-residencia`;
  const API_CAMBIAR_CLAVE = `${baseURL}/api/usuario/cambiar-clave`;
  const API_FOTO_PERFIL = `${baseURL}/api/usuario/foto-perfil`;

  function $(sel, root = document) { return root.querySelector(sel); }

  function setHidden(el, hidden) {
    if (!el) return;
    el.classList.toggle("d-none", !!hidden);
  }

  function safeSwal() {
    return (typeof window.Swal !== "undefined" && window.Swal && typeof window.Swal.fire === "function");
  }

  function swalWarn(title, text) {
    if (!safeSwal()) { alert(`${title}\n\n${text}`); return; }
    Swal.fire({ icon: "warning", title, text, confirmButtonColor: "#115C41" });
  }

  function swalErr(text) {
    if (!safeSwal()) { alert(`Error\n\n${text}`); return; }
    Swal.fire({ icon: "error", title: "Error", text, confirmButtonColor: "#BF3604" });
  }

  function swalOk(title, text) {
    if (!safeSwal()) { alert(`${title}\n\n${text}`); return; }
    Swal.fire({ icon: "success", title, text, confirmButtonColor: "#115C41" });
  }

  function swalInfo(title, text) {
    if (!safeSwal()) { alert(`${title}\n\n${text}`); return; }
    Swal.fire({ icon: "info", title, text, confirmButtonColor: "#115C41" });
  }

  async function fetchJSON(url, opts = {}) {
    const res = await fetch(url, {
      method: opts.method || "GET",
      cache: "no-store",
      credentials: "include",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        "Accept": "application/json",
        ...(opts.headers || {})
      },
      body: opts.body || null
    });

    const ct = (res.headers.get("content-type") || "").toLowerCase();
    if (!ct.includes("application/json")) {
      const txt = await res.text().catch(() => "");
      throw new Error("Respuesta no JSON. " + (txt ? txt.slice(0, 150) : ""));
    }

    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      throw new Error(data?.message || data?.mensaje || data?.error || `HTTP ${res.status}`);
    }
    return data;
  }

  function getBaseState(container) {
    const root = $("#dp-state", container) || document.getElementById("dp-state");
    return {
      tipo: (root?.dataset?.tipo || "").trim().toLowerCase(),
      codCondominio: (root?.dataset?.codigoCondominio || "").trim(),
      codUrbanizacion: (root?.dataset?.codigoUrbanizacion || "").trim(),
      direccion: (root?.dataset?.direccion || "").trim(),
      comprobante: (root?.dataset?.comprobante || "").trim(),
      ub_depto: (root?.dataset?.ubDepto || "").trim(),
      ub_prov: (root?.dataset?.ubProv || "").trim(),
      ub_dist: (root?.dataset?.ubDist || "").trim(),
    };
  }

  function setStep(container, step) {
    const steps = container.querySelectorAll(".ev-stepper .ev-step");
    const panels = container.querySelectorAll(".ev-step-panel");

    steps.forEach((s) => {
      const isActive = String(s.dataset.step) === String(step);
      s.classList.toggle("active", isActive);
      s.classList.toggle("done", Number(s.dataset.step) < Number(step));
    });

    panels.forEach((p) => {
      const show = String(p.dataset.panel) === String(step);
      p.classList.toggle("d-none", !show);
    });

    const btnAnterior = $("#btnAnterior", container);
    const btnSiguiente = $("#btnSiguiente", container);

    if (btnAnterior) btnAnterior.disabled = (Number(step) <= 1);
    if (btnSiguiente) btnSiguiente.disabled = (Number(step) >= 3);

    container.dataset.dpStep = String(step);
  }

  function currentStep(container) {
    return Number(container.dataset.dpStep || "1");
  }

  function resetSelect(sel, placeholder, { disabled = false } = {}) {
    if (!sel) return;
    sel.innerHTML = `<option value="">${placeholder}</option>`;
    sel.value = "";
    sel.disabled = !!disabled;
  }

  function fillSelect(sel, placeholder, arr, mapFn, selectedValue) {
    if (!sel) return;
    sel.innerHTML = `<option value="">${placeholder}</option>`;
    (arr || []).forEach((item) => {
      const opt = document.createElement("option");
      const { value, text } = mapFn(item);
      opt.value = value;
      opt.textContent = text;
      if (selectedValue !== undefined && selectedValue !== null && String(selectedValue) === String(value)) {
        opt.selected = true;
      }
      sel.appendChild(opt);
    });
  }

  async function initUbigeo(container, base) {
    const selDep = $("#dpUbDepto", container);
    const selProv = $("#dpUbProv", container);
    const selDist = $("#dpUbDist", container);
    if (!selDep || !selProv || !selDist) return;

    if (container.dataset.dpUbigeoInit === "1") return;
    container.dataset.dpUbigeoInit = "1";

    const preDep  = selDep.dataset.valorRegistrado  || base.ub_depto || "";
    const preProv = selProv.dataset.valorRegistrado || base.ub_prov  || "";
    const preDist = selDist.dataset.valorRegistrado || base.ub_dist  || "";

    resetSelect(selDep, "Cargando...", { disabled: true });
    resetSelect(selProv, "-- Seleccione --", { disabled: true });
    resetSelect(selDist, "-- Seleccione --", { disabled: true });

    async function loadProvincias(depId, preselectProv = "", preselectDist = "") {
      resetSelect(selProv, "Cargando...", { disabled: true });
      resetSelect(selDist, "-- Seleccione --", { disabled: true });

      const provs = await fetchJSON(API_UB_PROVS(depId));
      fillSelect(selProv, "-- Seleccione --", Array.isArray(provs) ? provs : [], (p) => ({
        value: p.codigo_provincia, text: p.nombre_provincia
      }), preselectProv);
      selProv.disabled = false;

      if (preselectProv) await loadDistritos(preselectProv, preselectDist);
    }

    async function loadDistritos(provId, preselectDist = "") {
      resetSelect(selDist, "Cargando...", { disabled: true });
      const dists = await fetchJSON(API_UB_DISTS(provId));
      fillSelect(selDist, "-- Seleccione --", Array.isArray(dists) ? dists : [], (d) => ({
        value: d.codigo_distrito, text: d.nombre_distrito
      }), preselectDist);
      selDist.disabled = false;
    }

    try {
      const deps = await fetchJSON(API_UB_DEPTOS);
      fillSelect(selDep, "-- Seleccione --", Array.isArray(deps) ? deps : [], (d) => ({
        value: d.codigo_departamento, text: d.nombre_departamento
      }), preDep);
      selDep.disabled = false;

      if (preDep) await loadProvincias(preDep, preProv, preDist);
    } catch (e) {
      console.error("[EV][Ubigeo] Error:", e);
      resetSelect(selDep, "No se pudo cargar", { disabled: true });
    }

    selDep.addEventListener("change", async () => {
      try {
        const depId = selDep.value;
        if (!depId) {
          resetSelect(selProv, "-- Seleccione --", { disabled: true });
          resetSelect(selDist, "-- Seleccione --", { disabled: true });
          return;
        }
        await loadProvincias(depId, "", "");
      } catch (e) {
        console.error("[EV][Ubigeo] change dep:", e);
      }
    });

    selProv.addEventListener("change", async () => {
      try {
        const provId = selProv.value;
        if (!provId) {
          resetSelect(selDist, "-- Seleccione --", { disabled: true });
          return;
        }
        await loadDistritos(provId, "");
      } catch (e) {
        console.error("[EV][Ubigeo] change prov:", e);
      }
    });
  }

  function currentTipo(container, base) {
    const rCondo = $("#dpTipoCondominio", container);
    const rUrb = $("#dpTipoUrbanizacion", container);
    if (rCondo && rCondo.checked) return "condominio";
    if (rUrb && rUrb.checked) return "urbanizacion";
    return (base.tipo || "").toLowerCase();
  }

  function getResidenciaNow(container, base) {
    const tipo = currentTipo(container, base);
    const condominio = $("#comboCondominio", container)?.value || "";
    const urbanizacion = $("#comboUrbanizacion", container)?.value || "";
    const direccion = ($("#direccion", container)?.value || "").trim();

    const ubD = $("#dpUbDepto", container)?.value || "";
    const ubP = $("#dpUbProv", container)?.value || "";
    const ubDi = $("#dpUbDist", container)?.value || "";

    return { tipo, condominio, urbanizacion, direccion, ubD, ubP, ubDi };
  }

  function residenciaChanged(container, base) {
    const now = getResidenciaNow(container, base);

    if (String(now.tipo) !== String(base.tipo)) return true;

    if (now.tipo === "condominio") {
      if (String(now.condominio) !== String(base.codCondominio)) return true;
    }
    if (now.tipo === "urbanizacion") {
      if (String(now.urbanizacion) !== String(base.codUrbanizacion)) return true;
    }

    if (String(now.direccion) !== String(base.direccion)) return true;
    if (String(now.ubD) !== String(base.ub_depto)) return true;
    if (String(now.ubP) !== String(base.ub_prov)) return true;
    if (String(now.ubDi) !== String(base.ub_dist)) return true;

    return false;
  }

  async function initCondominios(container, base) {
    const combo = $("#comboCondominio", container);
    if (!combo) return;

    if (combo.dataset.evLoaded === "1") return;
    combo.dataset.evLoaded = "1";

    const pre = combo.dataset.valorRegistrado || base.codCondominio || "";
    resetSelect(combo, "Cargando condominios...", { disabled: true });

    try {
      const data = await fetchJSON(API_CONDOMINIOS);
      const arr = Array.isArray(data) ? data : [];

      fillSelect(combo, "-- Seleccione condominio --", arr, (c) => ({
        value: c.codigo_condominio,
        text: c.nombre_condominio
      }), pre);

      const map = new Map();
      arr.forEach((c) => map.set(String(c.codigo_condominio), String(c.direccion_condominio || "")));
      combo._evDirMap = map;

      combo.disabled = false;

      if (pre) {
        const dir = map.get(String(pre)) || "";
        const inputDir = $("#direccion", container);
        if (inputDir) inputDir.value = dir || base.direccion || "";
      }
    } catch (e) {
      console.error("[EV][Condominios] Error:", e);
      resetSelect(combo, "No se pudo cargar", { disabled: true });
    }
  }

  async function initUrbanizaciones(container, base) {
    const combo = $("#comboUrbanizacion", container);
    if (!combo) return;

    if (combo.dataset.evLoaded === "1") return;
    combo.dataset.evLoaded = "1";

    const pre = combo.dataset.valorRegistrado || base.codUrbanizacion || "";
    resetSelect(combo, "Cargando urbanizaciones...", { disabled: true });

    try {
      const data = await fetchJSON(API_URBANIZACIONES);
      const arr = Array.isArray(data) ? data : [];

      fillSelect(combo, "-- Seleccione urbanización --", arr, (u) => ({
        value: u.codigo_urbanizacion,
        text: u.nombre_urbanizacion
      }), pre);

      const map = new Map();
      arr.forEach((u) => map.set(String(u.codigo_urbanizacion), String(u.direccion_urbanizacion || "")));
      combo._evDirMap = map;

      combo.disabled = false;

      if (pre) {
        const dir = map.get(String(pre)) || "";
        const inputDir = $("#direccion", container);
        if (inputDir) inputDir.value = dir || base.direccion || "";
      }
    } catch (e) {
      console.error("[EV][Urbanizaciones] Error:", e);
      resetSelect(combo, "No se pudo cargar", { disabled: true });
    }
  }

  function refreshResidenciaUI(container, base) {
    const wrapCondo = $("#wrapCondominio", container);
    const wrapUrb = $("#wrapUrbanizacion", container);
    const wrapUp = $("#wrapUploadDomicilio", container);

    const tipo = currentTipo(container, base);
    setHidden(wrapCondo, tipo !== "condominio");
    setHidden(wrapUrb, tipo !== "urbanizacion");

    if (tipo === "condominio") initCondominios(container, base);
    if (tipo === "urbanizacion") initUrbanizaciones(container, base);

    const changed = residenciaChanged(container, base);
    setHidden(wrapUp, !changed);

    const file = $("#dpDocDomicilio", container);
    if (file) file.required = !!changed;
  }

  function initFilePreview(container) {
    const file = $("#dpDocDomicilio", container);
    const wrap = $("#wrapFileSelected", container);
    const aName = $("#dpFileSelectedName", container);
    const meta = $("#dpFileSelectedMeta", container);
    const btnRemove = $("#btnRemoveSelectedFile", container);

    if (!file || !wrap || !aName || !meta || !btnRemove) return;
    if (file.dataset.evBound === "1") return;
    file.dataset.evBound = "1";

    function humanSize(n) {
      if (!Number.isFinite(n)) return "";
      const kb = n / 1024;
      if (kb < 1024) return `${kb.toFixed(0)} KB`;
      return `${(kb / 1024).toFixed(2)} MB`;
    }

    function clearSelected() {
      file.value = "";
      setHidden(wrap, true);
    }

    file.addEventListener("change", () => {
      const f = file.files && file.files[0] ? file.files[0] : null;
      if (!f) return clearSelected();

      aName.textContent = f.name || "archivo";
      meta.textContent = `${(f.type || "file").toLowerCase()} · ${humanSize(f.size)}`;
      setHidden(wrap, false);
    });

    btnRemove.addEventListener("click", clearSelected);
  }

  function fotoPerfilUrl(value) {
    const raw = String(value || "").trim();

    if (!raw) return "";

    if (/^https?:\/\//i.test(raw)) {
      return raw;
    }

    /*
     * Si el backend retorna una URL absoluta del proyecto:
     * /entrevecinos/resources/uploads/perfiles/...
     * no se vuelve a concatenar BASE_URL.
     */
    if (raw.startsWith("/")) {
      return raw;
    }

    return `${baseURL}/${raw.replace(/^\/+/, "")}`;
  }

  function fotoPerfilConCacheBuster(url) {
    const clean = String(url || "").trim();

    if (!clean) return "";

    if (/^blob:/i.test(clean) || /^data:/i.test(clean)) {
      return clean;
    }

    const sep = clean.includes("?") ? "&" : "?";
    return `${clean}${sep}v=${Date.now()}`;
  }

  function resolverUrlFotoPerfil(data) {
    const payload = data && typeof data === "object" ? data : {};
    const nested = payload.data && typeof payload.data === "object" ? payload.data : {};

    return fotoPerfilUrl(
      payload.url ||
      payload.foto_url ||
      payload.foto_perfil_url ||
      payload.foto_perfil ||
      nested.url ||
      nested.foto_url ||
      nested.foto_perfil_url ||
      nested.foto_perfil ||
      ""
    );
  }

  function actualizarAvataresGlobales(url) {
    const finalUrl = fotoPerfilConCacheBuster(url);
    if (!finalUrl) return;

    const selectors = [
      "[data-ev-avatar-img]",
      "#evDpFotoPreview",
      "#userDropdown img",
      ".user-menu .dropdown-menu img",
      ".ev-profile-photo-trigger img"
    ];

    const imgs = new Set();

    selectors.forEach((selector) => {
      document.querySelectorAll(selector).forEach((img) => {
        if (img && img.tagName && img.tagName.toLowerCase() === "img") {
          imgs.add(img);
        }
      });
    });

    imgs.forEach((img) => {
      img.src = finalUrl;
    });

    document.dispatchEvent(new CustomEvent("ev:perfil-foto-actualizada", {
      detail: { url: finalUrl }
    }));
  }

  function validarArchivoFotoPerfil(file) {
    if (!file) {
      return "Selecciona una imagen.";
    }

    const max = 2 * 1024 * 1024;
    const name = String(file.name || "");
    const type = String(file.type || "").toLowerCase();

    const okType = ["image/jpeg", "image/png", "image/webp"].includes(type);
    const okExt = /\.(jpg|jpeg|png|webp)$/i.test(name);

    if (!okType && !okExt) {
      return "Solo se permiten imágenes JPG, PNG o WEBP.";
    }

    if (Number(file.size || 0) <= 0) {
      return "La imagen seleccionada no es válida.";
    }

    if (Number(file.size || 0) > max) {
      return "La imagen debe pesar como máximo 2 MB.";
    }

    return "";
  }

  function crearInputFotoPerfil() {
    let input = document.getElementById("evDpFotoPerfilInput");

    if (!input) {
      input = document.createElement("input");
      input.type = "file";
      input.id = "evDpFotoPerfilInput";
      input.accept = "image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp";
      input.style.position = "fixed";
      input.style.left = "-9999px";
      input.style.width = "1px";
      input.style.height = "1px";
      input.style.opacity = "0";
      input.setAttribute("aria-hidden", "true");
      document.body.appendChild(input);
    }

    return input;
  }

  async function subirFotoPerfil(file, container) {
    const error = validarArchivoFotoPerfil(file);
    if (error) {
      swalWarn("Imagen no válida", error);
      return;
    }

    const trigger = container?.querySelector("[data-ev-avatar-trigger]");
    const preview = container?.querySelector("#evDpFotoPreview, [data-ev-avatar-img]");
    const originalTriggerHtml = trigger ? trigger.innerHTML : "";

    if (trigger) {
      trigger.disabled = true;
      trigger.classList.add("is-uploading");
      trigger.setAttribute("aria-busy", "true");
    }

    let localPreviewUrl = "";

    try {
      /*
       * Previsualización inmediata, sin esperar al servidor.
       * Si el servidor responde correctamente, se reemplaza por la URL final real.
       */
      if (preview && window.URL?.createObjectURL) {
        localPreviewUrl = URL.createObjectURL(file);
        preview.src = localPreviewUrl;
      }

      const fd = new FormData();
      fd.append("foto_perfil", file);
      fd.append("foto", file);
      fd.append("avatar", file);

      const res = await fetch(API_FOTO_PERFIL, {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "Accept": "application/json"
        },
        credentials: "include",
        cache: "no-store",
        body: fd
      });

      const data = await res.json().catch(() => ({}));

      if (res.status === 401 || res.status === 403) {
        throw new Error(data.mensaje || data.message || "Tu sesión no está activa. Vuelve a iniciar sesión.");
      }

      if (!res.ok || data.ok === false || data.success === false) {
        throw new Error(data.mensaje || data.message || data.error || `No se pudo actualizar la foto. HTTP ${res.status}`);
      }

      const urlFinal = resolverUrlFotoPerfil(data);

      if (urlFinal) {
        actualizarAvataresGlobales(urlFinal);
      } else if (localPreviewUrl) {
        /*
         * Fallback visual: el backend guardó la imagen, pero no devolvió URL.
         * Igual dejamos visible la previsualización local para no romper la UX.
         */
        actualizarAvataresGlobales(localPreviewUrl);
      }

      swalOk("Foto actualizada", "Tu foto de perfil fue actualizada correctamente.");
    } catch (e) {
      console.error("[EV][FotoPerfil] Error:", e);
      swalErr(e.message || "No se pudo actualizar la foto de perfil.");
    } finally {
      if (trigger) {
        trigger.disabled = false;
        trigger.classList.remove("is-uploading");
        trigger.removeAttribute("aria-busy");

        /*
         * No se restaura innerHTML porque el botón contiene la imagen actualizada.
         * Se conserva la estructura actual del DOM.
         */
        if (!trigger.querySelector("img") && originalTriggerHtml) {
          trigger.innerHTML = originalTriggerHtml;
        }
      }

      if (localPreviewUrl && window.URL?.revokeObjectURL) {
        setTimeout(() => URL.revokeObjectURL(localPreviewUrl), 1200);
      }
    }
  }

  function initFotoPerfil(container) {
    if (!container || container.dataset.dpFotoPerfilBound === "1") return;
    container.dataset.dpFotoPerfilBound = "1";

    const input = crearInputFotoPerfil();

    container.querySelectorAll("[data-ev-avatar-trigger]").forEach((trigger) => {
      if (trigger.dataset.evFotoPerfilBound === "1") return;
      trigger.dataset.evFotoPerfilBound = "1";

      trigger.addEventListener("click", (event) => {
        event.preventDefault();
        event.stopPropagation();

        input.value = "";
        input._evDpContainer = container;
        input.click();
      });
    });

    if (input.dataset.evFotoPerfilInputBound !== "1") {
      input.dataset.evFotoPerfilInputBound = "1";

      input.addEventListener("change", async () => {
        const file = input.files && input.files[0] ? input.files[0] : null;
        const targetContainer = input._evDpContainer || document.querySelector(".container-datos-personales");

        if (!file || !targetContainer) {
          input.value = "";
          return;
        }

        await subirFotoPerfil(file, targetContainer);
        input.value = "";
      });
    }

    document.addEventListener("ev:perfil-foto-actualizada", (event) => {
      const url = String(event?.detail?.url || "").trim();
      if (!url) return;

      container.querySelectorAll("[data-ev-avatar-img]").forEach((img) => {
        img.src = url;
      });
    });
  }

  async function guardarPaso1(container) {
    const btn = $("#btnGuardarPaso1", container);
    const original = btn ? btn.innerHTML : "";

    const telefono = ($("#telefono", container)?.value || "").trim();

    if (!telefono) return swalWarn("Teléfono requerido", "Ingresa tu número de teléfono.");
    if (!/^9\d{8}$/.test(telefono.replace(/\s+/g, ""))) {
      return swalWarn("Teléfono inválido", "El teléfono debe tener 9 dígitos y empezar con 9.");
    }

    if (btn) { btn.disabled = true; btn.classList.add("saving"); btn.innerHTML = "Guardando..."; }

    try {
      const res = await fetch(API_ACTUALIZAR_TELEFONO, {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-Requested-With": "XMLHttpRequest" },
        credentials: "include",
        body: JSON.stringify({ telefono })
      });

      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data.message || data.mensaje || data.error || `HTTP ${res.status}`);

      swalOk("Guardado", "Tu teléfono fue actualizado.");
    } catch (e) {
      console.error("[EV][Paso1] Error:", e);
      swalErr(e.message || "No se pudo guardar el teléfono.");
    } finally {
      if (btn) { btn.disabled = false; btn.classList.remove("saving"); btn.innerHTML = original; }
    }
  }

  async function guardarPaso2(container, base) {
    const btn = $("#btnGuardarPaso2", container);
    const original = btn ? btn.innerHTML : "";

    const now = getResidenciaNow(container, base);

    if (!now.ubD || !now.ubP || !now.ubDi) {
      return swalWarn("Ubigeo requerido", "Selecciona Departamento, Provincia y Distrito.");
    }

    if (now.tipo === "condominio") {
      if (!now.condominio) return swalWarn("Selecciona tu condominio", "Debes seleccionar un condominio.");
    } else if (now.tipo === "urbanizacion") {
      if (!now.urbanizacion) return swalWarn("Selecciona tu urbanización", "Debes seleccionar una urbanización.");
    } else {
      return swalWarn("Residencia no definida", "No se pudo determinar el tipo de residencia.");
    }

    const changed = residenciaChanged(container, base);

    if (!changed) {
      return swalInfo("Sin cambios", "No detecté cambios en tu residencia para enviar solicitud.");
    }

    const file = $("#dpDocDomicilio", container);
    const f = file?.files?.[0] || null;
    if (!f) return swalWarn("Adjunta un comprobante", "Para cambiar de domicilio debes adjuntar un comprobante.");

    const max = 5 * 1024 * 1024;
    const okType = /^(application\/pdf|image\/jpeg|image\/png)$/i.test(f.type);
    const okExt = /\.(pdf|jpg|jpeg|png)$/i.test(f.name || "");
    if (!(okType || okExt)) return swalWarn("Archivo no permitido", "Solo se permite PDF, JPG o PNG.");
    if (f.size > max) return swalWarn("Archivo muy pesado", "El archivo no debe superar 5MB.");

    if (btn) { btn.disabled = true; btn.classList.add("saving"); btn.innerHTML = "Enviando..."; }

    try {
      const fd = new FormData();
      fd.append("tipo_conjunto", now.tipo);
      fd.append("direccion", now.direccion);
      fd.append("codigo_condominio", now.tipo === "condominio" ? now.condominio : "");
      fd.append("codigo_urbanizacion", now.tipo === "urbanizacion" ? now.urbanizacion : "");
      fd.append("ubigeo_departamento", now.ubD);
      fd.append("ubigeo_provincia", now.ubP);
      fd.append("ubigeo_distrito", now.ubDi);
      fd.append("documento_domicilio", f);

      const res2 = await fetch(API_SOLICITAR_CAMBIO, {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
        credentials: "include",
        body: fd,
      });

      const data2 = await res2.json().catch(() => ({}));
      if (!res2.ok) throw new Error(data2.message || data2.mensaje || data2.error || `HTTP ${res2.status}`);

      swalOk("Solicitud enviada", "Tu solicitud de cambio de domicilio fue enviada. Un administrador la revisará.");
    } catch (e) {
      console.error("[EV][Paso2] Error:", e);
      swalErr(e.message || "No se pudo enviar la solicitud.");
    } finally {
      if (btn) { btn.disabled = false; btn.classList.remove("saving"); btn.innerHTML = original; }
    }
  }

  async function guardarPaso3(container) {
    const btn = $("#btnGuardarPaso3", container);
    const original = btn ? btn.innerHTML : "";

    const a = ($("#password_actual", container)?.value || "").trim();
    const n = ($("#password_nueva", container)?.value || "").trim();
    const c = ($("#password_confirmar", container)?.value || "").trim();

    if (!a || !n || !c) return swalWarn("Campos incompletos", "Completa contraseña actual, nueva y confirmación.");
    if (n !== c) return swalWarn("No coincide", "La nueva contraseña y la confirmación no coinciden.");
    if (n.length < 8) return swalWarn("Contraseña débil", "La contraseña debe tener mínimo 8 caracteres.");
    if (n === a) return swalWarn("Inválida", "La nueva contraseña debe ser distinta a la actual.");

    if (btn) { btn.disabled = true; btn.classList.add("saving"); btn.innerHTML = "Guardando..."; }

    try {
      const res = await fetch(API_CAMBIAR_CLAVE, {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-Requested-With": "XMLHttpRequest" },
        credentials: "include",
        body: JSON.stringify({
          password_actual: a,
          password_nueva: n,
          password_confirmar: c,
        }),
      });

      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data.message || data.mensaje || data.error || `HTTP ${res.status}`);

      $("#password_actual", container).value = "";
      $("#password_nueva", container).value = "";
      $("#password_confirmar", container).value = "";

      swalOk("Actualizado", "Tu contraseña fue cambiada correctamente.");
    } catch (e) {
      console.error("[EV][Paso3] Error:", e);
      swalErr(e.message || "No se pudo cambiar la contraseña.");
    } finally {
      if (btn) { btn.disabled = false; btn.classList.remove("saving"); btn.innerHTML = original; }
    }
  }

  function bindStepper(container) {
    container.querySelectorAll(".ev-stepper .ev-step").forEach((s) => {
      if (s.dataset.evBound === "1") return;
      s.dataset.evBound = "1";

      s.addEventListener("click", () => {
        const step = Number(s.dataset.step || "1");
        setStep(container, step);
      });
    });

    const btnAnterior = $("#btnAnterior", container);
    if (btnAnterior && btnAnterior.dataset.evBound !== "1") {
      btnAnterior.dataset.evBound = "1";
      btnAnterior.addEventListener("click", (e) => {
        e.preventDefault();
        const step = currentStep(container);
        if (step > 1) setStep(container, step - 1);
      });
    }

    const btnSiguiente = $("#btnSiguiente", container);
    if (btnSiguiente && btnSiguiente.dataset.evBound !== "1") {
      btnSiguiente.dataset.evBound = "1";
      btnSiguiente.addEventListener("click", (e) => {
        e.preventDefault();
        const step = currentStep(container);
        if (step < 3) setStep(container, step + 1);
      });
    }
  }

  function bindGuardar(container, base) {
    const b1 = $("#btnGuardarPaso1", container);
    if (b1 && b1.dataset.evBound !== "1") {
      b1.dataset.evBound = "1";
      b1.addEventListener("click", (e) => {
        e.preventDefault();
        guardarPaso1(container);
      });
    }

    const b2 = $("#btnGuardarPaso2", container);
    if (b2 && b2.dataset.evBound !== "1") {
      b2.dataset.evBound = "1";
      b2.addEventListener("click", (e) => {
        e.preventDefault();
        guardarPaso2(container, base);
      });
    }

    const b3 = $("#btnGuardarPaso3", container);
    if (b3 && b3.dataset.evBound !== "1") {
      b3.dataset.evBound = "1";
      b3.addEventListener("click", (e) => {
        e.preventDefault();
        guardarPaso3(container);
      });
    }
  }

  function initDatosPersonales(rootEl) {
    const container = rootEl || document.querySelector(".container-datos-personales");
    if (!container) return false;

    if (!container.dataset.dpStep) {
      container.dataset.dpStep = "1";
    }

    if (container.dataset.dpInitialized !== "1") {
      const base = getBaseState(container);

      setStep(container, currentStep(container));
      initUbigeo(container, base);
      initCondominios(container, base);
      initUrbanizaciones(container, base);

      const inputDir = $("#direccion", container);
      if (inputDir) inputDir.value = inputDir.value || base.direccion || "";

      ["#dpTipoCondominio", "#dpTipoUrbanizacion", "#comboCondominio", "#comboUrbanizacion"]
        .forEach((sel) => {
          const el = $(sel, container);
          if (!el || el.dataset.evBound === "1") return;
          el.dataset.evBound = "1";
          el.addEventListener("change", () => refreshResidenciaUI(container, base));
        });

      const cCondo = $("#comboCondominio", container);
      if (cCondo && cCondo.dataset.evDirBound !== "1") {
        cCondo.dataset.evDirBound = "1";
        cCondo.addEventListener("change", () => {
          const id = cCondo.value || "";
          const dir = cCondo._evDirMap ? (cCondo._evDirMap.get(String(id)) || "") : "";
          const d = $("#direccion", container);
          if (d) d.value = dir || "";
          refreshResidenciaUI(container, base);
        });
      }

      const cUrb = $("#comboUrbanizacion", container);
      if (cUrb && cUrb.dataset.evDirBound !== "1") {
        cUrb.dataset.evDirBound = "1";
        cUrb.addEventListener("change", () => {
          const id = cUrb.value || "";
          const dir = cUrb._evDirMap ? (cUrb._evDirMap.get(String(id)) || "") : "";
          const d = $("#direccion", container);
          if (d) d.value = dir || "";
          refreshResidenciaUI(container, base);
        });
      }

      refreshResidenciaUI(container, base);
      initFilePreview(container);
      initFotoPerfil(container);
      bindStepper(container);
      bindGuardar(container, base);

      container.dataset.dpInitialized = "1";
      return true;
    }

    bindStepper(container);
    initFotoPerfil(container);
    return true;
  }

  function initAll() {
    document.querySelectorAll(".container-datos-personales").forEach((node) => {
      initDatosPersonales(node);
    });
  }

  function boot() {
    initAll();
  }

  window.EV_DatosPersonales = {
    init: initDatosPersonales,
    initAll
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }

  document.addEventListener("ev:content-loaded", () => {
    initAll();
  });

  const observerTarget = document.getElementById("contenido-principal") || document.body;
  if (observerTarget && typeof MutationObserver !== "undefined") {
    const observer = new MutationObserver(() => {
      initAll();
    });
    observer.observe(observerTarget, { childList: true, subtree: true });
  }
})();
