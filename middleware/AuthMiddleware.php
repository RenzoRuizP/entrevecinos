<?php 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {

    public static function validarToken(): ?array {
        if (!isset($_COOKIE['auth_token'])) {
            error_log("⚠️ Cookie 'auth_token' no encontrada");
            return null;
        }

        $token = $_COOKIE['auth_token'];
        $claveSecreta = $_ENV['JWT_SECRET_KEY'];

        try {
            $decoded = JWT::decode($token, new Key($claveSecreta, 'HS256'));
            return (array) $decoded->data;
        } catch (Exception $e) {
            error_log("❌ Error al validar token: " . $e->getMessage());
            return null;
        }
    }

    public static function obtenerRol(): ?string {
        $usuario = self::validarToken();
        return $usuario['rol'] ?? null;
    }

    public static function obtenerNombre(): ?string {
        $usuario = self::validarToken();
        return $usuario['nombre'] ?? null;
    }

    public static function obtenerEmail(): ?string {
        $usuario = self::validarToken();
        return $usuario['email'] ?? null;
    }

    public static function tieneRol(string $rolEsperado): bool {
        $rol = self::obtenerRol();
        return $rol === $rolEsperado;
    }
}
