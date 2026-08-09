// views/js/registrarUser.js
document.addEventListener("DOMContentLoaded", () => {
  "use strict";

  const formCrearUsuario = document.getElementById("formCrearUsuario");
  if (!formCrearUsuario) return console.error("No se encontró formCrearUsuario en DOM");

  const MAX_BYTES = 2 * 1024 * 1024; // 2 MB
  const ALLOWED_EXT = ["jpg", "jpeg", "png", "pdf"];
  const EV_ORANGE = "#EA7C12";
  const btnRegistrar = document.getElementById("btnRegistrar");

  function getFileExt(name) {
    const parts = String(name || "").split(".");
    return (parts.length > 1 ? parts.pop() : "").toLowerCase();
  }

  function normalizeDocumento(value) {
    return String(value || "")
      .toUpperCase()
      .replace(/[^A-Z0-9]/g, "")
      .slice(0, 20);
  }

  function normalizeTelefono(value) {
    return String(value || "").replace(/\D+/g, "").slice(0, 9);
  }

  function normalizeEmail(value) {
    return String(value || "").trim().toLowerCase();
  }

  function claveValida(value) {
    const v = String(value || "");
    return v.length >= 8 && v.length <= 72 && /[A-Z]/.test(v) && /\d/.test(v) && /[^A-Za-z0-9]/.test(v);
  }

  async function readJsonSafe(response) {
    const text = await response.text();
    if (!text) return null;
    try {
      return JSON.parse(text);
    } catch (_) {
      return { _raw: text };
    }
  }

  function alertError(title, text) {
    return Swal.fire({
      icon: "error",
      title,
      text,
      confirmButtonText: "Revisar",
      confirmButtonColor: EV_ORANGE,
      allowOutsideClick: false,
    });
  }

  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function showRegistrationError(title, text) {
    return Swal.fire({
      title: escapeHtml(title || "No se pudo registrar"),
      html: `
        <div class="ev-swal-registro__error" aria-hidden="true"><i class="bi bi-x-lg"></i></div>
        <p class="ev-swal-registro__lead">Revisa la información ingresada</p>
        <p class="ev-swal-registro__text">${escapeHtml(text || "No fue posible completar el registro. Intenta nuevamente.")}</p>
      `,
      confirmButtonText: "Aceptar",
      background: "#FFFFFF",
      customClass: {
        popup: "ev-swal-registro ev-swal-registro--error",
        confirmButton: "ev-swal-registro__confirm",
      },
      buttonsStyling: false,
      showClass: { popup: "swal2-show ev-swal-registro--show" },
      hideClass: { popup: "swal2-hide" },
      allowOutsideClick: false,
      allowEscapeKey: false,
    });
  }

  function setSending(sending) {
    if (!btnRegistrar) return;

    if (sending) {
      if (!btnRegistrar.dataset.evOriginalHtml) {
        btnRegistrar.dataset.evOriginalHtml = btnRegistrar.innerHTML;
      }
      btnRegistrar.disabled = true;
      btnRegistrar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Enviando...';
      return;
    }

    btnRegistrar.innerHTML = btnRegistrar.dataset.evOriginalHtml || '<i class="bi bi-check-circle me-1" aria-hidden="true"></i>Registrar';
    const aceptaTerminos = Boolean(document.getElementById("acepta_terminos")?.checked);
    const aceptaPrivacidad = Boolean(document.getElementById("acepta_privacidad")?.checked);
    btnRegistrar.disabled = !(aceptaTerminos && aceptaPrivacidad);
  }

  formCrearUsuario.addEventListener("submit", async (e) => {
    e.preventDefault();

    const validacion = window.EVRegistroWizard?.validarTodo?.({ mostrarAlerta: true });
    if (validacion && !validacion.ok) return;

    const nombre = String(document.getElementById("nombre")?.value || "").trim();
    const documento = normalizeDocumento(document.getElementById("documento")?.value);
    const telefono = normalizeTelefono(document.getElementById("telefono")?.value);
    const email = normalizeEmail(document.getElementById("rEmail")?.value);
    const clave = String(document.getElementById("rClave")?.value || "");
    const confirmar = String(document.getElementById("confirmar_clave")?.value || "");

    const dep = document.getElementById("comboDepartamento")?.value || "";
    const prov = document.getElementById("comboProvincia")?.value || "";
    const dist = document.getElementById("comboDistrito")?.value || "";

    const tipo = String(document.getElementById("comboConjuntoResidencial")?.value || "").trim();
    const codigoCondominio = document.getElementById("comboCondominio")?.value || "";
    const codigoUrbanizacion = document.getElementById("comboUrbanizacion")?.value || "";
    const direccion = String(document.getElementById("direccion")?.value || "").trim();

    const fileInput = document.getElementById("comprobante_domicilio");
    const file = fileInput?.files?.[0] || null;

    const aceptaTerminos = Boolean(document.getElementById("acepta_terminos")?.checked);
    const aceptaPrivacidad = Boolean(document.getElementById("acepta_privacidad")?.checked);

    // Defensa adicional antes de enviar. El backend vuelve a validar todas estas reglas.
    if (!/^[A-Z0-9]{6,20}$/.test(documento)) {
      window.EVRegistroWizard?.mostrarPaso?.(1);
      await alertError("Documento no válido", "Ingresa entre 6 y 20 caracteres usando solo letras y números.");
      return;
    }

    if (!/^9\d{8}$/.test(telefono)) {
      window.EVRegistroWizard?.mostrarPaso?.(1);
      await alertError("Celular no válido", "Ingresa un celular peruano de 9 dígitos que comience con 9.");
      return;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) {
      window.EVRegistroWizard?.mostrarPaso?.(3);
      await alertError("Correo no válido", "Ingresa un correo electrónico válido. Ejemplo: nombre@correo.com.");
      return;
    }

    if (!claveValida(clave)) {
      window.EVRegistroWizard?.mostrarPaso?.(3);
      await alertError("Contraseña no válida", "Debe tener mínimo 8 caracteres, una mayúscula, un número y un símbolo.");
      return;
    }

    if (clave !== confirmar) {
      window.EVRegistroWizard?.mostrarPaso?.(3);
      document.getElementById("confirmar_clave")?.classList.add("is-invalid");
      await alertError("Las contraseñas no coinciden", "Verifica ambos campos antes de continuar.");
      return;
    }

    if (!aceptaTerminos || !aceptaPrivacidad) {
      document.getElementById("wrapAceptaTerminosRegistro")?.classList.toggle("is-invalid", !aceptaTerminos);
      document.getElementById("wrapAceptaPrivacidadRegistro")?.classList.toggle("is-invalid", !aceptaPrivacidad);
      window.EVRegistroWizard?.mostrarPaso?.(4);

      Swal.fire({
        icon: "warning",
        title: "Aceptaciones obligatorias",
        text: "Debes aceptar los Términos y Condiciones y la Política de Privacidad para registrarte.",
        confirmButtonColor: EV_ORANGE,
        allowOutsideClick: false,
      });
      return;
    }

    if (!dep || !prov || !dist || !tipo || !direccion) {
      window.EVRegistroWizard?.mostrarPaso?.(2);
      Swal.fire({
        icon: "warning",
        title: "Completa tu residencia",
        text: "Revisa los datos de residencia antes de continuar.",
        confirmButtonColor: EV_ORANGE,
      });
      return;
    }

    if (tipo === "condominio" && (!codigoCondominio || Number(codigoCondominio) <= 0)) {
      window.EVRegistroWizard?.mostrarPaso?.(2);
      Swal.fire({ icon: "warning", title: "Residencia", text: "Selecciona un condominio.", confirmButtonColor: EV_ORANGE });
      return;
    }

    if (tipo === "urbanizacion" && (!codigoUrbanizacion || Number(codigoUrbanizacion) <= 0)) {
      window.EVRegistroWizard?.mostrarPaso?.(2);
      Swal.fire({ icon: "warning", title: "Residencia", text: "Selecciona una urbanización.", confirmButtonColor: EV_ORANGE });
      return;
    }

    if (!file) {
      window.EVRegistroWizard?.mostrarPaso?.(2);
      Swal.fire({
        icon: "warning",
        title: "Comprobante requerido",
        text: "Adjunta el comprobante de domicilio para continuar.",
        confirmButtonColor: EV_ORANGE,
      });
      return;
    }

    if (file.size > MAX_BYTES) {
      window.EVRegistroWizard?.mostrarPaso?.(2);
      Swal.fire({ icon: "warning", title: "Archivo muy grande", text: "El comprobante no debe superar los 2 MB.", confirmButtonColor: EV_ORANGE });
      return;
    }

    const ext = getFileExt(file.name);
    if (!ALLOWED_EXT.includes(ext)) {
      window.EVRegistroWizard?.mostrarPaso?.(2);
      Swal.fire({ icon: "warning", title: "Formato no permitido", text: "Sube un archivo JPG, PNG o PDF.", confirmButtonColor: EV_ORANGE });
      return;
    }

    const fd = new FormData();
    fd.append("nombre", nombre);
    fd.append("documento", documento);
    fd.append("telefono", telefono);
    fd.append("email", email);
    fd.append("codigo_rol", "2");
    fd.append("clave", clave);
    fd.append("confirmar_clave", confirmar);
    fd.append("acepta_terminos", aceptaTerminos ? "1" : "0");
    fd.append("acepta_privacidad", aceptaPrivacidad ? "1" : "0");

    fd.append("codigo_departamento", String(dep));
    fd.append("codigo_provincia", String(prov));
    fd.append("codigo_distrito", String(dist));

    fd.append("tipo_conjunto", tipo);
    fd.append("codigo_condominio", tipo === "condominio" ? String(Number(codigoCondominio)) : "");
    fd.append("codigo_urbanizacion", tipo === "urbanizacion" ? String(Number(codigoUrbanizacion)) : "");
    fd.append("direccion", direccion);
    fd.append("comprobante_domicilio", file);

    const rawBase = window.EV?.baseUrl ?? window.BASE_URL ?? "";
    const base = rawBase.replace(/\/+$/, "");
    const endpoint = base + "/usuarios/registrar";

    setSending(true);

    try {
      const response = await fetch(endpoint, { method: "POST", body: fd });
      const result = await readJsonSafe(response);

      if (response.status === 409) {
        await showRegistrationError(
          result?.title || "No se pudo registrar",
          result?.message || "Ya existe una cuenta con esos datos. Inicia sesión o utiliza otro correo."
        );
        return;
      }

      if (!response.ok) {
        await showRegistrationError(
          "No se pudo registrar",
          result?.message || `Ocurrió un error (HTTP ${response.status}). Intenta nuevamente.`
        );
        return;
      }

      if (result?.success) {
        Swal.fire({
          title: "Registro enviado",
          html: `
            <div class="ev-swal-registro__success" aria-hidden="true"><i class="bi bi-check-lg"></i></div>
            <p class="ev-swal-registro__lead">Tu solicitud fue registrada correctamente.</p>
            <p class="ev-swal-registro__text">Soporte revisará la información y te avisaremos cuando tu cuenta esté lista.</p>
          `,
          confirmButtonText: "Aceptar",
          background: "#FFFFFF",
          customClass: {
            popup: "ev-swal-registro",
            confirmButton: "ev-swal-registro__confirm",
          },
          buttonsStyling: false,
          showClass: { popup: "swal2-show ev-swal-registro--show" },
          hideClass: { popup: "swal2-hide" },
          allowOutsideClick: false,
          allowEscapeKey: false,
        }).then(() => (window.location.href = base + "/"));
      } else {
        await showRegistrationError(
          "No se pudo registrar",
          result?.message || "No fue posible completar el registro. Intenta nuevamente."
        );
      }
    } catch (err) {
      console.error("Fetch error:", err);
      await showRegistrationError(
        "No se pudo conectar",
        "Verifica tu conexión e intenta nuevamente."
      );
    } finally {
      setSending(false);
    }
  });
});
