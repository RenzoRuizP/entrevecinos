// views/js/billetera.js
(function () {
  'use strict';

  const BASE = (window.BASE_URL || '').replace(/\/$/, '');
  const LOG_PREFIX = '[BILLETERA]';

  let refs = {
    wrapper: null,
    saldo: null,
    emptyState: null,
    movimientos: null,
  };

  // ------------------------------------
  // Helpers de log
  // ------------------------------------
  function log() {
    if (window.console && console.log) {
      console.log(LOG_PREFIX, ...arguments);
    }
  }

  function warn() {
    if (window.console && console.warn) {
      console.warn(LOG_PREFIX, ...arguments);
    }
  }

  function error() {
    if (window.console && console.error) {
      console.error(LOG_PREFIX, ...arguments);
    }
  }

  // ------------------------------------
  // Captura de referencias DOM de la vista
  // ------------------------------------
  function capturarRefs() {
    refs.wrapper     = document.querySelector('.ev-wallet-wrapper');
    if (!refs.wrapper) {
      return false; // la vista aún no está montada
    }

    refs.saldo       = document.getElementById('ev_wallet_saldo');
    refs.emptyState  = document.getElementById('ev_wallet_empty_state');
    refs.movimientos = document.getElementById('ev_wallet_movimientos');

    return true;
  }

  // ------------------------------------
  // Utilitarios
  // ------------------------------------
  function formatearMonto(monto) {
    const n = Number(monto || 0);
    return 'S/ ' + n.toLocaleString('es-PE', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function escapeHTML(str) {
    return (str || '').replace(/[&<>"']/g, function (c) {
      switch (c) {
        case '&': return '&amp;';
        case '<': return '&lt;';
        case '>': return '&gt;';
        case '"': return '&quot;';
        default:  return c;
      }
    });
  }

  // ------------------------------------
  // Llamar API para obtener saldo actual
  // ------------------------------------
  async function cargarSaldo() {
    if (!refs.saldo) {
      return;
    }

    // Endpoint API (mantenemos la ruta que ya usas)
    const url = `${BASE}/api/billetera/saldo`;

    try {
      const resp = await fetch(url, {
        method: 'GET',
        headers: {
          'Accept': 'application/json'
        },
        credentials: 'include',
      });

      if (resp.status === 401) {
        error('No autorizado al obtener saldo de billetera.');
        return;
      }

      if (!resp.ok) {
        const txt = await resp.text().catch(() => '');
        error('Error HTTP al obtener saldo:', resp.status, txt);
        return;
      }

      const json = await resp.json();

      if (!json.ok) {
        error('Respuesta API saldo no OK:', json);
        return;
      }

      // Soportar distintas claves: saldo_actual o saldo
      const saldo = (json.saldo_actual ?? json.saldo ?? 0);
      refs.saldo.textContent = formatearMonto(saldo);

    } catch (err) {
      error('Excepción al cargar saldo:', err);
    }
  }

  // ------------------------------------
  // Renderizar movimientos en tabla
  // ------------------------------------
  function renderizarMovimientos(lista) {
    if (!refs.movimientos || !refs.emptyState) return;

    if (!lista || !lista.length) {
      refs.emptyState.classList.remove('d-none');
      refs.movimientos.classList.add('d-none');
      refs.movimientos.innerHTML = '';
      return;
    }

    refs.emptyState.classList.add('d-none');
    refs.movimientos.classList.remove('d-none');

    const filas = lista.map((m) => {
      // Soportar tanto tipo_movimiento ('D'/'C') como tipo ('CARGO'/'ABONO')
      const tipoRaw = (m.tipo_movimiento || m.tipo || '').toUpperCase();
      const esDebito  = (tipoRaw === 'D' || tipoRaw === 'CARGO');
      const esCredito = (tipoRaw === 'C' || tipoRaw === 'ABONO');
      const signo = esDebito ? '-' : '+';

      const claseMonto = esDebito
        ? 'ev-wallet-monto--debito'
        : 'ev-wallet-monto--credito';

      const iconClass = esDebito
        ? 'bi-arrow-down-right-circle-fill ev-wallet-mov-icon ev-wallet-mov-icon--debito'
        : 'bi-arrow-up-right-circle-fill ev-wallet-mov-icon ev-wallet-mov-icon--credito';

      const desc = escapeHTML(m.descripcion || 'Movimiento en billetera');
      const origen = escapeHTML(m.origen || '');
      const ref = m.codigo_referencia
        ? ` · Ref: ${escapeHTML(String(m.codigo_referencia))}`
        : '';

      const monto = (typeof m.monto !== 'undefined') ? m.monto : 0;

      // Puede que no exista saldo_despues en la respuesta: lo manejamos elegante
      const saldoDespues = (typeof m.saldo_despues !== 'undefined' && m.saldo_despues !== null)
        ? formatearMonto(m.saldo_despues)
        : '';

      return `
        <tr>
          <td>
            <div class="ev-wallet-mov-concepto">
              <div class="ev-wallet-mov-header">
                <i class="${iconClass}"></i>
                <span class="ev-wallet-mov-titulo">${desc}</span>
              </div>
              <div class="ev-wallet-mov-detalle text-muted small">
                ${origen}${ref}
              </div>
            </div>
          </td>
          <td class="text-end">
            <span class="ev-wallet-mov-monto ${claseMonto}">
              ${signo} ${formatearMonto(monto)}
            </span>
          </td>
          <td class="text-end">
            <span class="ev-wallet-mov-saldo text-muted">
              ${saldoDespues}
            </span>
          </td>
        </tr>
      `;
    }).join('');

    const html = `
      <div class="table-responsive ev-wallet-table-wrapper">
        <table class="table align-middle ev-wallet-table">
          <thead>
            <tr>
              <th>Movimiento</th>
              <th class="text-end">Monto</th>
              <th class="text-end">Saldo después</th>
            </tr>
          </thead>
          <tbody>
            ${filas}
          </tbody>
        </table>
      </div>
    `;

    refs.movimientos.innerHTML = html;
  }

  // ------------------------------------
  // Cargar movimientos desde API
  // ------------------------------------
  async function cargarMovimientos() {
    if (!refs.movimientos) return;

    const url = `${BASE}/api/billetera/movimientos`;

    try {
      const resp = await fetch(url, {
        method: 'GET',
        headers: {
          'Accept': 'application/json'
        },
        credentials: 'include',
      });

      if (resp.status === 401) {
        error('No autorizado al obtener movimientos de billetera.');
        renderizarMovimientos([]);
        return;
      }

      if (!resp.ok) {
        const txt = await resp.text().catch(() => '');
        error('Error HTTP al obtener movimientos:', resp.status, txt);
        renderizarMovimientos([]);
        return;
      }

      const json = await resp.json();

      if (!json.ok) {
        error('Respuesta API movimientos no OK:', json);
        renderizarMovimientos([]);
        return;
      }

      // Soportar tanto data como movimientos
      const lista = json.data || json.movimientos || [];
      renderizarMovimientos(lista);

    } catch (err) {
      error('Excepción al cargar movimientos:', err);
      renderizarMovimientos([]);
    }
  }

  // ------------------------------------
  // Inicializar la vista de billetera
  // ------------------------------------
  function inicializarVista() {
    if (!capturarRefs()) {
      return;
    }

    log('Vista Mi Billetera detectada en DOM. BASE_URL:', BASE || '(vacía)');

    // Estado inicial
    if (refs.saldo) {
      refs.saldo.textContent = formatearMonto(0);
    }

    if (refs.emptyState) {
      refs.emptyState.classList.remove('d-none');
    }
    if (refs.movimientos) {
      refs.movimientos.classList.add('d-none');
      refs.movimientos.innerHTML = '';
    }

    // Cargar saldo real y movimientos
    cargarSaldo();
    cargarMovimientos();
  }

  // ------------------------------------
  // Cargar parcial /billetera en .content-wrapper
  // ------------------------------------
  async function cargarVistaParcialBilletera(contentWrapper) {
    const url = `${BASE}/billetera?partial=1`;

    try {
      const resp = await fetch(url, {
        method: 'GET',
        headers: {
          'X-Partial': '1',
          'Accept': 'text/html',
        },
        credentials: 'include',
      });

      if (resp.status === 401) {
        error('No autorizado al cargar billetera.');
        if (window.Swal?.fire) {
          Swal.fire({
            icon: 'error',
            title: 'Sesión expirada',
            text: 'Tu sesión ha expirado. Vuelve a iniciar sesión.',
          }).then(() => {
            window.location.href = `${BASE}/`;
          });
        } else {
          window.location.href = `${BASE}/`;
        }
        return;
      }

      if (!resp.ok) {
        const txt = await resp.text().catch(() => '');
        error('Error HTTP al cargar billetera:', resp.status, txt);
        if (window.Swal?.fire) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo cargar tu billetera. Intenta nuevamente.',
          });
        }
        return;
      }

      const html = await resp.text();
      contentWrapper.innerHTML = html;

      // Una vez insertado el HTML, inicializamos la vista
      inicializarVista();

    } catch (err) {
      error('Excepción al cargar billetera:', err);
      if (window.Swal?.fire) {
        Swal.fire({
          icon: 'error',
          title: 'Error de conexión',
          text: 'No pudimos cargar tu billetera. Revisa tu conexión e inténtalo otra vez.',
        });
      }
    }
  }

  // ------------------------------------
  // Hook al menú lateral "Mi billetera"
  // ------------------------------------
  function engancharMenuBilletera() {
    const contentWrapper = document.querySelector('.content-wrapper');
    if (!contentWrapper) {
      warn('No se encontró .content-wrapper, no se puede enganchar Mi billetera.');
      return;
    }

    // Buscar el enlace cuyo texto visible sea "Mi billetera"
    const enlaces = Array.from(document.querySelectorAll('a'));
    const linkBilletera = enlaces.find((a) => {
      const txt = (a.textContent || '').trim().toLowerCase();
      return txt === 'mi billetera';
    });

    if (!linkBilletera) {
      warn('No se encontró un enlace de menú con texto "Mi billetera".');
      return;
    }

    // Evitar múltiples registros
    if (linkBilletera.dataset.evWalletHooked === '1') {
      return;
    }
    linkBilletera.dataset.evWalletHooked = '1';

    linkBilletera.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      cargarVistaParcialBilletera(contentWrapper);
    });
  }

  // ------------------------------------
  // Inicialización estándar
  // ------------------------------------
  document.addEventListener('DOMContentLoaded', () => {
    engancharMenuBilletera();
    // Si la vista ya estuviera montada por acceso directo:
    inicializarVista();
  });

  // ------------------------------------
  // Soporte para carga dinámica
  // ------------------------------------
  const observer = new MutationObserver(() => {
    const wrapperActual = document.querySelector('.ev-wallet-wrapper');
    if (wrapperActual && wrapperActual !== refs.wrapper) {
      inicializarVista();
    }

    // Por si el menú se redibuja dinámicamente
    engancharMenuBilletera();
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true,
  });

  // Exponer para llamadas manuales
  window.EVWallet = {
    init: inicializarVista,
  };
})();
