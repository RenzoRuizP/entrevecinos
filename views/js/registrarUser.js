// views/js/registrarUser.js
document.addEventListener("DOMContentLoaded", () => {
  const formCrearUsuario = document.getElementById("formCrearUsuario");
  if (!formCrearUsuario) return console.error("No se encontró formCrearUsuario en DOM");

  formCrearUsuario.addEventListener("submit", async (e) => {
    e.preventDefault();

    const tipo = (document.getElementById("comboConjuntoResidencial")?.value || "").trim();
    const codigoCondominio = document.getElementById("comboCondominio")?.value || "";
    const codigoUrbanizacion = document.getElementById("comboUrbanizacion")?.value || "";
    const direccion = (document.getElementById("direccion")?.value || "").trim();

    const data = {
      nombre: document.getElementById("nombre").value.trim(),
      documento: document.getElementById("documento").value.trim(),
      telefono: document.getElementById("telefono").value.trim(),
      email: document.getElementById("rEmail").value.trim(),
      codigo_rol: 2, // vecino por defecto
      clave: document.getElementById("rClave").value,
      confirmar_clave: document.getElementById("confirmar_clave").value,
      fecha_inicio: new Date().toISOString().split("T")[0],

      // NUEVO
      tipo_conjunto: tipo,
      codigo_condominio: tipo === "condominio" ? Number(codigoCondominio || 0) : null,
      codigo_urbanizacion: tipo === "urbanizacion" ? Number(codigoUrbanizacion || 0) : null,
      direccion: direccion
    };

    // Validaciones cliente
    if (data.clave !== data.confirmar_clave) {
      Swal.fire("Error", "Las contraseñas no coinciden", "error");
      return;
    }

    if (!data.tipo_conjunto) {
      Swal.fire("Residencia", "Selecciona el tipo de conjunto residencial (Condominio o Urbanización).", "warning");
      return;
    }

    if (data.tipo_conjunto === "condominio" && (!data.codigo_condominio || data.codigo_condominio <= 0)) {
      Swal.fire("Residencia", "Selecciona un condominio.", "warning");
      return;
    }

    if (data.tipo_conjunto === "urbanizacion" && (!data.codigo_urbanizacion || data.codigo_urbanizacion <= 0)) {
      Swal.fire("Residencia", "Selecciona una urbanización.", "warning");
      return;
    }

    if (!data.direccion || data.direccion.length < 5) {
      Swal.fire("Residencia", "Ingresa una dirección válida.", "warning");
      return;
    }

    // Endpoint robusto usando window.BASE_URL
    const rawBase = window.BASE_URL || '';
    const base = rawBase.replace(/\/+$/,'');
    const endpoint = base + '/usuarios/registrar';

    try {
      const response = await fetch(endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          nombre: data.nombre,
          documento: data.documento,
          telefono: data.telefono,
          email: data.email,
          codigo_rol: data.codigo_rol,
          clave: data.clave,
          fecha_inicio: data.fecha_inicio,

          // NUEVO
          tipo_conjunto: data.tipo_conjunto,
          codigo_condominio: data.codigo_condominio,
          codigo_urbanizacion: data.codigo_urbanizacion,
          direccion: data.direccion
        }),
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
        Swal.fire("Éxito", result.message || "Usuario registrado", "success")
          .then(() => window.location.href = base + '/');
      } else {
        Swal.fire("Error", result.message || "No se pudo registrar", "error");
      }

    } catch (err) {
      console.error("Fetch error:", err);
      Swal.fire("Error", "No se pudo conectar con el servidor", "error");
    }
  });
});
