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
