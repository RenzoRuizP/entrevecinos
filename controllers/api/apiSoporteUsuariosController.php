<?php
// controllers/api/apiSoporteUsuariosController.php
declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/SoporteUsuarios.php';
require_once __DIR__ . '/../../models/Billetera.php';
require_once __DIR__ . '/../../models/Notificacion.php';
require_once __DIR__ . '/../../models/ConfiguracionPlataforma.php';

final class apiSoporteUsuariosController
{
    /* =========================
     * Helpers de sesión / rol
     * ========================= */
    private function rolActual(): int
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        return (int)($auth['codigo_rol'] ?? 0);
    }

    private function codigoSoporte(): int
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        return (int)($auth['codigo_usuario'] ?? 0);
    }

    private function puedeAccederSoporte(): bool
    {
        $adminId   = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        $soporteId = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;

        $rol = $this->rolActual();
        return ($rol === $adminId || $rol === $soporteId);
    }

    private function json(int $code, array $payload): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function normalizarEstado($raw): string
    {
        $v = strtolower(trim((string)$raw));

        return match ($v) {
            '1', 'revision', 'en_revision', 'en revisión' => 'revision',
            '2', 'habilitado', 'habilitados'              => 'habilitado',
            '3', 'observado', 'observados'                => 'observado',
            '0', 'inactivo', 'inactivos'                  => 'inactivo',
            'todos', 'all'                                => 'todos',
            default                                       => 'revision',
        };
    }

    private function normalizarConjunto($raw): string
    {
        $v = strtolower(trim((string)$raw));
        if ($v === '') return '';
        if (str_contains($v, 'cond')) return 'condominio';
        if (str_contains($v, 'urban')) return 'urbanizacion';
        return '';
    }

    /* =========================
     * GET /api/soporte/usuarios
     * ========================= */
    public function listar(): void
    {
        if (!$this->puedeAccederSoporte()) {
            $this->json(403, [
                'ok'      => false,
                'error'   => 'FORBIDDEN',
                'mensaje' => 'Acceso restringido.'
            ]);
            return;
        }

        $estado = $this->normalizarEstado($_GET['estado'] ?? 'revision');
        $q      = trim((string)($_GET['q'] ?? ''));
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = (int)($_GET['limit'] ?? 10);
        $limit  = ($limit <= 0) ? 10 : min($limit, 100);

        $conjunto   = $this->normalizarConjunto($_GET['conjunto'] ?? '');
        $conjuntoId = (int)($_GET['conjunto_id'] ?? 0);

        try {
            $m = new SoporteUsuarios();

            $res = $m->listar([
                'estado'      => $estado,
                'q'           => $q,
                'page'        => $page,
                'limit'       => $limit,
                'conjunto'    => $conjunto,
                'conjunto_id' => $conjuntoId,
            ]);

            $this->json(200, [
                'ok'   => true,
                'data' => $res
            ]);
        } catch (Throwable $e) {
            error_log('[EV][apiSoporteUsuariosController::listar] ' . $e->getMessage());
            $this->json(500, [
                'ok'      => false,
                'error'   => 'SERVER_ERROR',
                'mensaje' => 'Error interno del servidor.'
            ]);
        }
    }

    /* =========================
     * POST /api/soporte/usuarios/{id}/estado
     * ========================= */
    public function actualizarEstado($codigoUsuario): void
    {
        $codigoUsuario = (int)$codigoUsuario;

        if (!$this->puedeAccederSoporte()) {
            $this->json(403, [
                'ok'      => false,
                'error'   => 'FORBIDDEN',
                'mensaje' => 'Acceso restringido.'
            ]);
            return;
        }

        if ($codigoUsuario <= 0) {
            $this->json(400, [
                'ok'      => false,
                'error'   => 'CODIGO_INVALIDO',
                'mensaje' => 'Código de usuario inválido.'
            ]);
            return;
        }

        $raw = file_get_contents('php://input');
        $in  = json_decode($raw ?: '[]', true);
        if (!is_array($in)) {
            $in = [];
        }

        $estadoNuevo = isset($in['estado']) ? (int)$in['estado'] : -1;
        $observacion = trim((string)($in['observacion'] ?? ''));

        if (!in_array($estadoNuevo, [0, 1, 2], true)) {
            $this->json(400, [
                'ok'      => false,
                'error'   => 'ESTADO_INVALIDO',
                'mensaje' => 'Estado inválido.'
            ]);
            return;
        }

        try {
            $m = new SoporteUsuarios();

            $ok = $m->actualizarEstadoUsuario([
                'codigo_usuario' => $codigoUsuario,
                'estado'         => $estadoNuevo,
                'codigo_soporte' => $this->codigoSoporte(),
            ]);

            if (!$ok) {
                $this->json(404, [
                    'ok'      => false,
                    'error'   => 'USUARIO_NO_ENCONTRADO_O_SIN_CAMBIOS',
                    'mensaje' => 'Usuario no encontrado o sin cambios.'
                ]);
                return;
            }

            // =========================================================
            // Si se inactiva:
            // - quitar estado observado
            // - si viene observación, guardarla como referencia
            // =========================================================
            if ($estadoNuevo === 0) {
                $m->quitarObservado($codigoUsuario);

                if ($observacion !== '') {
                    $m->guardarObservacionRevision($codigoUsuario, $observacion);
                }
            }

            // =========================================================
            // Si se aprueba:
            // - limpiar observaciones/revisión previa
            // - aplicar bono bienvenida
            // =========================================================
            $bono = null;

            if ($estadoNuevo === 2) {
                $m->limpiarRevision($codigoUsuario);

                $configuracion = new ConfiguracionPlataforma();
                $alcance = $configuracion->obtenerAlcanceUsuario($codigoUsuario);
                $bonoHabilitado = $configuracion->obtenerMonetizacionPorAlcance(
                    ConfiguracionPlataforma::MON_BONO_BIENVENIDA,
                    (string)$alcance['tipo_alcance'],
                    (int)$alcance['codigo_alcance']
                );
                $bonoMonto = $configuracion->obtenerMonetizacionPorAlcance(
                    ConfiguracionPlataforma::MON_BONO_BIENVENIDA_MONTO,
                    (string)$alcance['tipo_alcance'],
                    (int)$alcance['codigo_alcance']
                );
                $estadoBilletera = $configuracion->obtenerEstadoBilleteraUsuario($codigoUsuario);

                $debeAplicarBono = (bool)($bonoHabilitado['valor_booleano'] ?? false)
                    && (bool)($estadoBilletera['billetera_disponible'] ?? false);
                $montoBono = max(0.0, (float)($bonoMonto['valor_decimal'] ?? 0));

                if ($debeAplicarBono && $montoBono > 0) {
                    $wallet = new Billetera();
                    $bono = $wallet->aplicarBonoBienvenida($codigoUsuario, $montoBono);

                    if (empty($bono['ok'])) {
                        error_log(
                            '[EV][apiSoporteUsuariosController::actualizarEstado] ' .
                            'Usuario aprobado pero falló bono. u=' . $codigoUsuario .
                            ' err=' . ($bono['error'] ?? ($bono['mensaje'] ?? 'DESCONOCIDO'))
                        );
                    }
                } else {
                    $bono = [
                        'ok' => true,
                        'aplicado' => false,
                        'omitido_por_configuracion' => true,
                        'mensaje' => 'Bono de bienvenida no habilitado para este alcance.',
                    ];
                }
            }

            if (in_array($estadoNuevo, [0, 2], true)) {
                try {
                    $aprobada = $estadoNuevo === 2;
                    $bonoAplicado = $aprobada && !empty($bono['ok']) && !empty($bono['aplicado']);
                    $mensajeNotificacion = $aprobada
                        ? ($bonoAplicado
                            ? 'Tu cuenta fue aprobada y ya puedes usar Entre Vecinos. También se acreditó tu saldo de bienvenida.'
                            : 'Tu cuenta fue aprobada y ya puedes usar Entre Vecinos.')
                        : ($observacion !== ''
                            ? $observacion
                            : 'Tu cuenta fue inactivada. Revisa la información disponible o comunícate con soporte.');

                    $notif = new Notificacion();
                    $notif->crearOActualizarNoLeida([
                        'codigo_usuario' => $codigoUsuario,
                        'categoria' => Notificacion::CAT_CUENTA,
                        'subcategoria' => $aprobada ? 'cuenta_aprobada' : 'cuenta_inactivada',
                        'referencia_id' => $codigoUsuario,
                        'titulo' => $aprobada ? 'Tu cuenta fue aprobada' : 'Tu cuenta fue inactivada',
                        'mensaje' => $mensajeNotificacion,
                        'payload' => [
                            'codigo_usuario' => $codigoUsuario,
                            'estado' => $estadoNuevo,
                            'bono_aplicado' => $bonoAplicado,
                            'ruta' => '/MenuPrincipal',
                        ],
                    ]);
                } catch (Throwable $eNotif) {
                    error_log('[EV][apiSoporteUsuariosController::actualizarEstado][notificacion] ' . $eNotif->getMessage());
                }
            }

            $this->json(200, [
                'ok'      => true,
                'mensaje' => 'Estado actualizado.',
                'bono'    => $bono
            ]);
        } catch (Throwable $e) {
            error_log('[EV][apiSoporteUsuariosController::actualizarEstado] ' . $e->getMessage());
            $this->json(500, [
                'ok'      => false,
                'error'   => 'SERVER_ERROR',
                'mensaje' => 'Error interno del servidor.'
            ]);
        }
    }
}