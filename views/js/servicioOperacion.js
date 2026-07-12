/* views/js/servicioOperacion.js
   EV — Punto 11: ejecución, reprogramación, incidencias y calificación de servicios. */
(function () {
  'use strict';

  if (window.EVServicioOperacion) return;

  const BASE = String(window.BASE_URL || window.EV_BASE_URL || '').replace(/\/+$/, '');
  const MAX_FILES = 5;
  const MAX_BYTES = 8 * 1024 * 1024;
  const FILE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];

  let currentId = 0;
  let currentData = null;
  let shell = null;
  let loading = false;
  let actionRunning = false;

  const esc = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

  const nl2br = (value) => esc(value).replace(/\n/g, '<br>');

  const money = (value) => {
    const n = Number(value);
    return Number.isFinite(n)
      ? `S/ ${n.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
      : '—';
  };

  const date = (value, withTime = false) => {
    const raw = String(value || '').trim();
    if (!raw) return '—';
    const source = raw.includes('T') ? raw : raw.replace(' ', 'T');
    const d = new Date(source.length <= 10 ? `${source}T00:00:00` : source);
    if (Number.isNaN(d.getTime())) return raw;
    return d.toLocaleString('es-PE', withTime
      ? { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }
      : { day: '2-digit', month: '2-digit', year: 'numeric' });
  };

  const time = (value) => {
    const raw = String(value || '').trim();
    return raw ? raw.slice(0, 5) : '—';
  };

  const imageUrl = (path) => {
    const raw = String(path || '').trim();
    if (!raw) return `${BASE}/public/img/placeholder-ev.png`;
    if (/^https?:\/\//i.test(raw)) return raw;
    return raw.startsWith('/') ? `${BASE}${raw}` : `${BASE}/${raw.replace(/^\/+/, '')}`;
  };

  const paymentLabel = (value) => ({
    contra_entrega: 'Pago contra entrega',
    adelanto_acordado: 'Adelanto acordado'
  }[String(value || '')] || 'No especificada');

  const roleLabel = (value) => ({
    solicitante: 'Comprador',
    comprador: 'Comprador',
    proveedor: 'Proveedor',
    sistema: 'EV',
    soporte: 'Soporte'
  }[String(value || '')] || 'Vecino');

  const stateClass = (state) => {
    const s = String(state || '');
    if (['servicio_confirmado_solicitante'].includes(s)) return 'success';
    if (['cancelada_solicitante', 'cancelada_proveedor', 'cancelada_soporte', 'rechazada_proveedor'].includes(s)) return 'danger';
    if (['incidencia_abierta', 'incidencia_en_atencion', 'solucion_pendiente_confirmacion', 'revision_soporte'].includes(s)) return 'warning';
    if (['servicio_en_ejecucion', 'servicio_realizado_proveedor'].includes(s)) return 'info';
    return 'pending';
  };

  const timelineIcon = (type) => {
    const t = String(type || '');
    if (t.includes('reprogramacion')) return 'bi-calendar2-week';
    if (t.includes('incidencia') || t.includes('problema')) return 'bi-exclamation-triangle';
    if (t.includes('solucion')) return 'bi-tools';
    if (t.includes('soporte')) return 'bi-headset';
    if (t.includes('completado') || t.includes('confirmado')) return 'bi-check2-circle';
    if (t.includes('iniciado')) return 'bi-play-circle';
    if (t.includes('realizado')) return 'bi-clipboard2-check';
    if (t.includes('cancel')) return 'bi-x-circle';
    if (t.includes('cotizacion')) return 'bi-receipt';
    return 'bi-chat-left-text';
  };

  function ensureStyles() {
    if (document.getElementById('ev-so-style')) return;
    const style = document.createElement('style');
    style.id = 'ev-so-style';
    style.textContent = `
      #ev-so-shell{position:fixed;inset:0;z-index:10920;display:none;align-items:center;justify-content:center;padding:14px;background:rgba(15,23,42,.56);backdrop-filter:blur(5px)}
      #ev-so-shell.is-open{display:flex}
      #ev-so-shell *{box-sizing:border-box}
      #ev-so-shell .ev-so-modal{width:min(1220px,100%);height:min(900px,calc(100dvh - 28px));display:flex;flex-direction:column;overflow:hidden;border-radius:28px;background:#F7F9F8;box-shadow:0 34px 100px rgba(15,23,42,.38);font-family:inherit}
      #ev-so-shell .ev-so-head{flex:0 0 auto;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:15px 20px;color:#fff;background:radial-gradient(circle at 85% 20%,rgba(255,255,255,.16),transparent 32%),linear-gradient(135deg,#0F592F,#0E7A43 58%,#16A34A)}
      #ev-so-shell .ev-so-head-left{min-width:0;display:flex;align-items:center;gap:12px}
      #ev-so-shell .ev-so-head-icon{width:44px;height:44px;display:grid;place-items:center;flex:0 0 auto;border-radius:15px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.22);font-size:1.2rem}
      #ev-so-shell .ev-so-title{font-weight:950;font-size:1.1rem;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
      #ev-so-shell .ev-so-sub{margin-top:3px;color:rgba(255,255,255,.78);font-size:.79rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
      #ev-so-shell .ev-so-close{width:40px;height:40px;display:grid;place-items:center;flex:0 0 auto;border:1px solid rgba(255,255,255,.2);border-radius:13px;background:rgba(255,255,255,.1);color:#fff;font-size:1.45rem;transition:.16s ease}
      #ev-so-shell .ev-so-close:hover{background:rgba(255,255,255,.2);transform:translateY(-1px)}
      #ev-so-shell .ev-so-body{flex:1 1 auto;min-height:0;overflow:auto;padding:16px}
      #ev-so-shell .ev-so-loading{min-height:420px;display:grid;place-items:center}
      #ev-so-shell .ev-so-spinner{width:58px;height:58px;border:5px solid rgba(22,163,74,.15);border-top-color:#0F592F;border-radius:50%;animation:evSoSpin .8s linear infinite}
      @keyframes evSoSpin{to{transform:rotate(360deg)}}
      #ev-so-shell .ev-so-layout{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:14px;align-items:start}
      #ev-so-shell .ev-so-main,#ev-so-shell .ev-so-side{display:grid;gap:14px;min-width:0}
      #ev-so-shell .ev-so-card{border:1px solid rgba(148,163,184,.18);border-radius:22px;background:#fff;box-shadow:0 12px 30px rgba(15,23,42,.065);overflow:hidden}
      #ev-so-shell .ev-so-card-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;border-bottom:1px solid #EEF2F7;background:linear-gradient(180deg,#fff,#FCFDFC)}
      #ev-so-shell .ev-so-card-title{display:flex;align-items:center;gap:9px;margin:0;color:#0F592F;font-size:.95rem;font-weight:950}
      #ev-so-shell .ev-so-card-title i{color:#EA7C12}
      #ev-so-shell .ev-so-card-body{padding:15px 16px}
      #ev-so-shell .ev-so-service{display:grid;grid-template-columns:92px minmax(0,1fr);gap:14px;align-items:start}
      #ev-so-shell .ev-so-cover{width:92px;height:92px;border-radius:20px;object-fit:cover;background:#F1F5F9;box-shadow:0 10px 22px rgba(15,23,42,.1)}
      #ev-so-shell .ev-so-service h3{margin:1px 0 5px;color:#0F592F;font-size:1.16rem;line-height:1.18;font-weight:950}
      #ev-so-shell .ev-so-service p{margin:0;color:#64748B;font-size:.86rem;line-height:1.48}
      #ev-so-shell .ev-so-state{display:inline-flex;align-items:center;gap:6px;margin-top:9px;padding:6px 10px;border-radius:999px;font-size:.75rem;font-weight:950;border:1px solid transparent}
      #ev-so-shell .ev-so-state.pending{color:#9A5B03;background:#FFF9EC;border-color:#FCD9BD}
      #ev-so-shell .ev-so-state.info{color:#1D4ED8;background:#EFF6FF;border-color:#BFDBFE}
      #ev-so-shell .ev-so-state.warning{color:#9A3412;background:#FFF7ED;border-color:#FED7AA}
      #ev-so-shell .ev-so-state.success{color:#166534;background:#F0FDF4;border-color:#BBF7D0}
      #ev-so-shell .ev-so-state.danger{color:#991B1B;background:#FEF2F2;border-color:#FECACA}
      #ev-so-shell .ev-so-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}
      #ev-so-shell .ev-so-box{min-width:0;padding:10px 11px;border:1px solid #E8EDF3;border-radius:15px;background:#fff}
      #ev-so-shell .ev-so-box span{display:block;margin-bottom:3px;color:#64748B;font-size:.7rem;font-weight:850;text-transform:uppercase;letter-spacing:.05em}
      #ev-so-shell .ev-so-box strong{display:block;color:#111827;font-size:.87rem;line-height:1.35;overflow-wrap:anywhere}
      #ev-so-shell .ev-so-box.is-current{background:linear-gradient(180deg,#F0FDF4,#fff);border-color:#BBF7D0}
      #ev-so-shell .ev-so-box.is-current strong{color:#0F592F}
      #ev-so-shell .ev-so-quote{display:grid;gap:10px}
      #ev-so-shell .ev-so-text{color:#475569;font-size:.87rem;line-height:1.55;white-space:normal;overflow-wrap:anywhere}
      #ev-so-shell .ev-so-price{font-size:1.35rem;font-weight:950;color:#0F592F}
      #ev-so-shell .ev-so-actions{display:flex;flex-wrap:wrap;gap:8px}
      #ev-so-shell .ev-so-btn{border-radius:13px;padding:.68rem .86rem;font-size:.82rem;font-weight:900;transition:.16s ease}
      #ev-so-shell .ev-so-btn:hover{transform:translateY(-1px)}
      #ev-so-shell .ev-so-btn-primary{border:0;color:#fff;background:linear-gradient(135deg,#0F592F,#16A34A);box-shadow:0 10px 20px rgba(15,89,47,.18)}
      #ev-so-shell .ev-so-btn-orange{border:0;color:#fff;background:linear-gradient(135deg,#EA7C12,#F59E0B);box-shadow:0 10px 20px rgba(234,124,18,.2)}
      #ev-so-shell .ev-so-btn-blue{color:#1D4ED8;background:#EFF6FF;border:1px solid #BFDBFE}
      #ev-so-shell .ev-so-btn-outline{color:#0F592F;background:#fff;border:1px solid rgba(15,89,47,.22)}
      #ev-so-shell .ev-so-btn-danger{color:#B91C1C;background:#FFF1F2;border:1px solid #FECACA}
      #ev-so-shell .ev-so-btn-muted{color:#475569;background:#F8FAFC;border:1px solid #E2E8F0}
      #ev-so-shell .ev-so-empty{padding:20px;text-align:center;color:#64748B;font-size:.86rem;border:1px dashed #CBD5E1;border-radius:16px;background:#F8FAFC}
      #ev-so-shell .ev-so-reprogram{border:1px solid #FED7AA;border-radius:17px;background:linear-gradient(180deg,#FFF7ED,#fff);padding:12px}
      #ev-so-shell .ev-so-reprogram-head{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
      #ev-so-shell .ev-so-reprogram h4{margin:0;color:#9A3412;font-size:.9rem;font-weight:950}
      #ev-so-shell .ev-so-reprogram p{margin:5px 0 0;color:#7C4A20;font-size:.82rem;line-height:1.45}
      #ev-so-shell .ev-so-mini-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin-top:10px}
      #ev-so-shell .ev-so-incident{border:1px solid #FECACA;border-radius:18px;background:linear-gradient(180deg,#FFF7F7,#fff);padding:13px}
      #ev-so-shell .ev-so-incident h4{margin:0 0 5px;color:#991B1B;font-size:.94rem;font-weight:950}
      #ev-so-shell .ev-so-incident-tag{display:inline-flex;margin-bottom:8px;padding:5px 9px;border-radius:999px;background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;font-size:.71rem;font-weight:900}
      #ev-so-shell .ev-so-incident-section{margin-top:10px;padding:10px;border-radius:14px;border:1px solid #E5E7EB;background:#fff}
      #ev-so-shell .ev-so-incident-section span{display:block;margin-bottom:4px;color:#64748B;font-size:.7rem;font-weight:900;text-transform:uppercase}
      #ev-so-shell .ev-so-files{display:flex;flex-wrap:wrap;gap:7px;margin-top:10px}
      #ev-so-shell .ev-so-file{display:inline-flex;align-items:center;gap:6px;padding:7px 9px;border-radius:11px;border:1px solid #E2E8F0;background:#fff;color:#0F592F;text-decoration:none;font-size:.76rem;font-weight:850;max-width:100%}
      #ev-so-shell .ev-so-file span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
      #ev-so-shell .ev-so-history{display:grid;gap:8px}
      #ev-so-shell .ev-so-history-item{display:grid;grid-template-columns:32px minmax(0,1fr);gap:9px;align-items:start;padding:9px 0;border-bottom:1px solid #F1F5F9}
      #ev-so-shell .ev-so-history-item:last-child{border-bottom:0}
      #ev-so-shell .ev-so-history-icon{width:31px;height:31px;display:grid;place-items:center;border-radius:11px;background:#F0FDF4;color:#0F592F;border:1px solid #DCFCE7}
      #ev-so-shell .ev-so-history-copy strong{display:block;color:#111827;font-size:.8rem;font-weight:900}
      #ev-so-shell .ev-so-history-copy p{margin:2px 0;color:#64748B;font-size:.77rem;line-height:1.4}
      #ev-so-shell .ev-so-history-copy time{color:#94A3B8;font-size:.69rem;font-weight:750}
      #ev-so-shell .ev-so-rating{padding:14px;border-radius:17px;border:1px solid #BBF7D0;background:linear-gradient(180deg,#F0FDF4,#fff)}
      #ev-so-shell .ev-so-rating h4{margin:0 0 5px;color:#166534;font-size:.91rem;font-weight:950}
      #ev-so-shell .ev-so-rating p{margin:0 0 10px;color:#3F6C50;font-size:.8rem;line-height:1.43}
      /* Los formularios de acción se abren sobre el modal operativo principal. */
      .swal2-container.ev-so-swal-container{z-index:11080!important}
      .swal2-container.ev-so-swal-container.swal2-backdrop-show{background:rgba(15,23,42,.42)!important;backdrop-filter:blur(3px)}
      .ev-so-swal-popup{border-radius:26px!important;border:1px solid #E5E7EB!important;box-shadow:0 30px 80px rgba(15,23,42,.24)!important}
      .ev-so-swal-title{color:#0F592F!important;font-weight:950!important;letter-spacing:-.03em!important}
      .ev-so-swal-html{text-align:left!important;color:#475569!important}
      .ev-so-swal-confirm{border:0!important;border-radius:14px!important;padding:12px 20px!important;background:linear-gradient(135deg,#EA7C12,#F59E0B)!important;color:#fff!important;font-weight:900!important}
      .ev-so-swal-cancel{border:1px solid #D1D5DB!important;border-radius:14px!important;padding:12px 20px!important;background:#fff!important;color:#475569!important;font-weight:900!important}
      .ev-so-form{display:grid;gap:11px;text-align:left}
      .ev-so-field{display:grid;gap:5px}
      .ev-so-field label{color:#334155;font-size:.78rem;font-weight:900}
      .ev-so-field input,.ev-so-field select,.ev-so-field textarea{width:100%;border:1px solid #DCE4EE;border-radius:13px;padding:10px 11px;background:#fff;color:#111827;font-size:.88rem;outline:none}
      .ev-so-field textarea{min-height:92px;resize:vertical}
      .ev-so-field input:focus,.ev-so-field select:focus,.ev-so-field textarea:focus{border-color:#16A34A;box-shadow:0 0 0 4px rgba(22,163,74,.1)}
      .ev-so-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}
      .ev-so-help{padding:10px 11px;border-radius:14px;border:1px solid #FED7AA;background:#FFF7ED;color:#9A3412;font-size:.79rem;line-height:1.45}
      .ev-so-stars{display:flex;justify-content:center;gap:7px;margin:8px 0}
      .ev-so-star{width:44px;height:44px;border:1px solid #E5E7EB;border-radius:14px;background:#fff;color:#CBD5E1;font-size:1.45rem;transition:.15s ease}
      .ev-so-star.is-active{color:#F59E0B;background:#FFF7ED;border-color:#FDBA74;transform:translateY(-1px)}
      .ev-so-tags{display:flex;flex-wrap:wrap;gap:7px;justify-content:center}
      .ev-so-tag{border:1px solid #DCE4EE;border-radius:999px;padding:7px 10px;background:#fff;color:#475569;font-size:.76rem;font-weight:850}
      .ev-so-tag.is-active{background:#F0FDF4;border-color:#86EFAC;color:#166534}
      @media(max-width:991.98px){#ev-so-shell .ev-so-layout{grid-template-columns:1fr}#ev-so-shell .ev-so-side{grid-template-columns:repeat(2,minmax(0,1fr))}}
      @media(max-width:767.98px){#ev-so-shell{padding:7px}#ev-so-shell .ev-so-modal{height:calc(100dvh - 14px);border-radius:22px}#ev-so-shell .ev-so-body{padding:10px}#ev-so-shell .ev-so-side{grid-template-columns:1fr}#ev-so-shell .ev-so-service{grid-template-columns:74px minmax(0,1fr)}#ev-so-shell .ev-so-cover{width:74px;height:74px}#ev-so-shell .ev-so-grid,#ev-so-shell .ev-so-mini-grid,.ev-so-form-grid{grid-template-columns:1fr}#ev-so-shell .ev-so-actions{flex-direction:column}#ev-so-shell .ev-so-btn{width:100%}}
    `;
    document.head.appendChild(style);
  }

  function ensureShell() {
    if (shell) return;
    ensureStyles();
    shell = document.createElement('div');
    shell.id = 'ev-so-shell';
    shell.innerHTML = `
      <section class="ev-so-modal" role="dialog" aria-modal="true" aria-labelledby="evSoTitle">
        <header class="ev-so-head">
          <div class="ev-so-head-left">
            <div class="ev-so-head-icon"><i class="bi bi-clipboard2-check"></i></div>
            <div style="min-width:0">
              <div class="ev-so-title" id="evSoTitle">Gestión del servicio</div>
              <div class="ev-so-sub" id="evSoSubtitle">Ejecución, coordinación y seguimiento dentro de EV</div>
            </div>
          </div>
          <button type="button" class="ev-so-close" data-ev-so-close aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
        </header>
        <div class="ev-so-body" id="evSoBody"></div>
      </section>`;
    document.body.appendChild(shell);
    shell.querySelector('[data-ev-so-close]')?.addEventListener('click', close);

    /*
     * El modal operativo contiene información y acciones críticas del servicio.
     * No debe cerrarse por un clic accidental sobre el fondo ni con Escape.
     * El cierre se realiza únicamente desde el botón X del encabezado.
     */
    shell.addEventListener('click', (e) => {
      if (e.target === shell) {
        e.preventDefault();
        e.stopPropagation();
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && shell?.classList.contains('is-open')) {
        e.preventDefault();
        e.stopPropagation();
      }
    });

    shell.addEventListener('click', handleShellClick);
  }

  function swalBase(options = {}) {
    return Object.assign({
      buttonsStyling: false,
      allowOutsideClick: false,
      target: document.body,
      customClass: {
        container: 'ev-so-swal-container',
        popup: 'ev-so-swal-popup',
        title: 'ev-so-swal-title',
        htmlContainer: 'ev-so-swal-html',
        confirmButton: 'ev-so-swal-confirm',
        cancelButton: 'ev-so-swal-cancel'
      }
    }, options);
  }

  async function alertMessage(icon, title, text) {
    if (window.Swal?.fire) {
      await Swal.fire(swalBase({ icon, title, text, confirmButtonText: 'Aceptar' }));
    } else {
      window.alert(`${title}\n\n${text}`);
    }
  }

  function loadingHtml() {
    return `<div class="ev-so-loading"><div><div class="ev-so-spinner"></div><div style="margin-top:13px;color:#64748B;font-weight:850;text-align:center">Cargando gestión...</div></div></div>`;
  }

  async function request(url, options = {}) {
    const optionHeaders = options.headers || {};
    const fetchOptions = { ...options };
    delete fetchOptions.headers;
    const resp = await fetch(url, {
      credentials: 'include',
      cache: 'no-store',
      ...fetchOptions,
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...optionHeaders }
    });
    const json = await resp.json().catch(() => ({}));
    if (resp.status === 401) {
      await alertMessage('info', 'Sesión finalizada', json.mensaje || 'Vuelve a iniciar sesión.');
      window.location.href = json.redirect || `${BASE}/login`;
      throw new Error('UNAUTHORIZED');
    }
    if (!resp.ok || json.ok === false) {
      throw new Error(json.mensaje || 'No se pudo completar la operación.');
    }
    return json;
  }

  async function load() {
    if (!currentId || loading) return;
    loading = true;
    const body = document.getElementById('evSoBody');
    if (body) body.innerHTML = loadingHtml();
    try {
      const json = await request(`${BASE}/api/servicios/solicitudes/${currentId}/operacion`);
      currentData = json.data || {};
      render();
    } catch (e) {
      if (body) {
        body.innerHTML = `<div class="ev-so-empty"><i class="bi bi-exclamation-circle me-1"></i>${esc(e.message || 'No se pudo cargar la gestión.')}</div>`;
      }
    } finally {
      loading = false;
    }
  }

  function scheduleText(s, prefix) {
    const f = s?.[`${prefix}_fecha`] || s?.[`fecha_${prefix}`] || null;
    return f ? date(f) : '—';
  }

  function renderFiles(files) {
    const list = Array.isArray(files) ? files : [];
    if (!list.length) return '';
    return `<div class="ev-so-files">${list.map((f) => {
      const isPdf = String(f.mime || '').toLowerCase() === 'application/pdf';
      return `<a class="ev-so-file" href="${esc(imageUrl(f.ruta))}" target="_blank" rel="noopener"><i class="bi ${isPdf ? 'bi-file-earmark-pdf' : 'bi-image'}"></i><span>${esc(f.nombre_original || 'Evidencia')}</span></a>`;
    }).join('')}</div>`;
  }

  function renderActionButton(action, label, icon, cls = 'outline') {
    return `<button type="button" class="btn ev-so-btn ev-so-btn-${cls}" data-ev-so-action="${esc(action)}"><i class="bi ${esc(icon)} me-1"></i>${esc(label)}</button>`;
  }

  function renderActions(data) {
    const p = data.permisos || {};
    const buttons = [];
    if (p.iniciar_servicio) buttons.push(renderActionButton('start', 'Iniciar servicio', 'bi-play-circle', 'primary'));
    if (p.marcar_realizado) buttons.push(renderActionButton('done', 'Marcar como realizado', 'bi-clipboard2-check', 'orange'));
    if (p.confirmar_realizado) buttons.push(renderActionButton('confirm-done', 'Confirmar servicio', 'bi-check2-circle', 'primary'));
    if (p.proponer_reprogramacion) buttons.push(renderActionButton('reschedule', 'Proponer reprogramación', 'bi-calendar2-week', 'blue'));
    if (p.responder_reprogramacion) {
      buttons.push(renderActionButton('accept-reschedule', 'Aceptar nueva fecha', 'bi-calendar2-check', 'primary'));
      buttons.push(renderActionButton('reject-reschedule', 'Rechazar nueva fecha', 'bi-calendar2-x', 'danger'));
    }
    if (p.cancelar_reprogramacion) buttons.push(renderActionButton('cancel-reschedule', 'Retirar propuesta', 'bi-arrow-counterclockwise', 'muted'));
    if (p.reportar_problema) buttons.push(renderActionButton('report', 'Reportar un problema', 'bi-exclamation-triangle', 'danger'));
    if (p.responder_incidencia) buttons.push(renderActionButton('reply-incident', 'Responder problema', 'bi-reply', 'blue'));
    if (p.registrar_solucion) buttons.push(renderActionButton('solution', 'Registrar solución', 'bi-tools', 'orange'));
    if (p.confirmar_solucion) buttons.push(renderActionButton('confirm-solution', 'Confirmar solución', 'bi-check2-circle', 'primary'));
    if (p.problema_persiste) buttons.push(renderActionButton('persists', 'El problema continúa', 'bi-arrow-repeat', 'danger'));
    if (p.solicitar_soporte) buttons.push(renderActionButton('support', 'Solicitar apoyo de EV', 'bi-headset', 'outline'));
    if (p.cancelar_servicio) buttons.push(renderActionButton('cancel-service', 'Cancelar coordinación', 'bi-x-circle', 'danger'));
    if (p.calificar) buttons.push(renderActionButton('rate', 'Calificar a la otra parte', 'bi-star', 'orange'));
    return buttons.length ? `<div class="ev-so-actions">${buttons.join('')}</div>` : `<div class="ev-so-empty">No hay acciones pendientes para tu rol en este momento.</div>`;
  }

  function renderReprogram(data) {
    const r = data.reprogramacion_pendiente;
    if (!r) return '';
    return `
      <div class="ev-so-reprogram">
        <div class="ev-so-reprogram-head">
          <div>
            <h4><i class="bi bi-calendar2-week me-1"></i>Reprogramación pendiente</h4>
            <p>Propuesta por ${esc(r.nombre_propone || roleLabel(r.rol_propone))}</p>
          </div>
          <span class="ev-so-state warning">Pendiente</span>
        </div>
        <div class="ev-so-mini-grid">
          <div class="ev-so-box"><span>Fecha propuesta</span><strong>${esc(date(r.fecha_nueva))}</strong></div>
          <div class="ev-so-box"><span>Horario</span><strong>${esc(time(r.hora_inicio_nueva))}${r.hora_fin_nueva ? ` – ${esc(time(r.hora_fin_nueva))}` : ''}</strong></div>
        </div>
        <div class="ev-so-incident-section"><span>Motivo</span><div class="ev-so-text">${nl2br(r.motivo)}</div></div>
        ${r.comentario ? `<div class="ev-so-incident-section"><span>Comentario</span><div class="ev-so-text">${nl2br(r.comentario)}</div></div>` : ''}
      </div>`;
  }

  function renderIncident(data) {
    const i = data.incidencia_activa;
    if (!i) return '';
    return `
      <div class="ev-so-incident">
        <span class="ev-so-incident-tag">${esc(i.categoria_texto || 'Problema reportado')}</span>
        <h4>Incidencia N.° ${esc(i.numero_incidencia || 1)}</h4>
        <div class="ev-so-incident-section"><span>Reportado por ${esc(i.nombre_reporta || roleLabel(i.rol_reporta))}</span><div class="ev-so-text">${nl2br(i.descripcion)}</div>${renderFiles((i.adjuntos || []).filter((a) => a.contexto === 'reporte'))}</div>
        ${i.respuesta ? `<div class="ev-so-incident-section"><span>Respuesta</span><div class="ev-so-text">${nl2br(i.respuesta)}</div>${renderFiles((i.adjuntos || []).filter((a) => a.contexto === 'respuesta'))}</div>` : ''}
        ${i.solucion ? `<div class="ev-so-incident-section"><span>Solución propuesta</span><div class="ev-so-text">${nl2br(i.solucion)}</div>${renderFiles((i.adjuntos || []).filter((a) => a.contexto === 'solucion'))}</div>` : ''}
        ${Number(i.requiere_soporte || 0) === 1 ? `<div class="ev-so-incident-section"><span>Soporte EV</span><div class="ev-so-text">El caso fue enviado al equipo de soporte para revisión.</div></div>` : ''}
      </div>`;
  }

  function renderTimeline(items) {
    const list = Array.isArray(items) ? items : [];
    if (!list.length) return `<div class="ev-so-empty">Aún no hay movimientos registrados.</div>`;
    return `<div class="ev-so-history">${list.slice(0, 30).map((item) => `
      <div class="ev-so-history-item">
        <div class="ev-so-history-icon"><i class="bi ${timelineIcon(item.tipo_interaccion)}"></i></div>
        <div class="ev-so-history-copy">
          <strong>${esc(item.nombre_autor || roleLabel(item.rol_autor))} · ${esc(roleLabel(item.rol_autor))}</strong>
          <p>${esc(item.mensaje || String(item.tipo_interaccion || '').replace(/_/g, ' '))}</p>
          <time>${esc(date(item.created_at, true))}</time>
        </div>
      </div>`).join('')}</div>`;
  }

  function renderReprogramHistory(items) {
    const list = Array.isArray(items) ? items : [];
    const closed = list.filter((x) => String(x.estado) !== 'pendiente');
    if (!closed.length) return `<div class="ev-so-empty">No se registraron reprogramaciones anteriores.</div>`;
    return closed.map((r) => `
      <div class="ev-so-history-item">
        <div class="ev-so-history-icon"><i class="bi bi-calendar2-week"></i></div>
        <div class="ev-so-history-copy">
          <strong>${esc(r.estado === 'aceptada' ? 'Reprogramación aceptada' : r.estado === 'rechazada' ? 'Reprogramación rechazada' : 'Reprogramación retirada')}</strong>
          <p>${esc(date(r.fecha_nueva))} · ${esc(time(r.hora_inicio_nueva))}${r.hora_fin_nueva ? ` – ${esc(time(r.hora_fin_nueva))}` : ''} · ${esc(r.motivo || '')}</p>
          <time>${esc(date(r.fecha_respuesta || r.created_at, true))}</time>
        </div>
      </div>`).join('');
  }

  function render() {
    const body = document.getElementById('evSoBody');
    if (!body || !currentData) return;
    const d = currentData;
    const s = d.solicitud || {};
    const q = d.cotizacion || {};
    document.getElementById('evSoTitle').textContent = s.titulo_servicio || 'Gestión del servicio';
    document.getElementById('evSoSubtitle').textContent = s.rol_actual === 'proveedor'
      ? `Servicio solicitado por ${s.nombre_comprador || 'un vecino'}`
      : `Servicio ofrecido por ${s.nombre_proveedor || 'un vecino'}`;

    const originalSchedule = `${date(s.fecha_ejecucion_original)} · ${time(s.hora_inicio_original)}${s.hora_fin_original ? ` – ${time(s.hora_fin_original)}` : ''}`;
    const currentSchedule = `${date(s.fecha_ejecucion_vigente)} · ${time(s.hora_inicio_vigente)}${s.hora_fin_vigente ? ` – ${time(s.hora_fin_vigente)}` : ''}`;
    const scheduleChanged = String(s.fecha_ejecucion_original || '') !== String(s.fecha_ejecucion_vigente || '')
      || String(s.hora_inicio_original || '') !== String(s.hora_inicio_vigente || '')
      || String(s.hora_fin_original || '') !== String(s.hora_fin_vigente || '');

    body.innerHTML = `
      <div class="ev-so-layout">
        <div class="ev-so-main">
          <section class="ev-so-card">
            <div class="ev-so-card-body">
              <div class="ev-so-service">
                <img class="ev-so-cover" src="${esc(imageUrl(s.imagen_portada))}" alt="${esc(s.titulo_servicio || 'Servicio')}">
                <div>
                  <h3>${esc(s.titulo_servicio || 'Servicio')}</h3>
                  <p>${esc(s.descripcion_servicio || 'Coordinación de servicio entre vecinos.')}</p>
                  <span class="ev-so-state ${stateClass(s.estado)}"><i class="bi bi-circle-fill" style="font-size:.48rem"></i>${esc(s.estado_texto || s.estado || 'En coordinación')}</span>
                </div>
              </div>
            </div>
          </section>

          <section class="ev-so-card">
            <div class="ev-so-card-head"><h3 class="ev-so-card-title"><i class="bi bi-calendar-check"></i>Fecha y horario del servicio</h3></div>
            <div class="ev-so-card-body">
              <div class="ev-so-grid">
                <div class="ev-so-box"><span>Fecha inicialmente cotizada</span><strong>${esc(originalSchedule)}</strong></div>
                <div class="ev-so-box is-current"><span>Fecha vigente</span><strong>${esc(currentSchedule)}</strong></div>
              </div>
              ${scheduleChanged ? `<div class="ev-so-help" style="margin-top:10px"><i class="bi bi-info-circle me-1"></i>La fecha vigente fue actualizada mediante una reprogramación aceptada. La fecha original se conserva en el historial.</div>` : ''}
            </div>
          </section>

          ${renderReprogram(d)}
          ${renderIncident(d)}

          <section class="ev-so-card">
            <div class="ev-so-card-head"><h3 class="ev-so-card-title"><i class="bi bi-lightning-charge"></i>Acciones disponibles</h3></div>
            <div class="ev-so-card-body">${renderActions(d)}</div>
          </section>

          <section class="ev-so-card">
            <div class="ev-so-card-head"><h3 class="ev-so-card-title"><i class="bi bi-clock-history"></i>Historial del servicio</h3></div>
            <div class="ev-so-card-body">${renderTimeline(d.timeline)}</div>
          </section>
        </div>

        <aside class="ev-so-side">
          <section class="ev-so-card">
            <div class="ev-so-card-head"><h3 class="ev-so-card-title"><i class="bi bi-receipt"></i>Cotización aceptada</h3></div>
            <div class="ev-so-card-body ev-so-quote">
              <div class="ev-so-price">${esc(money(q.monto_propuesto))}</div>
              <div class="ev-so-grid">
                <div class="ev-so-box"><span>Condición de pago</span><strong>${esc(paymentLabel(q.condicion_pago))}</strong></div>
                <div class="ev-so-box"><span>Duración</span><strong>${esc(q.duracion_estimada || 'A coordinar')}</strong></div>
              </div>
              ${q.condicion_pago === 'adelanto_acordado' ? `<div class="ev-so-box"><span>Adelanto acordado</span><strong>${esc(money(q.monto_adelanto))}</strong></div>` : ''}
              <div class="ev-so-box"><span>Alcance acordado</span><div class="ev-so-text">${nl2br(q.alcance_confirmado || 'No especificado')}</div></div>
              ${q.requisitos ? `<div class="ev-so-box"><span>Requisitos o condiciones</span><div class="ev-so-text">${nl2br(q.requisitos)}</div></div>` : ''}
            </div>
          </section>

          <section class="ev-so-card">
            <div class="ev-so-card-head"><h3 class="ev-so-card-title"><i class="bi bi-people"></i>Participantes</h3></div>
            <div class="ev-so-card-body ev-so-grid">
              <div class="ev-so-box"><span>Comprador</span><strong>${esc(s.nombre_comprador || 'Vecino')}</strong></div>
              <div class="ev-so-box"><span>Proveedor</span><strong>${esc(s.nombre_proveedor || 'Vecino')}</strong></div>
            </div>
          </section>

          <section class="ev-so-card">
            <div class="ev-so-card-head"><h3 class="ev-so-card-title"><i class="bi bi-calendar2-week"></i>Reprogramaciones anteriores</h3></div>
            <div class="ev-so-card-body"><div class="ev-so-history">${renderReprogramHistory(d.reprogramaciones)}</div></div>
          </section>

          ${d.calificacion ? `<section class="ev-so-card"><div class="ev-so-card-body"><div class="ev-so-rating"><h4><i class="bi bi-star-fill me-1"></i>${d.calificacion.estado === 'pendiente' ? 'Tu calificación está pendiente' : 'Calificación registrada'}</h4><p>${d.calificacion.estado === 'pendiente' ? `Califica a ${esc(d.calificacion.nombre_calificado || 'la otra parte')} para fortalecer la confianza entre vecinos.` : `Registraste ${esc(d.calificacion.puntaje || 0)} de 5 estrellas.`}</p>${d.calificacion.estado === 'pendiente' ? renderActionButton('rate', 'Calificar ahora', 'bi-star', 'orange') : ''}</div></div></section>` : ''}
        </aside>
      </div>`;
  }

  function formHtml(fields, help = '') {
    return `<div class="ev-so-form">${fields}${help ? `<div class="ev-so-help">${help}</div>` : ''}</div>`;
  }

  function getToday() {
    const d = new Date();
    const local = new Date(d.getTime() - d.getTimezoneOffset() * 60000);
    return local.toISOString().slice(0, 10);
  }

  function getTomorrow() {
    const d = new Date();
    d.setDate(d.getDate() + 1);
    const local = new Date(d.getTime() - d.getTimezoneOffset() * 60000);
    return local.toISOString().slice(0, 10);
  }

  function readFiles(input) {
    return input?.files ? Array.from(input.files) : [];
  }

  function validateFiles(files) {
    if (files.length > MAX_FILES) return `Puedes adjuntar como máximo ${MAX_FILES} archivos.`;
    for (const f of files) {
      if (!FILE_TYPES.includes(String(f.type || '').toLowerCase())) return 'Solo se permiten archivos JPG, PNG, WEBP o PDF.';
      if (Number(f.size || 0) <= 0 || Number(f.size || 0) > MAX_BYTES) return 'Cada archivo debe pesar como máximo 8 MB.';
    }
    return '';
  }

  async function actionPost(path, data = {}, options = {}) {
    actionRunning = true;
    try {
      let body;
      let headers = {};
      if (options.multipart) {
        body = data;
      } else {
        body = JSON.stringify(data);
        headers['Content-Type'] = 'application/json';
      }
      const json = await request(`${BASE}${path}`, { method: 'POST', body, headers });
      await alertMessage('success', 'Operación realizada', json.mensaje || 'Los cambios fueron registrados.');
      await load();
      document.dispatchEvent(new CustomEvent('ev:servicio-operacion-updated', { detail: { codigo_solicitud_servicio: currentId } }));
      return true;
    } catch (e) {
      await alertMessage('error', 'No se pudo completar', e.message || 'Revisa la información e inténtalo nuevamente.');
      return false;
    } finally {
      actionRunning = false;
    }
  }

  async function confirmSimple(title, text, confirmText, path) {
    if (!window.Swal?.fire) {
      if (!window.confirm(`${title}\n\n${text}`)) return;
      await actionPost(path);
      return;
    }
    const r = await Swal.fire(swalBase({
      icon: 'question', title, text, showCancelButton: true,
      confirmButtonText: confirmText, cancelButtonText: 'Volver'
    }));
    if (r.isConfirmed) await actionPost(path);
  }

  async function modalReschedule() {
    const s = currentData?.solicitud || {};
    const html = formHtml(`
      <div class="ev-so-form-grid">
        <div class="ev-so-field"><label>Nueva fecha *</label><input id="evSoNewDate" type="date" min="${getToday()}" value="${esc((s.fecha_ejecucion_vigente && s.fecha_ejecucion_vigente >= getToday()) ? s.fecha_ejecucion_vigente : getTomorrow())}"></div>
        <div class="ev-so-field"><label>Hora de inicio *</label><input id="evSoNewStart" type="time" value="${esc(String(s.hora_inicio_vigente || '09:00').slice(0,5))}"></div>
      </div>
      <div class="ev-so-field"><label>Hora de fin <small>(opcional)</small></label><input id="evSoNewEnd" type="time" value="${esc(String(s.hora_fin_vigente || '').slice(0,5))}"></div>
      <div class="ev-so-field"><label>Motivo de la reprogramación *</label><textarea id="evSoReason" maxlength="500" placeholder="Explica brevemente por qué necesitas cambiar la fecha."></textarea></div>
      <div class="ev-so-field"><label>Comentario adicional <small>(opcional)</small></label><textarea id="evSoComment" maxlength="1000" placeholder="Agrega alguna indicación útil para la otra parte."></textarea></div>`,
      'La nueva fecha solo será oficial cuando la otra parte la acepte. El alcance, precio, ubicación y condición de pago no cambian.'
    );
    const r = await Swal.fire(swalBase({
      title: 'Proponer reprogramación', html, showCancelButton: true,
      confirmButtonText: 'Enviar propuesta', cancelButtonText: 'Cancelar',
      preConfirm: () => {
        const fecha = document.getElementById('evSoNewDate')?.value || '';
        const inicio = document.getElementById('evSoNewStart')?.value || '';
        const fin = document.getElementById('evSoNewEnd')?.value || '';
        const motivo = document.getElementById('evSoReason')?.value.trim() || '';
        const comentario = document.getElementById('evSoComment')?.value.trim() || '';
        if (!fecha || !inicio || motivo.length < 5) {
          Swal.showValidationMessage('Completa la fecha, hora y un motivo de al menos 5 caracteres.');
          return false;
        }
        if (fin && fin <= inicio) {
          Swal.showValidationMessage('La hora de fin debe ser posterior a la hora de inicio.');
          return false;
        }
        return { fecha_nueva: fecha, hora_inicio_nueva: inicio, hora_fin_nueva: fin, motivo, comentario };
      }
    }));
    if (r.isConfirmed && r.value) await actionPost(`/api/servicios/solicitudes/${currentId}/reprogramaciones`, r.value);
  }

  async function respondReschedule(accept) {
    const rpg = currentData?.reprogramacion_pendiente;
    if (!rpg) return;
    const html = formHtml(`<div class="ev-so-field"><label>Comentario <small>(opcional)</small></label><textarea id="evSoRpgResponse" maxlength="500" placeholder="Puedes dejar una observación para la otra parte."></textarea></div>`,
      accept ? 'Al aceptar, la nueva fecha reemplazará la fecha operativa vigente y la fecha original permanecerá en el historial.' : 'Al rechazar, la fecha vigente actual se mantendrá sin cambios.'
    );
    const r = await Swal.fire(swalBase({
      icon: accept ? 'question' : 'warning',
      title: accept ? '¿Aceptar la nueva fecha?' : '¿Rechazar la reprogramación?',
      html, showCancelButton: true,
      confirmButtonText: accept ? 'Sí, aceptar' : 'Sí, rechazar', cancelButtonText: 'Volver',
      preConfirm: () => ({ comentario: document.getElementById('evSoRpgResponse')?.value.trim() || '' })
    }));
    if (r.isConfirmed) {
      await actionPost(`/api/servicios/solicitudes/${currentId}/reprogramaciones/${rpg.codigo_reprogramacion}/responder`, { accion: accept ? 'aceptar' : 'rechazar', comentario: r.value?.comentario || '' });
    }
  }

  async function cancelService() {
    const html = formHtml(`<div class="ev-so-field"><label>Motivo de cancelación *</label><textarea id="evSoCancelReason" maxlength="1000" placeholder="Explica por qué necesitas cancelar la coordinación."></textarea></div>`,
      'La cancelación quedará registrada en el historial y la otra parte recibirá una notificación. Durante el piloto no se aplican penalizaciones automáticas.'
    );
    const r = await Swal.fire(swalBase({
      icon: 'warning', title: 'Cancelar coordinación', html, showCancelButton: true,
      confirmButtonText: 'Cancelar servicio', cancelButtonText: 'Mantener coordinación',
      preConfirm: () => {
        const motivo = document.getElementById('evSoCancelReason')?.value.trim() || '';
        if (motivo.length < 5) { Swal.showValidationMessage('Escribe un motivo de al menos 5 caracteres.'); return false; }
        return { motivo_cancelacion: motivo };
      }
    }));
    if (r.isConfirmed && r.value) await actionPost(`/api/servicios/solicitudes/${currentId}/cancelar`, r.value);
  }

  async function reportProblem() {
    const categories = currentData?.categorias_incidencia || {};
    const options = Object.entries(categories).map(([v, t]) => `<option value="${esc(v)}">${esc(t)}</option>`).join('');
    const html = formHtml(`
      <div class="ev-so-field"><label>Tipo de problema *</label><select id="evSoIncidentCategory"><option value="">Selecciona una opción</option>${options}</select></div>
      <div class="ev-so-field"><label>Descripción *</label><textarea id="evSoIncidentDescription" maxlength="3000" placeholder="Describe qué ocurrió y qué parte de lo acordado no se cumplió."></textarea></div>
      <div class="ev-so-field"><label>Evidencias <small>(opcional, máximo 5)</small></label><input id="evSoIncidentFiles" type="file" multiple accept="image/jpeg,image/png,image/webp,application/pdf,.jpg,.jpeg,.png,.webp,.pdf"></div>`,
      'El reporte quedará vinculado a la cotización, conversación e historial del servicio. La calificación permanecerá bloqueada mientras el problema esté abierto.'
    );
    const r = await Swal.fire(swalBase({
      title: 'Reportar un problema', html, showCancelButton: true,
      confirmButtonText: 'Registrar problema', cancelButtonText: 'Cancelar',
      preConfirm: () => {
        const categoria = document.getElementById('evSoIncidentCategory')?.value || '';
        const descripcion = document.getElementById('evSoIncidentDescription')?.value.trim() || '';
        const files = readFiles(document.getElementById('evSoIncidentFiles'));
        const fileError = validateFiles(files);
        if (!categoria) { Swal.showValidationMessage('Selecciona el tipo de problema.'); return false; }
        if (descripcion.length < 10) { Swal.showValidationMessage('Describe el problema con al menos 10 caracteres.'); return false; }
        if (fileError) { Swal.showValidationMessage(fileError); return false; }
        return { categoria, descripcion, files };
      }
    }));
    if (r.isConfirmed && r.value) {
      const fd = new FormData();
      fd.append('categoria', r.value.categoria);
      fd.append('descripcion', r.value.descripcion);
      r.value.files.forEach((f) => fd.append('adjuntos[]', f));
      await actionPost(`/api/servicios/solicitudes/${currentId}/incidencias`, fd, { multipart: true });
    }
  }

  async function incidentTextAction(kind) {
    const config = {
      'reply-incident': { title: 'Responder problema', label: 'Respuesta *', field: 'respuesta', placeholder: 'Explica tu posición o las acciones que realizarás.', path: 'responder', min: 8, files: true, button: 'Enviar respuesta' },
      solution: { title: 'Registrar solución', label: 'Solución aplicada *', field: 'solucion', placeholder: 'Describe claramente cómo fue atendido o corregido el problema.', path: 'solucion', min: 8, files: true, button: 'Enviar solución' },
      persists: { title: 'El problema continúa', label: 'Detalle *', field: 'detalle', placeholder: 'Explica qué parte continúa pendiente o por qué la solución no fue suficiente.', path: 'persiste', min: 8, files: false, button: 'Informar que continúa' },
      support: { title: 'Solicitar apoyo de EV', label: 'Motivo *', field: 'motivo', placeholder: 'Explica por qué necesitas la intervención de soporte.', path: 'solicitar-soporte', min: 8, files: false, button: 'Solicitar soporte' }
    }[kind];
    if (!config) return;
    const html = formHtml(`
      <div class="ev-so-field"><label>${config.label}</label><textarea id="evSoIncidentText" maxlength="3000" placeholder="${esc(config.placeholder)}"></textarea></div>
      ${config.files ? `<div class="ev-so-field"><label>Evidencias <small>(opcional, máximo 5)</small></label><input id="evSoIncidentActionFiles" type="file" multiple accept="image/jpeg,image/png,image/webp,application/pdf,.jpg,.jpeg,.png,.webp,.pdf"></div>` : ''}`,
      kind === 'support' ? 'Soporte podrá revisar la solicitud, cotización, conversación, reprogramaciones, evidencias e historial del caso.' : ''
    );
    const r = await Swal.fire(swalBase({
      title: config.title, html, showCancelButton: true,
      confirmButtonText: config.button, cancelButtonText: 'Cancelar',
      preConfirm: () => {
        const text = document.getElementById('evSoIncidentText')?.value.trim() || '';
        const files = config.files ? readFiles(document.getElementById('evSoIncidentActionFiles')) : [];
        const fileError = validateFiles(files);
        if (text.length < config.min) { Swal.showValidationMessage(`Escribe al menos ${config.min} caracteres.`); return false; }
        if (fileError) { Swal.showValidationMessage(fileError); return false; }
        return { text, files };
      }
    }));
    if (r.isConfirmed && r.value) {
      const path = `/api/servicios/solicitudes/${currentId}/incidencias/${config.path}`;
      if (config.files) {
        const fd = new FormData();
        fd.append(config.field, r.value.text);
        r.value.files.forEach((f) => fd.append('adjuntos[]', f));
        await actionPost(path, fd, { multipart: true });
      } else {
        await actionPost(path, { [config.field]: r.value.text });
      }
    }
  }

  function ratingTags(role, score) {
    const provider = {
      high: ['Calidad del trabajo', 'Puntualidad', 'Buena comunicación', 'Cumplió lo acordado', 'Buena atención'],
      mid: ['Calidad aceptable', 'Comunicación mejorable', 'Puntualidad mejorable', 'Cumplimiento parcial', 'Atención correcta'],
      low: ['Calidad por mejorar', 'Impuntualidad', 'Mala comunicación', 'No cumplió lo acordado', 'Atención por mejorar']
    };
    const buyer = {
      high: ['Puntual', 'Buena comunicación', 'Coordinación clara', 'Cumplió lo acordado', 'Trato respetuoso'],
      mid: ['Coordinación aceptable', 'Comunicación mejorable', 'Puntualidad mejorable', 'Indicaciones poco claras', 'Trato correcto'],
      low: ['Impuntualidad', 'Mala comunicación', 'No cumplió lo acordado', 'Trato inadecuado']
    };
    const group = score >= 4 ? 'high' : score === 3 ? 'mid' : 'low';
    return (role === 'comprador' ? buyer : provider)[group];
  }

  async function rate() {
    const c = currentData?.calificacion;
    if (!c || c.estado !== 'pendiente') return;
    let score = 0;
    const html = `
      <div class="ev-so-form">
        <div style="text-align:center;color:#64748B;font-size:.88rem;line-height:1.5">Califica a <strong style="color:#0F592F">${esc(c.nombre_calificado || 'la otra parte')}</strong>. Tu opinión contribuye a la confianza de la comunidad.</div>
        <div class="ev-so-stars" id="evSoStars">${[1,2,3,4,5].map((n) => `<button type="button" class="ev-so-star" data-score="${n}" aria-label="${n} estrellas"><i class="bi bi-star-fill"></i></button>`).join('')}</div>
        <div id="evSoRatingPrompt" style="text-align:center;color:#0F592F;font-weight:900">Selecciona de 1 a 5 estrellas</div>
        <div class="ev-so-tags" id="evSoRatingTags"><span style="color:#94A3B8;font-size:.8rem">Las etiquetas aparecerán después de seleccionar las estrellas.</span></div>
        <div class="ev-so-field"><label>Comentario <small id="evSoCommentRequired">(opcional)</small></label><textarea id="evSoRatingComment" maxlength="1500" placeholder="Comparte un comentario útil y respetuoso."></textarea></div>
      </div>`;
    const r = await Swal.fire(swalBase({
      title: c.rol_calificado === 'proveedor' ? 'Califica al proveedor' : 'Califica al comprador',
      html, showCancelButton: true, confirmButtonText: 'Enviar calificación', cancelButtonText: 'Después',
      didOpen: (popup) => {
        const tagsBox = popup.querySelector('#evSoRatingTags');
        const prompt = popup.querySelector('#evSoRatingPrompt');
        const required = popup.querySelector('#evSoCommentRequired');
        popup.querySelectorAll('.ev-so-star').forEach((btn) => {
          btn.addEventListener('click', () => {
            score = Number(btn.dataset.score || 0);
            popup.querySelectorAll('.ev-so-star').forEach((b) => b.classList.toggle('is-active', Number(b.dataset.score) <= score));
            prompt.textContent = score >= 4 ? '¿En qué destacó?' : '¿Qué aspectos debería mejorar?';
            required.textContent = score <= 2 ? '(obligatorio)' : '(opcional)';
            tagsBox.innerHTML = ratingTags(c.rol_calificado, score).map((tag) => `<button type="button" class="ev-so-tag" data-tag="${esc(tag)}">${esc(tag)}</button>`).join('');
            tagsBox.querySelectorAll('.ev-so-tag').forEach((tagBtn) => tagBtn.addEventListener('click', () => tagBtn.classList.toggle('is-active')));
          });
        });
      },
      preConfirm: () => {
        const comment = document.getElementById('evSoRatingComment')?.value.trim() || '';
        if (score < 1) { Swal.showValidationMessage('Selecciona una calificación de 1 a 5 estrellas.'); return false; }
        if (score <= 2 && comment.length < 8) { Swal.showValidationMessage('Para 1 o 2 estrellas, escribe un comentario de al menos 8 caracteres.'); return false; }
        const tags = Array.from(document.querySelectorAll('.ev-so-tag.is-active')).map((x) => x.dataset.tag || '');
        return { puntaje: score, comentario: comment, etiquetas: tags };
      }
    }));
    if (r.isConfirmed && r.value) await actionPost(`/api/calificaciones-servicio/${c.codigo_calificacion_servicio}/enviar`, r.value);
  }

  async function handleShellClick(e) {
    const btn = e.target.closest('[data-ev-so-action]');
    if (!btn || actionRunning) return;
    const action = btn.dataset.evSoAction;
    if (action === 'start') return confirmSimple('¿Iniciar el servicio?', 'Se registrará la hora de inicio y ambas partes podrán continuar coordinando dentro de EV.', 'Sí, iniciar', `/api/servicios/solicitudes/${currentId}/iniciar`);
    if (action === 'done') return confirmSimple('¿Marcar el servicio como realizado?', 'El comprador deberá confirmar que el servicio terminó correctamente o reportar un problema.', 'Sí, marcar realizado', `/api/servicios/solicitudes/${currentId}/marcar-realizado`);
    if (action === 'confirm-done') return confirmSimple('¿Confirmar el servicio?', 'Al confirmar, el servicio quedará completado y se habilitará la calificación para ambas partes.', 'Sí, confirmar', `/api/servicios/solicitudes/${currentId}/confirmar-realizado`);
    if (action === 'reschedule') return modalReschedule();
    if (action === 'accept-reschedule') return respondReschedule(true);
    if (action === 'reject-reschedule') return respondReschedule(false);
    if (action === 'cancel-reschedule') {
      const r = currentData?.reprogramacion_pendiente;
      if (r) return confirmSimple('¿Retirar la propuesta?', 'La fecha vigente actual se mantendrá sin cambios.', 'Sí, retirar', `/api/servicios/solicitudes/${currentId}/reprogramaciones/${r.codigo_reprogramacion}/cancelar`);
    }
    if (action === 'cancel-service') return cancelService();
    if (action === 'report') return reportProblem();
    if (['reply-incident','solution','persists','support'].includes(action)) return incidentTextAction(action);
    if (action === 'confirm-solution') return confirmSimple('¿El problema quedó resuelto?', 'Al confirmar la solución, el servicio quedará completado y se habilitarán las calificaciones.', 'Sí, quedó resuelto', `/api/servicios/solicitudes/${currentId}/incidencias/confirmar-solucion`);
    if (action === 'rate') return rate();
  }

  async function open(id) {
    const value = Number(id || 0);
    if (!value) return;
    currentId = value;
    currentData = null;
    ensureShell();
    shell.classList.add('is-open');
    document.body.style.overflow = 'hidden';
    await load();
  }

  function close() {
    if (!shell || actionRunning) return;
    shell.classList.remove('is-open');
    document.body.style.overflow = '';
    currentId = 0;
    currentData = null;
  }

  window.EVServicioOperacion = { open, close, refresh: load };
})();
