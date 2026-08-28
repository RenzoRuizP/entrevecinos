<?php
declare(strict_types=1);

use Firebase\JWT\JWT;

class JwtConfig
{
    public static function generarToken(array $data): string
    {
        $tiempoActual = time();

        $claveSecreta = trim((string)ev_env('JWT_SECRET_KEY', ''));

        if ($claveSecreta === '') {
            throw new RuntimeException('JWT_SECRET_KEY no está configurado.');
        }

        $expiraEn = (int)ev_env('JWT_EXPIRATION_SECONDS', 7200);

        if ($expiraEn <= 0) {
            $expiraEn = 7200;
        }

        $payload = [
            'iat'  => $tiempoActual,
            'exp'  => $tiempoActual + $expiraEn,
            'data' => $data
        ];

        return JWT::encode($payload, $claveSecreta, 'HS256');
    }
}