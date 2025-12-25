// views/js/registrarUser.js
document.addEventListener("DOMContentLoaded", () => {
  const formCrearUsuario = document.getElementById("formCrearUsuario");
  if (!formCrearUsuario) return console.error("No se encontró formCrearUsuario en DOM");

  const MAX_BYTES = 2 * 1024 * 1024; // 2MB
  const ALLOWED_EXT = ["jpg", "jpeg", "png", "pdf"];

  function getFileExt(name) {
    const parts = (name || "").split(".");
    return (parts.length > 1 ? parts.pop() : "").toLowerCase();
  }

  formCrearUsuario.addEventListener("submit", async (e) => {
    e.preventDefault();

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
      Swal.fire("Error", "Las contraseñas no coinciden", "error");
      return;
    }

    if (!tipo) {
      Swal.fire("Residencia", "Selecciona el tipo de conjunto residencial (Condominio o Urbanización).", "warning");
      return;
    }

    if (tipo === "condominio" && (!codigoCondominio || Number(codigoCondominio) <= 0)) {
      Swal.fire("Residencia", "Selecciona un condominio.", "warning");
      return;
    }

    if (tipo === "urbanizacion" && (!codigoUrbanizacion || Number(codigoUrbanizacion) <= 0)) {
      Swal.fire("Residencia", "Selecciona una urbanización.", "warning");
      return;
    }

    if (!direccion || direccion.length < 5) {
      Swal.fire("Residencia", "Ingresa una dirección válida.", "warning");
      return;
    }

    // Archivo obligatorio
    if (!file) {
      Swal.fire("Residencia", "Debes subir el comprobante de domicilio (recibo de servicio).", "warning");
      return;
    }

    // Validación cliente (rápida)
    if (file.size > MAX_BYTES) {
      Swal.fire("Archivo", "El comprobante supera el tamaño máximo permitido (2 MB).", "warning");
      return;
    }

    const ext = getFileExt(file.name);
    if (!ALLOWED_EXT.includes(ext)) {
      Swal.fire("Archivo", "Formato no permitido. Sube JPG, PNG o PDF.", "warning");
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

    fd.append("tipo_conjunto", tipo);
    fd.append("codigo_condominio", tipo === "condominio" ? String(Number(codigoCondominio)) : "");
    fd.append("codigo_urbanizacion", tipo === "urbanizacion" ? String(Number(codigoUrbanizacion)) : "");
    fd.append("direccion", direccion);

    fd.append("comprobante_domicilio", file);

    const rawBase = window.BASE_URL || '';
    const base = rawBase.replace(/\/+$/,'');
    const endpoint = base + '/usuarios/registrar';

    try {
      const response = await fetch(endpoint, {
        method: "POST",
        body: fd
      });

      if (!response.ok) {
        const text = await response.text();
        console.error("HTTP error", response.status, text);
        Swal.fire("Error", `Error servidor: ${response.status}`, "error");
        return;
      }

      const text = await response.text();
      let result;
      try {
        result = JSON.parse(text);
      } catch (err) {
        console.error("Respuesta no JSON:", text);
        Swal.fire("Error", "Respuesta inesperada del servidor", "error");
        return;
      }

      if (result && result.success) {
        Swal.fire({
        title: "Éxito",
        text: result.message || "Usuario registrado con éxito",
        icon: "success",
        iconColor: "#16A34A",
        confirmButtonText: "OK",
        confirmButtonColor: "#16A34A",
        background: "#FFFFFF",
        allowOutsideClick: false,
        allowEscapeKey: false
      }).then(() => window.location.href = base + '/');

      } else {
        Swal.fire("Error", result.message || "No se pudo registrar", "error");
      }

    } catch (err) {
      console.error("Fetch error:", err);
      Swal.fire("Error", "No se pudo conectar con el servidor", "error");
    }
  });
});
