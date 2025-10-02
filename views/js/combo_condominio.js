document.addEventListener("DOMContentLoaded", function() {
    fetch(window.BASE_URL + "condominios/listar")
        .then(response => response.json())
        .then(result => {
            if (result.status !== "success" || !Array.isArray(result.data)) {
                console.error("Respuesta inesperada:", result);
                return;
            }

            let combo = document.getElementById("comboCondominio");
            result.data.forEach(condominio => {
                let option = document.createElement("option");
                option.value = condominio.codigo_condominio;
                option.textContent = condominio.nombre_condominio;
                combo.appendChild(option);
            });
        })
        .catch(err => console.error("Error cargando condominios:", err));
});
