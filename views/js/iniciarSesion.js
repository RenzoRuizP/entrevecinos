document.addEventListener("DOMContentLoaded", () => {
  const formLogin = document.getElementById("formLogin");

  formLogin.addEventListener("submit", async (e) => {
    e.preventDefault();

    const data = {
      email: document.getElementById("email").value,
      clave: document.getElementById("clave").value,
    };

    const rawBase = window.BASE_URL || "";
    const base = rawBase.replace(/\/+$/, "");
    const endpoint = base + "/login";

    try {
      const response = await fetch(endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
      });

      const result = await response.json();

      if (result.success) {
        Swal.fire("Éxito", result.message, "success").then(() => {
          window.location.href = base + "/MenuPrincipal";
        });
      } else {
        Swal.fire("Error", result.message, "error");
      }
    } catch (err) {
      console.error(err);
      Swal.fire("Error", "No se pudo conectar con el servidor", "error");
    }
  });
});
