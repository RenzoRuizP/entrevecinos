// views/js/aceptacionLegal.js
(() => {
  "use strict";

  document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("formAceptacionLegal");
    const chkTerminos = document.getElementById("aceptaTerminosLegal");
    const chkPrivacidad = document.getElementById("aceptaPrivacidadLegal");
    const btn = document.getElementById("btnAceptarLegal");
    const wrapTerminos = document.getElementById("wrapAceptaTerminos");
    const wrapPrivacidad = document.getElementById("wrapAceptaPrivacidad");

    if (!form || !chkTerminos || !chkPrivacidad || !btn) return;

    const base = String(window.EV?.baseUrl ?? window.BASE_URL ?? "").replace(/\/+$/, "");
    const endpoint = `${base}/api/documentos-legales/aceptar-vigentes`;
    const EV_ORANGE = "#EA7C12";
    const EV_GREEN = "#0F592F";

    const syncButton = () => {
      const ok = chkTerminos.checked && chkPrivacidad.checked;
      btn.disabled = !ok;
      if (chkTerminos.checked) wrapTerminos?.classList.remove("is-invalid");
      if (chkPrivacidad.checked) wrapPrivacidad?.classList.remove("is-invalid");
    };

    const setLoading = (loading) => {
      btn.disabled = loading || !(chkTerminos.checked && chkPrivacidad.checked);
      btn.innerHTML = loading
        ? '<span class="ev-al-spinner" aria-hidden="true"></span><span>Registrando...</span>'
        : '<i class="bi bi-check2-circle"></i><span>Aceptar y continuar</span>';
    };

    chkTerminos.addEventListener("change", syncButton);
    chkPrivacidad.addEventListener("change", syncButton);
    syncButton();

    form.addEventListener("submit", async (event) => {
      event.preventDefault();

      if (!chkTerminos.checked || !chkPrivacidad.checked) {
        wrapTerminos?.classList.toggle("is-invalid", !chkTerminos.checked);
        wrapPrivacidad?.classList.toggle("is-invalid", !chkPrivacidad.checked);

        await Swal.fire({
          icon: "warning",
          title: "Aceptación obligatoria",
          text: "Debes aceptar ambos documentos para continuar en Entre Vecinos.",
          confirmButtonText: "Entendido",
          confirmButtonColor: EV_ORANGE,
          allowOutsideClick: false,
        });
        return;
      }

      setLoading(true);

      try {
        const response = await fetch(endpoint, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          credentials: "same-origin",
          body: JSON.stringify({
            acepta_terminos: true,
            acepta_privacidad: true,
          }),
        });

        const payload = await response.json().catch(() => ({}));

        if (!response.ok || !payload.ok) {
          throw new Error(payload.mensaje || "No se pudieron registrar las aceptaciones.");
        }

        await Swal.fire({
          icon: "success",
          title: "Documentos aceptados",
          text: "Tu aceptación fue registrada correctamente.",
          confirmButtonText: "Continuar",
          confirmButtonColor: EV_GREEN,
          allowOutsideClick: false,
          allowEscapeKey: false,
        });

        window.location.href = payload.redirect || `${base}/MenuPrincipal`;
      } catch (error) {
        console.error("[EV][aceptacionLegal]", error);
        setLoading(false);

        await Swal.fire({
          icon: "error",
          title: "No se pudo completar",
          text: error?.message || "Ocurrió un error al registrar las aceptaciones.",
          confirmButtonText: "Reintentar",
          confirmButtonColor: EV_ORANGE,
        });
      }
    });
  });
})();
