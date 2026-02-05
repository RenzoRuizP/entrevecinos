// views/js/registrarUser.js
document.addEventListener("DOMContentLoaded", () => {
  const formCrearUsuario = document.getElementById("formCrearUsuario");
  if (!formCrearUsuario) return console.error("No se encontró formCrearUsuario en DOM");

  const MAX_BYTES = 2 * 1024 * 1024; // 2MB
  const ALLOWED_EXT = ["jpg", "jpeg", "png", "pdf"];
  const EV_ORANGE = "#EA7C12";

  function getFileExt(name) {
    const parts = (name || "").split(".");
    return (parts.length > 1 ? parts.pop() : "").toLowerCase();
  }

  async function readJsonSafe(response) {
    const text = await response.text();
    if (!text) return null;
    try {
      return JSON.parse(text);
    } catch (e) {
      return { _raw: text };
    }
  }

  formCrearUsuario.addEventListener("submit", async (e) => {
    e.preventDefault();

    const dep = document.getElementById("comboDepartamento")?.value || "";
    const prov = document.getElementById("comboProvincia")?.value || "";
    const dist = document.getElementById("comboDistrito")?.value || "";

    const tipo = (document.getElementById("comboConjuntoResidencial")?.value || "").trim();
    const codigoCondominio = document.getElementById("comboCondominio")?.value || "";
    const codigoUrbanizacion = document.getElementById("comboUrbanizacion")?.value || "";
    const direccion = (document.getElementById("direccion")?.value || "").trim();

    const fileInput = document.getElementById("comprobante_domicilio");
    const file = fileInput?.files?.[0] || null;

    const clave = document.getElementById("rClave").value;
    const confirmar = document.getElementById("confirmar_clave").value;

    // Validaciones base
    if (clave !== confirmar) {
      Swal.fire({
        icon: "error",
        title: "Contraseña",
        text: "Las contraseñas no coinciden",
        confirmButtonColor: EV_ORANGE,
      });
      return;
    }

    // Ubigeo obligatorio
    if (!dep) {
      Swal.fire({ icon: "warning", title: "Residencia", text: "Selecciona un departamento.", confirmButtonColor: EV_ORANGE });
      return;
    }
    if (!prov) {
      Swal.fire({ icon: "warning", title: "Residencia", text: "Selecciona una provincia.", confirmButtonColor: EV_ORANGE });
      return;
    }
    if (!dist) {
      Swal.fire({ icon: "warning", title: "Residencia", text: "Selecciona un distrito.", confirmButtonColor: EV_ORANGE });
      return;
    }

    if (!tipo) {
      Swal.fire({
        icon: "warning",
        title: "Residencia",
        text: "Selecciona el tipo de conjunto residencial (Condominio o Urbanización).",
        confirmButtonColor: EV_ORANGE,
      });
      return;
    }

    if (tipo === "condominio" && (!codigoCondominio || Number(codigoCondominio) <= 0)) {
      Swal.fire({ icon: "warning", title: "Residencia", text: "Selecciona un condominio.", confirmButtonColor: EV_ORANGE });
      return;
    }

    if (tipo === "urbanizacion" && (!codigoUrbanizacion || Number(codigoUrbanizacion) <= 0)) {
      Swal.fire({ icon: "warning", title: "Residencia", text: "Selecciona una urbanización.", confirmButtonColor: EV_ORANGE });
      return;
    }

    if (!direccion || direccion.length < 5) {
      Swal.fire({
        icon: "warning",
        title: "Residencia",
        text: "No se pudo obtener la dirección. Selecciona nuevamente tu condominio/urbanización.",
        confirmButtonColor: EV_ORANGE,
      });
      return;
    }

    if (!file) {
      Swal.fire({
        icon: "warning",
        title: "Residencia",
        text: "Debes subir el comprobante de domicilio (recibo de servicio).",
        confirmButtonColor: EV_ORANGE,
      });
      return;
    }

    if (file.size > MAX_BYTES) {
      Swal.fire({ icon: "warning", title: "Archivo", text: "El comprobante supera el tamaño máximo permitido (2 MB).", confirmButtonColor: EV_ORANGE });
      return;
    }

    const ext = getFileExt(file.name);
    if (!ALLOWED_EXT.includes(ext)) {
      Swal.fire({ icon: "warning", title: "Archivo", text: "Formato no permitido. Sube JPG, PNG o PDF.", confirmButtonColor: EV_ORANGE });
      return;
    }

    // Preparar FormData
    const fd = new FormData();
    fd.append("nombre", document.getElementById("nombre").value.trim());
    fd.append("documento", document.getElementById("documento").value.trim());
    fd.append("telefono", document.getElementById("telefono").value.trim());
    fd.append("email", document.getElementById("rEmail").value.trim());
    fd.append("codigo_rol", "2");
    fd.append("clave", clave);

    fd.append("codigo_departamento", String(dep));
    fd.append("codigo_provincia", String(prov));
    fd.append("codigo_distrito", String(dist));

    fd.append("tipo_conjunto", tipo);
    fd.append("codigo_condominio", tipo === "condominio" ? String(Number(codigoCondominio)) : "");
    fd.append("codigo_urbanizacion", tipo === "urbanizacion" ? String(Number(codigoUrbanizacion)) : "");
    fd.append("direccion", direccion);

    fd.append("comprobante_domicilio", file);

    const rawBase = window.BASE_URL || "";
    const base = rawBase.replace(/\/+$/, "");
    const endpoint = base + "/usuarios/registrar";

    try {
      const response = await fetch(endpoint, { method: "POST", body: fd });
      const result = await readJsonSafe(response);

      // ✅ Manejo por códigos HTTP
      if (response.status === 409) {
        const title = result?.title || "No se pudo registrar";
        const msg = result?.message || "Ya existe una cuenta con esos datos. Verifica e inténtalo nuevamente.";

        Swal.fire({
          icon: "error",
          title,
          text: msg,
          confirmButtonText: "OK",
          confirmButtonColor: EV_ORANGE,
          allowOutsideClick: false,
          allowEscapeKey: true,
        });
        return;
      }

      if (!response.ok) {
        const msg = result?.message || `Ocurrió un error (HTTP ${response.status}). Intenta nuevamente.`;
        Swal.fire({
          icon: "error",
          title: "No se pudo registrar",
          text: msg,
          confirmButtonColor: EV_ORANGE,
        });
        return;
      }

      // OK 200
      if (result && result.success) {
        Swal.fire({
          title: "¡Registro enviado!",
          text: result.message || "Tu registro fue enviado correctamente.",
          icon: "success",
          iconColor: "#16A34A",
          confirmButtonText: "OK",
          confirmButtonColor: "#16A34A",
          background: "#FFFFFF",
          allowOutsideClick: false,
          allowEscapeKey: false,
        }).then(() => (window.location.href = base + "/"));
      } else {
        Swal.fire({
          icon: "error",
          title: "No se pudo registrar",
          text: result?.message || "No se pudo registrar. Intenta nuevamente.",
          confirmButtonColor: EV_ORANGE,
        });
      }
    } catch (err) {
      console.error("Fetch error:", err);
      Swal.fire({
        icon: "error",
        title: "Conexión",
        text: "No se pudo conectar con el servidor. Intenta nuevamente.",
        confirmButtonColor: EV_ORANGE,
      });
    }
  });
});
