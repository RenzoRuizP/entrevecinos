<?php
// controllers/marketplaceController.php

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Producto.php';

class marketplaceController
{
    public function index()
    {
        try {
            // El router ya validó token, pero blindamos sin romper flujo
            $token = $_COOKIE['auth_token'] ?? null;
            $datosToken = $token ? SesionJWT::verificarToken($token) : null;

            $codigoUsuario = (int)($datosToken['codigo_usuario'] ?? 0);
            $email = (string)($datosToken['email'] ?? '');

            if ($codigoUsuario <= 0 || $email === '') {
                return $this->resolverNoAutorizado('token_invalido');
            }

            // Intentar cargar datos extendidos (puede fallar para algunos roles)
            $objUsuario = new User();
            $datosUsuario = $objUsuario->DatosUsuario($email);

            // ✅ Fallback si no hay datos extendidos
            if (!$datosUsuario || !is_array($datosUsuario)) {
                $datosUsuario = [
                    'nombre'       => $datosToken['nombre'] ?? 'Usuario',
                    'email'        => $email,
                    'rol'          => $datosToken['rol'] ?? null,
                    '_fallback'    => 1
                ];
            }

            $prod = new Producto();
            $rol = strtolower(trim((string)($datosToken['rol'] ?? $datosToken['nombre_rol'] ?? $datosUsuario['rol'] ?? '')));
            $codigoRol = (int)($datosToken['codigo_rol'] ?? 0);
            $adminRoleId = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
            $esAdminMarketplace = ($rol === 'admin' || $rol === 'administrador' || $codigoRol === $adminRoleId);
            $comunidadesMarketplaceAdmin = [];
            if ($esAdminMarketplace) {
                $comunidadesMarketplaceAdmin = $prod->listarComunidadesActivasMarketplace();

                /*
                 * El administrador general consulta distintas comunidades y no debe
                 * ingresar al Marketplace con una selección implícita. La comunidad
                 * se define únicamente cuando el usuario la elige en el selector.
                 */
                $conjunto = [
                    'tipo_conjunto' => null,
                    'nombre' => 'Selecciona una comunidad',
                    'codigo_comunidad' => 0,
                ];
            } else {
                $conjunto = $prod->obtenerNombreConjuntoActivoUsuario($codigoUsuario);
            }
            $datosUsuario['conjunto_tipo'] = $conjunto['tipo_conjunto'] ?? null;
            $datosUsuario['conjunto_nombre'] = $conjunto['nombre'] ?? null;
            $datosUsuario['conjunto_codigo'] = (int)($conjunto['codigo_comunidad'] ?? 0);
            $datosUsuario['condominio'] = $datosUsuario['conjunto_nombre'] ?? ($datosUsuario['condominio'] ?? null);

            header('X-Partial-Ok: 1');
            require __DIR__ . '/../views/marketplaceView.php';
            return;

        } catch (Throwable $e) {
            error_log("Error en marketplaceController::index -> " . $e->getMessage());

            if ($this->esPeticionParcial()) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'ok'      => false,
                    'error'   => 'SERVER_ERROR',
                    'mensaje' => 'Error del servidor al cargar Marketplace.'
                ]);
                return;
            }

            header('Location: ' . rtrim(BASE_URL, '/') . '/login');
            return;
        }
    }

    private function resolverNoAutorizado(string $motivo = 'no_autorizado')
    {
        if ($this->esPeticionParcial()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'UNAUTHORIZED', 'motivo' => $motivo]);
            return;
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/login');
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