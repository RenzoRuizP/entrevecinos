/* marketplace.js
   UX para Marketplace Entre Vecinos
   - Filtro por categoría
   - Búsqueda por título
   - Ordenamiento
*/

(function () {
  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

  const chips = $$('.ev-mp-chip');
  const searchInput = $('#mp-buscar');
  const cards = $$('.ev-mp-card');
  const resumenResultados = $('#mp-resumen-resultados');
  const emptyState = $('#mp-empty-state');
  const selectOrdenar = $('#mp-ordenar');

  let filtroCategoria = 'todos';
  let textoBusqueda = '';
  let criterioOrden = 'recientes';

  function normalizar(str) {
    return (str || '')
      .toString()
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '');
  }

  function aplicarFiltros() {
    let visibles = [];

    cards.forEach(card => {
      const categories = card.dataset.category || '';
      const titulo = card.dataset.titulo || '';

      const coincideCategoria =
        filtroCategoria === 'todos' ||
        categories.split(/\s+/).includes(filtroCategoria);

      const coincideBusqueda =
        textoBusqueda.trim() === '' ||
        normalizar(titulo).includes(normalizar(textoBusqueda));

      if (coincideCategoria && coincideBusqueda) {
        card.classList.remove('d-none');
        visibles.push(card);
      } else {
        card.classList.add('d-none');
      }
    });

    ordenarCards(visibles);

    if (visibles.length === 0) {
      emptyState && emptyState.classList.remove('d-none');
    } else {
      emptyState && emptyState.classList.add('d-none');
    }

    if (resumenResultados) {
      resumenResultados.textContent =
        `Mostrando ${visibles.length} resultado${visibles.length === 1 ? '' : 's'} en El Pilar`;
    }
  }

  function ordenarCards(visibles) {
    if (!visibles || visibles.length === 0) return;
    const grid = visibles[0].parentElement;
    if (!grid) return;

    visibles.sort((a, b) => {
      const precioA = parseFloat(a.dataset.precio || '0');
      const precioB = parseFloat(b.dataset.precio || '0');
      const ratingA = parseFloat(a.dataset.rating || '0');
      const ratingB = parseFloat(b.dataset.rating || '0');
      const recienteA = parseInt(a.dataset.reciente || '0', 10);
      const recienteB = parseInt(b.dataset.reciente || '0', 10);

      switch (criterioOrden) {
        case 'menor_precio':
          return precioA - precioB;
        case 'mayor_precio':
          return precioB - precioA;
        case 'mejor_valorados':
          return ratingB - ratingA;
        case 'recientes':
        default:
          return recienteB - recienteA; // más reciente primero
      }
    });

    visibles.forEach(card => grid.appendChild(card));
  }

  // Eventos chips
  chips.forEach(chip => {
    chip.addEventListener('click', () => {
      chips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      filtroCategoria = chip.dataset.category || 'todos';
      aplicarFiltros();
    });
  });

  // Búsqueda
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      textoBusqueda = searchInput.value;
      aplicarFiltros();
    });
  }

  // Ordenar
  if (selectOrdenar) {
    selectOrdenar.addEventListener('change', () => {
      criterioOrden = selectOrdenar.value || 'recientes';
      aplicarFiltros();
    });
  }

  // Inicial
  aplicarFiltros();
})();
