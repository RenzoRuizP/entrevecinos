<?php
// controllers/api/UsuarioDatosController.php
require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/Usuario.php';

class UsuarioDatosController
{
    /**
     * 🔹 Obtener los datos personales del usuario autenticado
     */
    public function obtenerDatos()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            // ✅ Verificar existencia del token
            if (empty($_COOKIE['auth_token'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Token no encontrado']);
                return;
            }

            $token = $_COOKIE['auth_token'];

            // ✅ Validar el token y obtener datos
            $jwt = new SesionJWT();
            $datosToken = $jwt->verificarToken($token); // devuelve ARRAY ['codigo_usuario'...]
            if (!$datosToken || empty($datosToken['codigo_usuario'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Token inválido o expirado']);
                return;
            }

            // ✅ Obtener datos desde el modelo
            $usuarioModel = new Usuario();
            $usuario = $usuarioModel->obtenerPorCodigo($datosToken['codigo_usuario']);
            var_dump($usuario);
            exit;
            if (!$usuario) {
                http_response_code(404);
                echo json_encode(['error' => 'Usuario no encontrado']);
                return;
            }

            echo json_encode([
                'success' => true,
                'usuario' => $usuario
            ]);

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Error del servidor',
                'detalle' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile()
            ]);
        }
    }

    /**
     * 🔹 Actualizar los datos personales del usuario autenticado
     */
    public function actualizarDatos()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            // ✅ Validar token JWT
            if (empty($_COOKIE['auth_token'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Token no encontrado']);
                return;
            }

            $jwt = new SesionJWT();
            $datosToken = $jwt->verificarToken($_COOKIE['auth_token']); // ARRAY
            if (!$datosToken || empty($datosToken['codigo_usuario'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Token inválido o expirado']);
                return;
            }

            // ✅ Leer cuerpo JSON
            $rawInput = file_get_contents("php://input");
            $data = json_decode($rawInput, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Formato JSON inválido', 'detalle' => json_last_error_msg()]);
                return;
            }

            if (empty($data['email']) || empty($data['nombre_completo'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos incompletos o inválidos']);
                return;
            }

            // (Opcional) Validar que comboDepartamento sea numérico si viene
            if (isset($data['comboDepartamento']) && $data['comboDepartamento'] !== '' &&
                !ctype_digit((string)$data['comboDepartamento'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Departamento inválido']);
                return;
            }

            // ✅ Actualizar en base de datos
            $usuarioModel = new Usuario();
            $actualizado = $usuarioModel->actualizarDatos($datosToken['codigo_usuario'], $data);
            
            if ($actualizado) {
                echo json_encode(['success' => true, 'message' => 'Datos actualizados correctamente']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'No se pudieron actualizar los datos']);
            }

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Error del servidor',
                'detalle' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile()
            ]);
        }
    }
}
