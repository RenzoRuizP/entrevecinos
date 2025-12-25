<?php
// controllers/marketplaceController.php

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../models/User.php';

class marketplaceController
{
    public function index()
    {
        try {
            // El router ya validó token, pero blindamos sin romper flujo
            $token = $_COOKIE['auth_token'] ?? null;
            $datosToken = $token ? SesionJWT::verificarToken($token) : null;

            $email = (string)($datosToken['email'] ?? '');
            if ($email === '') {
                return $this->resolverNoAutorizado('token_invalido');
            }

            // Intentar cargar datos extendidos (puede fallar para admin si no tiene residencia)
            $objUsuario = new User();
            $datosUsuario = $objUsuario->DatosUsuario($email);

            // ✅ SOLUCIÓN DE RAÍZ:
            // Si no hay datos extendidos, NO es “sesión expirada”.
            // Construimos fallback con datos del token.
            if (!$datosUsuario || !is_array($datosUsuario)) {
                $datosUsuario = [
                    'nombre'       => $datosToken['nombre'] ?? 'Usuario',
                    'email'        => $email,
                    // Tu vista usa $datosUsuario['condominio'] (y luego fallback a "tu condominio")
                    'condominio'   => $datosToken['condominio_nombre'] ?? null,
                    'torre'        => $datosToken['torre_nombre'] ?? null,
                    'departamento' => $datosToken['departamento_numero'] ?? null,
                    'rol'          => $datosToken['rol'] ?? null,
                    '_fallback'    => 1
                ];
            }

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
