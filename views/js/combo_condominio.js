document.addEventListener("DOMContentLoaded", function() {
    const comboCondominio = document.getElementById("comboCondominio");
    const comboTorre = document.getElementById("comboTorre");
    const comboDepartamento = document.getElementById("comboDepartamento");

    // Cargar condominios
    fetch(window.BASE_URL + "condominios")
        .then(res => res.json())
        .then(data => {
            data.forEach(c => {
                let opt = document.createElement("option");
                opt.value = c.codigo_condominio;
                opt.textContent = c.nombre_condominio;
                comboCondominio.appendChild(opt);
            });
        });

    // Cuando se elija condominio -> cargar torres
    comboCondominio.addEventListener("change", function() {
        comboTorre.innerHTML = "<option value=''>--Seleccione Torre--</option>";
        fetch(window.BASE_URL + "condominios/" + this.value + "/torres")
            .then(res => res.json())
            .then(data => {
                data.forEach(t => {
                    let opt = document.createElement("option");
                    opt.value = t.codigo_torre;
                    opt.textContent = t.nombre_torre;
                    comboTorre.appendChild(opt);
                });
            });
    });

    // Cuando se elija torre -> cargar departamentos
    comboTorre.addEventListener("change", function() {
        comboDepartamento.innerHTML = "<option value=''>--Seleccione Departamento--</option>";
        fetch(window.BASE_URL + "torres/" + this.value + "/departamentos")
            .then(res => res.json())
            .then(data => {
                data.forEach(d => {
                    let opt = document.createElement("option");
                    opt.value = d.codigo_departamento;
                    opt.textContent = d.numero_departamento;
                    comboDepartamento.appendChild(opt);
                });
            });
    });
});
