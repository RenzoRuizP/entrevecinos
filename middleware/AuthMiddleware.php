<?php 
// auth/AuthMiddleware.php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {

    /**
     * Valida el token JWT desde la cookie 'auth_token'
     * y devuelve los datos del usuario si es válido.
     * 
     * @return array|null
     */
    public static function validarToken(): ?array {
        if (!isset($_COOKIE['auth_token'])) {
            return null;
        }

        $token = $_COOKIE['auth_token'];
        $claveSecreta = $_ENV['JWT_SECRET_KEY'];

        try {
            $decoded = JWT::decode($token, new Key($claveSecreta, 'HS256'));
            return (array) $decoded->data; // Devuelve los datos del usuario
        } catch (Exception $e) {
            // Token inválido o expirado
            return null;
        }
    }

    /**
     * Devuelve el rol del usuario autenticado, o null si no está disponible.
     * 
     * @return string|null
     */
    public static function obtenerRol(): ?string {
        $usuario = self::validarToken();
        return $usuario['rol'] ?? null;
    }

    /**
     * Devuelve el nombre del usuario autenticado, o null si no está disponible.
     * 
     * @return string|null
     */
    public static function obtenerNombre(): ?string {
        $usuario = self::validarToken();
        return $usuario['nombre'] ?? null;
    }

    /**
     * Devuelve el email del usuario autenticado, o null si no está disponible.
     * 
     * @return string|null
     */
    public static function obtenerEmail(): ?string {
        $usuario = self::validarToken();
        return $usuario['email'] ?? null;
    }

    /**
     * Verifica si el usuario tiene un rol específico.
     * 
     * @param string $rolEsperado
     * @return bool
     */
    public static function tieneRol(string $rolEsperado): bool {
        $rol = self::obtenerRol();
        return $rol === $rolEsperado;
    }
}
