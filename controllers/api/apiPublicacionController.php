<?php
// controllers/api/apiPublicacionController.php
require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/Publicacion.php';

class apiPublicacionController
{
    public function registrarPublicacion()
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
            $publicacionModel = new Publicacion();
            $registrado = $publicacionModel->registrarPublicacion($data);
            
            if ($registrado) {
                echo json_encode(['success' => true, 'message' => 'Registro exitoso']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'No se pudo registrar la publicación']);
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
