<?php
// controllers/miPerfilController.php

require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../models/User.php';

class MiPerfilController
{
    public function index()
    {
        try {
            // ✅ 1. Verificar si el token existe
            if (!isset($_COOKIE['auth_token'])) {
                header("Location: /entrevecinos/?error=sin_token");
                exit;
            }

            $token = $_COOKIE['auth_token'];

            // ✅ 2. Verificar el token y obtener los datos del usuario
            $datosToken = SesionJWT::verificarToken($token);

            if (!$datosToken || empty($datosToken->email)) {
                header("Location: /entrevecinos/?error=token_invalido");
                exit;
            }

            // ✅ 3. Obtener el email desde el token
            $email = $datosToken->email;

            // ✅ 4. Consultar los datos completos del usuario
            $objUsuario = new User();
            $datosUsuario = $objUsuario->DatosUsuario($email);

            if (!$datosUsuario) {
                header("Location: /entrevecinos/?error=usuario_no_encontrado");
                exit;
            }

            // ✅ 5. Hacer disponibles los datos para la vista
            // (la vista puede acceder con $datosUsuario['campo'])
            require __DIR__ . '/../views/DatosPersonalesView.php';

        } catch (Exception $e) {
            error_log("Error en miPerfilController: " . $e->getMessage());
            header("Location: /entrevecinos/?error=token_error");
            exit;
        }
    }
}
