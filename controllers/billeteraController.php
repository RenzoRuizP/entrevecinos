<?php
// controllers/billeteraController.php

require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../models/User.php';

class billeteraController
{
    public function index()
    {
        try {
            // 1) Validación de token (refuerzo, además del router)
            $token = $_COOKIE['auth_token'] ?? null;
            if (!$token) {
                return $this->resolverNoAutorizado('sin_token');
            }

            $datosToken = SesionJWT::verificarToken($token);
            if (!$datosToken || empty($datosToken['email'])) {
                return $this->resolverNoAutorizado('token_invalido');
            }

            // 2) Obtener datos básicos del usuario (por si luego los usamos)
            $email       = $datosToken['email'];
            $objUsuario  = new User();
            $datosUsuario = $objUsuario->DatosUsuario($email);

            if (!$datosUsuario) {
                return $this->resolverNoAutorizado('usuario_no_encontrado');
            }

            // 3) Responder SIEMPRE como parcial (igual que marketplace/publicacion)
            header('X-Partial-Ok: 1');

            // Puedes usar $datosUsuario si lo necesitas dentro de la vista
            require __DIR__ . '/../views/billeteraView.php';
            return;

        } catch (Throwable $e) {
            error_log("Error en billeteraController::index -> " . $e->getMessage());

            if ($this->esPeticionParcial()) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'error'   => 'Error del servidor',
                    'detalle' => $e->getMessage()
                ]);
                return;
            }

            header("Location: /entrevecinos/?error=token_error");
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
            echo json_encode(['error' => 'No autorizado', 'motivo' => $motivo]);
            return;
        }
        header("Location: /entrevecinos/?error={$motivo}");
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
