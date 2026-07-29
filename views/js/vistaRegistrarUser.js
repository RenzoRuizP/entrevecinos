// views/js/vistaRegistrarUser.js
// Wizard de registro EV: navegación, validación por paso y mensajes inline.
document.addEventListener("DOMContentLoaded", () => {
  "use strict";

  const form = document.getElementById("formCrearUsuario");
  if (!form) return;

  let pasoActual = 1;
  const totalPasos = 4;
  const EV_CONFIRM_COLOR = "#EA7C12";

  const btnAnterior = document.getElementById("btnAnterior");
  const btnSiguiente = document.getElementById("btnSiguiente");
  const btnRegistrar = document.getElementById("btnRegistrar");
  const modalBody = document.querySelector("#crear_usuario .modal-body");
  const aceptaTerminos = document.getElementById("acepta_terminos");
  const aceptaPrivacidad = document.getElementById("acepta_privacidad");

  const $ = (id) => document.getElementById(id);

  function estaOculto(el) {
    if (!el) return true;
    const hiddenWrap = el.closest(".d-none");
    // Un paso puede estar oculto porque el usuario está en otra pestaña; aun así debe poder validarse.
    return Boolean(hiddenWrap && !hiddenWrap.classList.contains("step"));
  }

  function feedbackDe(el) {
    if (!el) return null;
    const parent = el.parentElement;
    return parent?.querySelector(".invalid-feedback") || null;
  }

  function marcarEstado(el, valido, mensaje = "") {
    if (!el) return valido;

    el.classList.toggle("is-invalid", !valido);
    el.classList.toggle("is-valid", valido && String(el.value || "").trim() !== "");
    el.setAttribute("aria-invalid", valido ? "false" : "true");

    const feedback = feedbackDe(el);
    if (feedback && mensaje) feedback.textContent = mensaje;

    const legalWrap = el.closest(".ev-register-legal__check");
    legalWrap?.classList.toggle("is-invalid", !valido);

    return valido;
  }

  function valor(el) {
    return String(el?.value || "").trim();
  }

  function validarDocumento(el) {
    const v = valor(el).toUpperCase();
    const ok = /^[A-Z0-9]{6,20}$/.test(v);
    return marcarEstado(
      el,
      ok,
      "Ingresa un documento válido de 6 a 20 caracteres usando solo letras y números."
    );
  }

  function validarTelefono(el) {
    const ok = /^9\d{8}$/.test(valor(el));
    return marcarEstado(
      el,
      ok,
      "Ingresa un celular peruano válido de 9 dígitos que comience con 9."
    );
  }

  function validarEmail(el) {
    const v = valor(el);
    const formatoComun = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    const ok = v.length <= 254 && formatoComun.test(v) && el.validity.valid;
    return marcarEstado(el, ok, "Ingresa un correo electrónico válido. Ejemplo: nombre@correo.com.");
  }

  function validarClave(el) {
    const v = String(el?.value || "");
    const ok = v.length >= 8 && v.length <= 72 && /[A-Z]/.test(v) && /\d/.test(v) && /[^A-Za-z0-9]/.test(v);
    return marcarEstado(
      el,
      ok,
      "La contraseña debe tener mínimo 8 caracteres, una mayúscula, un número y un símbolo."
    );
  }

  function validarConfirmacion(el) {
    const clave = String($("rClave")?.value || "");
    const confirmacion = String(el?.value || "");
    const ok = confirmacion !== "" && confirmacion === clave;
    return marcarEstado(el, ok, "Las contraseñas no coinciden. Verifica ambos campos.");
  }

  function validarArchivo(el) {
    if (!el || estaOculto(el)) return true;

    const file = el.files?.[0] || null;
    if (!file) {
      return marcarEstado(el, false, "Adjunta un comprobante de domicilio en formato JPG, PNG o PDF.");
    }

    const ext = String(file.name || "").split(".").pop().toLowerCase();
    const okExt = ["jpg", "jpeg", "png", "pdf"].includes(ext);
    const okSize = file.size <= 2 * 1024 * 1024;

    if (!okExt) return marcarEstado(el, false, "El formato debe ser JPG, PNG o PDF.");
    if (!okSize) return marcarEstado(el, false, "El archivo no debe superar los 2 MB.");

    return marcarEstado(el, true);
  }

  function validarCampo(el) {
    if (!el || el.disabled || estaOculto(el)) return true;

    switch (el.id) {
      case "nombre": {
        const ok = valor(el).length >= 3 && valor(el).length <= 120;
        return marcarEstado(el, ok, "Ingresa tu nombre completo.");
      }
      case "documento":
        return validarDocumento(el);
      case "telefono":
        return validarTelefono(el);
      case "rEmail":
        return validarEmail(el);
      case "rClave":
        return validarClave(el);
      case "confirmar_clave":
        return validarConfirmacion(el);
      case "comprobante_domicilio":
        return validarArchivo(el);
      default:
        break;
    }

    if (el.type === "checkbox" || el.type === "radio") {
      return marcarEstado(el, el.checked);
    }

    if (!el.hasAttribute("required")) {
      el.classList.remove("is-invalid");
      return true;
    }

    const ok = valor(el) !== "";
    let mensaje = "Completa este campo para continuar.";
    if (el.tagName === "SELECT") mensaje = "Selecciona una opción para continuar.";
    if (el.id === "nombre") mensaje = "Ingresa tu nombre completo.";

    return marcarEstado(el, ok, mensaje);
  }

  function validarResidencia() {
    const camposBase = [
      $("comboDepartamento"),
      $("comboProvincia"),
      $("comboDistrito"),
      $("comboConjuntoResidencial"),
    ];

    let ok = true;
    camposBase.forEach((el) => {
      if (!validarCampo(el)) ok = false;
    });
    const tipo = valor($("comboConjuntoResidencial"));

    if (tipo === "condominio") {
      const combo = $("comboCondominio");
      const valido = Boolean(combo && !combo.disabled && valor(combo));
      marcarEstado(combo, valido, "Selecciona un condominio.");
      ok = valido && ok;
    } else if (tipo === "urbanizacion") {
      const combo = $("comboUrbanizacion");
      const valido = Boolean(combo && !combo.disabled && valor(combo));
      marcarEstado(combo, valido, "Selecciona una urbanización.");
      ok = valido && ok;
    }

    if (tipo === "condominio" || tipo === "urbanizacion") {
      const direccion = $("direccion");
      const direccionOk = valor(direccion).length >= 5;
      if (!direccionOk) {
        const destino = tipo === "condominio" ? $("comboCondominio") : $("comboUrbanizacion");
        marcarEstado(destino, false, "Selecciona nuevamente una residencia con dirección registrada.");
      }
      ok = direccionOk && ok;
      ok = validarArchivo($("comprobante_domicilio")) && ok;
    }

    return ok;
  }

  function primerCampoInvalido(stepEl) {
    return Array.from(stepEl?.querySelectorAll(".is-invalid") || []).find((el) => !estaOculto(el) && !el.disabled) || null;
  }

  function enfocarPrimerError(stepEl) {
    const first = primerCampoInvalido(stepEl);
    if (!first) return;

    requestAnimationFrame(() => {
      first.scrollIntoView({ behavior: "smooth", block: "center" });
      try { first.focus({ preventScroll: true }); } catch (_) { first.focus(); }
    });
  }

  function mensajePaso(paso) {
    if (paso === 1) {
      if ($("telefono")?.classList.contains("is-invalid")) {
        return ["Revisa tu celular", "Ingresa un celular peruano de 9 dígitos que comience con 9."];
      }
      if ($("documento")?.classList.contains("is-invalid")) {
        return ["Revisa tu documento", "Usa entre 6 y 20 caracteres, solo letras y números."];
      }
      return ["Revisa tus datos", "Corrige los campos marcados para continuar."];
    }

    if (paso === 2) return ["Completa tu residencia", "Revisa los campos marcados y adjunta el comprobante de domicilio."];

    if (paso === 3) {
      if ($("confirmar_clave")?.classList.contains("is-invalid") && valor($("confirmar_clave"))) {
        return ["Las contraseñas no coinciden", "Verifica ambos campos antes de continuar."];
      }
      if ($("rEmail")?.classList.contains("is-invalid")) {
        return ["Correo no válido", "Ingresa un correo electrónico válido. Ejemplo: nombre@correo.com."];
      }
      return ["Revisa los datos de tu cuenta", "Corrige los campos marcados para continuar."];
    }

    return ["Aceptaciones obligatorias", "Debes aceptar los Términos y Condiciones y la Política de Privacidad para registrarte."];
  }

  function mostrarAlertaPaso(paso) {
    if (!window.Swal) return;
    const [title, text] = mensajePaso(paso);

    Swal.fire({
      icon: "warning",
      title,
      text,
      confirmButtonText: "Revisar",
      confirmButtonColor: EV_CONFIRM_COLOR,
      allowOutsideClick: false,
      allowEscapeKey: true,
    });
  }

  function validarPaso(paso, { mostrarAlerta = true, enfocar = true } = {}) {
    const stepEl = $(`formStep${paso}`);
    if (!stepEl) return true;

    let ok = true;

    if (paso === 2) {
      ok = validarResidencia();
    } else {
      const fields = stepEl.querySelectorAll("input, select, textarea");
      fields.forEach((el) => {
        if (!validarCampo(el)) ok = false;
      });
    }

    if (!ok) {
      if (enfocar) enfocarPrimerError(stepEl);
      if (mostrarAlerta) mostrarAlertaPaso(paso);
    }

    return ok;
  }

  function consentimientosAceptados() {
    return Boolean(aceptaTerminos?.checked && aceptaPrivacidad?.checked);
  }

  function sincronizarBotonRegistrar() {
    if (!btnRegistrar) return;
    btnRegistrar.disabled = pasoActual === totalPasos && !consentimientosAceptados();
  }

  function mostrarPaso(paso, { scrollTop = true } = {}) {
    pasoActual = Math.max(1, Math.min(totalPasos, Number(paso) || 1));

    for (let i = 1; i <= totalPasos; i++) {
      const formStep = $(`formStep${i}`);
      const progress = $(`step${i}`);
      if (!formStep || !progress) continue;

      const activo = i === pasoActual;
      formStep.classList.toggle("d-none", !activo);
      progress.classList.toggle("bg-success", activo);
      progress.classList.toggle("bg-secondary", !activo);
      progress.classList.toggle("is-complete", i < pasoActual);
      progress.setAttribute("aria-current", activo ? "step" : "false");
    }

    btnAnterior.disabled = pasoActual === 1;
    btnSiguiente.classList.toggle("d-none", pasoActual === totalPasos);
    btnRegistrar.classList.toggle("d-none", pasoActual !== totalPasos);
    sincronizarBotonRegistrar();

    if (scrollTop && modalBody) {
      modalBody.scrollTo({ top: 0, behavior: "smooth" });
    }
  }

  function validarTodo({ mostrarAlerta = true } = {}) {
    for (let paso = 1; paso <= totalPasos; paso++) {
      const ok = validarPaso(paso, { mostrarAlerta: false, enfocar: false });
      if (!ok) {
        mostrarPaso(paso);
        const stepEl = $(`formStep${paso}`);
        enfocarPrimerError(stepEl);
        if (mostrarAlerta) mostrarAlertaPaso(paso);
        return { ok: false, paso };
      }
    }

    return { ok: true, paso: totalPasos };
  }

  // Sanitización inmediata del celular peruano:
  // solo números, máximo 9 dígitos y el primer dígito debe ser 9.
  $("telefono")?.addEventListener("input", (e) => {
    const input = e.target;
    const digits = String(input.value || "").replace(/\D+/g, "").slice(0, 9);

    if (digits !== "" && !digits.startsWith("9")) {
      input.value = "";
      marcarEstado(
        input,
        false,
        "El número de celular debe comenzar con 9 y tener 9 dígitos."
      );
      return;
    }

    input.value = digits;
  });

  $("documento")?.addEventListener("input", (e) => {
    e.target.value = String(e.target.value || "")
      .toUpperCase()
      .replace(/[^A-Z0-9]/g, "")
      .slice(0, 20);
  });

  $("rEmail")?.addEventListener("input", (e) => {
    e.target.value = String(e.target.value || "").replace(/\s+/g, "");
  });

  // Validación progresiva sin alertas invasivas mientras el usuario escribe.
  form.addEventListener("input", (e) => {
    const el = e.target;
    if (!(el instanceof HTMLElement)) return;

    if (el.id === "rClave" && valor($("confirmar_clave"))) {
      validarConfirmacion($("confirmar_clave"));
    }

    if (el.classList.contains("is-invalid") || ["telefono", "documento", "rEmail", "confirmar_clave"].includes(el.id)) {
      validarCampo(el);
    }

    sincronizarBotonRegistrar();
  }, true);

  form.addEventListener("change", (e) => {
    const el = e.target;
    if (!(el instanceof HTMLElement)) return;
    validarCampo(el);
    sincronizarBotonRegistrar();
  }, true);

  form.addEventListener("focusout", (e) => {
    const el = e.target;
    if (!(el instanceof HTMLElement)) return;
    if (["documento", "telefono", "rEmail", "rClave", "confirmar_clave"].includes(el.id)) {
      validarCampo(el);
    }
  }, true);

  btnSiguiente?.addEventListener("click", () => {
    if (!validarPaso(pasoActual)) return;
    if (pasoActual < totalPasos) mostrarPaso(pasoActual + 1);
  });

  btnAnterior?.addEventListener("click", () => {
    if (pasoActual > 1) mostrarPaso(pasoActual - 1);
  });

  aceptaTerminos?.addEventListener("change", sincronizarBotonRegistrar);
  aceptaPrivacidad?.addEventListener("change", sincronizarBotonRegistrar);

  // API mínima para que registrarUser.js ejecute la misma validación antes del envío.
  window.EVRegistroWizard = {
    validarTodo,
    validarPaso,
    mostrarPaso,
    get pasoActual() { return pasoActual; },
  };

  mostrarPaso(1, { scrollTop: false });
});
