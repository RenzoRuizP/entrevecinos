<?php
$mensajeFuncionalidad = $mensajeFuncionalidad ?? 'Esta funcionalidad no está disponible durante la fase actual del piloto.';
?>
<section class="container-fluid py-4 px-3 px-lg-4">
  <div class="card border-0 shadow-sm rounded-4 mx-auto" style="max-width:760px;">
    <div class="card-body p-4 p-lg-5 text-center">
      <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
           style="width:72px;height:72px;background:#FFF4E8;color:#EA7C12;font-size:2rem;">
        <i class="bi bi-calendar2-check"></i>
      </div>
      <h2 class="fw-bold mb-2" style="color:#0F592F;">Funcionalidad temporalmente no disponible</h2>
      <p class="text-muted mb-4"><?= htmlspecialchars((string)$mensajeFuncionalidad, ENT_QUOTES, 'UTF-8') ?></p>
      <button type="button" class="btn px-4 py-2 fw-semibold text-white" style="background:#EA7C12;border-radius:12px;" data-ev-route="/MenuPrincipal">
        Volver al inicio
      </button>
    </div>
  </div>
</section>
