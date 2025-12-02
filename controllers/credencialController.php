<?php
// controllers/credencialController.php

require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../models/User.php';

class credencialController
{
    public function index()
    {
        try {
            // 1) Validación de token (el router ya valida, esto es refuerzo)
            $token = $_COOKIE['auth_token'] ?? null;
            if (!$token) {
                return $this->resolverNoAutorizado('sin_token');
            }

            $datosToken = SesionJWT::verificarToken($token);
            if (!$datosToken || empty($datosToken['email'])) {
                return $this->resolverNoAutorizado('token_invalido');
            }

            // 2) Obtener datos completos del usuario (para mostrar correo, nombre, etc.)
            $email = $datosToken['email'];
            $objUsuario = new User();
            $datosUsuario = $objUsuario->DatosUsuario($email);

            if (!$datosUsuario) {
                return $this->resolverNoAutorizado('usuario_no_encontrado');
            }

            // 3) Devolver siempre vista parcial para inyectar en #contenido-principal
            header('X-Partial-Ok: 1');
            require __DIR__ . '/../views/CredencialesView.php';
            return;

        } catch (Throwable $e) {
            error_log("Error en credencialController::index -> " . $e->getMessage());

            if ($this->esPeticionParcial()) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'error'   => 'Error del servidor',
                    'detalle' => $e->getMessage()
                ]);
                return;
            }

            header("Location: /entrevecinos/?error=credencial_error");
            return;
        }
    }

    // --------------------------------------------------------
    // Helpers internos (mismo patrón que miPerfilController)
    // --------------------------------------------------------
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

    private function resolverNoAutorizado(string $motivo = 'no_autorizado')
    {
        if ($this->esPeticionParcial()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'No autorizado', 'motivo' => $motivo]);
            return;
        }
        header("Location: /entrevecinos/?error={$motivo}");
        return;
    }
}
