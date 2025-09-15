<?php
class MenuPrincipalController {
    public function index() {
        // Si necesitas validar que exista el token antes de cargar la vista
        if (empty($_COOKIE['auth_token'])) {
            header("Location: /?error=sin_token");
            exit;
        }

        // 👇 Aquí carga la vista real
        require __DIR__ . '/../views/menuPrincipalView.php';
    }
}
