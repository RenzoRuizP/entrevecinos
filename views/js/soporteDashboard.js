// views/js/soporteDashboard.js
document.addEventListener("DOMContentLoaded", () => {
  const body = document.getElementById("evAtenderAhoraBody");

  // KPIs
  const el = (id) => document.getElementById(id);

  // Data demo (luego lo reemplazamos por fetch a APIs)
  const data = {
    kpis: {
      cuentas: { pendientes: 5, aprobadas_hoy: 8, rechazadas: 1 },
      publicaciones: { en_revision: 6, reportadas: 4, suspendidas: 2 },
      recargas: { pendientes: 3, validadas_hoy: 10, observadas: 1 }
    },
    atender: [
      { fecha: "19:30 hoy", tipo: "Cuenta pendiente de verificación (15 min)", prioridad: "alta", url: (window.BASE_URL || "") + "/atender-cuentas" },
      { fecha: "18:50 hoy", tipo: "Publicación reportada por vecino", prioridad: "media", url: (window.BASE_URL || "") + "/atender-publicacion" },
      { fecha: "18:20 hoy", tipo: "Recarga pendiente con comprobante", prioridad: "alta", url: (window.BASE_URL || "") + "/atender-recargas" }
    ]
  };

  // Set KPIs
  if (el("kpiCuentasPend")) el("kpiCuentasPend").textContent = data.kpis.cuentas.pendientes;
  if (el("kpiCuentasAprob")) el("kpiCuentasAprob").textContent = data.kpis.cuentas.aprobadas_hoy;
  if (el("kpiCuentasRech")) el("kpiCuentasRech").textContent = data.kpis.cuentas.rechazadas;

  if (el("kpiPubRevision")) el("kpiPubRevision").textContent = data.kpis.publicaciones.en_revision;
  if (el("kpiPubReport")) el("kpiPubReport").textContent = data.kpis.publicaciones.reportadas;
  if (el("kpiPubSusp")) el("kpiPubSusp").textContent = data.kpis.publicaciones.suspendidas;

  if (el("kpiRecPend")) el("kpiRecPend").textContent = data.kpis.recargas.pendientes;
  if (el("kpiRecVal")) el("kpiRecVal").textContent = data.kpis.recargas.validadas_hoy;
  if (el("kpiRecObs")) el("kpiRecObs").textContent = data.kpis.recargas.observadas;

  // Render tabla “Atender ahora”
  if (!body) return;

  const normPriority = (p) => (p || "").toString().trim().toLowerCase();

  const badgeClass = (p) => {
    const v = normPriority(p);
    if (v === "alta") return "ev-badge ev-badge-alta";
    if (v === "baja") return "ev-badge ev-badge-baja";
    return "ev-badge ev-badge-media";
  };

  const badgeText = (p) => {
    const v = normPriority(p);
    if (v === "alta") return "alta";
    if (v === "baja") return "baja";
    return "media";
  };

  if (!data.atender.length) {
    body.innerHTML = `<tr><td colspan="3" class="text-center py-4 ev-empty">No hay solicitudes pendientes.</td></tr>`;
    return;
  }

  body.innerHTML = data.atender.map(item => `
    <tr>
      <td class="fw-semibold">${item.fecha}</td>
      <td>
        <div class="d-flex align-items-center gap-2">
          <span class="${badgeClass(item.prioridad)}">${badgeText(item.prioridad)}</span>
          <span class="fw-semibold">${item.tipo}</span>
        </div>
      </td>
      <td class="text-end">
        <a class="ev-btn-atender" href="${item.url}">Atender</a>
      </td>
    </tr>
  `).join("");
});
