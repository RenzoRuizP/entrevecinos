// views/js/cuentaObservada.js
(function () {
  "use strict";

  const body = document.body;

  const baseUrl = (
    body?.dataset?.baseUrl ||
    window.BASE_URL ||
    ""
  ).replace(/\/+$/, "");

  const modoVista = String(body?.dataset?.modoVista || "").trim();
  const tipoObservacion = String(body?.dataset?.tipoObservacion || "").trim();
  const esCambioResidencia = String(body?.dataset?.esCambioResidencia || "0") === "1";
  const estadoSolicitudResidencia = String(body?.dataset?.estadoSolicitudResidencia || "").trim();

  const MAX_FILE_BYTES = 5 * 1024 * 1024;
  const ALLOWED_EXTENSIONS = ["pdf", "jpg", "jpeg", "png"];

  function byId(id) {
    return document.getElementById(id);
  }

  function endpoint(path) {
    const cleanPath = String(path || "").replace(/^\/+/, "");
    return `${baseUrl}/${cleanPath}`;
  }

  function tieneSweetAlert() {
    return typeof window.Swal !== "undefined" && typeof window.Swal.fire === "function";
  }

  function mostrarAlerta(options) {
    const finalOptions = {
      confirmButtonText: "Aceptar",
      confirmButtonColor: "#EA7C12",
      ...options,

      // EV: evita cierre accidental del modal.
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: true,
      backdrop: true,

      // EV: evita desplazamiento visual/layout shift al abrir SweetAlert2.
      scrollbarPadding: false,
      heightAuto: false
    };

    if (tieneSweetAlert()) {
      return Swal.fire(finalOptions);
    }

    const title = finalOptions?.title || "Mensaje";
    const text = finalOptions?.text || finalOptions?.html || "";
    window.alert(`${title}\n\n${text}`);
    return Promise.resolve({ isConfirmed: true });
  }

  function extensionArchivo(nombre) {
    const parts = String(nombre || "").split(".");
    if (parts.length < 2) return "";
    return parts.pop().toLowerCase();
  }

  function formatoBytes(bytes) {
    const n = Number(bytes || 0);

    if (n < 1024) return `${n} B`;
    if (n < 1024 * 1024) return `${(n / 1024).toFixed(1)} KB`;

    return `${(n / (1024 * 1024)).toFixed(1)} MB`;
  }

  function nombreArchivoSeguro(nombre) {
    const raw = String(nombre || "").trim();
    if (!raw) return "Archivo seleccionado";

    if (raw.length <= 58) return raw;

    const ext = extensionArchivo(raw);
    const base = ext ? raw.slice(0, -(ext.length + 1)) : raw;

    return `${base.slice(0, 34)}...${ext ? "." + ext : ""}`;
  }

  function validarArchivo(file) {
    if (!file) {
      return {
        ok: false,
        mensaje: "Debes seleccionar un recibo."
      };
    }

    const ext = extensionArchivo(file.name);

    if (!ALLOWED_EXTENSIONS.includes(ext)) {
      return {
        ok: false,
        mensaje: "Formato no permitido. Sube un archivo PDF, JPG, JPEG o PNG."
      };
    }

    if (Number(file.size || 0) <= 0) {
      return {
        ok: false,
        mensaje: "El archivo seleccionado está vacío."
      };
    }

    if (Number(file.size || 0) > MAX_FILE_BYTES) {
      return {
        ok: false,
        mensaje: `El archivo pesa ${formatoBytes(file.size)}. El máximo permitido es 5 MB.`
      };
    }

    return {
      ok: true,
      mensaje: "Archivo válido."
    };
  }

  function actualizarNombreArchivo(file) {
    const label = byId("evSelectedFileName");

    if (!label) return;

    if (!file) {
      label.classList.remove("has-file");
      label.textContent = "Ningún archivo seleccionado";
      return;
    }

    const ext = extensionArchivo(file.name).toUpperCase();
    const size = formatoBytes(file.size);

    label.classList.add("has-file");
    label.innerHTML = `
      <i class="bi bi-file-earmark-check me-1"></i>
      ${escapeHtml(nombreArchivoSeguro(file.name))}
      <span class="ev-file-meta"> · ${escapeHtml(ext)} · ${escapeHtml(size)}</span>
    `;
  }

  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function setSubmitting(form, submitting) {
    const submit = form?.querySelector('button[type="submit"]');
    const input = byId("evComprobante");
    const uploadZone = document.querySelector(".ev-upload-zone");

    if (submit) {
      submit.disabled = submitting;

      if (submitting) {
        submit.dataset.originalHtml = submit.innerHTML;
        submit.innerHTML = `
          <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
          Enviando...
        `;
      } else if (submit.dataset.originalHtml) {
        submit.innerHTML = submit.dataset.originalHtml;
      }
    }

    if (input) {
      input.disabled = submitting;
    }

    if (uploadZone) {
      uploadZone.classList.toggle("is-disabled", submitting);
    }
  }

  function mostrarLoadingEnvio() {
    if (!tieneSweetAlert()) return;

    Swal.fire({
      title: "Enviando recibo",
      text: "Estamos procesando tu archivo. No cierres esta ventana.",
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,
      showConfirmButton: false,
      backdrop: true,

      // EV: evita desplazamiento visual/layout shift al abrir SweetAlert2.
      scrollbarPadding: false,
      heightAuto: false,

      didOpen: () => {
        Swal.showLoading();
      }
    });
  }

  function cerrarLoading() {
    if (tieneSweetAlert()) {
      Swal.close();
    }
  }

  function mostrarGracias(mensajeBackend, tipoSubsanacion) {
    const form = byId("evFormReenviar");
    const box = byId("evGraciasBox");

    if (form) {
      form.classList.add("d-none");
    }

    if (box) {
      box.classList.remove("d-none");
    }

    const esResidencia =
      tipoSubsanacion === "cambio_residencia" ||
      esCambioResidencia ||
      tipoObservacion === "cambio_residencia";

    const titulo = esResidencia
      ? "Recibo reenviado"
      : "Recibo reenviado";

    const texto = mensajeBackend || (
      esResidencia
        ? "Tu cambio de residencia volvió a revisión."
        : "Tu cuenta volvió a revisión."
    );

    return mostrarAlerta({
      icon: "success",
      title: titulo,
      text: texto
    }).then(() => {
      window.location.href = endpoint("cuenta-observada");
    });
  }

  async function leerJsonSeguro(resp) {
    const text = await resp.text();

    if (!text) {
      return {};
    }

    try {
      return JSON.parse(text);
    } catch (e) {
      return {
        ok: false,
        mensaje: "La respuesta del servidor no tiene un formato válido."
      };
    }
  }

  async function enviarComprobante(e) {
    e.preventDefault();

    const form = e.currentTarget;
    const input = byId("evComprobante");
    const file = input?.files?.[0] || null;

    const validacion = validarArchivo(file);

    if (!validacion.ok) {
      await mostrarAlerta({
        icon: "warning",
        title: "Archivo inválido",
        text: validacion.mensaje
      });

      if (input) input.focus();
      actualizarNombreArchivo(null);
      return;
    }

    const formData = new FormData(form);

    try {
      setSubmitting(form, true);
      mostrarLoadingEnvio();

      const resp = await fetch(endpoint("api/cuenta-observada/reenviar"), {
        method: "POST",
        headers: {
          "X-Partial": "1"
        },
        credentials: "include",
        body: formData
      });

      const json = await leerJsonSeguro(resp);

      if (!resp.ok || json.ok !== true) {
        const msg = json?.mensaje || "No se pudo reenviar el recibo.";
        throw new Error(msg);
      }

      cerrarLoading();

      const tipoSubsanacion = String(json?.data?.tipo_subsanacion || "");
      await mostrarGracias(json?.mensaje || "", tipoSubsanacion);

    } catch (err) {
      cerrarLoading();

      await mostrarAlerta({
        icon: "error",
        title: "No se pudo enviar",
        text: err?.message || "Ocurrió un error al reenviar el recibo."
      });
    } finally {
      setSubmitting(form, false);
    }
  }

  function mostrarInfoSoporte() {
    const esObservado = modoVista === "observado";

    let title = "Información de revisión";
    let html = `
      <div style="text-align:left;line-height:1.55">
        <p style="margin-bottom:10px">
          Durante esta revisión, Soporte EV verificará que tus datos y el recibo de domicilio correspondan a la comunidad registrada.
        </p>
        <p style="margin-bottom:0">
          Cuando termine la evaluación, recibirás una notificación con el resultado o con las correcciones necesarias.
        </p>
      </div>
    `;

    if (esObservado && esCambioResidencia) {
      title = "Cambio de residencia observado";
      html = `
        <div style="text-align:left">
          <p style="margin-bottom:10px">
            Soporte EV identificó un dato que debe corregirse en tu solicitud de cambio de residencia.
          </p>
          <p style="margin-bottom:10px">
            Revisa la observación, adjunta el recibo corregido y vuelve a enviarlo.
          </p>
          <p style="margin-bottom:0">
            Después de enviarlo, la solicitud volverá automáticamente a revisión.
          </p>
        </div>
      `;
    } else if (esObservado) {
      title = "Cuenta observada";
      html = `
        <div style="text-align:left">
          <p style="margin-bottom:10px">
            Soporte EV identificó un dato que debe corregirse en tu registro.
          </p>
          <p style="margin-bottom:10px">
            Revisa la observación, adjunta el recibo corregido y vuelve a enviarlo.
          </p>
          <p style="margin-bottom:0">
            Después de enviarlo, tu cuenta volverá automáticamente a revisión.
          </p>
        </div>
      `;
    } else if (esCambioResidencia || estadoSolicitudResidencia === "pendiente") {
      title = "Cambio de residencia en revisión";
      html = `
        <div style="text-align:left">
          <p style="margin-bottom:10px">
            Soporte EV está validando tu solicitud de cambio de residencia y el recibo enviado.
          </p>
          <p style="margin-bottom:0">
            Al finalizar la revisión, recibirás una notificación con el resultado o con cualquier corrección necesaria.
          </p>
        </div>
      `;
    }

    mostrarAlerta({
      icon: "info",
      title,
      html
    });
  }

  function mostrarEntendidoRevision() {
    const esResidenciaPendiente =
      esCambioResidencia ||
      tipoObservacion === "cambio_residencia_pendiente" ||
      estadoSolicitudResidencia === "pendiente";

    const title = esResidenciaPendiente
      ? "Cambio de residencia en revisión"
      : "Cuenta en revisión";

    const text = esResidenciaPendiente
      ? "Te avisaremos cuando soporte termine de revisar tu cambio de residencia."
      : "Te avisaremos cuando soporte termine de revisar tu cuenta.";

    mostrarAlerta({
      icon: "success",
      title,
      text
    });
  }

  function wireForm() {
    const form = byId("evFormReenviar");
    if (!form || form.dataset.evWired === "1") return;

    form.dataset.evWired = "1";
    form.addEventListener("submit", enviarComprobante);
  }

  function wireInfoButtons() {
    const btnInfo = byId("evBtnInfoSupport");
    if (btnInfo && btnInfo.dataset.evWired !== "1") {
      btnInfo.dataset.evWired = "1";
      btnInfo.addEventListener("click", mostrarInfoSoporte);
    }

    const linksInfo = document.querySelectorAll(".js-ev-soporte-link");
    linksInfo.forEach((link) => {
      if (link.dataset.evWired === "1") return;
      link.dataset.evWired = "1";
      link.addEventListener("click", mostrarInfoSoporte);
    });

    const btnEntendido = byId("evBtnEntendido");
    if (btnEntendido && btnEntendido.dataset.evWired !== "1") {
      btnEntendido.dataset.evWired = "1";
      btnEntendido.addEventListener("click", mostrarEntendidoRevision);
    }
  }

  function wireFileInput() {
    const input = byId("evComprobante");
    const uploadZone = document.querySelector(".ev-upload-zone");

    if (!input || input.dataset.evWired === "1") return;

    input.dataset.evWired = "1";

    input.addEventListener("change", async () => {
      const file = input.files?.[0] || null;

      if (!file) {
        actualizarNombreArchivo(null);
        return;
      }

      const validacion = validarArchivo(file);

      if (!validacion.ok) {
        input.value = "";
        actualizarNombreArchivo(null);

        await mostrarAlerta({
          icon: "warning",
          title: "Archivo inválido",
          text: validacion.mensaje
        });

        return;
      }

      actualizarNombreArchivo(file);

      if (uploadZone) {
        uploadZone.classList.add("has-file");
      }
    });

    if (uploadZone && uploadZone.dataset.evWired !== "1") {
      uploadZone.dataset.evWired = "1";

      uploadZone.addEventListener("dragover", (e) => {
        e.preventDefault();
        uploadZone.classList.add("is-dragover");
      });

      uploadZone.addEventListener("dragleave", () => {
        uploadZone.classList.remove("is-dragover");
      });

      uploadZone.addEventListener("drop", async (e) => {
        e.preventDefault();
        uploadZone.classList.remove("is-dragover");

        const file = e.dataTransfer?.files?.[0] || null;

        if (!file) return;

        const validacion = validarArchivo(file);

        if (!validacion.ok) {
          input.value = "";
          actualizarNombreArchivo(null);

          await mostrarAlerta({
            icon: "warning",
            title: "Archivo inválido",
            text: validacion.mensaje
          });

          return;
        }

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        input.files = dataTransfer.files;

        actualizarNombreArchivo(file);
        uploadZone.classList.add("has-file");
      });
    }
  }

  function init() {
    wireForm();
    wireInfoButtons();
    wireFileInput();
    actualizarNombreArchivo(null);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();