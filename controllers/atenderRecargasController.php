<?php
// controllers/atenderRecargasController.php

require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Producto.php';

class atenderRecargasController
{
    public function index()
    {
        try {
            $token = $_COOKIE['auth_token'] ?? null;
            if (!$token) {
                return $this->resolverNoAutorizado('sin_token');
            }

            $datosToken = SesionJWT::verificarToken($token);
            if (!$datosToken || empty($datosToken['email'])) {
                return $this->resolverNoAutorizado('token_invalido');
            }

            // ✅ autorización por rol
            $rol = (int)($datosToken['codigo_rol'] ?? 0);
            if (!in_array($rol, [1, 3], true)) {
                http_response_code(403);
                if ($this->esPeticionParcial()) {
                    echo "<div class='alert alert-warning m-3'>Acceso restringido (solo Soporte/Admin).</div>";
                    return;
                }
                header('Location: ' . rtrim(BASE_URL, '/') . '/?error=forbidden');
                return;
            }

            $email = $datosToken['email'];
            $objUsuario = new User();
            $datosUsuario = $objUsuario->DatosUsuario($email);

            if (!$datosUsuario) {
                return $this->resolverNoAutorizado('usuario_no_encontrado');
            }

            $esAdministradorGeneralConsulta = $rol === 1;
            $comunidadesConsultaAdmin = $esAdministradorGeneralConsulta ? (new Producto())->listarComunidadesActivasMarketplace() : [];
            $evAdminScopeModule = 'recargas';
            $evAdminScopeDescription = 'Revisa solicitudes de recarga de vecinos pertenecientes a la comunidad seleccionada.';

            header('X-Partial-Ok: 1');
            require __DIR__ . '/../views/AtenderRecargasView.php';
            return;

        } catch (Throwable $e) {
            error_log("Error en atenderRecargasController::index -> " . $e->getMessage());

            if ($this->esPeticionParcial()) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'error'   => 'Error del servidor',
                    'detalle' => $e->getMessage()
                ]);
                return;
            }

            header('Location: ' . rtrim(BASE_URL, '/') . '/?error=token_error');
            return;
        }
    }

    private function resolverNoAutorizado(string $motivo = 'no_autorizado')
    {
        if ($this->esPeticionParcial()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'No autorizado', 'motivo' => $motivo]);
            return;
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/?error=' . rawurlencode($motivo));
        return;
    }

    private function esPeticionParcial(): bool
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
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
}
