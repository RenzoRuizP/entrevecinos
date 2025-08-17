<?php

use Firebase\JWT\JWT;

class JwtConfig {
    public static function generarToken(array $data): string {
        $tiempoActual = time();
        $claveSecreta = $_ENV['JWT_SECRET_KEY'];
        $expiraEn = intval($_ENV['JWT_EXPIRATION_SECONDS']);

        $payload = [
            'iat' => $tiempoActual,
            'exp' => $tiempoActual + $expiraEn,
            'data' => $data
        ];

        return JWT::encode($payload, $claveSecreta, 'HS256');
    }
}
