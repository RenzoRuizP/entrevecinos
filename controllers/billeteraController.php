<?php
// controllers/billeteraController.php

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Producto.php';

class billeteraController
{
    public function index()
    {
        try {
            // 1) Validación de token (refuerzo, además del router)
            $token = $_COOKIE['auth_token'] ?? null;
            $datosToken = $token ? SesionJWT::verificarToken($token) : null;

            $codigoUsuario = (int)($datosToken['codigo_usuario'] ?? 0);
            $email = trim((string)($datosToken['email'] ?? ''));

            if ($codigoUsuario <= 0 || $email === '') {
                return $this->resolverNoAutorizado('token_invalido');
            }

            // 2) Intentar enriquecer con User.php, pero SIN bloquear la vista si falla
            $objUsuario = new User();
            $datosUsuario = $objUsuario->DatosUsuario($email);

            if (!$datosUsuario || !is_array($datosUsuario)) {
                $datosUsuario = [
                    'codigo_usuario' => $codigoUsuario,
                    'nombre'         => $datosToken['nombre'] ?? 'Usuario',
                    'email'          => $email,
                    'rol'            => $datosToken['rol'] ?? null,
                    '_fallback'      => 1,
                ];
            } else {
                if (empty($datosUsuario['codigo_usuario'])) {
                    $datosUsuario['codigo_usuario'] = $codigoUsuario;
                }
                if (empty($datosUsuario['email'])) {
                    $datosUsuario['email'] = $email;
                }
            }

            // 3) Enriquecer con conjunto activo (igual criterio que marketplace)
            try {
                $prod = new Producto();
                $conjunto = $prod->obtenerNombreConjuntoActivoUsuario($codigoUsuario);

                $datosUsuario['conjunto_tipo']   = $conjunto['tipo_conjunto'] ?? null;
                $datosUsuario['conjunto_nombre'] = $conjunto['nombre'] ?? null;
                $datosUsuario['condominio']      = $datosUsuario['conjunto_nombre'] ?? ($datosUsuario['condominio'] ?? null);
            } catch (Throwable $e) {
                // No romper la vista por esto
            }

            // 4) Responder como parcial
            header('X-Partial-Ok: 1');
            require __DIR__ . '/../views/billeteraView.php';
            return;

        } catch (Throwable $e) {
            error_log("Error en billeteraController::index -> " . $e->getMessage());

            if ($this->esPeticionParcial()) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'ok'      => false,
                    'error'   => 'SERVER_ERROR',
                    'mensaje' => 'Error del servidor al cargar Mi billetera.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            header('Location: ' . rtrim(BASE_URL, '/') . '/login');
            return;
        }
    }

    // ------------------------------
    // Helpers privados
    // ------------------------------
    private function resolverNoAutorizado(string $motivo = 'no_autorizado')
    {
        if ($this->esPeticionParcial()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok'     => false,
                'error'  => 'UNAUTHORIZED',
                'motivo' => $motivo
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/login');
        return;
    }

    private function esPeticionParcial(): bool
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
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