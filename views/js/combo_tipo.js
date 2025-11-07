// combo_tipo.js
function inicializarComboTipo() {
    const combotipo = document.getElementById("comboTipo");
   // const comboTorre = document.getElementById("comboTorre");
   // const comboDepartamento = document.getElementById("comboDepartamento");

    if (!combotipo) {
        console.warn("No se encontraron combo de tipo DOM.");
        return;
    }

    const valorRegistradoTipo = comboTipo.dataset.valorRegistrado;
    //const valorRegistradoTorre = comboTorre.dataset.valorRegistrado;
   // const valorRegistradoDepartamento = comboDepartamento.dataset.valorRegistrado;

    // 🔹 Normaliza URL evitando doble slash
    const buildURL = (path) => {
        if (!window.BASE_URL) {
            console.error("window.BASE_URL no está definido");
            return path;
        }
        return window.BASE_URL.replace(/\/+$/, '') + '/' + path.replace(/^\/+/, '');
    };

    // 🔹 Función genérica para cargar combos
    async function cargarCombo(url, combo, placeholder, mapFn, valorSeleccionado = null) {
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
                if (valorSeleccionado && valorSeleccionado == value) {
                    opt.selected = true;
                }
                combo.appendChild(opt);
            });
        } catch (error) {
            console.error(`Error al cargar ${placeholder}:`, error);
        }
    }

    // 🔹 Cargar condominios y seleccionar valor registrado
    cargarCombo(
        buildURL('tipos'),
        comboTipo,
        '--Seleccione Tipos--',
        c => ({ value: c.codigo_tipo, text: c.nombre }),
        valorRegistradoTipo
    ).then(() => {
        // Si hay condominio registrado, cargar torres automáticamente
        if (valorRegistradoTipo) {
            comboTipo.dispatchEvent(new Event('change'));
        }
    });

}

// 🔹 Esperar a que exista el combo antes de inicializar
function esperarComboYInicializar() {
    const combo = document.getElementById("comboTipo");
    if (combo) {
        inicializarComboTipo();
    } else {
        setTimeout(esperarComboYInicializar, 100);
    }
}

//document.addEventListener("DOMContentLoaded", esperarComboYInicializar);
