<?php
// controllers/miPerfilController.php
declare(strict_types=1);

require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../models/User.php';

class miPerfilController
{
    public function index()
    {
        try {
            // 1) Validación de token (router ya valida, esto es refuerzo)
            $token = $_COOKIE['auth_token'] ?? null;
            if (!$token) {
                $this->renderNoAutorizadoHtml('sin_token');
                return;
            }

            $datosToken = SesionJWT::verificarToken($token);
            if (!$datosToken || empty($datosToken['email'])) {
                $this->renderNoAutorizadoHtml('token_invalido');
                return;
            }

            // 2) Obtener datos completos del usuario
            $email = (string)$datosToken['email'];

            $objUsuario = new User();
            $datosUsuario = $objUsuario->DatosUsuario($email);

            if (!$datosUsuario) {
                $this->renderNoAutorizadoHtml('usuario_no_encontrado');
                return;
            }

            // 3) Render HTML SIEMPRE
            header('Content-Type: text/html; charset=utf-8');
            header('X-Partial-Ok: 1');

            // Tu vista espera $datosUsuario
            require __DIR__ . '/../views/datosPersonalesView.php';
            return;

        } catch (Throwable $e) {
            error_log("Error en miPerfilController::index -> " . $e->getMessage());

            // IMPORTANTE: NO devolver JSON aquí (menu AJAX espera HTML)
            $this->renderErrorHtml(
                'Error al cargar Datos personales',
                'Ocurrió un error interno. Revisa el log de PHP/Apache para el detalle.',
                500
            );
            return;
        }
    }

    private function esPeticionParcial(): bool
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }
        if (isset($_SERVER['HTTP_X_PARTIAL']) && $_SERVER['HTTP_X_PARTIAL'] === '1') {
            return true;
        }
        if (isset($_GET['partial']) && $_GET['partial'] === '1') {
            return true;
        }
        return false;
    }

    private function renderNoAutorizadoHtml(string $motivo): void
    {
        // En parcial: mostramos alerta HTML (no JSON)
        if ($this->esPeticionParcial()) {
            $this->renderErrorHtml(
                'Sesión no válida',
                'Tu sesión no está disponible (' . $motivo . '). Vuelve a iniciar sesión.',
                401
            );
            return;
        }

        // Acceso directo (no parcial): redirigir al login
        header("Location: /entrevecinos/?error={$motivo}");
    }

    private function renderErrorHtml(string $titulo, string $mensaje, int $httpCode = 500): void
    {
        http_response_code($httpCode);
        header('Content-Type: text/html; charset=utf-8');

        echo '<div class="alert alert-danger border-0 shadow-sm rounded-4" style="max-width:900px;margin:16px auto;">';
        echo '  <div class="d-flex align-items-start gap-2">';
        echo '    <i class="bi bi-exclamation-triangle-fill" style="font-size:20px;"></i>';
        echo '    <div>';
        echo '      <h6 class="mb-1" style="font-weight:800;">' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</h6>';
        echo '      <div style="opacity:.9;">' . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') . '</div>';
        echo '      <small class="d-block mt-2 text-muted">Si esto ocurre en local, revisa el log de Apache/PHP.</small>';
        echo '    </div>';
        echo '  </div>';
        echo '</div>';
    }
}
