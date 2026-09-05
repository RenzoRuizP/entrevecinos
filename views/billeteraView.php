<?php
require_once __DIR__ . '/../Config/config.php';

$evRetiroBancos = ev_retiro_bank_rules();
$evWalletSection = in_array(($evWalletSection ?? 'resumen'), ['resumen', 'recargar', 'retirar'], true)
    ? (string)$evWalletSection
    : 'resumen';

$evWalletMeta = [
    'resumen' => [
        'kicker' => 'BILLETERA · RESUMEN',
        'title' => 'Billetera',
        'subtitle' => 'Consulta tu saldo y la trazabilidad de los movimientos realizados dentro de Entre Vecinos.',
        'icon' => 'bi-wallet2',
    ],
    'recargar' => [
        'kicker' => 'BILLETERA · RECARGAR SALDO',
        'title' => 'Recargar saldo',
        'subtitle' => 'Registra una recarga mediante Yape o Plin y consulta el estado de cada solicitud enviada.',
        'icon' => 'bi-plus-circle',
    ],
    'retirar' => [
        'kicker' => 'BILLETERA · RETIRAR SALDO',
        'title' => 'Retirar saldo',
        'subtitle' => 'Gestiona tu cuenta bancaria, solicita una liquidación por corte y revisa el historial de tus pagos.',
        'icon' => 'bi-bank',
    ],
];
$evWalletPage = $evWalletMeta[$evWalletSection];

$evWalletShellUrl = static function (string $ruta): string {
    return rtrim(BASE_URL, '/') . '/MenuPrincipal?ev_goto=' . rawurlencode($ruta);
};
?>
<script>
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
  window.EV_WALLET_CONFIG = Object.freeze({
    section: <?= json_encode($evWalletSection, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
    recargasDisponibles: <?= (($evRecargasDisponibles ?? true) === true) ? 'true' : 'false' ?>,
    retirosDisponibles: <?= (($evEsVecino ?? false) === true) ? 'true' : 'false' ?>,
    csrf: <?= json_encode((string)($evWalletCsrf ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
    bancosRetiro: <?= json_encode($evRetiroBancos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
  });
</script>

<?php include_once __DIR__ . '/estilos/billeteraEstilo.php'; ?>

<div class="container-fluid ev-wallet-page ev-wallet-wrapper fade-in" data-wallet-section="<?= htmlspecialchars($evWalletSection, ENT_QUOTES, 'UTF-8') ?>">
  <section class="ev-wallet-hero mb-4">
    <div class="ev-wallet-hero-content">
      <div class="ev-wallet-title-wrap">
        <div class="ev-wallet-title-icon" aria-hidden="true">
          <i class="bi <?= htmlspecialchars($evWalletPage['icon'], ENT_QUOTES, 'UTF-8') ?>"></i>
        </div>

        <div>
          <div class="ev-wallet-kicker"><?= htmlspecialchars($evWalletPage['kicker'], ENT_QUOTES, 'UTF-8') ?></div>
          <h2 class="ev-wallet-title"><?= htmlspecialchars($evWalletPage['title'], ENT_QUOTES, 'UTF-8') ?></h2>
          <p class="ev-wallet-subtitle"><?= htmlspecialchars($evWalletPage['subtitle'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
      </div>

      <?php if ($evWalletSection === 'resumen'): ?>
        <div class="ev-wallet-summary-grid" aria-label="Resumen de billetera">
          <article class="ev-wallet-summary-card ev-wallet-summary-card--balance">
            <span>Saldo disponible</span>
            <strong id="ev_wallet_saldo">S/ 0.00</strong>
            <small>Disponible en tu billetera</small>
          </article>
          <article class="ev-wallet-summary-card">
            <span>Movimientos</span>
            <strong id="ev_wallet_total_movimientos">0</strong>
            <small>Cargos y abonos registrados</small>
          </article>
          <article class="ev-wallet-summary-card">
            <span>Recargas</span>
            <strong id="ev_wallet_total_recargas">0</strong>
            <small>Solicitudes enviadas</small>
          </article>
        </div>
      <?php elseif ($evWalletSection === 'recargar'): ?>
        <div class="ev-wallet-summary-grid ev-wallet-summary-grid--compact" aria-label="Resumen de recargas">
          <article class="ev-wallet-summary-card ev-wallet-summary-card--balance">
            <span>Saldo disponible</span>
            <strong id="ev_wallet_saldo">S/ 0.00</strong>
            <small>Saldo actual de tu billetera</small>
          </article>
          <article class="ev-wallet-summary-card">
            <span>Recargas registradas</span>
            <strong id="ev_wallet_total_recargas">0</strong>
            <small>Solicitudes enviadas a validación</small>
          </article>
          <article class="ev-wallet-summary-card ev-wallet-summary-card--secure">
            <span>Validación EV</span>
            <strong><i class="bi bi-shield-check"></i></strong>
            <small>Soporte revisa cada comprobante</small>
          </article>
        </div>
      <?php else: ?>
        <div class="ev-wallet-summary-grid" aria-label="Resumen de retiro">
          <article class="ev-wallet-summary-card ev-wallet-summary-card--balance">
            <span>Disponible ahora</span>
            <strong id="ev_retiro_saldo_actual">S/ 0.00</strong>
            <small>Saldo actual de tu billetera</small>
          </article>
          <article class="ev-wallet-summary-card">
            <span>En retiro</span>
            <strong id="ev_retiro_en_proceso">S/ 0.00</strong>
            <small>Pendiente de pago o revisión</small>
          </article>
          <article class="ev-wallet-summary-card ev-wallet-summary-card--payment">
            <span>Próximo pago</span>
            <strong id="ev_retiro_proximo_pago">—</strong>
            <small>Según la ventana de corte vigente</small>
          </article>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <?php if ($evWalletSection === 'resumen'): ?>
    <section class="ev-wallet-panel">
      <header class="ev-wallet-panel-head">
        <div>
          <h5>Resumen financiero</h5>
          <p>Accede a las operaciones principales de tu billetera y revisa los últimos movimientos sin mezclar procesos.</p>
        </div>
        <button type="button" class="btn ev-wallet-btn-outline" data-bs-toggle="modal" data-bs-target="#modalSoporteBilletera">
          <i class="bi bi-headset"></i><span>Soporte técnico</span>
        </button>
      </header>

      <div class="ev-wallet-panel-body">
        <section class="ev-wallet-action-grid" aria-label="Operaciones de billetera">
          <?php if (($evRecargasDisponibles ?? true) === true): ?>
            <a class="ev-wallet-action-card ev-wallet-action-card--orange"
               href="<?= htmlspecialchars($evWalletShellUrl('/billetera/recargar'), ENT_QUOTES, 'UTF-8') ?>"
               data-ev-wallet-route="/billetera/recargar">
              <span class="ev-wallet-action-icon"><i class="bi bi-plus-circle"></i></span>
              <span class="ev-wallet-action-copy">
                <strong>Recargar saldo</strong>
                <small>Registra un pago por Yape o Plin y consulta su validación.</small>
              </span>
              <i class="bi bi-arrow-right ev-wallet-action-arrow" aria-hidden="true"></i>
            </a>
          <?php else: ?>
            <article class="ev-wallet-action-card is-disabled" aria-disabled="true">
              <span class="ev-wallet-action-icon"><i class="bi bi-plus-circle"></i></span>
              <span class="ev-wallet-action-copy">
                <strong>Recargar saldo</strong>
                <small>Las recargas no están disponibles en tu comunidad.</small>
              </span>
            </article>
          <?php endif; ?>

          <?php if (($evEsVecino ?? false) === true): ?>
            <a class="ev-wallet-action-card"
               href="<?= htmlspecialchars($evWalletShellUrl('/billetera/retirar'), ENT_QUOTES, 'UTF-8') ?>"
               data-ev-wallet-route="/billetera/retirar">
              <span class="ev-wallet-action-icon"><i class="bi bi-bank"></i></span>
              <span class="ev-wallet-action-copy">
                <strong>Retirar saldo</strong>
                <small>Gestiona tu cuenta bancaria y solicita el retiro en el corte vigente.</small>
              </span>
              <i class="bi bi-arrow-right ev-wallet-action-arrow" aria-hidden="true"></i>
            </a>
          <?php endif; ?>
        </section>

        <section class="ev-wallet-activity-block" aria-labelledby="evWalletMovimientosTitle">
          <header class="ev-wallet-activity-head">
            <div class="ev-wallet-activity-title-wrap">
              <span class="ev-wallet-activity-icon" aria-hidden="true"><i class="bi bi-clock-history"></i></span>
              <div>
                <h3 id="evWalletMovimientosTitle">Movimientos recientes</h3>
                <p>Consulta cargos, abonos, devoluciones, ventas liberadas y retiros registrados.</p>
              </div>
            </div>
          </header>
          <div id="ev_wallet_empty_state" class="ev-wallet-empty">
            <i class="bi bi-wallet2"></i>
            <div><strong>Aún no tienes movimientos en tu billetera.</strong><span>Cuando compres, vendas o recargues, aparecerán aquí.</span></div>
          </div>
          <div id="ev_wallet_movimientos" class="ev-wallet-movimientos d-none"></div>
        </section>
      </div>
    </section>

  <?php elseif ($evWalletSection === 'recargar'): ?>
    <section class="ev-wallet-panel">
      <header class="ev-wallet-panel-head">
        <div>
          <h5>Nueva recarga</h5>
          <p>Registra los mismos datos que figuran en tu comprobante. Soporte EV validará la solicitud antes de acreditar el saldo.</p>
        </div>
        <button type="button" class="btn ev-wallet-btn-outline" data-bs-toggle="modal" data-bs-target="#modalSoporteBilletera">
          <i class="bi bi-headset"></i><span>Soporte técnico</span>
        </button>
      </header>

      <div class="ev-wallet-panel-body">
        <section class="ev-wallet-recharge-form-card" aria-labelledby="modalRecargaTitulo">
          <div class="ev-wallet-section-heading">
            <span class="ev-wallet-section-heading-icon ev-wallet-section-heading-icon--orange"><i class="bi bi-receipt"></i></span>
            <div>
              <h3 id="modalRecargaTitulo">Registrar recarga</h3>
              <p>Completa la operación y adjunta una imagen clara del comprobante.</p>
            </div>
          </div>

          <form id="formRecargaSaldo" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" id="recarga_codigo" name="recarga_codigo" value="">
            <input type="hidden" id="recarga_modo" name="recarga_modo" value="crear">

            <div id="recarga_alerta_subsanacion" class="alert alert-warning d-none mb-3" role="alert">
              <div class="fw-semibold mb-1"><i class="bi bi-exclamation-circle me-1"></i> Recarga observada</div>
              <div id="recarga_alerta_subsanacion_texto" class="small mb-0">Corrige los datos observados por soporte y vuelve a enviarla.</div>
            </div>

            <div class="row g-4 align-items-stretch">
              <div class="col-xl-7">
                <div class="ev-wallet-inline-form-panel h-100">
                  <div class="row g-3 ev-wallet-recarga-form-grid">
                    <div class="col-md-6">
                      <label class="form-label" for="recarga_tipo">Tipo de billetera</label>
                      <select class="form-select" id="recarga_tipo" name="recarga_tipo">
                        <option value="">Selecciona</option>
                        <option value="yape">Yape</option>
                        <option value="plin">Plin</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label" for="recarga_monto">Monto a recargar</label>
                      <input type="number" min="0.01" step="0.01" class="form-control" id="recarga_monto" name="recarga_monto" placeholder="Ej. 10.00">
                    </div>
                    <div class="col-12">
                      <label class="form-label" for="recarga_operacion">ID de operación</label>
                      <input type="text" class="form-control" id="recarga_operacion" name="recarga_operacion" placeholder="Ej. AJ5075653">
                      <div class="form-text">Ingresa exactamente el código/ID que figura en Yape o Plin.</div>
                    </div>
                    <div class="col-12">
                      <label class="form-label" for="recarga_imagen">Comprobante o recibo</label>
                      <input type="file" class="form-control" id="recarga_imagen" name="recarga_imagen" accept="image/*">
                      <div class="form-text" id="recarga_imagen_help">Sube una foto clara del voucher o comprobante de la recarga.</div>
                    </div>
                  </div>

                  <div class="ev-wallet-inline-actions">
                    <button type="button" class="btn ev-wallet-btn-outline" id="btnLimpiarRecarga">Limpiar</button>
                    <button type="button" class="btn ev-wallet-btn-primary" id="btnEnviarRecarga">Guardar</button>
                  </div>
                </div>
              </div>

              <div class="col-xl-5">
                <div class="ev-wallet-payment-guide h-100">
                  <span class="ev-wallet-payment-guide-badge"><i class="bi bi-shield-check"></i> Pago seguro</span>
                  <h4>Realiza el pago y registra el comprobante</h4>
                  <p>Selecciona Yape o Plin para visualizar el QR correspondiente. El saldo se acreditará únicamente después de la validación de Soporte EV.</p>
                  <div class="ev-wallet-qr-card text-center d-none" id="ev_qr_card">
                    <img src="<?= BASE_URL ?>resources/images/plin.jpeg" alt="QR billetera" class="ev-wallet-qr-img" id="ev_qr_img">
                    <p class="ev-wallet-qr-title mb-1" id="ev_qr_title">Paga tu recarga con Plin</p>
                    <p class="ev-wallet-qr-text mb-0" id="ev_qr_text">Escanea este código, realiza el pago y registra el comprobante.</p>
                  </div>
                  <div class="ev-wallet-payment-steps" id="ev_wallet_recarga_steps">
                    <span><b>1</b> Selecciona Yape o Plin.</span>
                    <span><b>2</b> Realiza el pago por el monto indicado.</span>
                    <span><b>3</b> Registra el ID y adjunta el comprobante.</span>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </section>

        <section class="ev-wallet-activity-block" aria-labelledby="evWalletRecargasTitle">
          <header class="ev-wallet-activity-head">
            <div class="ev-wallet-activity-title-wrap">
              <span class="ev-wallet-activity-icon" aria-hidden="true"><i class="bi bi-receipt-cutoff"></i></span>
              <div>
                <h3 id="evWalletRecargasTitle">Mis recargas</h3>
                <p>Consulta el estado de tus solicitudes y las observaciones registradas por Soporte.</p>
              </div>
            </div>
            <button type="button" class="btn ev-wallet-btn-refresh" id="btnRefrescarRecargas" aria-label="Actualizar recargas" title="Actualizar">
              <i class="bi bi-arrow-clockwise"></i><span>Actualizar</span>
            </button>
          </header>
          <div id="ev_recargas_empty" class="ev-wallet-empty">
            <i class="bi bi-receipt"></i>
            <div><strong>Aún no registras recargas.</strong><span>Cuando envíes una solicitud, podrás consultar aquí su estado.</span></div>
          </div>
          <div id="ev_recargas_table" class="d-none"></div>
        </section>
      </div>
    </section>

  <?php else: ?>
    <section class="ev-wallet-panel">
      <header class="ev-wallet-panel-head">
        <div>
          <h5>Liquidación de ventas</h5>
          <p>Solicita una sola liquidación por corte. El importe final se calcula al cierre y EV mantiene S/ 20.00 en tu billetera.</p>
        </div>
        <button type="button" class="btn ev-wallet-btn-outline" data-bs-toggle="modal" data-bs-target="#modalSoporteBilletera">
          <i class="bi bi-headset"></i><span>Soporte técnico</span>
        </button>
      </header>

      <div class="ev-wallet-panel-body">
        <section class="ev-wallet-withdraw-block ev-wallet-withdraw-block--standalone" aria-labelledby="evWalletRetirosTitle">
          <header class="ev-wallet-withdraw-head">
            <div class="ev-wallet-activity-title-wrap">
              <span class="ev-wallet-activity-icon ev-wallet-activity-icon--withdraw" aria-hidden="true"><i class="bi bi-calendar2-check"></i></span>
              <div>
                <h3 id="evWalletRetirosTitle">Corte vigente</h3>
                <p id="ev_retiro_corte_detalle">Consultando la ventana de retiro disponible...</p>
              </div>
            </div>
            <span class="ev-wallet-withdraw-badge" id="ev_retiro_estado_corte">Cargando corte...</span>
          </header>

          <div class="ev-wallet-withdraw-account">
            <div>
              <span class="ev-wallet-withdraw-label">Cuenta para recibir pagos</span>
              <strong id="ev_retiro_cuenta_resumen">Aún no registrada</strong>
              <small id="ev_retiro_cuenta_estado">Registra una cuenta bancaria a tu nombre.</small>
            </div>
            <div class="ev-wallet-withdraw-actions">
              <button type="button" class="btn ev-wallet-btn-outline" id="btnCuentaRetiro" data-bs-toggle="modal" data-bs-target="#modalCuentaRetiro">Registrar cuenta</button>
              <button type="button" class="btn ev-wallet-btn-primary" id="btnRetirarSaldo" disabled>Retirar saldo</button>
            </div>
          </div>

          <div id="ev_retiro_mensaje" class="ev-wallet-withdraw-message d-none" role="status"></div>

          <div class="ev-wallet-withdraw-history">
            <div class="ev-wallet-withdraw-history-head">
              <div>
                <h4>Historial de retiros</h4>
                <p>Revisa la jornada, el monto definitivo y el estado de cada liquidación.</p>
              </div>
            </div>
            <div id="ev_retiros_historial" class="ev-wallet-withdraw-history-body">
              <div class="ev-wallet-empty ev-wallet-empty--compact">
                <i class="bi bi-cash-stack"></i>
                <div><strong>Aún no tienes retiros.</strong><span>Cuando solicites uno, aparecerá aquí.</span></div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </section>
  <?php endif; ?>
</div>

<?php if ($evWalletSection === 'retirar' && ($evEsVecino ?? false) === true): ?>
<div class="modal fade ev-modal ev-modal-login" id="modalCuentaRetiro" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content ev-modal-content">
      <div class="modal-header ev-login-modal-header">
        <h5 class="modal-title mb-0">Cuenta para recibir pagos</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body ev-login-modal-body">
        <form id="formCuentaRetiro" autocomplete="off">
          <div class="ev-wallet-bank-owner mb-3">
            <span>El titular se toma de tu cuenta Entre Vecinos</span>
            <strong id="retiro_titular_nombre"><?= htmlspecialchars((string)($datosUsuario['nombre'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></strong>
            <small id="retiro_titular_documento">Documento: <?= htmlspecialchars((string)($datosUsuario['documento'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></small>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="retiro_banco">Banco</label>
              <select class="form-select" id="retiro_banco" name="banco" required>
                <option value="">Selecciona</option>
                <?php foreach ($evRetiroBancos as $evBancoNombre => $evBancoRegla): ?>
                  <option value="<?= htmlspecialchars((string)$evBancoNombre, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$evBancoNombre, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback" id="retiro_banco_error"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="retiro_tipo_cuenta">Tipo de cuenta</label>
              <select class="form-select" id="retiro_tipo_cuenta" name="tipo_cuenta" required>
                <option value="">Selecciona</option>
                <option value="ahorros">Ahorros</option>
                <option value="corriente">Corriente</option>
              </select>
              <div class="invalid-feedback" id="retiro_tipo_cuenta_error">Selecciona el tipo de cuenta.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="retiro_numero_cuenta">Número de cuenta</label>
              <input class="form-control" id="retiro_numero_cuenta" name="numero_cuenta" inputmode="numeric" pattern="[0-9]*" maxlength="20" autocomplete="off" placeholder="Solo números" required aria-describedby="retiro_numero_cuenta_help">
              <div class="form-text" id="retiro_numero_cuenta_help">Selecciona el banco y tipo de cuenta para validar la longitud.</div>
              <div class="invalid-feedback" id="retiro_numero_cuenta_error"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="retiro_cci">CCI</label>
              <input class="form-control" id="retiro_cci" name="cci" inputmode="numeric" pattern="[0-9]*" maxlength="20" autocomplete="off" placeholder="20 dígitos" required aria-describedby="retiro_cci_help">
              <div class="form-text" id="retiro_cci_help">Ingresa los 20 dígitos, sin espacios ni guiones.</div>
              <div class="invalid-feedback" id="retiro_cci_error"></div>
            </div>
            <div class="col-12">
              <label class="ev-wallet-bank-declaration">
                <input type="checkbox" id="retiro_declara_titularidad" name="declara_titularidad" value="1">
                <span>Declaro que la cuenta bancaria registrada se encuentra a mi nombre.</span>
              </label>
            </div>
          </div>
          <div id="retiro_cuenta_observacion" class="alert alert-warning d-none mt-3 mb-0"></div>
        </form>
      </div>
      <div class="modal-footer ev-login-modal-footer ev-wallet-recarga-actions justify-content-end">
        <button type="button" class="btn ev-btn-modal-outline" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn ev-btn-modal-primary" id="btnGuardarCuentaRetiro">Guardar</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="modal fade ev-modal ev-modal-login" id="modalSoporteBilletera" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content ev-modal-content">
      <div class="modal-header ev-login-modal-header">
        <h5 class="modal-title mb-0"><i class="bi bi-headset me-2"></i> Soporte técnico</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body ev-login-modal-body text-center">
        <p class="mb-3 text-muted">Si tienes dudas sobre tus recargas, movimientos o retiros, contáctanos:</p>
        <div class="p-3 border rounded bg-light mx-auto" style="max-width:360px;">
          <p class="fw-bold mb-1 text-dark">Soporte técnico</p>
          <p class="mb-2">Lunes a Viernes: <strong>8:00 AM – 8:00 PM</strong></p>
          <p class="fs-5 text-success mb-0"><i class="bi bi-whatsapp me-1"></i> 956 969 182</p>
          <p class="fs-5 text-success mb-0"><i class="bi bi-telephone-fill me-1"></i> 956 969 182</p>
        </div>
        <p class="text-muted small mb-0 mt-3">Nuestro equipo te ayudará a resolver cualquier problema con tu cuenta o billetera.</p>
      </div>
      <div class="modal-footer ev-login-modal-footer justify-content-between">
        <button type="button" class="btn ev-btn-modal-outline" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Cancelar</button>
        <a href="tel:956969182" class="btn ev-btn-modal-primary ev-btn-modal-cta"><i class="bi bi-telephone me-1"></i> Llamar ahora</a>
      </div>
    </div>
  </div>
</div>
