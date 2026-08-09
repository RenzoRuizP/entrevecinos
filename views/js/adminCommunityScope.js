(function(){
  'use strict';
  const parse=(value)=>{const [tipo,codigoRaw]=String(value||'').split('|');const codigo=Number(codigoRaw||0);return {tipo:['condominio','urbanizacion'].includes(tipo)?tipo:'',codigo:Number.isInteger(codigo)&&codigo>0?codigo:0};};
  function get(module){const el=document.querySelector(`[data-ev-admin-scope="${CSS.escape(String(module||''))}"]`);if(!el)return {tipo:'',codigo:0,label:'',selected:false};const p=parse(el.value);return {...p,label:el.options?.[el.selectedIndex]?.textContent?.trim()||'',selected:Boolean(p.tipo&&p.codigo)};}
  function update(select){const card=select.closest('[data-ev-admin-scope-card]');const status=card?.querySelector('[data-ev-admin-scope-status]');const p=parse(select.value);if(status)status.textContent=p.tipo&&p.codigo?`Consultando: ${select.options[select.selectedIndex]?.textContent?.trim()||'comunidad seleccionada'}.`:'Sin filtro: se muestran todas las comunidades. Selecciona una para acotar la consulta.';}
  function init(root=document){root.querySelectorAll?.('select[data-ev-admin-scope]').forEach(select=>{if(select.dataset.evAdminScopeBound==='1')return;select.dataset.evAdminScopeBound='1';update(select);select.addEventListener('change',()=>{update(select);document.dispatchEvent(new CustomEvent('ev:admin-community-change',{detail:{module:select.dataset.evAdminScope,...get(select.dataset.evAdminScope)}}));});});}
  window.EVAdminCommunityScope={get,init};
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',()=>init());else init();
  document.addEventListener('ev:partial-loaded',e=>init(e.target instanceof Element?e.target:document));
  document.addEventListener('ev:content-loaded',e=>init(e.target instanceof Element?e.target:document));
})();
