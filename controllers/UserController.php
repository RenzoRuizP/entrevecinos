<?php
// controllers/UserController.php
require_once __DIR__ . '/../models/User.php';

class UserController {

    public function registrar() {
        header('Content-Type: application/json; charset=utf-8');

        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            echo json_encode([
                "success" => false,
                "message" => "Datos inválidos"
            ]);
            return;
        }

        // Validación mínima server-side (no confíes en el cliente)
        $tipo = $data['tipo_conjunto'] ?? '';
        $direccion = trim($data['direccion'] ?? '');

        if ($tipo !== 'condominio' && $tipo !== 'urbanizacion') {
            echo json_encode(["success" => false, "message" => "Tipo de conjunto residencial inválido"]);
            return;
        }

        if ($tipo === 'condominio' && empty($data['codigo_condominio'])) {
            echo json_encode(["success" => false, "message" => "Debes seleccionar un condominio"]);
            return;
        }

        if ($tipo === 'urbanizacion' && empty($data['codigo_urbanizacion'])) {
            echo json_encode(["success" => false, "message" => "Debes seleccionar una urbanización"]);
            return;
        }

        if (strlen($direccion) < 5) {
            echo json_encode(["success" => false, "message" => "Dirección inválida"]);
            return;
        }

        try {
            $userModel = new User();
            $ok = $userModel->registrar($data);

            if ($ok) {
                echo json_encode([
                    "success" => true,
                    "message" => "Usuario registrado con éxito"
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "No se pudo registrar el usuario"
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
}
