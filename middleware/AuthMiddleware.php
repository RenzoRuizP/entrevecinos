<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {
    public static function validarToken(): ?array {
        if (!isset($_COOKIE['auth_token'])) {
            return null;
        }

        $token = $_COOKIE['auth_token'];
        $claveSecreta = $_ENV['JWT_SECRET_KEY'];

        try {
            $decoded = JWT::decode($token, new Key($claveSecreta, 'HS256'));
            return (array)$decoded->data; // retornamos solo los datos de usuario
        } catch (Exception $e) {
            return null; // token inválido o expirado
        }
    }
}
