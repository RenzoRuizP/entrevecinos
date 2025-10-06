<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    public static function loginForm() {
        // Renderiza la vista del login
        include __DIR__ . '/../views/login.php';
    }

    public static function login() {
        $input = json_decode(file_get_contents("php://input"), true);

        $email = $input['email'] ?? '';
        $clave = $input['clave'] ?? '';

        if (empty($email) || empty($clave)) {
            echo json_encode(["success" => false, "message" => "Complete todos los campos"]);
            return;
        }

        $userModel = new User();
        $usuario = $userModel->buscarPorEmail($email);

        if (!$usuario) {
            echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
            return;
        }

        if (!password_verify($clave, $usuario['clave'])) {
            echo json_encode(["success" => false, "message" => "Contraseña incorrecta"]);
            return;
        }

        if ($usuario['estado'] !== '1') { // 1 = Activo
            echo json_encode(["success" => false, "message" => "Usuario inactivo"]);
            return;
        }

        // Iniciar sesión PHP
        session_start();
        $_SESSION['usuario'] = [
            "codigo_usuario" => $usuario['codigo_usuario'],
            "nombre" => $usuario['nombre'],
            "email" => $usuario['email'],
            "rol" => $usuario['codigo_rol']
        ];

        echo json_encode(["success" => true, "message" => "Bienvenido " . $usuario['nombre']]);
    }

    public static function logout() {
        session_start();
        session_destroy();
        header("Location: /entrevecinos/");
        exit;
    }
}
