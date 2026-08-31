(function(){
  'use strict';

  function initGestionRetiros() {
  const root = document.getElementById('evGestionRetiros');
  if (!root || root.dataset.evRetirosInit === '1') return;
  root.dataset.evRetirosInit = '1';
  const BASE = (window.EV?.baseUrl ?? window.BASE_URL ?? '').replace(/\/$/, '');
  const ES_ADMIN = root.dataset.admin === '1';
  const CSRF = String(root.dataset.csrf || '');
  const state = { retiros: [], cuentas: [], cortes: [] };

  const $ = (id) => document.getElementById(id);
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (m)=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  const money = (n) => 'S/ ' + Number(n || 0).toLocaleString('es-PE',{minimumFractionDigits:2,maximumFractionDigits:2});
  const dateTime = (v) => { if(!v) return '—'; const d=new Date(String(v).replace(' ','T')); return Number.isNaN(d.getTime())?String(v):d.toLocaleString('es-PE',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'}); };
  const dateOnly = (v) => { if(!v) return '—'; const d=new Date(`${v}T12:00:00`); return Number.isNaN(d.getTime())?String(v):d.toLocaleDateString('es-PE',{day:'2-digit',month:'2-digit',year:'numeric'}); };
  const estadoTxt = (e) => ({solicitado:'Solicitado',programado:'Programado',pagado:'Pagado',observado:'Observado',cancelado:'Cancelado',sin_saldo:'Sin saldo',pendiente:'Pendiente',validada:'Validada',observada:'Observada'})[String(e||'').toLowerCase()] || String(e||'—');

  function swal(opts){
    if (!window.Swal?.fire) return null;
    return Swal.fire(Object.assign({buttonsStyling:false,heightAuto:false,customClass:{popup:'ev-swal-popup',confirmButton:'ev-swal-confirm btn-ev-orange',cancelButton:'ev-swal-cancel btn-ev-outline'}},opts||{}));
  }
  function ok(msg){ if(window.Swal?.fire) return swal({icon:'success',title:'Listo',text:msg,showConfirmButton:false,timer:1500}); alert(msg); }
  function err(msg){ if(window.Swal?.fire) return swal({icon:'error',title:'Ocurrió un problema',text:msg,confirmButtonText:'Entendido'}); alert(msg); }

  async function read(resp){ const ct=(resp.headers.get('content-type')||'').toLowerCase(); if(ct.includes('application/json')) return resp.json().catch(()=>({})); const t=await resp.text().catch(()=>''); try{return JSON.parse(t)}catch(_){return {ok:false,mensaje:t||'Respuesta no válida.'}} }
  async function request(url, opts={}){
    const resp=await fetch(url,Object.assign({credentials:'include',headers:{Accept:'application/json'}},opts));
    const data=await read(resp);
    if(resp.status===401 && data.redirect){ window.location.assign(data.redirect); throw new Error('SESSION'); }
    return {resp,data};
  }
  function headersJson(){ return {'Accept':'application/json','Content-Type':'application/json','X-EV-CSRF':CSRF}; }

  function activateTab(name){
    root.querySelectorAll('[data-ret-tab]').forEach(b=>b.classList.toggle('is-active',b.dataset.retTab===name));
    root.querySelectorAll('[data-ret-panel]').forEach(p=>p.classList.toggle('is-active',p.dataset.retPanel===name));
    if(name==='cuentas' && ES_ADMIN) cargarCuentas();
    if(name==='cortes' && ES_ADMIN) cargarCortes();
  }

  function setSummary(r={}){
    $('evRetSumSolicitados').textContent=String(r.solicitados||0);
    $('evRetSumProgramados').textContent=String(r.programados||0);
    $('evRetSumObservados').textContent=String(r.observados||0);
    $('evRetSumMonto').textContent=money(r.monto_pendiente||0);
  }

  function retiroRows(items){
    if(!items.length) return '<div class="ev-withdraw-empty">No hay retiros con estos filtros.</div>';
    const grupos=new Map();
    items.forEach(r=>{
      const key=`${r.fecha_pago_programada||''}|${r.jornada_nombre||''}`;
      if(!grupos.has(key)) grupos.set(key,[]);
      grupos.get(key).push(r);
    });
    return Array.from(grupos.values()).map(grupo=>{
      const cab=grupo[0]||{};
      const total=grupo.reduce((acc,r)=>acc+Number((r.monto_final!==null&&r.monto_final!==undefined)?r.monto_final:r.monto_estimado||0),0);
      const rows=grupo.map(r=>{
        const m=r.monto_final!==null && r.monto_final!==undefined?r.monto_final:r.monto_estimado;
        return `<tr>
          <td data-label="Retiro"><strong>${esc(r.codigo)}</strong><span class="ev-ret-sub">${esc(dateTime(r.fecha_solicitud))}</span></td>
          <td data-label="Vendedor"><strong>${esc(r.usuario?.nombre||'—')}</strong><span class="ev-ret-sub">DNI ${esc(r.usuario?.documento||'—')}</span></td>
          <td data-label="Monto"><strong>${esc(money(m))}</strong>${r.saldo_cierre!==null&&r.saldo_cierre!==undefined?`<span class="ev-ret-sub">Saldo al cierre ${esc(money(r.saldo_cierre))}</span>`:''}</td>
          <td data-label="Estado"><span class="ev-ret-state ev-ret-state--${esc(r.estado)}">${esc(estadoTxt(r.estado))}</span></td>
          <td data-label="Acción" class="text-end ev-ret-actions-cell"><button type="button" class="ev-ret-review" data-action="ver-retiro" data-id="${Number(r.codigo_retiro)}">Revisar</button></td>
        </tr>`;
      }).join('');
      return `<section class="ev-ret-batch">
        <header class="ev-ret-batch-head">
          <div><span>Jornada de pago</span><strong>${esc(cab.jornada_nombre||'—')} · ${esc(dateOnly(cab.fecha_pago_programada))}</strong></div>
          <div class="ev-ret-batch-meta"><span>${grupo.length} vendedor${grupo.length===1?'':'es'} con retiro</span><strong>${esc(money(total))}</strong></div>
        </header>
        <div class="table-responsive"><table class="table align-middle ev-ret-responsive-table"><thead><tr><th>Retiro</th><th>Vendedor</th><th>Monto</th><th>Estado</th><th></th></tr></thead><tbody>${rows}</tbody></table></div>
      </section>`;
    }).join('');
  }

  async function cargarRetiros(){
    const q=encodeURIComponent(String($('evRetSearch')?.value||'').trim());
    const estado=encodeURIComponent(String($('evRetEstado')?.value||''));
    const fechaPago=encodeURIComponent(String($('evRetFechaPago')?.value||''));
    const box=$('evRetLista'); if(box) box.innerHTML='<div class="ev-withdraw-loading">Cargando retiros...</div>';
    try{
      const {resp,data}=await request(`${BASE}/api/retiros/gestion?q=${q}&estado=${estado}&fecha_pago=${fechaPago}`);
      if(!resp.ok||!data.ok) return err(data.mensaje||'No se pudieron cargar los retiros.');
      state.retiros=Array.isArray(data.data?.items)?data.data.items:[];
      setSummary(data.data?.resumen||{});
      if(box) box.innerHTML=retiroRows(state.retiros);
    }catch(e){ if(String(e.message)!=='SESSION') err('No se pudo conectar con Gestión de retiros.'); }
  }

  function detalleHtml(r){
    const tieneMontoFinal=r.monto_final!==null&&r.monto_final!==undefined;
    const monto=tieneMontoFinal?r.monto_final:r.monto_estimado;
    const estado=String(r.estado||'');
    const estadoLabel=estadoTxt(estado);
    const montoLabel=tieneMontoFinal?'Monto a pagar':'Monto estimado';
    const obs=r.observacion?`<div class="ev-ret-detail-alert"><strong>Observación administrativa</strong><span>${esc(r.observacion)}</span></div>`:'';
    const pago=`${esc(r.jornada_nombre||'Jornada')} · ${esc(dateOnly(r.fecha_pago_programada))}`;

    return `<div class="ev-ret-detail-hero">
      <div class="ev-ret-detail-hero-main">
        <span class="ev-ret-detail-code">${esc(r.codigo||'Retiro')}</span>
        <div class="ev-ret-detail-person">${esc(r.usuario?.nombre||'—')}</div>
        <div class="ev-ret-detail-meta">DNI ${esc(r.usuario?.documento||'—')} · ${pago}</div>
      </div>
      <div class="ev-ret-detail-hero-amount">
        <span>${esc(montoLabel)}</span>
        <strong>${esc(money(monto))}</strong>
        <em class="ev-ret-state ev-ret-state--${esc(estado)}">${esc(estadoLabel)}</em>
      </div>
    </div>
    <div class="ev-ret-detail-grid">
      <section class="ev-ret-detail-card ev-ret-detail-card--bank">
        <div class="ev-ret-detail-card-head"><span class="ev-ret-detail-card-icon"><i class="bi bi-bank" aria-hidden="true"></i></span><div><h6>Cuenta de pago</h6><p>Cuenta registrada para esta solicitud.</p></div></div>
        <div class="ev-ret-detail-row"><span>Banco</span><strong>${esc(r.cuenta?.banco||'—')}</strong></div>
        <div class="ev-ret-detail-row"><span>Tipo de cuenta</span><strong>${esc(estadoTxt(r.cuenta?.tipo_cuenta)||r.cuenta?.tipo_cuenta||'—')}</strong></div>
        <div class="ev-ret-detail-row"><span>Número de cuenta</span><strong class="ev-ret-account">${esc(r.cuenta?.numero_cuenta||'—')}</strong></div>
        <div class="ev-ret-detail-row"><span>CCI</span><strong class="ev-ret-account">${esc(r.cuenta?.cci||'—')}</strong></div>
        <div class="ev-ret-detail-row"><span>Titular</span><strong>${esc(r.cuenta?.titular_nombre||'—')}</strong></div>
      </section>
      <section class="ev-ret-detail-card">
        <div class="ev-ret-detail-card-head"><span class="ev-ret-detail-card-icon"><i class="bi bi-calendar3" aria-hidden="true"></i></span><div><h6>Corte y pago</h6><p>Ventana asignada al solicitar el retiro.</p></div></div>
        <div class="ev-ret-detail-row"><span>Jornada</span><strong>${esc(r.jornada_nombre||'—')}</strong></div>
        <div class="ev-ret-detail-row"><span>Inicio del corte</span><strong>${esc(dateTime(r.corte_inicio))}</strong></div>
        <div class="ev-ret-detail-row"><span>Fin del corte</span><strong>${esc(dateTime(r.corte_fin))}</strong></div>
        <div class="ev-ret-detail-row"><span>Pago programado</span><strong>${esc(dateOnly(r.fecha_pago_programada))}</strong></div>
      </section>
      <section class="ev-ret-detail-card ev-ret-detail-card--wide">
        <div class="ev-ret-detail-card-head"><span class="ev-ret-detail-card-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span><div><h6>Liquidación</h6><p>Resumen del saldo usado para calcular el retiro.</p></div></div>
        <div class="ev-ret-detail-finance-grid">
          <div><span>Saldo al solicitar</span><strong>${esc(money(r.saldo_solicitud))}</strong></div>
          <div><span>Saldo al cierre</span><strong>${r.saldo_cierre===null||r.saldo_cierre===undefined?'Pendiente':esc(money(r.saldo_cierre))}</strong></div>
          <div><span>Saldo que conserva</span><strong>${esc(money(r.saldo_minimo))}</strong></div>
          <div class="is-primary"><span>${esc(montoLabel)}</span><strong>${esc(money(monto))}</strong></div>
        </div>
        ${r.fecha_pago||r.numero_operacion||r.comprobante_path?`<div class="ev-ret-detail-payment"><div class="ev-ret-detail-payment-title">Pago registrado</div>${r.fecha_pago?`<div class="ev-ret-detail-row"><span>Fecha de pago</span><strong>${esc(dateTime(r.fecha_pago))}</strong></div>`:''}${r.numero_operacion?`<div class="ev-ret-detail-row"><span>Número de operación</span><strong>${esc(r.numero_operacion)}</strong></div>`:''}${r.comprobante_path?`<div class="ev-ret-detail-row"><span>Comprobante</span><strong><a class="ev-ret-proof-link" href="${esc(BASE+'/'+String(r.comprobante_path).replace(/^\/+/,''))}" target="_blank" rel="noopener">Ver comprobante</a></strong></div>`:''}</div>`:''}
      </section>
    </div>${obs}`;
  }

  function abrirDetalle(id){
    const r=state.retiros.find(x=>Number(x.codigo_retiro)===Number(id)); if(!r) return;
    $('evRetDetalleBody').innerHTML=detalleHtml(r);
    const footer=$('evRetDetalleFooter');
    let actions='';
    if(ES_ADMIN && ['programado','observado'].includes(String(r.estado))){
      actions=`<div class="ev-ret-detail-actions"><button type="button" class="btn ev-ret-action ev-ret-action-cancel" data-action="cancelar-retiro" data-id="${Number(id)}">Cancelar y reintegrar</button>${String(r.estado)==='programado'?`<button type="button" class="btn ev-ret-action ev-ret-action-observe" data-action="observar-retiro" data-id="${Number(id)}">Observar</button>`:''}<button type="button" class="btn ev-ret-action ev-ret-action-pay" data-action="pagar-retiro" data-id="${Number(id)}">Registrar pago</button></div>`;
    }
    footer.innerHTML=actions;
    footer.classList.toggle('d-none',actions==='');
    const el=$('modalDetalleRetiro'); if(el&&window.bootstrap?.Modal) bootstrap.Modal.getOrCreateInstance(el).show();
  }

  async function observarRetiro(id){
    const r=window.Swal?.fire?await swal({title:'Observar retiro',input:'textarea',inputLabel:'Motivo de la observación',inputPlaceholder:'Describe la incidencia...',showCancelButton:true,confirmButtonText:'Guardar observación',cancelButtonText:'Cancelar',inputValidator:v=>!String(v||'').trim()?'Indica el motivo.':undefined}):{isConfirmed:true,value:prompt('Motivo de la observación:')};
    if(!r?.isConfirmed||!String(r.value||'').trim()) return;
    const {resp,data}=await request(`${BASE}/api/retiros/gestion/${id}/observar`,{method:'POST',headers:headersJson(),body:JSON.stringify({observacion:String(r.value).trim()})});
    if(!resp.ok||!data.ok) return err(data.mensaje||'No se pudo observar el retiro.');
    bootstrap.Modal.getInstance($('modalDetalleRetiro'))?.hide(); await ok(data.mensaje); cargarRetiros();
  }

  async function cancelarRetiro(id){
    const r=window.Swal?.fire?await swal({title:'Cancelar retiro',input:'textarea',inputLabel:'Motivo',text:'El monto reservado volverá a la billetera del vecino.',showCancelButton:true,confirmButtonText:'Cancelar y reintegrar',cancelButtonText:'Volver',inputValidator:v=>!String(v||'').trim()?'Indica el motivo.':undefined}):{isConfirmed:true,value:prompt('Motivo:')};
    if(!r?.isConfirmed||!String(r.value||'').trim()) return;
    const {resp,data}=await request(`${BASE}/api/retiros/gestion/${id}/cancelar`,{method:'POST',headers:headersJson(),body:JSON.stringify({motivo:String(r.value).trim()})});
    if(!resp.ok||!data.ok) return err(data.mensaje||'No se pudo cancelar el retiro.');
    bootstrap.Modal.getInstance($('modalDetalleRetiro'))?.hide(); await ok(data.mensaje); cargarRetiros();
  }

  function abrirPago(id){
    $('pagoRetiroId').value=String(id); $('pagoRetiroOperacion').value=''; $('pagoRetiroComprobante').value='';
    bootstrap.Modal.getInstance($('modalDetalleRetiro'))?.hide();
    const el=$('modalPagoRetiro'); if(el&&window.bootstrap?.Modal) bootstrap.Modal.getOrCreateInstance(el).show();
  }

  async function registrarPago(){
    const id=Number($('pagoRetiroId')?.value||0); const op=String($('pagoRetiroOperacion')?.value||'').trim(); const file=$('pagoRetiroComprobante')?.files?.[0];
    if(id<=0||!op) return err('Registra el número de operación.'); if(!file) return err('Adjunta el comprobante del pago.');
    const fd=new FormData(); fd.append('numero_operacion',op); fd.append('comprobante',file);
    const btn=$('btnConfirmarPagoRetiro'); btn.disabled=true;
    try{
      const {resp,data}=await request(`${BASE}/api/retiros/gestion/${id}/pagar`,{method:'POST',headers:{'Accept':'application/json','X-EV-CSRF':CSRF},body:fd});
      if(!resp.ok||!data.ok) return err(data.mensaje||'No se pudo registrar el pago.');
      bootstrap.Modal.getInstance($('modalPagoRetiro'))?.hide(); await ok(data.mensaje); cargarRetiros();
    }finally{btn.disabled=false;}
  }

  function cuentaRows(items){
    if(!items.length) return '<div class="ev-withdraw-empty">No hay cuentas registradas.</div>';
    return `<div class="table-responsive"><table class="table align-middle ev-ret-responsive-table"><thead><tr><th>Vecino</th><th>Banco</th><th>Cuenta / CCI</th><th>Estado</th><th></th></tr></thead><tbody>${items.map(i=>{const c=i.cuenta||{}; return `<tr>
      <td data-label="Vecino"><strong>${esc(i.usuario_nombre)}</strong><span class="ev-ret-sub">DNI ${esc(i.usuario_documento)}</span></td>
      <td data-label="Banco"><strong>${esc(c.banco||'—')}</strong><span class="ev-ret-sub">${esc(c.tipo_cuenta||'')}</span></td>
      <td data-label="Cuenta / CCI"><span class="ev-ret-account">${esc(c.numero_cuenta||'—')}</span><span class="ev-ret-sub ev-ret-account">CCI ${esc(c.cci||'—')}</span><span class="ev-ret-sub">Titular: ${esc(c.titular_nombre||'—')}</span></td>
      <td data-label="Estado"><span class="ev-ret-state ev-ret-state--${esc(c.estado)}">${esc(estadoTxt(c.estado))}</span>${c.observacion?`<span class="ev-ret-sub">${esc(c.observacion)}</span>`:''}</td>
      <td data-label="Acciones" class="text-end ev-ret-actions-cell">${c.estado!=='validada'?`<button class="ev-ret-review" data-action="validar-cuenta" data-id="${Number(i.codigo_cuenta_bancaria)}">Validar</button>`:''} <button class="ev-ret-review ev-ret-review--orange" data-action="observar-cuenta" data-id="${Number(i.codigo_cuenta_bancaria)}">Observar</button></td>
    </tr>`}).join('')}</tbody></table></div>`;
  }

  async function cargarCuentas(){
    if(!ES_ADMIN) return; const box=$('evRetCuentas'); if(box) box.innerHTML='<div class="ev-withdraw-loading">Cargando cuentas...</div>';
    const {resp,data}=await request(`${BASE}/api/retiros/gestion/cuentas`); if(!resp.ok||!data.ok) return err(data.mensaje||'No se pudieron cargar las cuentas.'); state.cuentas=Array.isArray(data.data)?data.data:[]; if(box) box.innerHTML=cuentaRows(state.cuentas);
  }

  async function cambiarCuenta(id,accion){
    let observacion='';
    if(accion==='observar'){
      const r=window.Swal?.fire?await swal({title:'Observar cuenta bancaria',input:'textarea',inputLabel:'Motivo',showCancelButton:true,confirmButtonText:'Guardar observación',cancelButtonText:'Cancelar',inputValidator:v=>!String(v||'').trim()?'Indica el motivo.':undefined}):{isConfirmed:true,value:prompt('Motivo:')};
      if(!r?.isConfirmed||!String(r.value||'').trim()) return; observacion=String(r.value).trim();
    } else {
      const r=window.Swal?.fire?await swal({icon:'question',title:'Validar cuenta bancaria',text:'Confirma que los datos y la titularidad fueron revisados.',showCancelButton:true,confirmButtonText:'Sí, validar',cancelButtonText:'Cancelar'}):{isConfirmed:confirm('¿Validar cuenta?')};
      if(!r?.isConfirmed) return;
    }
    const {resp,data}=await request(`${BASE}/api/retiros/gestion/cuentas/${id}/estado`,{method:'POST',headers:headersJson(),body:JSON.stringify({accion,observacion})});
    if(!resp.ok||!data.ok) return err(data.mensaje||'No se pudo actualizar la cuenta.'); await ok(data.mensaje); cargarCuentas();
  }

  const dias={1:'Lunes',2:'Martes',3:'Miércoles',4:'Jueves',5:'Viernes',6:'Sábado',7:'Domingo'};
  function dayOptions(selected){return Object.entries(dias).map(([v,n])=>`<option value="${v}" ${Number(selected)===Number(v)?'selected':''}>${n}</option>`).join('');}
  function corteCards(items){
    if(!items.length) return '<div class="ev-withdraw-empty">No existe configuración de cortes.</div>';
    return items.map(c=>`<article class="ev-withdraw-cut-card" data-cut-id="${Number(c.codigo_retiro_configuracion)}">
      <div class="ev-withdraw-cut-card-head">
        <div class="ev-withdraw-cut-card-title">
          <span class="ev-withdraw-cut-card-icon" aria-hidden="true"><i class="bi bi-calendar2-check"></i></span>
          <div><h4>${esc(c.nombre_jornada)}</h4><p>Ventana operativa · America/Lima</p></div>
        </div>
        <span class="ev-withdraw-cut-day">Pago: ${esc(dias[Number(c.dia_pago)]||'—')}</span>
      </div>
      <div class="ev-withdraw-cut-fields">
        <div class="ev-withdraw-cut-field"><label>Nombre de jornada</label><input class="form-control" data-f="nombre_jornada" value="${esc(c.nombre_jornada)}"></div>
        <div class="ev-withdraw-cut-field"><label>Día de pago</label><div class="form-control ev-withdraw-readonly-field">${esc(dias[Number(c.dia_pago)]||'—')}</div></div>
        <div class="ev-withdraw-cut-field"><label>Inicio del corte</label><select class="form-select mb-2" data-f="dia_inicio_corte">${dayOptions(c.dia_inicio_corte)}</select><input type="time" class="form-control" data-f="hora_inicio_corte" value="${esc(c.hora_inicio_corte)}"></div>
        <div class="ev-withdraw-cut-field"><label>Fin del corte</label><select class="form-select mb-2" data-f="dia_fin_corte">${dayOptions(c.dia_fin_corte)}</select><input type="time" class="form-control" data-f="hora_fin_corte" value="${esc(c.hora_fin_corte)}"></div>
        <div class="ev-withdraw-cut-field"><label>Saldo que permanece</label><div class="form-control ev-withdraw-readonly-field">S/ ${Number(c.saldo_minimo||20).toFixed(2)}</div></div>
      </div>
      <div class="ev-withdraw-cut-actions"><label class="ev-withdraw-cut-switch"><input type="checkbox" data-f="activo" ${Number(c.activo)===1?'checked':''}> Jornada activa</label><button type="button" class="ev-withdraw-save" data-action="guardar-corte" data-id="${Number(c.codigo_retiro_configuracion)}">Guardar cambios</button></div>
    </article>`).join('');
  }

  async function cargarCortes(){
    if(!ES_ADMIN) return; const box=$('evRetCortes'); if(box) box.innerHTML='<div class="ev-withdraw-loading">Cargando configuración...</div>';
    const {resp,data}=await request(`${BASE}/api/retiros/gestion/configuracion`); if(!resp.ok||!data.ok) return err(data.mensaje||'No se pudo cargar la configuración.'); state.cortes=Array.isArray(data.data)?data.data:[]; if(box) box.innerHTML=corteCards(state.cortes);
  }

  async function guardarCorte(id){
    const card=root.querySelector(`[data-cut-id="${Number(id)}"]`); if(!card) return;
    const val=(f)=>card.querySelector(`[data-f="${f}"]`);
    const payload={nombre_jornada:String(val('nombre_jornada')?.value||'').trim(),dia_inicio_corte:Number(val('dia_inicio_corte')?.value||0),hora_inicio_corte:String(val('hora_inicio_corte')?.value||''),dia_fin_corte:Number(val('dia_fin_corte')?.value||0),hora_fin_corte:String(val('hora_fin_corte')?.value||''),activo:!!val('activo')?.checked};
    const {resp,data}=await request(`${BASE}/api/retiros/gestion/configuracion/${id}`,{method:'POST',headers:headersJson(),body:JSON.stringify(payload)}); if(!resp.ok||!data.ok) return err(data.mensaje||'No se pudo guardar el corte.'); await ok(data.mensaje); cargarCortes();
  }

  let timer=null;
  root.addEventListener('click',(e)=>{
    const tab=e.target.closest('[data-ret-tab]'); if(tab){activateTab(tab.dataset.retTab); return;}
    const b=e.target.closest('[data-action]'); if(!b) return; const id=Number(b.dataset.id||0); const a=b.dataset.action;
    if(a==='ver-retiro') abrirDetalle(id);
    if(ES_ADMIN && a==='observar-retiro') observarRetiro(id).catch(()=>err('No se pudo actualizar el retiro.'));
    if(ES_ADMIN && a==='cancelar-retiro') cancelarRetiro(id).catch(()=>err('No se pudo cancelar el retiro.'));
    if(ES_ADMIN && a==='pagar-retiro') abrirPago(id);
    if(ES_ADMIN && a==='validar-cuenta') cambiarCuenta(id,'validar').catch(()=>err('No se pudo validar la cuenta.'));
    if(ES_ADMIN && a==='observar-cuenta') cambiarCuenta(id,'observar').catch(()=>err('No se pudo observar la cuenta.'));
    if(ES_ADMIN && a==='guardar-corte') guardarCorte(id).catch(()=>err('No se pudo guardar el corte.'));
  });
  $('evRetEstado')?.addEventListener('change',cargarRetiros);
  $('evRetFechaPago')?.addEventListener('change',cargarRetiros);
  $('evRetSearch')?.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(cargarRetiros,320);});
  $('btnRefrescarRetiros')?.addEventListener('click',()=>{cargarRetiros(); if(ES_ADMIN){const active=root.querySelector('[data-ret-tab].is-active')?.dataset.retTab;if(active==='cuentas')cargarCuentas();if(active==='cortes')cargarCortes();}});
  $('btnConfirmarPagoRetiro')?.addEventListener('click',()=>registrarPago().catch(()=>err('No se pudo registrar el pago.')));

  cargarRetiros();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGestionRetiros, { once: true });
  } else {
    initGestionRetiros();
  }
  document.addEventListener('ev:content-loaded', initGestionRetiros);
  window.EVGestionRetiros = { init: initGestionRetiros };
})();
