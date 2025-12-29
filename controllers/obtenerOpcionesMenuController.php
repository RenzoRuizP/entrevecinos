<?php
// controllers/obtenerOpcionesMenuController.php

declare(strict_types=1);

require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../resources/util/functions/Helper.class.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // 1) Token desde cookie
    $token = $_COOKIE['auth_token'] ?? null;

    if (!$token || trim((string)$token) === '') {
        Helper::imprimeJSON(401, 'No autenticado (sin token).', []);
        exit;
    }

    // 2) Verificar token (tu método detallado)
    $rTok = SesionJWT::verificarTokenDetallado((string)$token);

    if (empty($rTok['ok'])) {
        $motivo = $rTok['error'] ?? 'TOKEN_INVALIDO';
        Helper::imprimeJSON(401, "No autenticado ({$motivo}).", []);
        exit;
    }

    $data = $rTok['data'] ?? [];
    $rol  = is_array($data) ? ($data['rol'] ?? null) : null;

    if (!$rol || trim((string)$rol) === '') {
        Helper::imprimeJSON(401, 'No se pudo determinar el rol del usuario.', []);
        exit;
    }

    // 3) Obtener opciones de menú por rol
    $objSesion = new SesionJWT();
    $menus = $objSesion->obtenerOpcionesMenu($rol);

    // 4) Respuesta OK
    Helper::imprimeJSON(200, '', ['menus' => $menus]);
    exit;

} catch (Throwable $e) {
    error_log('[EV][obtenerOpcionesMenuController] ' . $e->getMessage());
    Helper::imprimeJSON(500, 'Error interno al obtener opciones del menú.', []);
    exit;
}
