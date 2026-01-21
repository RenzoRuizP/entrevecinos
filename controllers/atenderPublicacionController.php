<?php
// controllers/atenderPublicacionController.php

require_once __DIR__ . '/../models/SesionJWT.php';

class atenderPublicacionController
{
    private function obtenerUsuarioAuth(): ?array
    {
        $token = $_COOKIE['auth_token'] ?? null;
        if (!$token) return null;
        $u = SesionJWT::verificarToken($token);
        return is_array($u) ? $u : null;
    }

    private function esSoporteOrAdmin(array $u): bool
    {
        $codigoRol = (int)($u['codigo_rol'] ?? 0);
        return ($codigoRol === 3 || $codigoRol === 1);
    }

    private function esParcial(): bool
    {
        if (!empty($_GET['partial']) && $_GET['partial'] === '1') return true;
        $xrw = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        if ($xrw && strtolower($xrw) === 'xmlhttprequest') return true;
        $xp = $_SERVER['HTTP_X_PARTIAL'] ?? '';
        return ($xp === '1');
    }

    public function index(): void
    {
        $u = $this->obtenerUsuarioAuth();
        if (!$u) {
            http_response_code(401);
            if ($this->esParcial()) {
                echo "<div class='alert alert-warning m-3'>Sesión expirada. Vuelve a iniciar sesión.</div>";
                return;
            }
            header('Location: ' . (defined('BASE_URL') ? rtrim(BASE_URL, '/') . '/login' : '/login'));
            return;
        }

        if (!$this->esSoporteOrAdmin($u)) {
            http_response_code(403);

            if ($this->esParcial()) {
                ?>
                <div class="alert alert-warning m-3 shadow-sm rounded-3">
                    <h5 class="mb-1"><i class="bi bi-shield-lock-fill me-2"></i>Acceso denegado</h5>
                    <div>Solo <b>Soporte</b> o <b>Administrador</b> puede acceder a <b>Atender publicación</b>.</div>
                </div>
                <?php
                return;
            }

            echo "Acceso denegado.";
            return;
        }

        $viewPath = __DIR__ . '/../views/AtenderPublicacionView.php';
        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo "Error interno: no se encontró la vista views/AtenderPublicacionView.php.";
            return;
        }

        require $viewPath;
    }
}
