// views/js/atenderLibroReclamaciones.js
(() => {
  'use strict';
  const BASE = String(window.EV?.baseUrl ?? window.BASE_URL ?? '').replace(/\/$/, '');
  let estado = 'pendientes';
  let tipo = 'all';
  let page = 1;
  let busy = false;
  let timer = null;

  const esc = (v) => String(v ?? '').replace(/[&<>'"]/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
  const fmt = (v) => { if (!v) return '—'; const d = new Date(String(v).replace(' ', 'T')); return Number.isNaN(d.getTime()) ? String(v) : d.toLocaleString('es-PE',{dateStyle:'medium',timeStyle:'short'}); };
  const money = (v) => (v === null || v === '' || typeof v === 'undefined') ? 'No indicado' : new Intl.NumberFormat('es-PE',{style:'currency',currency:'PEN'}).format(Number(v));
  const labelEstado = (v) => ({registrado:'Registrado',en_revision:'En revisión',respondido:'Respondido',cerrado:'Cerrado'}[v] || v);
  const swal = (o={}) => ({...o,customClass:{container:'ev-so-swal-container',popup:'ev-lr-swal-popup',...(o.customClass||{})}});

  async function api(url, options={}) {
    const res = await fetch(url,{credentials:'same-origin',...options});
    const json = await res.json().catch(()=>({ok:false,mensaje:'Respuesta inválida del servidor.'}));
    if (!res.ok || json.ok === false) throw new Error(json.mensaje || 'No se pudo completar la operación.');
    return json;
  }

  function refs(){return {root:document.querySelector('.ev-lr-page'),list:document.getElementById('evLrList'),loading:document.getElementById('evLrLoading'),empty:document.getElementById('evLrEmpty'),error:document.getElementById('evLrError'),search:document.getElementById('evLrSearch'),pagination:document.getElementById('evLrPagination')};}

  async function loadSummary(){
    try { const j=await api(`${BASE}/api/soporte/libro-reclamaciones/resumen`); const d=j.data||{};
      document.getElementById('evLrKpiNuevos').textContent=Number(d.registrados||0);
      document.getElementById('evLrKpiRevision').textContent=Number(d.en_revision||0);
      document.getElementById('evLrKpiRespondidos').textContent=Number(d.respondidos||0);
      document.getElementById('evLrKpiHoy').textContent=Number(d.recibidos_hoy||0);
    } catch(e){ console.warn(e); }
  }

  function card(it){
    const id=Number(it.codigo_libro_reclamacion||0);
    const name=`${it.consumidor_nombres||''} ${it.consumidor_apellidos||''}`.trim();
    return `<article class="ev-lr-case">
      <div class="ev-lr-case-main">
        <div class="ev-lr-case-top"><span class="ev-lr-case-number">${esc(it.numero_hoja||`#${id}`)}</span><span class="ev-lr-case-title">${esc(it.descripcion_bien||'Registro')}</span><span class="ev-lr-badge ev-lr-type">${esc(it.tipo_registro||'')}</span><span class="ev-lr-badge ev-lr-badge-${esc(it.estado||'registrado')}">${esc(labelEstado(it.estado))}</span></div>
        <div class="ev-lr-case-meta"><span><i class="bi bi-person"></i>${esc(name||'Consumidor')}</span><span><i class="bi bi-card-text"></i>${esc(it.numero_documento||'')}</span><span><i class="bi bi-envelope"></i>${esc(it.correo||'')}</span><span><i class="bi bi-clock"></i>${esc(fmt(it.fecha_registro))}</span></div>
        <div class="ev-lr-case-desc">${esc(it.detalle||'')}</div>
      </div>
      <button type="button" class="btn ev-lr-btn-detail" data-lr-detail="${id}"><i class="bi bi-search me-1"></i>Revisar</button>
    </article>`;
  }

  function pagination(p){
    const r=refs(); if(!r.pagination) return;
    const pages=Number(p?.pages||1), current=Number(p?.page||1), total=Number(p?.total||0);
    r.pagination.classList.toggle('d-none', total===0);
    r.pagination.innerHTML=`<button type="button" data-lr-page="${current-1}" ${current<=1?'disabled':''}><i class="bi bi-chevron-left"></i> Anterior</button><span>Página ${current} de ${pages} · ${total} registros</span><button type="button" data-lr-page="${current+1}" ${current>=pages?'disabled':''}>Siguiente <i class="bi bi-chevron-right"></i></button>`;
  }

  async function load(silent=false){
    const r=refs(); if(!r.root||busy)return; busy=true;
    if(!silent){r.loading?.classList.remove('d-none');r.list.innerHTML='';r.empty?.classList.add('d-none');}
    r.error?.classList.add('d-none');
    try{
      const q=new URLSearchParams({estado,tipo,buscar:String(r.search?.value||'').trim(),page:String(page),size:'20'});
      const j=await api(`${BASE}/api/soporte/libro-reclamaciones?${q}`); const arr=Array.isArray(j.data)?j.data:[];
      r.list.innerHTML=arr.map(card).join(''); r.empty?.classList.toggle('d-none',arr.length>0); pagination(j.pagination||{});
    }catch(e){r.error.textContent=e.message;r.error.classList.remove('d-none');}
    finally{r.loading?.classList.add('d-none');busy=false;}
  }

  function detailHtml(d){
    const x=d.registro||{}, hist=Array.isArray(d.historial)?d.historial:[];
    const full=`${x.consumidor_nombres||''} ${x.consumidor_apellidos||''}`.trim();
    const rep=`${x.representante_nombres||''} ${x.representante_apellidos||''}`.trim();
    return `<div class="ev-lr-detail">
      <div class="ev-lr-detail-grid">
        <div class="ev-lr-box"><span>Número</span><strong>${esc(x.numero_hoja||'—')}</strong></div><div class="ev-lr-box"><span>Estado</span><strong>${esc(labelEstado(x.estado))}</strong></div>
        <div class="ev-lr-box"><span>Consumidor</span><strong>${esc(full)}</strong></div><div class="ev-lr-box"><span>Documento</span><strong>${esc(`${x.tipo_documento||''} ${x.numero_documento||''}`)}</strong></div>
        <div class="ev-lr-box"><span>Correo</span><strong>${esc(x.correo||'')}</strong></div><div class="ev-lr-box"><span>Teléfono</span><strong>${esc(x.telefono||'')}</strong></div>
        <div class="ev-lr-box ev-lr-wide"><span>Domicilio</span><strong>${esc(x.domicilio||'')}</strong></div>
        ${Number(x.es_menor||0)===1?`<div class="ev-lr-box ev-lr-wide"><span>Representante</span><strong>${esc(rep)} · ${esc(`${x.representante_tipo_documento||''} ${x.representante_numero_documento||''}`)}</strong></div>`:''}
        <div class="ev-lr-box"><span>Tipo</span><strong>${esc(x.tipo_registro||'')}</strong></div><div class="ev-lr-box"><span>Producto o servicio</span><strong>${esc(x.tipo_bien||'')}</strong></div>
        <div class="ev-lr-box"><span>Monto</span><strong>${esc(money(x.monto_reclamado))}</strong></div><div class="ev-lr-box"><span>Fecha</span><strong>${esc(fmt(x.fecha_registro))}</strong></div>
      </div>
      <section class="ev-lr-section"><h6>Descripción del producto o servicio</h6><p>${esc(x.descripcion_bien||'')}</p></section>
      <section class="ev-lr-section"><h6>Detalle</h6><p>${esc(x.detalle||'')}</p></section>
      <section class="ev-lr-section"><h6>Pedido concreto</h6><p>${esc(x.pedido_concreto||'')}</p></section>
      ${x.respuesta_publica?`<section class="ev-lr-section"><h6>Respuesta registrada</h6><p>${esc(x.respuesta_publica)}</p></section>`:''}
      <section class="ev-lr-section"><h6>Historial</h6><div class="ev-lr-history">${hist.map(h=>`<article><strong>${esc(String(h.evento||'').replaceAll('_',' '))} · ${esc(h.actor||'EV')}</strong><p>${esc(h.detalle||'')}</p><time>${esc(fmt(h.fecha_evento))}</time></article>`).join('')||'<p>Sin eventos adicionales.</p>'}</div></section>
    </div>`;
  }

  async function attend(id, current, previous=''){
    const result=await Swal.fire(swal({title:'Registrar atención',html:`<div class="ev-lr-response-form"><label>Nuevo estado<select id="evLrNewState"><option value="en_revision" ${current==='en_revision'?'selected':''}>En revisión</option><option value="respondido" ${current==='respondido'?'selected':''}>Respondido</option><option value="cerrado" ${current==='cerrado'?'selected':''}>Cerrado</option></select></label><label>Medio de respuesta<select id="evLrMedium"><option value="correo">Correo</option><option value="telefono">Teléfono</option><option value="domicilio">Domicilio</option><option value="otro">Otro</option></select></label><label>Respuesta o actuación<textarea id="evLrAnswer" maxlength="10000" placeholder="Redacta una respuesta clara, completa y respetuosa.">${esc(previous)}</textarea></label><div class="ev-lr-response-note">Para marcar como Respondido o Cerrado, la respuesta es obligatoria. La respuesta quedará visible en la consulta pública y el sistema intentará enviarla al correo registrado.</div></div>`,showCancelButton:true,confirmButtonText:'Guardar atención',cancelButtonText:'Volver',width:720,preConfirm:()=>{const estado=String(document.getElementById('evLrNewState')?.value||''),medio_respuesta=String(document.getElementById('evLrMedium')?.value||'correo'),respuesta=String(document.getElementById('evLrAnswer')?.value||'').trim();if(['respondido','cerrado'].includes(estado)&&respuesta.length<10){Swal.showValidationMessage('Escribe una respuesta de al menos 10 caracteres.');return false;}return{estado,medio_respuesta,respuesta};}}));
    if(!result.isConfirmed)return;
    try{Swal.fire(swal({title:'Guardando...',showConfirmButton:false,allowOutsideClick:false,allowEscapeKey:false,didOpen:()=>Swal.showLoading()}));const j=await api(`${BASE}/api/soporte/libro-reclamaciones/${id}/atender`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(result.value)});await Swal.fire(swal({icon:j.advertencia?'warning':'success',title:'Atención registrada',text:j.advertencia||j.mensaje||'Los cambios fueron guardados.',confirmButtonText:'Aceptar'}));await Promise.all([load(),loadSummary()]);}catch(e){await Swal.fire(swal({icon:'error',title:'No se pudo guardar',text:e.message,confirmButtonText:'Aceptar'}));}
  }

  async function openDetail(id){
    try{Swal.fire(swal({title:'Cargando registro...',showConfirmButton:false,allowOutsideClick:false,didOpen:()=>Swal.showLoading()}));const j=await api(`${BASE}/api/soporte/libro-reclamaciones/${id}`);const x=j.data?.registro||{};const result=await Swal.fire(swal({title:`${String(x.tipo_registro||'Registro').replace(/^./,c=>c.toUpperCase())} · ${x.numero_hoja||''}`,html:detailHtml(j.data||{}),width:920,showCancelButton:true,confirmButtonText:'Cerrar',cancelButtonText:'Registrar atención',reverseButtons:true,allowOutsideClick:false,allowEscapeKey:false}));if(result.dismiss===Swal.DismissReason.cancel)await attend(id,String(x.estado||'registrado'),String(x.respuesta_publica||''));}catch(e){await Swal.fire(swal({icon:'error',title:'No se pudo abrir',text:e.message,confirmButtonText:'Aceptar'}));}
  }

  function bind(){
    document.querySelectorAll('.ev-lr-tab').forEach(b=>{if(b.dataset.bound)return;b.dataset.bound='1';b.addEventListener('click',()=>{document.querySelectorAll('.ev-lr-tab').forEach(x=>x.classList.remove('active'));b.classList.add('active');estado=b.dataset.estado||'pendientes';page=1;load();});});
    const select=document.getElementById('evLrTipo');if(select&&!select.dataset.bound){select.dataset.bound='1';select.addEventListener('change',()=>{tipo=select.value||'all';page=1;load();});}
    const search=document.getElementById('evLrSearch');if(search&&!search.dataset.bound){search.dataset.bound='1';let t;search.addEventListener('input',()=>{clearTimeout(t);t=setTimeout(()=>{page=1;load();},350);});}
    const refresh=document.getElementById('evLrRefresh');if(refresh&&!refresh.dataset.bound){refresh.dataset.bound='1';refresh.addEventListener('click',()=>Promise.all([load(),loadSummary()]));}
    const root=document.querySelector('.ev-lr-page');if(root&&!root.dataset.bound){root.dataset.bound='1';root.addEventListener('click',e=>{const detail=e.target.closest('[data-lr-detail]');if(detail)openDetail(Number(detail.dataset.lrDetail||0));const p=e.target.closest('[data-lr-page]');if(p&&!p.disabled){page=Math.max(1,Number(p.dataset.lrPage||1));load();}});}
  }
  function stop(){if(timer){clearInterval(timer);timer=null;}}
  function start(){stop();timer=setInterval(()=>{if(!document.hidden&&document.querySelector('.ev-lr-page')){load(true);loadSummary();}},30000);}
  function init(){if(!document.querySelector('.ev-lr-page')){stop();return false;}bind();load();loadSummary();start();return true;}
  window.EVLibroReclamaciones={init,refresh:()=>Promise.all([load(),loadSummary()]),stop};
  document.addEventListener('ev:content-loaded',()=>setTimeout(init,100));document.addEventListener('ev:nav-end',()=>setTimeout(init,100));
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
