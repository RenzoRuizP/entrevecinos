  // 🧩 Delegación de eventos como respaldo adicional
  document.addEventListener("click", async (e) => {
    // Detectar clic en el botón guardar, incluso si se cargó dinámicamente
    if (e.target && e.target.id === "btnGuardar") {
      const form = document.getElementById("formDatosPersonales");
      if (!form) return; // si aún no se cargó, no hace nada

      console.log("🟢 (Delegación) Click detectado en btnGuardar");

      const nombre = document.getElementById("nombre_completo")?.value.trim() || "";
      const email = document.getElementById("email")?.value.trim() || "";

      if (!nombre || !email) {
        Swal.fire({
          icon: "warning",
          title: "Campos requeridos",
          text: "Por favor ingresa al menos tu nombre y correo electrónico.",
        });
        return;
      }

      Swal.fire({
        title: "Guardando cambios...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
      });

      try {
        const response = await fetch(`${window.BASE_URL}api/usuario/actualizar`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          credentials: "include",
          body: JSON.stringify({
            nombre_completo: nombre,
            email,
            telefono: document.getElementById("telefono")?.value.trim() || "",
            documento: document.getElementById("documento")?.value.trim() || "",
            // direccion_condominio: document.getElementById("direccion_condominio")?.value.trim() || "",
            comboCondominio: document.getElementById("comboCondominio")?.value || "",
            comboTorre: document.getElementById("comboTorre")?.value || "",
            comboDepartamento: document.getElementById("comboDepartamento")?.value || "",
          }),
        });

        const result = await response.json();
        console.log("📬 (Delegación) Respuesta del servidor:", result);

        if (!response.ok || !result.success) throw new Error(result.error || "No se pudo guardar la información");

        Swal.fire({
          icon: "success",
          title: "Datos actualizados correctamente",
          timer: 1500,
          showConfirmButton: false,
        });
      } catch (err) {
        console.error("❌ (Delegación) Error al guardar:", err);
        Swal.fire({
          icon: "error",
          title: "Error al guardar",
          text: err.message || "Ocurrió un error al guardar los datos.",
        });
      }
    }
  });
