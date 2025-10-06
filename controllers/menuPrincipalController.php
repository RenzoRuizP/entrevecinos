<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
class MenuPrincipalController {
    public function index() {
        // ✅ Validar token JWT
        $usuario = AuthMiddleware::validarToken();

        if (!$usuario) {
            // Token inválido o expirado
            header("Location: /?error=token_expirado");
            exit;
        }

        // Puedes pasar los datos del usuario a la vista si deseas
        $nombre = $usuario['nombre'] ?? 'Usuario';
        $rol = $usuario['rol'] ?? 'Sin rol';

        // Cargar vista principal
        require __DIR__ . '/../views/menuPrincipalView.php';
    }
}