<?php
// controllers/productoController.php

require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ConfiguracionPlataforma.php';

class productoController
{
    public function index()
    {
        try {
            // 1) Validación de token (el router ya valida, esto es refuerzo)
            $token = $_COOKIE['auth_token'] ?? null;
            if (!$token) {
                return $this->resolverNoAutorizado('sin_token');
            }

            // SesionJWT::verificarToken() debe devolver ARRAY
            $datosToken = SesionJWT::verificarToken($token);
            if (!$datosToken || empty($datosToken['email'])) {
                return $this->resolverNoAutorizado('token_invalido');
            }

            // 2) Obtener datos completos del usuario
            $email = $datosToken['email'];
            $objUsuario = new User();
            $datosUsuario = $objUsuario->DatosUsuario($email);

            if (!$datosUsuario) {
                return $this->resolverNoAutorizado('usuario_no_encontrado');
            }

            $codigoUsuario = (int)($datosUsuario['codigo_usuario'] ?? $datosToken['codigo_usuario'] ?? 0);
            $configuracion = new ConfiguracionPlataforma();
            $alcance = $configuracion->obtenerAlcanceUsuario($codigoUsuario);
            $evPublicarProductosDisponible = (bool)($configuracion->obtenerFuncionalidadPorAlcance(
                ConfiguracionPlataforma::FUNC_PUBLICAR_PRODUCTOS,
                (string)$alcance['tipo_alcance'],
                (int)$alcance['codigo_alcance']
            )['habilitada'] ?? false);
            $evPublicarServiciosDisponible = (bool)($configuracion->obtenerFuncionalidadPorAlcance(
                ConfiguracionPlataforma::FUNC_PUBLICAR_SERVICIOS,
                (string)$alcance['tipo_alcance'],
                (int)$alcance['codigo_alcance']
            )['habilitada'] ?? false);

            // 3) SIEMPRE devolver el parcial (evitamos redirecciones al panel)
            //    La vista usa $datosUsuario y BASE_URL para renderizar el formulario
            header('X-Partial-Ok: 1');
            require __DIR__ . '/../views/productoView.php';
            return;

        } catch (Throwable $e) {
            error_log("Error en productoController::index -> " . $e->getMessage());

            // Si es parcial/AJAX, responde JSON de error
            if ($this->esPeticionParcial()) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'error'   => 'Error del servidor',
                    'detalle' => $e->getMessage()
                ]);
                return;
            }

            // Acceso directo: redirigir al login
            header('Location: ' . rtrim(BASE_URL, '/') . '/?error=token_error');
            return;
        }
    }

    private function esPeticionParcial(): bool
    {
        // fetch/ajax clásico
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }
        // header explícito
        if (isset($_SERVER['HTTP_X_PARTIAL']) && $_SERVER['HTTP_X_PARTIAL'] === '1') {
            return true;
        }
        // querystring ?partial=1
        if (isset($_GET['partial']) && $_GET['partial'] === '1') {
            return true;
        }
        return false;
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
}
