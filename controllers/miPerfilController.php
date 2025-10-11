<?php
// controllers/miPerfilController.php

require_once __DIR__ . '/../models/SesionJWT.php';

class MiPerfilController
{
    public function index()
    {
        try {
            // 🔹 Validar token
            if (!isset($_COOKIE['auth_token'])) {
                header("Location: /entrevecinos/?error=sin_token");
                exit;
            }

            $token = $_COOKIE['auth_token'];
            $datosToken = SesionJWT::verificarToken($token);

            if (!$datosToken) {
                header("Location: /entrevecinos/?error=token_error");
                exit;
            }

            // ✅ Cargar la vista DatosPersonalesView
            require_once __DIR__ . '/../views/DatosPersonalesView.php';

        } catch (Exception $e) {
            header("Location: /entrevecinos/?error=token_error");
            exit;
        }
    }
}
