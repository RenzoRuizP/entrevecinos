// views/js/atenderServicios.js
(function () {
  'use strict';
  const KEY = '__EV_ATENDER_SERVICIOS_V1__';
  if (window[KEY]) { window.EVAtenderServicios?.init?.(); return; }
  window[KEY] = true;

  const BASE = String(window.EV?.baseUrl ?? window.BASE_URL ?? '').replace(/\/+$/, '');
  let estado = 'abiertas';
  let timer = null;
  let loading = false;
  let cache = new Map();

  const esc = (v) => String(v ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
  const fmt = (v, time = false) => {
    const raw = String(v || '').trim(); if (!raw) return '—';
    const d = new Date(raw.replace(' ','T')); if (Number.isNaN(d.getTime())) return raw;
    return d.toLocaleString('es-PE', time ? {day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'} : {day:'2-digit',month:'2-digit',year:'numeric'});
  };
  const money = (v) => Number.isFinite(Number(v)) ? `S/ ${Number(v).toLocaleString('es-PE',{minimumFractionDigits:2,maximumFractionDigits:2})}` : '—';
  const img = (v) => { const r=String(v||'').trim(); if(!r)return `${BASE}/public/img/placeholder-ev.png`; if(/^https?:/i.test(r))return r; return r.startsWith('/')?`${BASE}${r}`:`${BASE}/${r}`; };
  const badge = (s) => {
    const x=String(s||'');
    if(['resuelta','cerrada','cancelada'].includes(x)) return ['ev-as-badge ev-as-badge-done','Resuelta'];
    if(x==='esperando_informacion') return ['ev-as-badge ev-as-badge-wait','Esperando información'];
    return ['ev-as-badge ev-as-badge-open','Revisión de soporte'];
  };
  const swal = (o={}) => Object.assign({buttonsStyling:false,customClass:{popup:'ev-as-swal',title:'ev-as-swal-title',confirmButton:'ev-as-swal-confirm',cancelButton:'ev-as-swal-cancel'},allowOutsideClick:false},o);

  async function api(url, options={}) {
    const resp = await fetch(url,{credentials:'include',cache:'no-store',headers:{Accept:'application/json',...(options.headers||{})},...options});
    const json = await resp.json().catch(()=>({}));
    if(resp.status===401){window.location.href=`${BASE}/login`;throw new Error('Sesión finalizada.');}
    if(!resp.ok||json?.ok===false) throw new Error(json?.mensaje||'No se pudo completar la operación.');
    return json;
  }

  function scopeParams(){const s=window.EVAdminCommunityScope?.get?.('servicios')||{};const q=new URLSearchParams();if(s.selected){q.set('tipo_conjunto',s.tipo);q.set('codigo_comunidad',String(s.codigo));}return q;}

  function refs(){return {root:document.querySelector('.ev-as-page'),list:document.getElementById('evAsList'),loading:document.getElementById('evAsLoading'),empty:document.getElementById('evAsEmpty'),error:document.getElementById('evAsError'),search:document.getElementById('evAsSearch')};}

  async function loadSummary(){
    try{const sp=scopeParams();const j=await api(`${BASE}/api/soporte/servicios/resumen${sp.toString()?`?${sp}`:''}`);const d=j.data||{};document.getElementById('evAsKpiAbiertas').textContent=Number(d.abiertas||0);document.getElementById('evAsKpiPendientes').textContent=Number(d.pendientes||0);document.getElementById('evAsKpiResueltas').textContent=Number(d.resueltas_hoy||0);}catch(e){console.warn(e);}
  }

  function card(it){
    const b=badge(it.estado); const id=Number(it.codigo_incidencia||0);
    return `<article class="ev-as-case">
      <div class="ev-as-case-main">
        <div class="ev-as-case-top"><span class="ev-as-case-id">CASO #${id}</span><span class="ev-as-case-title">${esc(it.titulo_servicio||'Servicio')}</span><span class="${b[0]}">${b[1]}</span></div>
        <div class="ev-as-case-meta">
          <span><i class="bi bi-person"></i>Comprador: ${esc(it.nombre_comprador||'Vecino')}</span>
          <span><i class="bi bi-person-workspace"></i>Proveedor: ${esc(it.nombre_proveedor||'Vecino')}</span>
          <span><i class="bi bi-flag"></i>Reportó: ${esc(it.nombre_reporta||'Vecino')}</span>
          <span><i class="bi bi-clock"></i>${esc(fmt(it.fecha_escalamiento_soporte||it.updated_at,true))}</span>
        </div>
        <div class="ev-as-case-desc">${esc(it.descripcion||'')}</div>
      </div>
      <button type="button" class="btn ev-as-btn-detail" data-as-detail="${id}"><i class="bi bi-search me-1"></i>Revisar caso</button>
    </article>`;
  }

  async function load(silent=false){
    const r=refs(); if(!r.root||loading)return; loading=true;
    if(!silent){r.loading?.classList.remove('d-none');r.list.innerHTML='';r.empty?.classList.add('d-none');}
    r.error?.classList.add('d-none');
    try{
      const q=scopeParams();q.set('estado',estado);q.set('buscar',String(r.search?.value||'').trim());q.set('size','50');
      const j=await api(`${BASE}/api/soporte/servicios?${q}`);const arr=Array.isArray(j.data)?j.data:[];cache=new Map(arr.map(x=>[Number(x.codigo_incidencia),x]));
      r.list.innerHTML=arr.map(card).join('');r.empty?.classList.toggle('d-none',arr.length>0);
    }catch(e){r.error.textContent=e.message;r.error.classList.remove('d-none');}
    finally{r.loading?.classList.add('d-none');loading=false;}
  }

  const fileHtml=(files)=>Array.isArray(files)&&files.length?`<div class="ev-as-files">${files.map(f=>`<a class="ev-as-file" href="${esc(img(f.ruta))}" target="_blank" rel="noopener"><i class="bi ${String(f.mime||'').includes('pdf')?'bi-file-earmark-pdf':'bi-image'}"></i>${esc(f.nombre_original||'Archivo')}</a>`).join('')}</div>`:'';
  const timeRange=(c)=>{const a=String(c.hora_inicio_vigente||c.hora_inicio_cotizada||'').slice(0,5),b=String(c.hora_fin_vigente||c.hora_fin_cotizada||'').slice(0,5);return a&&b?`${a} – ${b}`:(a||'—');};

  function detailHtml(d){
    const c=d.caso||{}, files=d.adjuntos||[], reps=d.reprogramaciones||[], timeline=d.timeline||[];
    return `<div class="ev-as-detail">
      <div class="ev-as-detail-head"><img src="${esc(img(c.imagen_portada))}" alt=""><div><h4>${esc(c.titulo_servicio||'Servicio')}</h4><p>Solicitud #${Number(c.codigo_solicitud_servicio||0)} · Incidencia #${Number(c.codigo_incidencia||0)}</p><div class="ev-as-case-meta"><span><i class="bi bi-person"></i>${esc(c.nombre_comprador||'Vecino')}</span><span><i class="bi bi-person-workspace"></i>${esc(c.nombre_proveedor||'Vecino')}</span></div></div></div>
      <div class="ev-as-detail-grid">
        <div class="ev-as-detail-box"><span>Fecha vigente</span><strong>${esc(fmt(c.fecha_ejecucion_vigente||c.fecha_cotizada))}</strong></div>
        <div class="ev-as-detail-box"><span>Horario</span><strong>${esc(timeRange(c))}</strong></div>
        <div class="ev-as-detail-box"><span>Monto cotizado</span><strong>${esc(money(c.monto_propuesto))}</strong></div>
        <div class="ev-as-detail-box"><span>Condición de pago</span><strong>${esc(c.condicion_pago==='adelanto_acordado'?'Adelanto acordado':'Pago contra entrega')}</strong></div>
      </div>
      <section class="ev-as-section"><h6>Alcance aceptado</h6><p>${esc(c.alcance_confirmado||'—')}</p></section>
      <section class="ev-as-section"><h6>Problema reportado · ${esc(String(c.categoria||'').replaceAll('_',' '))}</h6><p>${esc(c.descripcion||'—')}</p>${fileHtml(files.filter(f=>f.contexto==='reporte'))}</section>
      ${c.respuesta?`<section class="ev-as-section"><h6>Respuesta registrada</h6><p>${esc(c.respuesta)}</p>${fileHtml(files.filter(f=>f.contexto==='respuesta'))}</section>`:''}
      ${c.solucion?`<section class="ev-as-section"><h6>Solución propuesta</h6><p>${esc(c.solucion)}</p>${fileHtml(files.filter(f=>f.contexto==='solucion'))}</section>`:''}
      ${reps.length?`<section class="ev-as-section"><h6>Reprogramaciones</h6><p>${reps.map(x=>`${fmt(x.fecha_anterior)} → ${fmt(x.fecha_nueva)} · ${String(x.hora_inicio_nueva||'').slice(0,5)} · ${x.estado}`).join('\n')}</p></section>`:''}
      <section class="ev-as-section"><h6>Historial de trazabilidad</h6><div class="ev-as-timeline">${timeline.map(x=>`<article class="ev-as-event"><strong>${esc(x.nombre_autor||x.rol_autor||'EV')} · ${esc(String(x.tipo_interaccion||'').replaceAll('_',' '))}</strong><p>${esc(x.mensaje||'')}</p><time>${esc(fmt(x.created_at,true))}</time></article>`).join('')}</div></section>
    </div>`;
  }

  async function resolve(id,title){
    const r=await Swal.fire(swal({title:'Registrar actuación de soporte',html:`<div class="ev-as-resolve-form"><label>Acción</label><select id="evAsAction"><option value="solicitar_informacion">Solicitar información adicional</option><option value="reanudar_atencion">Devolver a atención entre las partes</option><option value="confirmar_completado">Cerrar como servicio completado</option><option value="cancelar_servicio">Cerrar y cancelar el servicio</option></select><label>Fundamento o indicación</label><textarea id="evAsComment" maxlength="3000" placeholder="Explica la decisión o la información requerida."></textarea><div class="ev-as-resolve-note">Soporte no aplica sanciones en este punto. Los bloqueos, restricciones y medidas de moderación corresponden al punto 14.</div></div>`,showCancelButton:true,confirmButtonText:'Registrar',cancelButtonText:'Volver',preConfirm:()=>{const a=String(document.getElementById('evAsAction')?.value||''),c=String(document.getElementById('evAsComment')?.value||'').trim();if(c.length<8){Swal.showValidationMessage('Escribe una explicación de al menos 8 caracteres.');return false;}return{accion:a,comentario:c};}}));
    if(!r.isConfirmed)return;
    try{Swal.fire(swal({title:'Registrando...',html:'<div class="ev-as-loading"><span></span><p>Guardando resolución</p></div>',showConfirmButton:false,allowEscapeKey:false}));await api(`${BASE}/api/soporte/servicios/${id}/resolver`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(r.value)});await Swal.fire(swal({icon:'success',title:'Actuación registrada',text:'Las partes fueron notificadas.',confirmButtonText:'Aceptar'}));await Promise.all([load(),loadSummary()]);}catch(e){await Swal.fire(swal({icon:'error',title:'No se pudo registrar',text:e.message,confirmButtonText:'Aceptar'}));}
  }

  async function openDetail(id){
    try{Swal.fire(swal({title:'Cargando caso...',html:'<div class="ev-as-loading"><span></span><p>Recuperando trazabilidad</p></div>',showConfirmButton:false}));const j=await api(`${BASE}/api/soporte/servicios/${id}`);const c=j.data?.caso||{};const cerrado=['resuelta','cerrada','cancelada'].includes(String(c.estado||''));const r=await Swal.fire(swal({title:`Caso de servicio #${id}`,html:detailHtml(j.data||{}),width:920,showCancelButton:!cerrado,confirmButtonText:'Cerrar',cancelButtonText:'Registrar actuación',reverseButtons:true}));if(r.dismiss===Swal.DismissReason.cancel)await resolve(id,c.titulo_servicio||'Servicio');}catch(e){await Swal.fire(swal({icon:'error',title:'No se pudo abrir el caso',text:e.message,confirmButtonText:'Aceptar'}));}
  }

  function bind(){
    document.querySelectorAll('.ev-as-tab').forEach(b=>{if(b.dataset.bound)return;b.dataset.bound='1';b.addEventListener('click',()=>{document.querySelectorAll('.ev-as-tab').forEach(x=>x.classList.remove('active'));b.classList.add('active');estado=b.dataset.estado||'abiertas';load();});});
    const search=document.getElementById('evAsSearch');if(search&&!search.dataset.bound){search.dataset.bound='1';let t;search.addEventListener('input',()=>{clearTimeout(t);t=setTimeout(()=>load(),350);});}
    const ref=document.getElementById('evAsRefresh');if(ref&&!ref.dataset.bound){ref.dataset.bound='1';ref.addEventListener('click',()=>Promise.all([load(),loadSummary()]));}
    if(!document.body.dataset.evAsScopeBound){document.body.dataset.evAsScopeBound='1';document.addEventListener('ev:admin-community-change',e=>{if(e.detail?.module==='servicios')Promise.all([load(),loadSummary()]);});}
    const root=document.querySelector('.ev-as-page');if(root&&!root.dataset.bound){root.dataset.bound='1';root.addEventListener('click',e=>{const b=e.target.closest('[data-as-detail]');if(b)openDetail(Number(b.dataset.asDetail||0));});}
  }
  function stop(){if(timer){clearInterval(timer);timer=null;}}
  function start(){stop();timer=setInterval(()=>{if(!document.hidden&&document.querySelector('.ev-as-page')){load(true);loadSummary();}},20000);}
  function init(){if(!document.querySelector('.ev-as-page')){stop();return false;}bind();load();loadSummary();start();return true;}
  window.EVAtenderServicios={init,refresh:()=>Promise.all([load(),loadSummary()]),stop};
  document.addEventListener('ev:content-loaded',()=>setTimeout(init,100));document.addEventListener('ev:nav-end',()=>setTimeout(init,100));
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
