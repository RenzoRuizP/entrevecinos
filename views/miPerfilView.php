<?php
require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../Config/config.php';

// ============================================================
// 1) Verificar existencia de cookie JWT (auth_token)
// ============================================================
if (!isset($_COOKIE['auth_token'])) {
  echo '<div class="alert alert-warning text-center mt-4">
          Tu sesión ha expirado. Por favor, inicia sesión nuevamente.
        </div>';
  exit;
}

$token = $_COOKIE['auth_token'];

// ============================================================
// 2) Verificar y decodificar token
// ============================================================
$datosToken = SesionJWT::verificarToken($token);

if (!$datosToken || !is_array($datosToken)) {
  echo '<div class="alert alert-danger text-center mt-4">
          Token inválido o expirado. Inicia sesión nuevamente.
        </div>';
  exit;
}

// Esperamos del token al menos:
// - codigo_usuario
// - nombre
// - email
// - rol
// - condominio_nombre (si lo has agregado como hicimos en SesionJWT)
$codigoUsuario = $datosToken['codigo_usuario'] ?? null;

$usuarioModel = new Usuario();
$usuarioBD = null;

if ($codigoUsuario) {
  try {
    $usuarioBD = $usuarioModel->obtenerPorCodigo($codigoUsuario);
  } catch (Exception $e) {
    // Si falla, continuamos solo con los datos del token
    $usuarioBD = null;
  }
}

// ============================================================
// 3) Normalizar datos para la vista
// ============================================================
$nombre = $usuarioBD['nombre_completo'] 
          ?? ($datosToken['nombre'] ?? 'Usuario');

$email = $usuarioBD['email'] 
         ?? ($datosToken['email'] ?? '');

$rol = $datosToken['rol'] ?? 'Vecino';

$condominio = $usuarioBD['condominio'] 
              ?? ($datosToken['condominio_nombre'] ?? 'Tu condominio');

?>
<link rel="stylesheet" href="<?= BASE_URL ?>css/miPerfil.css">

<div class="container py-4 fade-in">
  <div class="card shadow-lg border-0 rounded-4">
    <div class="card-header bg-success text-white text-center rounded-top-4">
      <h4 class="mb-0">Mi Perfil</h4>
    </div>
    <div class="card-body">
      <div class="row align-items-center mb-4">
        <div class="col-md-3 text-center">
          <img src="<?= BASE_URL ?>resources/images/avatar/default.png" 
               alt="Usuario" 
               class="img-fluid rounded-circle shadow-sm" 
               style="width: 130px; height: 130px; object-fit: cover;">
        </div>
        <div class="col-md-9">
          <h5 class="fw-bold mb-3">
            <?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>
          </h5>
          <p>
            <i class="bi bi-envelope-fill text-success"></i>
            <?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>
          </p>
          <p>
            <i class="bi bi-person-badge-fill text-success"></i>
            <?= htmlspecialchars($rol, ENT_QUOTES, 'UTF-8') ?>
          </p>
          <p>
            <i class="bi bi-house-door-fill text-success"></i>
            <?= htmlspecialchars($condominio, ENT_QUOTES, 'UTF-8') ?>
          </p>
        </div>
      </div>

      <hr>

      <div class="text-center">
        <button class="btn btn-outline-success px-4 rounded-pill" id="editarPerfilBtn">
          <i class="bi bi-pencil-square"></i> Editar Perfil
        </button>
      </div>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>views/js/miPerfil.js"></script>
