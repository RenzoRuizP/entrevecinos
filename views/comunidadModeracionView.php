<?php
// views/comunidadModeracionView.php
require_once __DIR__ . '/../Config/config.php';
?>

<?php include_once __DIR__ . '/estilos/comunidadBaseEstilo.php'; ?>

<section class="ev-com-shell fade-in" aria-label="Moderación de Comunidad">
  <header class="ev-com-hero">
    <div>
      <span class="ev-com-kicker">
        <i class="bi bi-shield-check"></i> Comunidad
      </span>

      <h1 class="ev-com-title">Avisos EV y moderación</h1>

      <p class="ev-com-subtitle">
        Gestiona los avisos propios de Entre Vecinos y supervisa publicaciones de las
        comunidades cuando exista un reporte o incumplimiento.
      </p>
    </div>

    <div class="ev-com-pill">
      <i class="bi bi-shield-lock-fill"></i>
      <span>Acceso: Soporte EV / Administrador</span>
    </div>
  </header>

  <div class="ev-com-grid" aria-label="Funciones de moderación">
    <article class="ev-com-card">
      <div class="ev-com-card-icon"><i class="bi bi-broadcast"></i></div>
      <h3>Avisos EV</h3>
      <p>Avisos operativos globales o dirigidos a una comunidad específica.</p>
    </article>

    <article class="ev-com-card">
      <div class="ev-com-card-icon"><i class="bi bi-eye-slash"></i></div>
      <h3>Moderación</h3>
      <p>Ocultamiento motivado de publicaciones que incumplan las reglas de EV.</p>
    </article>

    <article class="ev-com-card">
      <div class="ev-com-card-icon"><i class="bi bi-clock-history"></i></div>
      <h3>Historial</h3>
      <p>Trazabilidad de publicaciones, moderaciones y reactivaciones realizadas.</p>
    </article>
  </div>

  <div class="ev-com-notice" role="status">
    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
    <div>
      <strong>Acceso de moderación habilitado.</strong>
      La gestión funcional de avisos, ocultamientos y reactivaciones se incorporará en el
      bloque de backend y API de Comunidad.
    </div>
  </div>
</section>
