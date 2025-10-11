<?php
// controllers/api/UsuarioDatosController.php
require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/Usuario.php';

class UsuarioDatosController
{
    public function obtenerDatos()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            // Verificar si existe cookie JWT
            if (!isset($_COOKIE['jwt_token'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Token no encontrado']);
                return;
            }

            $token = $_COOKIE['jwt_token'];

            $jwt = new SesionJWT();
            $datosToken = $jwt->verificarToken($token);

            if (!$datosToken) {
                http_response_code(401);
                echo json_encode(['error' => 'Token inválido o expirado']);
                return;
            }

            $usuario = new Usuario();
            $data = $usuario->obtenerPorId($datosToken['id_usuario']);

            if ($data) {
                echo json_encode(['success' => true, 'usuario' => $data]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Usuario no encontrado']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error del servidor', 'detalle' => $e->getMessage()]);
        }
    }
}
