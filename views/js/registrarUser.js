// views/js/user.js
document.addEventListener("DOMContentLoaded", () => {
  const formCrearUsuario = document.getElementById("formCrearUsuario");
  if (!formCrearUsuario) return console.error("No se encontró formCrearUsuario en DOM");

  formCrearUsuario.addEventListener("submit", async (e) => {
    e.preventDefault();

    const data = {
      nombre: document.getElementById("nombre").value.trim(),
      documento: document.getElementById("documento").value.trim(),
      telefono: document.getElementById("telefono").value.trim(),
      email: document.getElementById("rEmail").value.trim(),
      codigo_condominio: document.getElementById("comboCondominio").value,
      codigo_torre: document.getElementById("comboTorre").value,
      codigo_departamento: document.getElementById("comboDepartamento").value,
      codigo_rol: 2, // vecino por defecto
      clave: document.getElementById("rClave").value,
      confirmar_clave: document.getElementById("confirmar_clave").value,
      fecha_inicio: new Date().toISOString().split("T")[0]
    };

    // validaciones cliente
    if (data.clave !== data.confirmar_clave) {
      Swal.fire("Error", "Las contraseñas no coinciden", "error");
      return;
    }

    // Crear endpoint robusto usando window.BASE_URL (quita slash final si existe)
    const rawBase = window.BASE_URL || '';
    const base = rawBase.replace(/\/+$/,''); // '/entrevecinos' o ''
    const endpoint = base + '/usuarios/registrar'; // '/entrevecinos/usuarios/registrar'

    try {
      // Enviar JSON
      const response = await fetch(endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          nombre: data.nombre,
          documento: data.documento,
          telefono: data.telefono,
          email: data.email,
          codigo_rol: data.codigo_rol,
          codigo_departamento: Number(data.codigo_departamento),
          fecha_inicio: data.fecha_inicio,
          clave: data.clave
        }),
      });

      // DEBUG: ver estado y cuerpo crudo cuando algo falla
      if (!response.ok) {
        const text = await response.text();
        console.error("HTTP error", response.status, text);
        Swal.fire("Error", `Error servidor: ${response.status}`, "error");
        return;
      }

      // Intentar parsear JSON (protegido)
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
          .then(() => window.location.href = base + '/'); // ir a login
      } else {
        Swal.fire("Error", result.message || "No se pudo registrar", "error");
      }

    } catch (err) {
      console.error("Fetch error:", err);
      Swal.fire("Error", "No se pudo conectar con el servidor", "error");
    }
  });
});
