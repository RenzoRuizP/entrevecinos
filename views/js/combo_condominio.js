document.addEventListener("DOMContentLoaded", function () {
    const comboCondominio = document.getElementById("comboCondominio");
    const comboTorre = document.getElementById("comboTorre");
    const comboDepartamento = document.getElementById("comboDepartamento");

    if (!comboCondominio || !comboTorre || !comboDepartamento) return;

    // 🔹 Normaliza URL evitando doble slash
    const buildURL = (path) => window.BASE_URL.replace(/\/+$/, '') + '/' + path.replace(/^\/+/, '');

    // 🔹 Función genérica para cargar combos
    async function cargarCombo(url, combo, placeholder, mapFn) {
        try {
            const res = await fetch(url);
            if (!res.ok) throw new Error(`Error al cargar datos desde ${url}`);
            const data = await res.json();

            combo.innerHTML = `<option value="">${placeholder}</option>`;
            data.forEach(item => {
                const opt = document.createElement("option");
                const { value, text } = mapFn(item);
                opt.value = value;
                opt.textContent = text;
                combo.appendChild(opt);
            });
        } catch (error) {
            console.error(`Error al cargar ${placeholder}:`, error);
        }
    }

    // 🔹 Cargar condominios
    cargarCombo(
        buildURL('condominios'),
        comboCondominio,
        '--Seleccione Condominio--',
        c => ({ value: c.codigo_condominio, text: c.nombre_condominio })
    );

    // 🔹 Cargar torres según condominio
    comboCondominio.addEventListener("change", function () {
        comboTorre.innerHTML = "<option value=''>--Seleccione Torre--</option>";
        comboDepartamento.innerHTML = "<option value=''>--Seleccione Departamento--</option>";

        if (!this.value) return;

        cargarCombo(
            buildURL(`condominios/${this.value}/torres`),
            comboTorre,
            '--Seleccione Torre--',
            t => ({ value: t.codigo_torre, text: t.nombre_torre })
        );
    });

    // 🔹 Cargar departamentos según torre
    comboTorre.addEventListener("change", function () {
        comboDepartamento.innerHTML = "<option value=''>--Seleccione Departamento--</option>";
        if (!this.value) return;

        cargarCombo(
            buildURL(`torres/${this.value}/departamentos`),
            comboDepartamento,
            '--Seleccione Departamento--',
            d => ({ value: d.codigo_departamento, text: d.numero_departamento })
        );
    });
});
