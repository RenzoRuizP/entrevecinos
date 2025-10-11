
<?php
require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../config/config.php';

if (!isset($_COOKIE['jwt_token'])) {
  echo '<div class="alert alert-warning text-center mt-4">Tu sesión ha expirado. Por favor, inicia sesión nuevamente.</div>';
  exit;
}

$jwt = new SesionJWT();
$token = $_COOKIE['jwt_token'];
$datosToken = $jwt->verificarToken($token);

if (!$datosToken) {
  echo '<div class="alert alert-danger text-center mt-4">Token inválido o expirado. Inicia sesión nuevamente.</div>';
  exit;
}

$usuarioModel = new Usuario();
$usuario = $usuarioModel->obtenerPorId($datosToken['id_usuario']);
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/css/miPerfil.css">

<div class="container py-4 fade-in">
  <div class="card shadow-lg border-0 rounded-4">
    <div class="card-header bg-success text-white text-center rounded-top-4">
      <h4 class="mb-0">Mi Perfil</h4>
    </div>
    <div class="card-body">
      <div class="row align-items-center mb-4">
        <div class="col-md-3 text-center">
          <img src="<?= BASE_URL ?>/resources/images/avatar/default.png" 
               alt="Usuario" 
               class="img-fluid rounded-circle shadow-sm" 
               style="width: 130px; height: 130px; object-fit: cover;">
        </div>
        <div class="col-md-9">
          <h5 class="fw-bold mb-3"><?= htmlspecialchars($usuario['nombre']) ?></h5>
          <p><i class="bi bi-envelope-fill text-success"></i> <?= htmlspecialchars($usuario['email']) ?></p>
          <p><i class="bi bi-person-badge-fill text-success"></i> <?= htmlspecialchars($usuario['rol']) ?></p>
          <p><i class="bi bi-house-door-fill text-success"></i> <?= htmlspecialchars($usuario['condominio']) ?></p>
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

<script src="<?= BASE_URL ?>/views/js/miPerfil.js"></script>