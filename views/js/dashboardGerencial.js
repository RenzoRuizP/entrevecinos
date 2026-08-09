(function(){
'use strict';
const BASE=(window.EV?.baseUrl||window.BASE_URL||'').toString().replace(/\/+$/,'');
const CSRF=()=>document.getElementById('evDashboardGerencial')?.dataset?.csrf||'';
let catalogos={departamentos:[],provincias:[],distritos:[],comunidades:[]};
let charts={operations:null,income:null};
let lastSeries={};
let resizeTimer=null;
let chartResizeObserver=null;
let lastChartWidth=0;
let initialized=false;
const $=(s,r=document)=>r.querySelector(s); const $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
const money=v=>'S/ '+Number(v||0).toLocaleString('es-PE',{minimumFractionDigits:2,maximumFractionDigits:2});
const integer=v=>Number(v||0).toLocaleString('es-PE');
const esc=v=>String(v??'').replace(/[&<>'"]/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[m]));
async function notice(icon,title,text){if(window.Swal?.fire)return Swal.fire({icon,title,text,confirmButtonText:'Aceptar',confirmButtonColor:'#EA7C12',allowOutsideClick:false,allowEscapeKey:false});window.alert(`${title}
${text}`);}
async function json(url,opt={}){const r=await fetch(url,{credentials:'same-origin',headers:{Accept:'application/json',...(opt.headers||{})},...opt});const t=await r.text();let j={};try{j=t?JSON.parse(t):{};}catch(e){throw new Error('La respuesta del servidor no es válida.');}if(!r.ok||j.ok===false)throw new Error(j.mensaje||`HTTP ${r.status}`);return j;}
function root(){return $('#evDashboardGerencial');}
function toggleDates(){const custom=$('#evDgPeriodo')?.value==='personalizado';$$('.ev-dg-custom-date').forEach(x=>x.hidden=!custom);if($('#evDgFechaRefWrap'))$('#evDgFechaRefWrap').hidden=custom;}
function alcanceLabel(v){return {global:'Todo Entre Vecinos',departamento:'Departamento',provincia:'Provincia',distrito:'Distrito',condominio:'Condominio',urbanizacion:'Urbanización'}[v]||'Seleccionar';}
function optionsFor(type){if(type==='departamento')return catalogos.departamentos;if(type==='provincia')return catalogos.provincias;if(type==='distrito')return catalogos.distritos;if(type==='condominio')return catalogos.comunidades.filter(x=>x.tipo_conjunto==='condominio').map(x=>({codigo:x.codigo_comunidad,nombre:`${x.nombre_comunidad} · ${x.nombre_distrito||''}`}));if(type==='urbanizacion')return catalogos.comunidades.filter(x=>x.tipo_conjunto==='urbanizacion').map(x=>({codigo:x.codigo_comunidad,nombre:`${x.nombre_comunidad} · ${x.nombre_distrito||''}`}));return[];}
function syncScope(){const type=$('#evDgAlcance').value;const wrap=$('#evDgValorWrap'),sel=$('#evDgValor');wrap.hidden=type==='global';$('#evDgValorLabel').textContent=alcanceLabel(type);sel.innerHTML='<option value="0">-- Seleccionar --</option>'+optionsFor(type).map(x=>`<option value="${Number(x.codigo)}">${esc(x.nombre)}</option>`).join('');}
function params(){const p=new URLSearchParams({periodo:$('#evDgPeriodo').value,fecha_referencia:$('#evDgFechaRef').value||new Date().toISOString().slice(0,10),tipo_alcance:$('#evDgAlcance').value,codigo_alcance:$('#evDgValor').value||'0'});if($('#evDgPeriodo').value==='personalizado'){p.set('fecha_desde',$('#evDgDesde').value);p.set('fecha_hasta',$('#evDgHasta').value);}return p;}
function scopeText(){const type=$('#evDgAlcance').value;if(type==='global')return'Todo Entre Vecinos';const s=$('#evDgValor');return s.options[s.selectedIndex]?.text||alcanceLabel(type);}
function setLoading(on){$('#evDgLoading').hidden=!on;$('#evDgError').hidden=true;}
function renderKpis(k={}){$$('[data-kpi]').forEach(e=>e.textContent=integer(k[e.dataset.kpi]));$$('[data-kpi-currency]').forEach(e=>e.textContent=money(k[e.dataset.kpiCurrency]));$$('[data-kpi-money]').forEach(e=>{const key=e.dataset.kpiMoney;e.textContent=(key==='ticket_promedio_servicio'?'Servicio: ':'')+money(k[key])+(key==='monto_ventas_productos'?' vendidos':key==='monto_servicios'?' acordados':'');});}
function destroyChart(name){if(charts[name]){charts[name].destroy();charts[name]=null;}}
function isCompactChart(){return window.matchMedia?.('(max-width: 700px)').matches===true;}
function compactChartLabels(raw=[]){
  const labels=Array.isArray(raw)?raw.map(v=>String(v??'')):[];
  if(!isCompactChart())return labels;
  const step=labels.length>24?4:(labels.length>14?2:1);
  return labels.map((label,index)=>{
    const short=/^\d{2}\/\d{2}$/.test(label)?label.slice(0,2):label;
    if(step===1||index===0||index===labels.length-1||index%step===0)return short;
    return '';
  });
}
function chartOptions(currency=false){
  const compact=isCompactChart();
  return {responsive:true,maintainAspectRatio:false,scaleBeginAtZero:true,scaleShowGridLines:true,scaleGridLineColor:'rgba(148,163,184,.13)',scaleLineColor:'rgba(148,163,184,.22)',scaleFontFamily:'Poppins',scaleFontSize:compact?9:10,bezierCurve:true,bezierCurveTension:.35,pointDot:true,pointDotRadius:compact?2:3,datasetFill:false,animationSteps:compact?36:60,multiTooltipTemplate:currency?'<%= datasetLabel %>: S/ <%= Number(value).toFixed(2) %>':'<%= datasetLabel %>: <%= value %>'};
}
function prepareCanvas(id){
  const canvas=$(id);
  if(!canvas)return null;
  canvas.removeAttribute('width');
  canvas.removeAttribute('height');
  return canvas;
}
function renderCharts(s={}){
  if(!window.Chart)return;
  lastSeries=s||{};
  const labels=compactChartLabels(s.labels||[]);destroyChart('operations');destroyChart('income');
  const operationData={labels,datasets:[
    {label:'Usuarios',fillColor:'rgba(14,122,67,.08)',strokeColor:'#0E7A43',pointColor:'#0E7A43',pointStrokeColor:'#fff',data:s.usuarios||[]},
    {label:'Ventas',fillColor:'rgba(234,124,18,.05)',strokeColor:'#EA7C12',pointColor:'#EA7C12',pointStrokeColor:'#fff',data:s.ventas||[]},
    {label:'Servicios',fillColor:'rgba(124,58,237,.04)',strokeColor:'#7C3AED',pointColor:'#7C3AED',pointStrokeColor:'#fff',data:s.servicios||[]}
  ]};
  const incomeData={labels,datasets:[
    {label:'Comisiones productos',fillColor:'rgba(14,122,67,.78)',strokeColor:'#0E7A43',highlightFill:'rgba(14,122,67,.9)',highlightStroke:'#0F592F',data:s.comisiones_productos||[]},
    {label:'Publicación servicios',fillColor:'rgba(234,124,18,.78)',strokeColor:'#EA7C12',highlightFill:'rgba(234,124,18,.9)',highlightStroke:'#C46B05',data:s.publicacion_servicios||[]}
  ]};
  const operationsCanvas=prepareCanvas('#evDgOperationsChart');
  const incomeCanvas=prepareCanvas('#evDgIncomeChart');
  if(operationsCanvas)charts.operations=new Chart(operationsCanvas.getContext('2d')).Line(operationData,chartOptions(false));
  if(incomeCanvas)charts.income=new Chart(incomeCanvas.getContext('2d')).Bar(incomeData,chartOptions(true));
}
function humanState(v){return String(v||'').replace(/_/g,' ').replace(/\b\w/g,m=>m.toUpperCase());}
function renderStates(id,rows=[]){const el=$(id);el.innerHTML=rows.length?rows.slice(0,7).map(r=>`<div class="ev-dg-status-row"><span>${esc(humanState(r.estado))}</span><strong>${integer(r.total)}</strong></div>`).join(''):'<div class="ev-dg-status-row"><span>Sin registros</span><strong>0</strong></div>';}
function renderGoal(m={}){const pct=Math.max(0,Math.min(100,Number(m.porcentaje||0)));$('#evDgGoalPct').textContent=pct.toFixed(pct%1?1:0)+'%';$('#evDgGoalRing').style.setProperty('--pct',pct);$('#evDgGoalBar').style.width=pct+'%';$('#evDgGoalActual').textContent=money(m.ingreso_actual);$('#evDgGoalTarget').textContent=m.configurada?money(m.monto_objetivo):'Sin meta';$('#evDgGoalMissing').textContent=money(m.monto_faltante);$('#evDgSetupNote').hidden=!m.setup_requerido;$('#evDgEditGoal').disabled=!!m.setup_requerido;$('#evDgGoalInput').value=m.configurada?Number(m.monto_objetivo).toFixed(2):'';}
function renderCommunities(rows=[]){$('#evDgCommunityCount').textContent=`${rows.length} ${rows.length===1?'comunidad':'comunidades'}`;$('#evDgCommunityBody').innerHTML=rows.length?rows.map(r=>`<tr><td><span class="ev-dg-community-name"><strong>${esc(r.nombre_comunidad)}</strong><small>${r.tipo_conjunto==='urbanizacion'?'Urbanización':'Condominio'}</small></span></td><td>${esc([r.nombre_distrito,r.nombre_provincia,r.nombre_departamento].filter(Boolean).join(' · '))}</td><td>${integer(r.total_usuarios)}</td><td>${integer(r.total_publicaciones)}</td><td>${integer(r.total_ventas)}</td><td>${integer(r.total_servicios)}</td><td><strong>${money(r.total_ingresos)}</strong></td><td>${r.ultima_actividad&&r.ultima_actividad!=='1000-01-01'?esc(r.ultima_actividad):'Sin actividad'}</td></tr>`).join(''):'<tr><td colspan="8" class="ev-dg-empty-cell">No hay comunidades para el alcance seleccionado.</td></tr>';}
async function load(){setLoading(true);try{const r=await json(`${BASE}/api/admin/dashboard-gerencial?${params()}`);const d=r.data||{};renderKpis(d.kpis);renderCharts(d.series);renderGoal(d.meta);renderStates('#evDgPedidosEstados',d.resumen_estados?.pedidos||[]);renderStates('#evDgServiciosEstados',d.resumen_estados?.servicios||[]);renderCommunities(d.comunidades||[]);$('#evDgPeriodoLabel').textContent=d.filtros?.etiqueta_periodo||'Periodo seleccionado';$('#evDgScopeLabel').textContent=scopeText();$('#evDgGoalContext').textContent=`${d.filtros?.etiqueta_periodo||''} · ${scopeText()}`;}catch(e){$('#evDgError').hidden=false;$('#evDgErrorText').textContent=e.message;}finally{setLoading(false);}}
async function saveGoal(ev){ev.preventDefault();const amount=Number($('#evDgGoalInput').value||0);if(!(amount>0)){await notice('warning','Revisa la meta','Ingresa un monto mayor a S/ 0.00.');return;}try{const body=Object.fromEntries(params());body.monto_objetivo=amount;const r=await json(`${BASE}/api/admin/dashboard-gerencial/meta`,{method:'POST',headers:{'Content-Type':'application/json','X-EV-CSRF':CSRF()},body:JSON.stringify(body)});bootstrap.Modal.getInstance($('#evDgGoalModal'))?.hide();if(window.EVSwal?.success)await window.EVSwal.success('Meta guardada',r.mensaje||'La meta fue registrada correctamente.');else await notice('success','Meta guardada',r.mensaje||'La meta fue registrada correctamente.');await load();}catch(e){await notice('error','No se pudo guardar',e.message);}}
function scheduleChartRedraw(){clearTimeout(resizeTimer);resizeTimer=setTimeout(()=>{if(root())renderCharts(lastSeries);},180);}
function observeChartWidth(){
  chartResizeObserver?.disconnect?.();
  const grid=$('.ev-dg-grid-charts');
  if(!grid||typeof ResizeObserver==='undefined')return;
  lastChartWidth=Math.round(grid.getBoundingClientRect().width||0);
  chartResizeObserver=new ResizeObserver(entries=>{
    const width=Math.round(entries[0]?.contentRect?.width||0);
    if(!width||Math.abs(width-lastChartWidth)<2)return;
    lastChartWidth=width;
    scheduleChartRedraw();
  });
  chartResizeObserver.observe(grid);
}
async function init(){const r=root();if(!r||r.dataset.initialized==='1')return;r.dataset.initialized='1';try{const j=await json(`${BASE}/api/admin/dashboard-gerencial/catalogos`);catalogos=j.data||catalogos;}catch(e){$('#evDgError').hidden=false;$('#evDgErrorText').textContent=e.message;return;}$('#evDgPeriodo').addEventListener('change',toggleDates);$('#evDgAlcance').addEventListener('change',syncScope);$('#evDgAplicar').addEventListener('click',load);$('#evDgReset').addEventListener('click',()=>{$('#evDgPeriodo').value='mes';$('#evDgFechaRef').value=new Date().toISOString().slice(0,10);$('#evDgAlcance').value='global';toggleDates();syncScope();load();});$('#evDgEditGoal').addEventListener('click',()=>bootstrap.Modal.getOrCreateInstance($('#evDgGoalModal'),{backdrop:'static',keyboard:false}).show());$('#evDgGoalForm').addEventListener('submit',saveGoal);window.addEventListener('resize',scheduleChartRedraw,{passive:true});observeChartWidth();toggleDates();syncScope();await load();}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();document.addEventListener('ev:partial-loaded',init);document.addEventListener('ev:content-loaded',init);
})();
