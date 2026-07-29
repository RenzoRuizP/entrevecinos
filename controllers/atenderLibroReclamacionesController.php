<?php
// controllers/atenderLibroReclamacionesController.php

declare(strict_types=1);

final class atenderLibroReclamacionesController
{
    public function index(): void
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        $rolId = (int)($auth['codigo_rol'] ?? 0);
        $rolNombre = strtolower(trim((string)($auth['rol'] ?? $auth['nombre_rol'] ?? '')));
        $adminId = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        $soporteId = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;

        if (!in_array($rolId, [$adminId, $soporteId], true)
            && !in_array($rolNombre, ['admin', 'administrador', 'soporte'], true)) {
            http_response_code(403);
            echo '<div class="alert alert-danger m-4">Acceso restringido al equipo de soporte.</div>';
            return;
        }

        require_once __DIR__ . '/../views/atenderLibroReclamacionesView.php';
    }
}
