/* views/js/servicioConversacion.js
   EV — Conversación privada y cotización final de servicios. */
(function () {
  'use strict';

  if (window.EVServicioConversacion) return;

  const BASE = String(window.EV?.baseUrl ?? window.BASE_URL ?? '').replace(/\/+$/, '');
  const POLL_MS = 5000;

  let currentId = 0;
  let currentData = null;
  let modal = null;
  let pollTimer = null;
  let dialogoEnCurso = false;
  let accionEnCurso = false;

  const esc = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

  const money = (value) => {
    const n = Number(value);
    return Number.isFinite(n)
      ? `S/ ${n.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
      : '—';
  };

  const date = (value, withTime = false) => {
    const raw = String(value || '').trim();
    if (!raw) return '—';
    const d = new Date(raw.replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) return raw;
    return d.toLocaleString('es-PE', withTime
      ? { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }
      : { day: '2-digit', month: '2-digit', year: 'numeric' });
  };

  const imageUrl = (path) => {
    const raw = String(path || '').trim();
    if (!raw) return `${BASE}/public/img/placeholder-ev.png`;
    if (/^https?:\/\//i.test(raw)) return raw;
    return raw.startsWith('/') ? `${BASE}${raw}` : `${BASE}/${raw.replace(/^\/+/, '')}`;
  };

  const labelEstado = (estado) => ({
    pendiente_proveedor: 'Esperando inicio de conversación',
    ajuste_solicitado: 'Ajuste solicitado',
    ajuste_cotizacion_solicitado: 'Nueva cotización requerida',
    cotizacion_final_enviada: 'Cotización final por revisar',
    cotizacion_vencida: 'Cotización vencida',
    coordinacion_confirmada: 'Pendiente de ejecución',
    servicio_en_ejecucion: 'Servicio en ejecución',
    servicio_realizado_proveedor: 'Pendiente de confirmación',
    incidencia_abierta: 'Problema reportado',
    incidencia_en_atencion: 'Problema en atención',
    solucion_pendiente_confirmacion: 'Solución pendiente de confirmación',
    revision_soporte: 'En revisión por soporte',
    servicio_confirmado_solicitante: 'Servicio completado',
    observacion_reportada: 'Observación reportada',
    cotizacion_rechazada_solicitante: 'Cotización rechazada',
    cancelada_solicitante: 'Cancelada por comprador',
    cancelada_proveedor: 'Cancelada por proveedor',
    rechazada_proveedor: 'Rechazada por proveedor',
    sin_respuesta_proveedor: 'Sin respuesta del proveedor'
  }[String(estado || '')] || 'En conversación');

  const labelPago = (condicion) => ({
    contra_entrega: 'Pago contra entrega',
    adelanto_acordado: 'Adelanto acordado'
  }[String(condicion || '')] || 'No especificada');

  const time = (value) => {
    const raw = String(value || '').trim();
    return raw ? raw.slice(0, 5) : '';
  };

  const quoteTimeRange = (quote) => {
    const inicio = time(quote?.hora_inicio);
    const fin = time(quote?.hora_fin);
    if (inicio && fin) return `${inicio} – ${fin}`;
    if (inicio) return inicio;
    if (fin) return fin;
    return '';
  };

  const minutesFromTime = (value) => {
    const raw = String(value || '').trim().slice(0, 5);
    if (!/^(?:[01]\d|2[0-3]):[0-5]\d$/.test(raw)) return null;
    const [h, m] = raw.split(':').map(Number);
    return (h * 60) + m;
  };

  const durationTextFromMinutes = (minutes) => {
    const total = Number(minutes || 0);
    if (!Number.isFinite(total) || total <= 0) return '';
    const h = Math.floor(total / 60);
    const m = total % 60;
    if (h > 0 && m > 0) return `${h} ${h === 1 ? 'hora' : 'horas'} ${m} min`;
    if (h > 0) return `${h} ${h === 1 ? 'hora' : 'horas'}`;
    return `${m} min`;
  };

  const durationFromTimes = (start, end) => {
    const ini = minutesFromTime(start);
    const fin = minutesFromTime(end);
    if (ini === null || fin === null || fin <= ini) return '';
    return durationTextFromMinutes(fin - ini);
  };

  const labelEstadoCotizacion = (estado) => ({
    vigente: 'Vigente',
    aceptada: 'Aceptada',
    reemplazada: 'Reemplazada',
    requiere_actualizacion: 'Requiere ajuste',
    cancelada_solicitante: 'Cancelada'
  }[String(estado || '')] || String(estado || 'Sin estado'));


  function ensure() {
    if (modal) return;

    const css = `
      #ev-sc-shell{position:fixed;inset:0;z-index:10880;display:none;align-items:center;justify-content:center;padding:16px;background:rgba(15,23,42,.52);backdrop-filter:blur(4px)}
      #ev-sc-shell.ev-show{display:flex}
      #ev-sc-shell *{box-sizing:border-box}
      #ev-sc-shell .ev-sc-modal{width:min(1180px,100%);height:min(850px,calc(100dvh - 32px));min-height:0;display:flex;flex-direction:column;overflow:hidden;border:0;border-radius:26px;background:#F4F7F5;box-shadow:0 32px 90px rgba(15,23,42,.34);font-family:inherit}
      #ev-sc-shell .ev-sc-head{position:relative;z-index:3;flex:0 0 auto;min-height:74px;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 20px;background:linear-gradient(135deg,#0F592F 0%,#128447 57%,#16A34A 100%);color:#fff}
      #ev-sc-shell .ev-sc-head-left{display:flex;align-items:center;gap:12px;min-width:0}
      #ev-sc-shell .ev-sc-icon{width:42px;height:42px;display:grid;place-items:center;flex:0 0 auto;border:1px solid rgba(255,255,255,.22);border-radius:14px;background:rgba(255,255,255,.13);font-size:1.18rem}
      #ev-sc-shell .ev-sc-title{font-weight:900;font-size:1.04rem;line-height:1.22;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
      #ev-sc-shell .ev-sc-sub{margin-top:2px;color:rgba(255,255,255,.78);font-size:.78rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
      #ev-sc-shell .ev-sc-close{width:38px;height:38px;display:grid;place-items:center;border:0;border-radius:12px;color:#fff;background:rgba(255,255,255,.12);font-size:1.45rem;line-height:1;cursor:pointer;transition:.16s ease}
      #ev-sc-shell .ev-sc-close:hover{background:rgba(255,255,255,.2);transform:translateY(-1px)}
      #ev-sc-shell .ev-sc-grid{flex:1 1 auto;min-height:0;overflow:hidden;display:grid;grid-template-columns:minmax(0,1fr) 350px}
      #ev-sc-shell .ev-sc-main{min-width:0;min-height:0;overflow:hidden;display:flex;flex-direction:column;border-right:1px solid #E2E8F0;background:#F8FAF9}
      #ev-sc-shell .ev-sc-context{flex:0 0 auto;padding:13px 16px;border-bottom:1px solid #E6ECE8;background:#fff}
      #ev-sc-shell .ev-sc-service{display:flex;align-items:center;gap:11px;min-width:0}
      #ev-sc-shell .ev-sc-cover{width:48px;height:48px;object-fit:cover;border:1px solid #E5E7EB;border-radius:14px;background:#F1F5F9}
      #ev-sc-shell .ev-sc-name{font-size:.97rem;font-weight:900;color:#123F2A;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
      #ev-sc-shell .ev-sc-meta{margin-top:3px;color:#64748B;font-size:.77rem}
      #ev-sc-shell .ev-sc-chip{display:inline-flex;align-items:center;gap:5px;margin-top:5px;padding:4px 8px;border:1px solid rgba(22,163,74,.18);border-radius:999px;color:#166534;background:#F0FDF4;font-size:.66rem;font-weight:900}
      #ev-sc-shell .ev-sc-thread{flex:1 1 auto;min-height:0;overflow:auto;overscroll-behavior:contain;scrollbar-gutter:stable;padding:16px 18px 18px;background:linear-gradient(180deg,#F8FBF9 0%,#EEF4F0 100%);scrollbar-width:thin;scrollbar-color:rgba(15,89,47,.34) transparent}
      #ev-sc-shell .ev-sc-thread::-webkit-scrollbar,#ev-sc-shell .ev-sc-side::-webkit-scrollbar{width:8px}
      #ev-sc-shell .ev-sc-thread::-webkit-scrollbar-track,#ev-sc-shell .ev-sc-side::-webkit-scrollbar-track{background:transparent}
      #ev-sc-shell .ev-sc-thread::-webkit-scrollbar-thumb,#ev-sc-shell .ev-sc-side::-webkit-scrollbar-thumb{border:2px solid transparent;border-radius:999px;background:rgba(15,89,47,.30);background-clip:padding-box}
      #ev-sc-shell .ev-sc-thread::-webkit-scrollbar-thumb:hover,#ev-sc-shell .ev-sc-side::-webkit-scrollbar-thumb:hover{background:rgba(15,89,47,.48);background-clip:padding-box}
      #ev-sc-shell .ev-sc-message{display:flex;margin:0 0 11px}
      #ev-sc-shell .ev-sc-message.me{justify-content:flex-end}
      #ev-sc-shell .ev-sc-bubble{max-width:min(82%,620px);padding:10px 12px;border:1px solid #E2E8F0;border-radius:17px 17px 17px 5px;background:#fff;box-shadow:0 5px 14px rgba(15,23,42,.05)}
      #ev-sc-shell .ev-sc-message.me .ev-sc-bubble{border-color:rgba(22,163,74,.22);border-radius:17px 17px 5px 17px;background:linear-gradient(135deg,#EAF8EF,#FFF)}
      #ev-sc-shell .ev-sc-author{margin-bottom:4px;color:#0F592F;font-size:.7rem;font-weight:900}
      #ev-sc-shell .ev-sc-text{color:#334155;font-size:.86rem;line-height:1.48;white-space:pre-wrap;overflow-wrap:anywhere}
      #ev-sc-shell .ev-sc-time{margin-top:5px;color:#94A3B8;font-size:.66rem;font-weight:700}
      #ev-sc-shell .ev-sc-system{margin:9px auto 13px;max-width:760px;padding:10px 12px;border:1px solid #DCEAE1;border-radius:13px;background:rgba(255,255,255,.88);color:#4B5F52;font-size:.78rem;line-height:1.46;text-align:center;box-shadow:0 6px 15px rgba(15,23,42,.035)}
      #ev-sc-shell .ev-sc-system strong{color:#0F592F}
      #ev-sc-shell .ev-sc-pics{display:flex;flex-wrap:wrap;gap:7px;margin-top:8px}
      #ev-sc-shell .ev-sc-pics a{width:72px;height:72px;display:block;overflow:hidden;border:1px solid #E2E8F0;border-radius:11px;background:#F8FAFC}
      #ev-sc-shell .ev-sc-pics img{width:100%;height:100%;object-fit:cover;display:block}
      #ev-sc-shell .ev-sc-compose{position:relative;z-index:2;flex:0 0 auto;padding:12px 14px;border-top:1px solid #E3EAE5;background:#fff;box-shadow:0 -10px 22px rgba(15,23,42,.045)}
      #ev-sc-shell .ev-sc-compose-row{display:grid;grid-template-columns:42px minmax(0,1fr) auto;gap:8px;align-items:end}
      #ev-sc-shell .ev-sc-attach,#ev-sc-shell .ev-sc-send{border:0;border-radius:13px;cursor:pointer;font-weight:900;transition:.16s ease}
      #ev-sc-shell .ev-sc-attach{height:42px;color:#0F592F;background:#ECFDF3;border:1px solid rgba(22,163,74,.18)}
      #ev-sc-shell .ev-sc-send{min-height:42px;padding:0 15px;color:#fff;background:linear-gradient(135deg,#D97706,#EA7C12);box-shadow:0 10px 20px rgba(217,119,6,.28)}
      #ev-sc-shell .ev-sc-attach:hover{transform:translateY(-1px);filter:brightness(1.03)}
      #ev-sc-shell .ev-sc-send:hover{transform:translateY(-1px);background:linear-gradient(135deg,#C46B05,#D46F0F);box-shadow:0 14px 26px rgba(217,119,6,.36);filter:brightness(1.02)}
      #ev-sc-shell .ev-sc-input{min-height:42px;max-height:110px;resize:vertical;padding:10px 11px;border:1px solid #D8E3DB;border-radius:13px;background:#fff;color:#1F2937;font:inherit;font-size:.84rem;outline:0}
      #ev-sc-shell .ev-sc-input:focus{border-color:#16A34A;box-shadow:0 0 0 3px rgba(22,163,74,.11)}
      #ev-sc-shell .ev-sc-helper{margin-top:7px;color:#94A3B8;font-size:.68rem}
      #ev-sc-shell .ev-sc-files{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:7px}
      #ev-sc-shell .ev-sc-file{display:inline-flex;align-items:center;gap:5px;max-width:100%;padding:5px 8px;border:1px solid #DCEAE1;border-radius:999px;background:#F0FDF4;color:#166534;font-size:.69rem;font-weight:800;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
      #ev-sc-shell .ev-sc-side{min-height:0;overflow:auto;overscroll-behavior:contain;scrollbar-gutter:stable;padding:14px;background:linear-gradient(180deg,#FFFFFF,#F7FAF8);scrollbar-width:thin;scrollbar-color:rgba(15,89,47,.28) transparent}
      #ev-sc-shell .ev-sc-card{margin-bottom:11px;padding:13px;border:1px solid #E4EBE6;border-radius:17px;background:#fff;box-shadow:0 7px 18px rgba(15,23,42,.045)}
      #ev-sc-shell .ev-sc-kicker{margin-bottom:8px;color:#94A3B8;font-size:.66rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
      #ev-sc-shell .ev-sc-state{display:flex;align-items:flex-start;justify-content:space-between;gap:8px}
      #ev-sc-shell .ev-sc-state strong{color:#0F592F;font-size:.88rem;line-height:1.3}
      #ev-sc-shell .ev-sc-pill{flex:0 0 auto;display:inline-flex;padding:4px 7px;border:1px solid rgba(22,163,74,.16);border-radius:999px;background:#F0FDF4;color:#166534;font-size:.62rem;font-weight:900}
      #ev-sc-shell .ev-sc-detail-row{display:grid;grid-template-columns:minmax(86px,.7fr) minmax(0,1.3fr);gap:8px;padding:8px 0;border-bottom:1px solid #F0F4F1}
      #ev-sc-shell .ev-sc-detail-row:last-child{border-bottom:0}
      #ev-sc-shell .ev-sc-detail-row span{color:#718096;font-size:.71rem;font-weight:800}
      #ev-sc-shell .ev-sc-detail-row b{color:#334155;font-size:.76rem;line-height:1.35;text-align:right;overflow-wrap:anywhere}
      #ev-sc-shell .ev-sc-proposal{margin-top:9px;padding:11px;border:1px solid rgba(234,124,18,.24);border-radius:14px;background:linear-gradient(180deg,#FFF9ED,#fff)}
      #ev-sc-shell .ev-sc-proposal.is-current{border-color:rgba(234,124,18,.42);box-shadow:0 10px 20px rgba(234,124,18,.08)}
      #ev-sc-shell .ev-sc-proposal-head{display:flex;justify-content:space-between;gap:7px;align-items:flex-start}
      #ev-sc-shell .ev-sc-proposal h4{margin:0;color:#9A3412;font-size:.82rem;font-weight:900}
      #ev-sc-shell .ev-sc-proposal-state{padding:3px 6px;border-radius:999px;background:#fff;border:1px solid rgba(234,124,18,.2);color:#9A3412;font-size:.6rem;font-weight:900}
      #ev-sc-shell .ev-sc-proposal-grid{display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-top:9px}
      #ev-sc-shell .ev-sc-proposal-box{padding:8px;border:1px solid rgba(234,124,18,.15);border-radius:10px;background:#fff}
      #ev-sc-shell .ev-sc-proposal-box span{display:block;color:#93642C;font-size:.62rem;font-weight:900}
      #ev-sc-shell .ev-sc-proposal-box b{display:block;margin-top:2px;color:#442F16;font-size:.74rem;line-height:1.35;overflow-wrap:anywhere}
      #ev-sc-shell .ev-sc-proposal p{margin:8px 0 0;color:#5E4A31;font-size:.75rem;line-height:1.45;white-space:pre-wrap;overflow-wrap:anywhere}
      #ev-sc-shell .ev-sc-action{display:flex;align-items:center;justify-content:center;gap:7px;width:100%;margin-top:9px;padding:10px 11px;border:1px solid rgba(22,163,74,.2);border-radius:12px;color:#0F592F;background:#fff;font:inherit;font-size:.78rem;font-weight:900;cursor:pointer;transition:.16s ease}
      #ev-sc-shell .ev-sc-action:hover{transform:translateY(-1px);background:#F0FDF4;box-shadow:0 8px 16px rgba(15,23,42,.07)}
      #ev-sc-shell .ev-sc-action.orange{border:0;color:#fff;background:linear-gradient(135deg,#D97706,#EA7C12);box-shadow:0 10px 20px rgba(217,119,6,.2)}
      #ev-sc-shell .ev-sc-action.blue{border-color:#BFDBFE;color:#1D4ED8;background:#EFF6FF}
      #ev-sc-shell .ev-sc-action.danger{border-color:#FECACA;color:#B91C1C;background:#FFF5F5}
      #ev-sc-shell .ev-sc-form{display:grid;gap:9px}
      #ev-sc-shell .ev-sc-form label{display:grid;gap:4px;color:#475569;font-size:.72rem;font-weight:900}
      #ev-sc-shell .ev-sc-form input,#ev-sc-shell .ev-sc-form select,#ev-sc-shell .ev-sc-form textarea{width:100%;padding:9px;border:1px solid #DCE4EE;border-radius:10px;background:#fff;color:#1F2937;font:inherit;font-size:.8rem}
      #ev-sc-shell .ev-sc-form input[readonly]{background:linear-gradient(180deg,#F0FDF4,#fff);border-color:rgba(22,163,74,.22);color:#0F592F;font-weight:900;cursor:not-allowed}
      #ev-sc-shell .ev-sc-form textarea{min-height:72px;resize:vertical}
      #ev-sc-shell .ev-sc-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
      #ev-sc-shell .ev-sc-form-note{padding:9px 10px;border:1px solid rgba(234,124,18,.24);border-radius:12px;color:#92400E;background:#FFF9ED;font-size:.72rem;line-height:1.42}
      #ev-sc-shell .ev-sc-hidden{display:none!important}
      @media(max-width:900px){#ev-sc-shell{padding:0}#ev-sc-shell .ev-sc-modal{width:100%;height:100dvh;border-radius:0}#ev-sc-shell .ev-sc-grid{display:flex;flex-direction:column;min-height:0;overflow:hidden}#ev-sc-shell .ev-sc-main{min-height:0;flex:1 1 auto;border-right:0;border-bottom:1px solid #E2E8F0}#ev-sc-shell .ev-sc-side{display:block;min-height:0;max-height:42dvh;flex:0 0 auto;overflow:auto;padding:12px}#ev-sc-shell .ev-sc-bubble{max-width:88%}}
      /* Refinamiento UX/UI EV: jerarquía de conversación y cotización. */
      #ev-sc-shell .ev-sc-thread{padding:20px 22px 22px;background:radial-gradient(circle at top right,rgba(22,163,74,.07),transparent 32%),linear-gradient(180deg,#FBFDFC 0%,#EEF5F0 100%)}
      #ev-sc-shell .ev-sc-message{margin-bottom:14px}
      #ev-sc-shell .ev-sc-bubble{padding:12px 14px;border-radius:18px 18px 18px 6px;box-shadow:0 8px 20px rgba(15,23,42,.055)}
      #ev-sc-shell .ev-sc-message.me .ev-sc-bubble{border-radius:18px 18px 6px 18px;background:linear-gradient(135deg,#E9F9EF,#FFFFFF)}
      #ev-sc-shell .ev-sc-author{font-size:.72rem;letter-spacing:.01em}
      #ev-sc-shell .ev-sc-text{font-size:.9rem;line-height:1.58}
      #ev-sc-shell .ev-sc-time{font-size:.67rem;font-variant-numeric:tabular-nums}
      #ev-sc-shell .ev-sc-system{padding:12px 15px;border-radius:15px}
      #ev-sc-shell .ev-sc-compose{padding:13px 16px 12px;background:linear-gradient(180deg,#FFFFFF,#FCFDFC)}
      #ev-sc-shell .ev-sc-compose-label{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:0 2px 7px;color:#0F592F;font-size:.73rem;font-weight:900}
      #ev-sc-shell .ev-sc-compose-label small{color:#94A3B8;font-size:.66rem;font-weight:700}
      #ev-sc-shell .ev-sc-input{min-height:52px;padding:12px 13px;border-radius:15px;line-height:1.45}
      #ev-sc-shell .ev-sc-attach{height:52px;border-radius:15px}
      #ev-sc-shell .ev-sc-send{min-height:52px;border-radius:15px}
      #ev-sc-shell .ev-sc-card{border-radius:18px;box-shadow:0 9px 22px rgba(15,23,42,.045)}
      #ev-sc-shell .ev-sc-proposal{border-radius:16px;padding:13px}
      #ev-sc-shell .ev-sc-proposal-box-total{background:linear-gradient(180deg,#FFF7ED,#FFFFFF);border-color:rgba(234,124,18,.22)}
      #ev-sc-shell .ev-sc-proposal-box-total b{color:#9A3412;font-size:.9rem}
      #ev-sc-shell .ev-sc-field-help{display:block;margin-top:4px;color:#8A5C26;font-size:.65rem;line-height:1.38;font-weight:700}
      #ev-sc-shell .ev-sc-payment-summary{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:7px;padding:10px 11px;border:1px solid rgba(22,163,74,.18);border-radius:12px;background:#F0FDF4;color:#166534;font-size:.72rem;font-weight:900}
      #ev-sc-shell .ev-sc-payment-summary strong{font-size:.9rem;color:#0F592F}
      @media(max-width:560px){#ev-sc-shell .ev-sc-head{padding:12px}#ev-sc-shell .ev-sc-context{padding:11px 12px}#ev-sc-shell .ev-sc-thread{padding:12px}#ev-sc-shell .ev-sc-compose{padding:10px}#ev-sc-shell .ev-sc-send{font-size:0;width:42px;padding:0}#ev-sc-shell .ev-sc-send i{font-size:.95rem}#ev-sc-shell .ev-sc-form-grid{grid-template-columns:1fr}}

      /* Capa final UX/UI EV: panel de cotización premium y consistencia comprador/vendedor. */
      #ev-sc-shell .ev-sc-side{padding:16px;background:linear-gradient(180deg,#FCFDFC 0%,#F6FAF7 100%)}
      #ev-sc-shell .ev-sc-card{padding:15px;border-color:#E2ECE5;background:rgba(255,255,255,.96);box-shadow:0 12px 28px rgba(15,23,42,.055)}
      #ev-sc-shell .ev-sc-state{align-items:center}
      #ev-sc-shell .ev-sc-state strong{font-size:.95rem;letter-spacing:-.01em}
      #ev-sc-shell .ev-sc-state-dot{width:11px;height:11px;flex:0 0 auto;border-radius:999px;background:#22C55E;border:3px solid #DCFCE7;box-shadow:0 0 0 1px rgba(22,163,74,.17)}
      #ev-sc-shell .ev-sc-quote-editor{padding:14px;overflow:visible}
      #ev-sc-shell .ev-sc-quote-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:9px}
      #ev-sc-shell .ev-sc-quote-title{margin:2px 0 0;color:#0F592F;font-size:1.05rem;line-height:1.15;font-weight:950;letter-spacing:-.018em}
      #ev-sc-shell .ev-sc-quote-badge{display:inline-flex;align-items:center;gap:4px;flex:0 0 auto;padding:5px 8px;border:1px solid rgba(234,124,18,.26);border-radius:999px;color:#9A4A05;background:#FFF7ED;font-size:.67rem;font-weight:900}
      #ev-sc-shell .ev-sc-form-note{margin:0 0 11px;padding:11px 12px;border-color:rgba(234,124,18,.32);border-radius:14px;background:linear-gradient(135deg,#FFF8ED,#FFFDF9);color:#A34A0B;font-size:.73rem;line-height:1.48}
      #ev-sc-shell .ev-sc-quote-form{gap:11px}
      #ev-sc-shell .ev-sc-quote-section{display:grid;gap:9px;padding:11px;border:1px solid #E6EFE8;border-radius:15px;background:linear-gradient(180deg,#FFFFFF,#FBFDFC)}
      #ev-sc-shell .ev-sc-quote-section-title{display:flex;align-items:center;gap:7px;color:#0F592F;font-size:.72rem;font-weight:950;letter-spacing:.045em;text-transform:uppercase}
      #ev-sc-shell .ev-sc-quote-section-title i{color:#EA7C12;font-size:.85rem}
      #ev-sc-shell .ev-sc-quote-section:first-of-type{border-color:rgba(22,163,74,.22);background:linear-gradient(180deg,#FFFFFF,#F7FCF8)}
      #ev-sc-shell .ev-sc-quote-section:first-of-type textarea{min-height:112px}
      #ev-sc-shell .ev-sc-form label{gap:6px;color:#344B3C;font-size:.73rem;line-height:1.25}
      #ev-sc-shell .ev-sc-form input,#ev-sc-shell .ev-sc-form select,#ev-sc-shell .ev-sc-form textarea{min-height:42px;padding:10px 11px;border-color:#D7E4DA;border-radius:12px;box-shadow:inset 0 1px 1px rgba(15,23,42,.02);transition:border-color .16s ease,box-shadow .16s ease,background .16s ease}
      #ev-sc-shell .ev-sc-form textarea{min-height:94px;line-height:1.48}
      #ev-sc-shell .ev-sc-form input:focus,#ev-sc-shell .ev-sc-form select:focus,#ev-sc-shell .ev-sc-form textarea:focus{border-color:#16A34A;box-shadow:0 0 0 3px rgba(22,163,74,.10);background:#FFFFFF;outline:none}
      #ev-sc-shell .ev-sc-payment-field{margin-top:1px}
      #ev-sc-shell .ev-sc-form-grid{gap:10px}
      #ev-sc-shell .ev-sc-field-help{margin-top:1px;color:#9A5B12;font-size:.66rem;line-height:1.38}
      #ev-sc-shell .ev-sc-payment-summary{margin-top:0;padding:11px 12px;border-radius:13px;background:linear-gradient(135deg,#ECFDF3,#F8FFFA)}
      #ev-sc-shell .ev-sc-quote-upload{display:grid;grid-template-columns:36px minmax(0,1fr) auto;align-items:center;gap:9px;width:100%;padding:10px;border:1px dashed rgba(22,163,74,.42);border-radius:14px;background:linear-gradient(135deg,#F3FFF7,#FFFFFF);color:#0F592F;text-align:left;cursor:pointer;transition:.16s ease}
      #ev-sc-shell .ev-sc-quote-upload:hover{transform:translateY(-1px);border-color:#16A34A;box-shadow:0 9px 18px rgba(15,89,47,.08)}
      #ev-sc-shell .ev-sc-quote-upload-icon{width:36px;height:36px;display:grid;place-items:center;border-radius:11px;background:#DCFCE7;color:#15803D}
      #ev-sc-shell .ev-sc-quote-upload b{display:block;font-size:.74rem;font-weight:900}
      #ev-sc-shell .ev-sc-quote-upload small{display:block;margin-top:2px;color:#6B7E70;font-size:.64rem;line-height:1.32}
      #ev-sc-shell .ev-sc-quote-upload>i{font-size:1rem;color:#16A34A}
      #ev-sc-shell .ev-sc-quote-files{display:grid;grid-template-columns:repeat(auto-fill,minmax(82px,1fr));gap:7px}
      #ev-sc-shell .ev-sc-quote-file{overflow:hidden;border:1px solid #DDEBE1;border-radius:11px;background:#FFFFFF}
      #ev-sc-shell .ev-sc-quote-file img{display:block;width:100%;height:66px;object-fit:cover;background:#F1F5F9}
      #ev-sc-shell .ev-sc-quote-file span{display:block;padding:6px 6px 1px;color:#315240;font-size:.63rem;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
      #ev-sc-shell .ev-sc-quote-file small{display:block;padding:0 6px 6px;color:#94A3B8;font-size:.58rem;font-weight:700}
      #ev-sc-shell .ev-sc-quote-actions{position:sticky;bottom:-1px;z-index:3;display:grid;gap:8px;margin:2px -1px -1px;padding:10px 0 0;background:linear-gradient(180deg,rgba(252,253,252,0),#FCFDFC 18%)}
      #ev-sc-shell .ev-sc-quote-send{margin-top:0;min-height:46px;border-radius:14px;font-size:.82rem;box-shadow:0 13px 24px rgba(217,119,6,.24)}
      #ev-sc-shell .ev-sc-quote-back{margin-top:0;border-radius:14px;background:#FFFFFF}
      #ev-sc-shell .ev-sc-compose{border-top-color:#DDEAE0}
      #ev-sc-shell .ev-sc-context{padding:14px 18px}
      #ev-sc-shell .ev-sc-service{gap:12px}
      #ev-sc-shell .ev-sc-cover{width:52px;height:52px;border-radius:16px;box-shadow:0 7px 14px rgba(15,23,42,.07)}
      #ev-sc-shell .ev-sc-chip{padding:5px 9px;font-size:.67rem}
      #ev-sc-shell .ev-sc-system{max-width:680px;border-color:#D9EADF;background:linear-gradient(135deg,rgba(255,255,255,.97),rgba(246,252,248,.97))}
      #ev-sc-shell .ev-sc-pics a{width:78px;height:78px;border-radius:13px;box-shadow:0 5px 12px rgba(15,23,42,.08)}
      #ev-sc-shell .ev-sc-action.blue{border-color:#BFD7FF;background:linear-gradient(135deg,#EFF6FF,#F9FBFF)}
      #ev-sc-shell .ev-sc-action.danger{background:linear-gradient(135deg,#FFF6F6,#FFFDFD)}
      #ev-sc-shell .ev-sc-action.orange:hover{background:linear-gradient(135deg,#C86B05,#EA7C12);box-shadow:0 14px 25px rgba(217,119,6,.30)}
      @media(max-width:900px){
        #ev-sc-shell .ev-sc-side{padding:12px}
        #ev-sc-shell .ev-sc-quote-actions{position:static;background:transparent}
      }
      @media(max-width:560px){
        #ev-sc-shell .ev-sc-quote-section{padding:10px}
        #ev-sc-shell .ev-sc-quote-upload{grid-template-columns:34px minmax(0,1fr)}
        #ev-sc-shell .ev-sc-quote-upload>i{display:none}
        #ev-sc-shell .ev-sc-form-grid{grid-template-columns:1fr}
      }
    `;

    const root = document.createElement('div');
    root.id = 'ev-sc-shell';
    root.innerHTML = `
      <style>${css}</style>
      <section class="ev-sc-modal" role="dialog" aria-modal="true" aria-label="Conversación de servicio">
        <header class="ev-sc-head">
          <div class="ev-sc-head-left">
            <div class="ev-sc-icon"><i class="bi bi-chat-square-text"></i></div>
            <div>
              <div id="evScTitle" class="ev-sc-title">Conversación de servicio</div>
              <div id="evScSub" class="ev-sc-sub">Cargando…</div>
            </div>
          </div>
          <button type="button" class="ev-sc-close" aria-label="Cerrar">×</button>
        </header>
        <div class="ev-sc-grid">
          <main class="ev-sc-main">
            <div id="evScContext" class="ev-sc-context"></div>
            <div id="evScThread" class="ev-sc-thread"></div>
            <footer id="evScCompose" class="ev-sc-compose"></footer>
          </main>
          <aside id="evScSide" class="ev-sc-side"></aside>
        </div>
      </section>
    `;
    document.body.appendChild(root);
    root.querySelector('.ev-sc-close').addEventListener('click', close);
    // La conversación es un expediente de negociación: solo se cierra con la X.
    document.addEventListener('keydown', (event) => {
      if (
        event.key === 'Escape'
        && modal?.classList.contains('ev-show')
        && !(window.Swal?.isVisible?.())
      ) {
        event.preventDefault();
        event.stopPropagation();
      }
    }, true);
    modal = root;
  }

  function filesHtml(items) {
    const files = Array.isArray(items) ? items : [];
    if (!files.length) return '';
    return `<div class="ev-sc-pics">${files.map((file) => `
      <a href="${esc(imageUrl(file.ruta))}" target="_blank" rel="noopener">
        <img src="${esc(imageUrl(file.ruta))}" alt="${esc(file.nombre_original || 'Imagen adjunta')}">
      </a>
    `).join('')}</div>`;
  }

  function quoteHtml(quote) {
    const isCurrent = ['vigente', 'aceptada', 'requiere_actualizacion'].includes(String(quote.estado || ''));
    const expiry = quote.fecha_vencimiento ? date(quote.fecha_vencimiento, true) : '—';
    const adelanto = Number(quote.monto_adelanto || 0);
    const tieneAdelanto = String(quote.condicion_pago || '') === 'adelanto_acordado' && adelanto > 0;
    const horas = quoteTimeRange(quote);
    const duracion = String(quote.duracion_estimada || durationFromTimes(quote.hora_inicio, quote.hora_fin) || '').trim();

    return `
      <article class="ev-sc-proposal ${isCurrent ? 'is-current' : ''}">
        <div class="ev-sc-proposal-head">
          <h4><i class="bi bi-file-earmark-check"></i> Cotización final · Versión ${Number(quote.version || 1)}</h4>
          <span class="ev-sc-proposal-state">${esc(labelEstadoCotizacion(quote.estado))}</span>
        </div>
        <div class="ev-sc-proposal-grid">
          <div class="ev-sc-proposal-box ev-sc-proposal-box-total"><span>Precio final total</span><b>${esc(money(quote.monto_propuesto))}</b></div>
          <div class="ev-sc-proposal-box"><span>Condición de pago</span><b>${esc(labelPago(quote.condicion_pago))}</b></div>
          ${tieneAdelanto ? `<div class="ev-sc-proposal-box"><span>Adelanto acordado</span><b>${esc(money(quote.monto_adelanto))}</b></div>` : ''}
          ${tieneAdelanto ? `<div class="ev-sc-proposal-box"><span>Saldo contra entrega</span><b>${esc(money(quote.saldo_contra_entrega))}</b></div>` : ''}
        </div>
        <p><b>Servicio incluido y condiciones acordadas:</b> ${esc(quote.alcance_confirmado || '—')}</p>
        ${quote.fecha_propuesta ? `<p><b>Fecha acordada:</b> ${esc(date(quote.fecha_propuesta))}</p>` : ''}
        ${horas ? `<p><b>Horario:</b> ${esc(horas)}</p>` : ''}
        ${duracion ? `<p><b>Duración estimada:</b> ${esc(duracion)}</p>` : ''}
        ${quote.mensaje_proveedor ? `<p><b>Mensaje:</b> ${esc(quote.mensaje_proveedor)}</p>` : ''}
        ${quote.fecha_vencimiento && quote.estado === 'vigente' ? `<p><b>Vigencia:</b> hasta ${esc(expiry)}</p>` : ''}
        ${quote.motivo_estado ? `<p><b>Actualización:</b> ${esc(quote.motivo_estado)}</p>` : ''}
        ${filesHtml(quote.adjuntos)}
      </article>
    `;
  }

  function eventHtml(event, me) {
    const type = String(event.tipo_interaccion || '');
    const authorIsMe = event.rol_autor === me;

    const specials = {
      solicitud_creada: ['Solicitud enviada', 'El comprador inició esta solicitud de cotización.'],
      cotizacion_final_enviada: ['Cotización final emitida', 'El proveedor envió una cotización formal para revisión.'],
      cotizacion_final_aceptada: ['Cotización aceptada', 'La coordinación comercial quedó confirmada.'],
      ajuste_cotizacion_solicitado: ['Ajuste solicitado', event.mensaje || 'El comprador solicitó una nueva versión de cotización.'],
      cotizacion_final_rechazada: ['Cotización rechazada', event.mensaje || 'La solicitud quedó cerrada.'],
      servicio_marcado_realizado: ['Servicio marcado como realizado', 'El proveedor espera la confirmación del comprador.'],
      servicio_confirmado: ['Servicio confirmado', 'El comprador confirmó la realización del servicio.'],
      observacion_reportada: ['Observación registrada', event.mensaje || 'La observación quedó registrada en el historial.'],
      solicitud_cancelada: ['Coordinación cancelada', event.mensaje || 'La coordinación fue cancelada.'],
      coordinacion_cancelada_proveedor: ['Coordinación cancelada', event.mensaje || 'El proveedor canceló la coordinación.']
    };

    if (specials[type]) {
      const [title, text] = specials[type];
      return `<div class="ev-sc-system"><strong>${esc(title)}</strong><br>${esc(text)}${filesHtml(event.adjuntos)}</div>`;
    }

    return `
      <div class="ev-sc-message ${authorIsMe ? 'me' : ''}">
        <div class="ev-sc-bubble">
          <div class="ev-sc-author">${esc(authorIsMe ? 'Tú' : (event.nombre_autor || 'Vecino'))}</div>
          ${event.mensaje ? `<div class="ev-sc-text">${esc(event.mensaje)}</div>` : ''}
          ${filesHtml(event.adjuntos)}
          <div class="ev-sc-time">${esc(date(event.created_at, true))}</div>
        </div>
      </div>
    `;
  }

  function conversationActions(data) {
    const state = String(data.estado || '');
    const role = String(data.rol_actual || '');
    const actions = [];
    if (role === 'proveedor' && ['pendiente_proveedor', 'ajuste_solicitado', 'ajuste_cotizacion_solicitado', 'cotizacion_vencida'].includes(state)) {
      const textoBoton = state === 'ajuste_cotizacion_solicitado' ? 'Emitir nueva cotización final' : 'Emitir cotización final';
      actions.push(`<button type="button" class="ev-sc-action orange" data-sc-action="quote"><i class="bi bi-file-earmark-plus"></i> ${textoBoton}</button>`);
    }


    if (role === 'solicitante' && state === 'cotizacion_final_enviada') {
      actions.push(`<button type="button" class="ev-sc-action" data-sc-action="accept"><i class="bi bi-check2-circle"></i> Aceptar cotización final</button>`);
      actions.push(`<button type="button" class="ev-sc-action blue" data-sc-action="adjust"><i class="bi bi-pencil-square"></i> Solicitar ajuste</button>`);
      actions.push(`<button type="button" class="ev-sc-action danger" data-sc-action="reject"><i class="bi bi-x-circle"></i> Rechazar cotización</button>`);
    }
    const estadosOperacion = [
      'coordinacion_confirmada',
      'servicio_en_ejecucion',
      'servicio_realizado_proveedor',
      'incidencia_abierta',
      'incidencia_en_atencion',
      'solucion_pendiente_confirmacion',
      'revision_soporte',
      'servicio_confirmado_solicitante'
    ];
    if (estadosOperacion.includes(state)) {
      actions.push(`<button type="button" class="ev-sc-action orange" data-sc-action="manage"><i class="bi bi-clipboard2-check"></i> Gestionar servicio</button>`);
    }
    if (role === 'solicitante' && ['pendiente_proveedor', 'ajuste_solicitado', 'ajuste_cotizacion_solicitado', 'cotizacion_vencida'].includes(state)) {
      actions.push(`<button type="button" class="ev-sc-action danger" data-sc-action="cancel-buyer"><i class="bi bi-x-circle"></i> Cancelar solicitud</button>`);
    }

    return actions.join('');
  }

  function renderComposer(data) {
    const composer = document.getElementById('evScCompose');
    if (!composer) return;

    if (!data.puede_enviar_mensajes) {
      composer.innerHTML = `<div class="ev-sc-helper">Esta conversación se conserva como historial y ya no admite mensajes nuevos.</div>`;
      return;
    }

    composer.innerHTML = `
      <div id="evScFiles" class="ev-sc-files"></div>
      <div class="ev-sc-compose-label"><span>Mensaje de negociación</span><small>Enter para enviar · Shift + Enter para una nueva línea</small></div>
      <div class="ev-sc-compose-row">
        <input id="evScAttach" type="file" accept="image/jpeg,image/png,image/webp" multiple hidden>
        <button id="evScAttachButton" type="button" class="ev-sc-attach" title="Adjuntar imágenes" aria-label="Adjuntar imágenes"><i class="bi bi-paperclip"></i></button>
        <textarea id="evScMessage" class="ev-sc-input" maxlength="1500" aria-label="Mensaje de negociación"></textarea>
        <button id="evScSend" type="button" class="ev-sc-send"><i class="bi bi-send"></i><span>Enviar</span></button>
      </div>
      <div class="ev-sc-helper">Toda la negociación, incluida la ubicación cuando corresponda, debe mantenerse dentro de EV · JPG, PNG o WEBP · máximo 5 imágenes de 5 MB.</div>
    `;
  }

  function estaCercaDelFinal(threadEl, tolerancia = 96) {
    if (!threadEl) return true;
    return (threadEl.scrollHeight - threadEl.scrollTop - threadEl.clientHeight) <= tolerancia;
  }

  function render(data) {
    currentData = data;

    const threadEl = document.getElementById('evScThread');
    const mantenerAlFinal = !threadEl || estaCercaDelFinal(threadEl);

    document.getElementById('evScTitle').textContent = data.titulo_servicio || 'Conversación de servicio';
    document.getElementById('evScSub').textContent = `Solicitud #${Number(data.codigo_solicitud_servicio || 0)} · Negociación privada`;
    document.getElementById('evScContext').innerHTML = `
      <div class="ev-sc-service">
        <img class="ev-sc-cover" src="${esc(imageUrl(data.imagen_portada))}" alt="">
        <div>
          <div class="ev-sc-name">${esc(data.titulo_servicio || 'Servicio')}</div>
          <div class="ev-sc-meta">${esc(data.categoria_grupo_nombre || data.categoria_nombre || 'Servicio entre vecinos')}</div>
          <span class="ev-sc-chip"><i class="bi bi-shield-check"></i> Todo lo acordado queda registrado en EV</span>
        </div>
      </div>
    `;

    const me = data.rol_actual;
    const thread = (Array.isArray(data.interacciones) ? data.interacciones : [])
      .map((event) => eventHtml(event, me)).join('');
    threadEl.innerHTML = thread || `<div class="ev-sc-system"><strong>Conversación lista</strong><br>Usa este espacio para negociar el servicio, referencias, fecha, horario, ubicación y todos los detalles antes de emitir la cotización final.</div>`;

    const quotes = Array.isArray(data.propuestas) ? data.propuestas : [];
    const side = document.getElementById('evScSide');
    side.innerHTML = `
      <section class="ev-sc-card">
        <div class="ev-sc-kicker">Estado de la solicitud</div>
        <div class="ev-sc-state">
          <strong>${esc(labelEstado(data.estado))}</strong>
          <span class="ev-sc-state-dot" aria-label="Estado activo"></span>
        </div>
      </section>

      <section class="ev-sc-card">
        <div class="ev-sc-kicker">Cotizaciones finales</div>
        ${String(data.estado || '') === 'ajuste_cotizacion_solicitado' && data.motivo_estado ? `<div class="ev-sc-system"><strong>Ajuste solicitado por el comprador</strong><br>${esc(data.motivo_estado)}</div>` : ''}
        ${quotes.length ? quotes.map(quoteHtml).join('') : `<div class="ev-sc-helper">Aún no se emitió una cotización final. Primero conversen y aclaren las condiciones.</div>`}
        ${conversationActions(data)}
      </section>
    `;

    renderComposer(data);
    bindControls();

    if (mantenerAlFinal) {
      requestAnimationFrame(() => { threadEl.scrollTop = threadEl.scrollHeight; });
    }
  }

  function selectedFiles() {
    return Array.from(document.getElementById('evScAttach')?.files || []);
  }

  function renderFiles() {
    const holder = document.getElementById('evScFiles');
    if (!holder) return;
    holder.innerHTML = selectedFiles().map((file) => `<span class="ev-sc-file"><i class="bi bi-image"></i>${esc(file.name)}</span>`).join('');
  }

  function idSolicitudActivo() {
    const id = Number(currentId || 0);
    if (!Number.isInteger(id) || id <= 0) {
      throw new Error('No se pudo identificar la solicitud. Cierra y vuelve a abrir la conversación.');
    }
    return id;
  }

  function ocultarConversacionParaDialogo() {
    const estabaVisible = Boolean(modal?.classList.contains('ev-show'));
    if (estabaVisible) {
      modal.classList.remove('ev-show');
    }
    dialogoEnCurso = true;
    return estabaVisible;
  }

  function restaurarConversacionDespuesDeDialogo(estabaVisible) {
    dialogoEnCurso = false;
    if (estabaVisible && modal && currentId > 0) {
      modal.classList.add('ev-show');
    }
  }

  async function request(url, body) {
    const response = await fetch(url, {
      method: 'POST',
      credentials: 'include',
      headers: { Accept: 'application/json' },
      body
    });
    const json = await response.json().catch(() => ({}));
    if (!response.ok || json?.ok === false) {
      throw new Error(json?.mensaje || 'No se pudo procesar la operación.');
    }
    return json;
  }

  async function load(silent = false) {
    if (!currentId) return;
    try {
      const response = await fetch(`${BASE}/api/servicios/solicitudes/${currentId}/conversacion`, {
        credentials: 'include',
        headers: { Accept: 'application/json' },
        cache: 'no-store'
      });
      const json = await response.json().catch(() => ({}));
      if (!response.ok || json?.ok === false) {
        throw new Error(json?.mensaje || 'No se pudo cargar la conversación.');
      }
      render(json.data || {});
    } catch (error) {
      if (!silent) toast('error', error.message || 'No se pudo cargar la conversación.');
    }
  }

  function toast(type, text) {
    if (window.Swal?.fire) {
      return Swal.fire({
        icon: type === 'error' ? 'error' : type === 'warning' ? 'warning' : 'info',
        title: type === 'error' ? 'No se pudo continuar' : 'Revisa esta información',
        text,
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#EA7C12',
        allowOutsideClick: false,
        allowEscapeKey: true
      });
    }
    window.alert(text);
    return Promise.resolve();
  }

  async function mostrarAlertaCotizacion(message) {
    const estabaVisible = ocultarConversacionParaDialogo();
    try {
      if (window.Swal?.fire) {
        await Swal.fire({
          icon: 'warning',
          title: 'Revisa esta información',
          text: message,
          confirmButtonText: 'Entendido',
          confirmButtonColor: '#EA7C12',
          allowOutsideClick: false,
          allowEscapeKey: false
        });
      } else {
        window.alert(message);
      }
    } finally {
      restaurarConversacionDespuesDeDialogo(estabaVisible);
    }
  }

  async function ask(title, options = {}) {
    if (!window.Swal?.fire) {
      return { isConfirmed: window.confirm(options.text || title), value: options.defaultValue || '' };
    }
    return Swal.fire({
      title,
      text: options.text || '',
      input: options.input || undefined,
      inputValue: options.defaultValue || '',
      inputLabel: options.inputLabel || '',
      inputPlaceholder: options.placeholder || '',
      inputAttributes: options.inputAttributes || {},
      showCancelButton: true,
      confirmButtonText: options.confirmText || 'Continuar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#EA7C12',
      preConfirm: options.preConfirm
    });
  }

  async function ejecutarAccionConConfirmacion(titulo, opciones, ejecutar) {
    if (accionEnCurso) return false;

    const codigoSolicitud = idSolicitudActivo();
    const estabaVisible = ocultarConversacionParaDialogo();

    let resultado;
    try {
      resultado = await ask(titulo, opciones);
    } catch (error) {
      restaurarConversacionDespuesDeDialogo(estabaVisible);
      throw error;
    }

    if (!resultado?.isConfirmed) {
      restaurarConversacionDespuesDeDialogo(estabaVisible);
      return false;
    }

    accionEnCurso = true;
    try {
      await ejecutar(resultado, codigoSolicitud);
      restaurarConversacionDespuesDeDialogo(estabaVisible);
      await load(true);
      return true;
    } catch (error) {
      // El modal permanece oculto mientras se comunica el error para no superponer capas.
      await toast('error', error?.message || 'No se pudo procesar la operación.');
      restaurarConversacionDespuesDeDialogo(estabaVisible);
      return false;
    } finally {
      accionEnCurso = false;
    }
  }

  async function sendMessage() {
    const text = String(document.getElementById('evScMessage')?.value || '').trim();
    const files = selectedFiles();

    if (!text && !files.length) {
      toast('warning', 'Escribe un mensaje o adjunta al menos una imagen.');
      return;
    }
    if (files.length > 5 || files.some((file) => file.size > 5242880 || !['image/jpeg', 'image/png', 'image/webp'].includes(file.type))) {
      toast('warning', 'Solo puedes adjuntar hasta 5 imágenes JPG, PNG o WEBP de máximo 5 MB cada una.');
      return;
    }

    const fd = new FormData();
    fd.append('mensaje', text);
    files.forEach((file) => fd.append('adjuntos_mensaje[]', file));

    const codigoSolicitud = idSolicitudActivo();
    await request(`${BASE}/api/servicios/solicitudes/${codigoSolicitud}/mensajes`, fd);
    await load(true);
  }

  function quoteForm() {
    const side = document.getElementById('evScSide');
    if (!side) return;

    side.innerHTML = `
      <section class="ev-sc-card ev-sc-quote-editor">
        <div class="ev-sc-quote-top">
          <div>
            <div class="ev-sc-kicker">Emitir cotización final</div>
            <h3 class="ev-sc-quote-title">Formaliza el acuerdo</h3>
          </div>
          <span class="ev-sc-quote-badge"><i class="bi bi-file-earmark-check"></i> 72 h</span>
        </div>

        <div class="ev-sc-form-note">
          Esta cotización registrará el acuerdo negociado y reemplazará cualquier versión vigente anterior.
        </div>

        <div id="evScQuoteForm" class="ev-sc-form ev-sc-quote-form">
          <section class="ev-sc-quote-section">
            <div class="ev-sc-quote-section-title"><i class="bi bi-clipboard2-check"></i> Acuerdo comercial</div>
            <label>Servicio incluido y condiciones acordadas
              <textarea name="alcance_confirmado" maxlength="2500" required></textarea>
            </label>
          </section>

          <section class="ev-sc-quote-section">
            <div class="ev-sc-quote-section-title"><i class="bi bi-cash-stack"></i> Precio y pago</div>
            <label>Precio final total (S/)
              <input name="monto_propuesto" type="number" min="0.01" step="0.01" required inputmode="decimal">
              <small class="ev-sc-field-help">Debe incluir todos los costos acordados: traslado, materiales, instalación u otros.</small>
            </label>

            <label class="ev-sc-payment-field">Condición de pago
              <select name="condicion_pago" id="evScCondicionPago">
                <option value="contra_entrega">Pago contra entrega</option>
                <option value="adelanto_acordado">Adelanto acordado</option>
              </select>
            </label>

            <div id="evScAdelantoWrap" class="ev-sc-hidden">
              <label>Monto de adelanto (S/)
                <input name="monto_adelanto" id="evScMontoAdelanto" type="number" min="0.01" step="0.01" inputmode="decimal">
              </label>
              <div class="ev-sc-payment-summary">
                <span>Saldo contra entrega</span>
                <strong id="evScSaldoContraEntrega">S/ 0.00</strong>
              </div>
            </div>
          </section>

          <section class="ev-sc-quote-section">
            <div class="ev-sc-quote-section-title"><i class="bi bi-calendar2-week"></i> Agenda del servicio</div>
            <label>Fecha acordada
              <input name="fecha_propuesta" type="date" required>
            </label>
            <div class="ev-sc-form-grid">
              <label>Hora de inicio <input name="hora_inicio" type="time"></label>
              <label>Hora de fin <input name="hora_fin" type="time"></label>
            </div>
            <label>Duración estimada <input name="duracion_estimada" id="evScDuracionEstimada" maxlength="180" readonly></label>
          </section>

          <section class="ev-sc-quote-section">
            <div class="ev-sc-quote-section-title"><i class="bi bi-images"></i> Mensaje y referencias</div>
            <label>Mensaje final para el comprador
              <textarea name="mensaje_proveedor" maxlength="1500"></textarea>
            </label>

            <input id="evScQuoteAttach" type="file" accept="image/jpeg,image/png,image/webp" multiple hidden>
            <button id="evScQuoteAttachButton" type="button" class="ev-sc-quote-upload">
              <span class="ev-sc-quote-upload-icon"><i class="bi bi-image"></i></span>
              <span>
                <b>Adjuntar imágenes</b>
                <small>Opcional · máximo 5 imágenes JPG, PNG o WEBP de 5 MB.</small>
              </span>
              <i class="bi bi-plus-circle"></i>
            </button>
            <div id="evScQuoteFiles" class="ev-sc-quote-files"></div>
          </section>

          <div class="ev-sc-quote-actions">
            <button type="button" id="evScQuoteSend" class="ev-sc-action orange ev-sc-quote-send"><i class="bi bi-send-check"></i> Enviar cotización final</button>
            <button type="button" id="evScQuoteBack" class="ev-sc-action ev-sc-quote-back"><i class="bi bi-arrow-left"></i> Volver a la conversación</button>
          </div>
        </div>
      </section>
    `;

    const condicion = document.getElementById('evScCondicionPago');
    const adelantoWrap = document.getElementById('evScAdelantoWrap');
    const montoTotal = document.querySelector('#evScQuoteForm [name="monto_propuesto"]');
    const montoAdelanto = document.getElementById('evScMontoAdelanto');
    const saldo = document.getElementById('evScSaldoContraEntrega');
    const horaInicio = document.querySelector('#evScQuoteForm [name="hora_inicio"]');
    const horaFin = document.querySelector('#evScQuoteForm [name="hora_fin"]');
    const duracion = document.getElementById('evScDuracionEstimada');

    const actualizarDuracion = () => {
      if (!duracion) return;
      duracion.value = durationFromTimes(horaInicio?.value, horaFin?.value);
    };

    const actualizarPago = () => {
      const esAdelanto = condicion?.value === 'adelanto_acordado';
      adelantoWrap?.classList.toggle('ev-sc-hidden', !esAdelanto);
      if (!esAdelanto) return;

      const total = Math.max(0, Number(montoTotal?.value || 0));
      const adelanto = Math.max(0, Number(montoAdelanto?.value || 0));
      if (saldo) saldo.textContent = money(Math.max(0, total - adelanto));
    };

    condicion?.addEventListener('change', actualizarPago);
    montoTotal?.addEventListener('input', actualizarPago);
    montoAdelanto?.addEventListener('input', actualizarPago);
    horaInicio?.addEventListener('input', actualizarDuracion);
    horaFin?.addEventListener('input', actualizarDuracion);
    horaInicio?.addEventListener('change', actualizarDuracion);
    horaFin?.addEventListener('change', actualizarDuracion);

    document.getElementById('evScQuoteAttachButton')?.addEventListener('click', () => {
      document.getElementById('evScQuoteAttach')?.click();
    });
    document.getElementById('evScQuoteAttach')?.addEventListener('change', renderQuoteFiles);

    actualizarPago();
    actualizarDuracion();
    renderQuoteFiles();

    document.getElementById('evScQuoteBack')?.addEventListener('click', () => render(currentData));
    document.getElementById('evScQuoteSend')?.addEventListener('click', () => sendQuote().catch((error) => toast('error', error.message)));
  }

  function quoteSelectedFiles() {
    return Array.from(document.getElementById('evScQuoteAttach')?.files || []);
  }

  function renderQuoteFiles() {
    const holder = document.getElementById('evScQuoteFiles');
    if (!holder) return;

    const files = quoteSelectedFiles();
    if (!files.length) {
      holder.innerHTML = '';
      return;
    }

    holder.innerHTML = files.map((file, index) => {
      const url = URL.createObjectURL(file);
      return `
        <article class="ev-sc-quote-file">
          <img src="${esc(url)}" alt="${esc(file.name)}">
          <span>${esc(file.name)}</span>
          <small>Imagen ${index + 1}</small>
        </article>
      `;
    }).join('');
  }

  async function sendQuote() {
    const form = document.getElementById('evScQuoteForm');
    if (!form) return;

    const alcance = String(form.querySelector('[name="alcance_confirmado"]')?.value || '').trim();
    const montoTotal = Number(form.querySelector('[name="monto_propuesto"]')?.value || 0);
    const condicionPago = String(form.querySelector('[name="condicion_pago"]')?.value || '');
    const montoAdelanto = Number(form.querySelector('[name="monto_adelanto"]')?.value || 0);
    const fecha = String(form.querySelector('[name="fecha_propuesta"]')?.value || '').trim();
    const horaInicio = String(form.querySelector('[name="hora_inicio"]')?.value || '').trim();
    const horaFin = String(form.querySelector('[name="hora_fin"]')?.value || '').trim();
    const duracionCalculada = durationFromTimes(horaInicio, horaFin);
    const files = quoteSelectedFiles();

    if (alcance === '') {
      await mostrarAlertaCotizacion('Describe el servicio incluido y las condiciones acordadas.');
      return;
    }
    if (!Number.isFinite(montoTotal) || montoTotal <= 0) {
      await mostrarAlertaCotizacion('Ingresa un precio final total válido.');
      return;
    }
    if (!fecha) {
      await mostrarAlertaCotizacion('Indica la fecha acordada.');
      return;
    }
    if (horaInicio && horaFin && horaFin <= horaInicio) {
      await mostrarAlertaCotizacion('La hora de fin debe ser posterior a la hora de inicio.');
      return;
    }
    if (condicionPago === 'adelanto_acordado') {
      if (!Number.isFinite(montoAdelanto) || montoAdelanto <= 0) {
        await mostrarAlertaCotizacion('Ingresa el monto de adelanto acordado.');
        return;
      }
      if (montoAdelanto > montoTotal) {
        await mostrarAlertaCotizacion('El monto de adelanto no puede ser mayor que el precio final total.');
        return;
      }
    }
    if (files.length > 5 || files.some((file) => file.size > 5242880 || !['image/jpeg', 'image/png', 'image/webp'].includes(file.type))) {
      await mostrarAlertaCotizacion('Solo puedes adjuntar hasta 5 imágenes JPG, PNG o WEBP de máximo 5 MB.');
      return;
    }

    const fd = new FormData();
    ['alcance_confirmado', 'monto_propuesto', 'condicion_pago', 'monto_adelanto', 'fecha_propuesta', 'hora_inicio', 'hora_fin', 'mensaje_proveedor']
      .forEach((name) => fd.append(name, String(form.querySelector(`[name="${name}"]`)?.value || '').trim()));
    fd.append('duracion_estimada', duracionCalculada);
    files.forEach((file) => fd.append('adjuntos_propuesta[]', file));

    const codigoSolicitud = idSolicitudActivo();
    const boton = document.getElementById('evScQuoteSend');
    if (boton) {
      boton.disabled = true;
      boton.innerHTML = '<i class="bi bi-arrow-repeat"></i> Enviando cotización…';
    }

    try {
      await request(`${BASE}/api/servicios/solicitudes/${codigoSolicitud}/cotizacion-final`, fd);
      await load(true);
    } catch (error) {
      await mostrarAlertaCotizacion(error?.message || 'No se pudo enviar la cotización final.');
    } finally {
      if (boton && document.body.contains(boton)) {
        boton.disabled = false;
        boton.innerHTML = '<i class="bi bi-send-check"></i> Enviar cotización final';
      }
    }
  }

  async function acceptQuote() {
    await ejecutarAccionConConfirmacion('Aceptar cotización final', {
      text: 'Confirma solo si el servicio incluido, precio final total, fecha, horario y condición de pago son correctos.',
      confirmText: 'Aceptar cotización'
    }, async (_resultado, codigoSolicitud) => {
      await request(`${BASE}/api/servicios/solicitudes/${codigoSolicitud}/aceptar-cotizacion-final`, new URLSearchParams());
    });
  }

  async function adjustQuote() {
    await ejecutarAccionConConfirmacion('Solicitar ajuste', {
      input: 'textarea',
      inputLabel: 'Detalle del ajuste solicitado',
      confirmText: 'Enviar ajuste',
      inputAttributes: { maxlength: 1500 },
      preConfirm: (value) => {
        const text = String(value || '').trim();
        if (text.length < 8) {
          Swal.showValidationMessage('Explica el ajuste con al menos 8 caracteres.');
          return false;
        }
        return text;
      }
    }, async (resultado, codigoSolicitud) => {
      await request(`${BASE}/api/servicios/solicitudes/${codigoSolicitud}/solicitar-ajuste-cotizacion`, new URLSearchParams({ mensaje: resultado.value }));
    });
  }

  async function rejectQuote() {
    await ejecutarAccionConConfirmacion('Rechazar cotización final', {
      input: 'textarea',
      inputLabel: 'Motivo del rechazo',
      confirmText: 'Rechazar cotización',
      inputAttributes: { maxlength: 500 },
      preConfirm: (value) => {
        const text = String(value || '').trim();
        if (text.length < 5) {
          Swal.showValidationMessage('Indica un motivo breve.');
          return false;
        }
        return text;
      }
    }, async (resultado, codigoSolicitud) => {
      await request(`${BASE}/api/servicios/solicitudes/${codigoSolicitud}/rechazar-cotizacion-final`, new URLSearchParams({ motivo: resultado.value }));
    });
  }

  async function cancel(role) {
    await ejecutarAccionConConfirmacion('Cancelar coordinación', {
      input: 'textarea',
      inputLabel: 'Motivo de cancelación',
      confirmText: 'Cancelar coordinación',
      inputAttributes: { maxlength: 500 },
      preConfirm: (value) => {
        const text = String(value || '').trim();
        if (text.length < 5) {
          Swal.showValidationMessage('Indica un motivo de al menos 5 caracteres.');
          return false;
        }
        return text;
      }
    }, async (resultado, codigoSolicitud) => {
      const endpoint = role === 'proveedor' ? 'cancelar-proveedor' : 'cancelar';
      await request(`${BASE}/api/servicios/solicitudes/${codigoSolicitud}/${endpoint}`, new URLSearchParams({ motivo: resultado.value }));
    });
  }

  async function markCompleted() {
    await ejecutarAccionConConfirmacion('Marcar servicio realizado', {
      text: 'Úsalo solo cuando hayas completado el servicio acordado. El comprador deberá confirmarlo o registrar una observación.',
      confirmText: 'Sí, marcar realizado'
    }, async (_resultado, codigoSolicitud) => {
      await request(`${BASE}/api/servicios/solicitudes/${codigoSolicitud}/marcar-realizado`, new URLSearchParams());
    });
  }

  async function confirmCompleted() {
    await ejecutarAccionConConfirmacion('Confirmar servicio realizado', {
      text: 'Confirma únicamente si el servicio se realizó según la cotización final aceptada.',
      confirmText: 'Confirmar servicio'
    }, async (_resultado, codigoSolicitud) => {
      await request(`${BASE}/api/servicios/solicitudes/${codigoSolicitud}/confirmar-realizado`, new URLSearchParams());
    });
  }

  async function reportIssue() {
    await ejecutarAccionConConfirmacion('Reportar observación', {
      input: 'textarea',
      inputLabel: 'Detalle de la observación',
      confirmText: 'Registrar observación',
      inputAttributes: { maxlength: 1500 },
      preConfirm: (value) => {
        const text = String(value || '').trim();
        if (text.length < 8) {
          Swal.showValidationMessage('Describe la observación con al menos 8 caracteres.');
          return false;
        }
        return text;
      }
    }, async (resultado, codigoSolicitud) => {
      await request(`${BASE}/api/servicios/solicitudes/${codigoSolicitud}/reportar-observacion`, new URLSearchParams({ mensaje: resultado.value }));
    });
  }

  async function openServiceOperation() {
    const idSolicitud = Number(currentId || 0);
    if (!idSolicitud) return;

    if (!window.EVServicioOperacion?.open) {
      const id = 'ev-servicio-operacion-script';
      let script = document.getElementById(id);
      if (!script) {
        script = document.createElement('script');
        script.id = id;
        script.src = `${BASE}/views/js/servicioOperacion.js`;
        document.head.appendChild(script);
      }
      await new Promise((resolve) => {
        if (window.EVServicioOperacion?.open) return resolve();
        script.addEventListener('load', resolve, { once: true });
        script.addEventListener('error', resolve, { once: true });
        window.setTimeout(resolve, 3500);
      });
    }

    if (!window.EVServicioOperacion?.open) {
      await toast('error', 'No se pudo cargar la gestión del servicio.');
      return;
    }

    close();
    window.EVServicioOperacion.open(idSolicitud);
  }

  function bindControls() {
    document.getElementById('evScAttachButton')?.addEventListener('click', () => document.getElementById('evScAttach')?.click());
    document.getElementById('evScAttach')?.addEventListener('change', renderFiles);
    document.getElementById('evScSend')?.addEventListener('click', () => sendMessage().catch((error) => toast('error', error.message)));
    document.getElementById('evScMessage')?.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        document.getElementById('evScSend')?.click();
      }
    });

    document.querySelector('[data-sc-action="quote"]')?.addEventListener('click', quoteForm);
    document.querySelector('[data-sc-action="accept"]')?.addEventListener('click', () => acceptQuote().catch((error) => toast('error', error.message)));
    document.querySelector('[data-sc-action="adjust"]')?.addEventListener('click', () => adjustQuote().catch((error) => toast('error', error.message)));
    document.querySelector('[data-sc-action="reject"]')?.addEventListener('click', () => rejectQuote().catch((error) => toast('error', error.message)));
    document.querySelector('[data-sc-action="cancel-buyer"]')?.addEventListener('click', () => cancel('solicitante').catch((error) => toast('error', error.message)));
    document.querySelector('[data-sc-action="cancel-provider"]')?.addEventListener('click', () => cancel('proveedor').catch((error) => toast('error', error.message)));
    document.querySelector('[data-sc-action="completed"]')?.addEventListener('click', () => markCompleted().catch((error) => toast('error', error.message)));
    document.querySelector('[data-sc-action="confirm-completed"]')?.addEventListener('click', () => confirmCompleted().catch((error) => toast('error', error.message)));
    document.querySelector('[data-sc-action="issue"]')?.addEventListener('click', () => reportIssue().catch((error) => toast('error', error.message)));
    document.querySelector('[data-sc-action="manage"]')?.addEventListener('click', () => openServiceOperation().catch((error) => toast('error', error.message)));
  }

  function editing() {
    if (document.getElementById('evScQuoteForm')) return true;
    const msg = document.getElementById('evScMessage');
    const files = document.getElementById('evScAttach');
    return Boolean(String(msg?.value || '').trim() || (files?.files?.length || 0));
  }

  function close() {
    modal?.classList.remove('ev-show');
    dialogoEnCurso = false;
    accionEnCurso = false;
    currentId = 0;
    currentData = null;
    if (pollTimer) {
      clearInterval(pollTimer);
      pollTimer = null;
    }
  }

  function open(id) {
    ensure();
    dialogoEnCurso = false;
    accionEnCurso = false;
    currentId = Number(id || 0);
    if (!currentId) return;
    modal.classList.add('ev-show');
    load();
    if (pollTimer) clearInterval(pollTimer);
    pollTimer = setInterval(() => {
      if (!currentId || dialogoEnCurso || document.hidden || editing()) return;
      load(true);
    }, POLL_MS);
  }

  window.EVServicioConversacion = {
    open,
    close,
    refresh: () => load()
  };
})();
