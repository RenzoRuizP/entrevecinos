<?php
// controllers/api/apiSoporteProductosController.php
declare(strict_types=1);

require_once __DIR__ . '/../../models/ProductoSoporte.php';
require_once __DIR__ . '/../../models/Notificacion.php';

class apiSoporteProductosController
{
    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function auth(): array
    {
        $u = $GLOBALS['EV_AUTH'] ?? [];
        return is_array($u) ? $u : [];
    }

    private function requireAuth(): array
    {
        $u = $this->auth();
        $id = (int)($u['codigo_usuario'] ?? 0);
        if ($id <= 0) {
            $this->json(401, [
                'ok' => false,
                'error' => 'UNAUTHORIZED',
                'motivo' => 'sin token'
            ]);
        }
        return $u;
    }

    private function puedeAtenderPublicaciones(array $u): bool
    {
        $rol = (int)($u['codigo_rol'] ?? 0);
        $adminId = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        $soporteId = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;
        return $rol === $adminId || $rol === $soporteId;
    }

    private function requireSoporte(array $u): void
    {
        if (!$this->puedeAtenderPublicaciones($u)) {
            $this->json(403, [
                'ok' => false,
                'error' => 'FORBIDDEN',
                'motivo' => 'sin permiso',
                'mensaje' => 'No tienes permisos para atender publicaciones.'
            ]);
        }
    }

    private function getString(string $key, string $default = ''): string
    {
        return trim((string)($_GET[$key] ?? $default));
    }

    private function getInt(string $key, int $default = 0): int
    {
        $v = $_GET[$key] ?? null;
        return ($v === null || $v === '') ? $default : (int)$v;
    }

    private function readJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw ?: '{}', true);
        return is_array($data) ? $data : [];
    }

    public function listar(): void
    {
        try {
            $u = $this->requireAuth();
            $this->requireSoporte($u);

            $estado = strtolower($this->getString('estado', 'pendiente'));
            $q = $this->getString('q', '');
            $page = max(1, $this->getInt('page', 1));
            $size = max(1, min(50, $this->getInt('size', 10)));

            if ($q === '') {
                $q = $this->getString('search', '');
            }

            $permitidos = ['borrador', 'pendiente', 'aprobada', 'rechazada', 'todas'];
            if (!in_array($estado, $permitidos, true)) {
                $estado = 'pendiente';
            }

            $m = new ProductoSoporte();
            $r = $m->listarSoporte([
                'estado' => $estado,
                'q' => $q,
                'page' => $page,
                'size' => $size,
            ]);

            $this->json(200, [
                'ok' => true,
                'total' => (int)($r['total'] ?? 0),
                'page' => (int)($r['page'] ?? $page),
                'size' => (int)($r['size'] ?? $size),
                'counts' => $r['counts'] ?? [
                    'borradores' => 0,
                    'pendientes' => 0,
                    'aprobadas' => 0,
                    'rechazadas' => 0,
                ],
                'items' => $r['items'] ?? [],
            ]);
        } catch (Throwable $e) {
            error_log('[EV][apiSoporteProductosController][listar] ' . $e->getMessage());
            $this->json(500, [
                'ok' => false,
                'error' => 'SERVER_ERROR',
                'mensaje' => 'Error interno al listar productos.'
            ]);
        }
    }

    public function detalle($id): void
    {
        try {
            $u = $this->requireAuth();
            $this->requireSoporte($u);

            $id = (int)$id;
            if ($id <= 0) {
                $this->json(400, ['ok' => false, 'error' => 'BAD_REQUEST', 'mensaje' => 'ID inválido.']);
            }

            $m = new ProductoSoporte();
            $row = $m->obtenerDetalle($id);
            if (!$row) {
                $this->json(404, ['ok' => false, 'error' => 'NOT_FOUND', 'mensaje' => 'No existe el producto.']);
            }

            $this->json(200, ['ok' => true, 'item' => $row]);
        } catch (Throwable $e) {
            error_log('[EV][apiSoporteProductosController][detalle] ' . $e->getMessage());
            $this->json(500, [
                'ok' => false,
                'error' => 'SERVER_ERROR',
                'mensaje' => 'Error interno al obtener detalle.'
            ]);
        }
    }

    /**
     * Endpoint legado bloqueado deliberadamente.
     * Toda aprobación, rechazo u observación debe pasar por /revisar
     * para mantener la trazabilidad de producto_revision y no saltar
     * reglas de negocio, incluidos los cupos del piloto de servicios.
     */
    public function actualizarEstado($id): void
    {
        $u = $this->requireAuth();
        $this->requireSoporte($u);

        $id = (int)$id;
        if ($id <= 0) {
            $this->json(400, ['ok' => false, 'error' => 'BAD_REQUEST', 'mensaje' => 'ID inválido.']);
        }

        $this->json(409, [
            'ok' => false,
            'error' => 'FLUJO_REVISION_REQUERIDO',
            'mensaje' => 'El cambio directo de estado está bloqueado. Usa la acción Aprobar, Rechazar u Observar del flujo de revisión para mantener la trazabilidad de la publicación.'
        ]);
    }

    public function revisar($id): void
    {
        try {
            $u = $this->requireAuth();
            $this->requireSoporte($u);

            $id = (int)$id;
            if ($id <= 0) {
                $this->json(400, ['ok' => false, 'error' => 'BAD_REQUEST', 'mensaje' => 'ID inválido.']);
            }

            $body = $this->readJsonBody();
            $accion = strtolower(trim((string)($body['accion'] ?? '')));
            $comentario = trim((string)($body['comentario'] ?? ''));

            $permitidas = ['aprobar', 'rechazar', 'observar'];
            if (!in_array($accion, $permitidas, true)) {
                $this->json(400, [
                    'ok' => false,
                    'error' => 'BAD_REQUEST',
                    'mensaje' => 'Acción inválida.',
                    'permitidos' => $permitidas
                ]);
            }

            if (($accion === 'rechazar' || $accion === 'observar') && mb_strlen($comentario) < 3) {
                $this->json(400, [
                    'ok' => false,
                    'error' => 'BAD_REQUEST',
                    'mensaje' => 'Comentario obligatorio para rechazar u observar.'
                ]);
            }

            if (mb_strlen($comentario) > 500) {
                $comentario = mb_substr($comentario, 0, 500);
            }

            $m = new ProductoSoporte();
            $detalleProducto = $m->obtenerDetalle($id);
            $estadoAnterior = $m->obtenerVisibleActual($id);

            if ($estadoAnterior === null) {
                $this->json(404, [
                    'ok' => false,
                    'error' => 'NOT_FOUND',
                    'mensaje' => 'No existe el producto.'
                ]);
            }

            if ((int)$estadoAnterior !== 1) {
                $msg = match ((int)$estadoAnterior) {
                    0 => 'La publicación está en Borrador. No se revisa por soporte.',
                    2 => 'La publicación ya está Aprobada. No requiere revisión.',
                    3 => 'La publicación está Rechazada. Es un estado final (solo lectura).',
                    default => 'La publicación no está en estado Pendiente.'
                };

                $this->json(409, [
                    'ok' => false,
                    'error' => 'ESTADO_NO_PERMITE_REVISION',
                    'mensaje' => $msg,
                    'estado_actual' => (int)$estadoAnterior
                ]);
            }

            $estadoNuevo = match ($accion) {
                'aprobar' => 2,
                'rechazar' => 3,
                default => (int)$estadoAnterior,
            };

            $codigoSoporte = (int)($u['codigo_usuario'] ?? 0);
            if ($codigoSoporte <= 0) {
                $this->json(401, ['ok' => false, 'error' => 'UNAUTHORIZED', 'mensaje' => 'Usuario soporte inválido.']);
            }

            $m->registrarRevisionTablaExistente(
                $id,
                $codigoSoporte,
                (int)$estadoAnterior,
                (int)$estadoNuevo,
                $comentario
            );

            if ($accion === 'aprobar' || $accion === 'rechazar') {
                $ok = $m->actualizarEstadoSoporte($id, (int)$estadoNuevo);
                if (!$ok) {
                    $this->json(500, [
                        'ok' => false,
                        'error' => 'UPDATE_FAILED',
                        'mensaje' => 'No se pudo actualizar el estado del producto.'
                    ]);
                }
            }

            $msg = match ($accion) {
                'aprobar' => 'Publicación aprobada.',
                'rechazar' => 'Publicación rechazada.',
                default => 'Publicación observada. Se notificará el comentario al usuario.',
            };

            $codigoPropietario = (int)($detalleProducto['codigo_usuario'] ?? 0);
            if ($codigoPropietario > 0) {
                try {
                    $tipoPublicacion = strtolower(trim((string)($detalleProducto['tipo_publicacion'] ?? 'producto')));
                    $tituloProducto = trim((string)($detalleProducto['titulo'] ?? 'tu publicación'));
                    $subcategoria = 'publicacion_' . match ($accion) {
                        'aprobar' => 'aprobada',
                        'rechazar' => 'rechazada',
                        default => 'observada',
                    };
                    $mensajeUsuario = match ($accion) {
                        'aprobar' => 'Tu publicación “' . $tituloProducto . '” fue aprobada y ya está disponible según las reglas de EV.',
                        'rechazar' => $comentario !== '' ? $comentario : 'Tu publicación no superó la revisión de soporte.',
                        default => $comentario !== '' ? $comentario : 'Tu publicación necesita una corrección antes de continuar.',
                    };

                    $notif = new Notificacion($m->getDblink());
                    $notif->crearOActualizarNoLeida([
                        'codigo_usuario' => $codigoPropietario,
                        'categoria' => Notificacion::CAT_PUBLICACION,
                        'subcategoria' => $subcategoria,
                        'referencia_id' => $id,
                        'titulo' => match ($accion) {
                            'aprobar' => 'Tu publicación fue aprobada',
                            'rechazar' => 'Tu publicación fue rechazada',
                            default => 'Tu publicación fue observada',
                        },
                        'mensaje' => $mensajeUsuario,
                        'payload' => [
                            'codigo_producto' => $id,
                            'tipo_publicacion' => $tipoPublicacion,
                            'titulo_producto' => $tituloProducto,
                            'comentario_soporte' => $comentario,
                            'ruta' => '/publicacion',
                        ],
                    ]);
                } catch (Throwable $eNotif) {
                    error_log('[EV][apiSoporteProductosController::revisar][notificacion] ' . $eNotif->getMessage());
                }
            }

            try {
                $rolSoporte = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;
                $notificacionSoporte = new Notificacion($m->getDblink());
                $notificacionSoporte->marcarLeidasPorReferenciaRol(
                    $rolSoporte,
                    Notificacion::CAT_SOPORTE,
                    'publicacion_pendiente',
                    $id
                );
            } catch (Throwable $eNotifSoporte) {
                error_log('[EV][apiSoporteProductosController::revisar][resolver_notificacion_soporte] ' . $eNotifSoporte->getMessage());
            }

            $this->json(200, [
                'ok' => true,
                'mensaje' => $msg,
                'codigo_producto' => $id,
                'estado_anterior' => (int)$estadoAnterior,
                'estado_nuevo' => (int)$estadoNuevo
            ]);
        } catch (Throwable $e) {
            error_log('[EV][apiSoporteProductosController][revisar] ' . $e->getMessage());
            $this->json(500, [
                'ok' => false,
                'error' => 'SERVER_ERROR',
                'mensaje' => 'Error interno al registrar revisión.'
            ]);
        }
    }
}
