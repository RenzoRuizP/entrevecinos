<?php
// models/Dashboard.php
// Dashboard principal del vecino - Entre Vecinos

declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class Dashboard extends Conexion
{
    private const ESTADOS_PEDIDO_CERRADO = [
        'entrega_confirmada_comprador',
        'rechazado_vendedor',
        'cancelado_comprador',
        'cancelado_vendedor',
        'cancelado_sistema',
        'cancelado_soporte',
        'sin_respuesta_vendedor',
        'cancelado',
        'cerrado',
        'finalizado'
    ];

    public function obtenerDashboardVecino(int $codigoUsuario): array
    {
        if ($codigoUsuario <= 0) {
            return [
                'ok' => false,
                'error' => 'USUARIO_INVALIDO',
                'mensaje' => 'Usuario inválido.'
            ];
        }

        try {
            $usuario = $this->obtenerUsuario($codigoUsuario);
            $residencia = $this->obtenerResidenciaActual($codigoUsuario);
            $resumen = $this->obtenerResumen($codigoUsuario);

            return [
                'ok' => true,
                'data' => [
                    'usuario' => $usuario,
                    'residencia' => $residencia,
                    'resumen' => $resumen,
                    'actividad_reciente' => $this->obtenerActividadReciente($codigoUsuario),
                    'novedades_comunidad' => $this->obtenerNovedadesComunidad($residencia),
                    'publicaciones_recientes' => $this->obtenerPublicacionesRecientes($codigoUsuario, $residencia),
                    'rutas' => [
                        'marketplace' => '/marketplace',
                        'publicacion' => '/publicacion',
                        'mis_compras' => '/mis-pedidos-comprador',
                        'mis_ventas' => '/mis-pedidos-vendedor',
                        'billetera' => '/billetera',
                        'notificaciones' => '/notificaciones-residencia',
                        'comunidad' => '/comunidad'
                    ]
                ]
            ];
        } catch (Throwable $e) {
            error_log('[EV][Dashboard][obtenerDashboardVecino] ' . $e->getMessage());

            return [
                'ok' => false,
                'error' => 'ERROR_DASHBOARD_VECINO',
                'mensaje' => 'No se pudo cargar el dashboard del vecino.'
            ];
        }
    }

    private function obtenerUsuario(int $codigoUsuario): array
    {
        $sql = "
            SELECT
                codigo_usuario,
                TRIM(COALESCE(nombre, 'Vecino')) AS nombre,
                email,
                telefono
            FROM usuario
            WHERE codigo_usuario = :codigo_usuario
            LIMIT 1
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'codigo_usuario' => (int)($row['codigo_usuario'] ?? $codigoUsuario),
            'nombre' => (string)($row['nombre'] ?? 'Vecino'),
            'email' => (string)($row['email'] ?? ''),
            'telefono' => (string)($row['telefono'] ?? ''),
        ];
    }

    private function obtenerResidenciaActual(int $codigoUsuario): array
    {
        $sql = "
            SELECT
                ur.codigo_usuario_residencia,
                ur.tipo_conjunto,
                ur.codigo_condominio,
                ur.codigo_urbanizacion,
                ur.direccion,
                CASE
                    WHEN LOWER(TRIM(ur.tipo_conjunto)) = 'urbanizacion'
                        THEN COALESCE(u.nombre_urbanizacion, 'Urbanización')
                    WHEN LOWER(TRIM(ur.tipo_conjunto)) = 'condominio'
                        THEN COALESCE(c.nombre_condominio, 'Condominio')
                    ELSE 'Tu comunidad'
                END AS conjunto_nombre,
                CASE
                    WHEN LOWER(TRIM(ur.tipo_conjunto)) = 'urbanizacion'
                        THEN COALESCE(u.direccion_urbanizacion, ur.direccion)
                    WHEN LOWER(TRIM(ur.tipo_conjunto)) = 'condominio'
                        THEN COALESCE(c.direccion_condominio, ur.direccion)
                    ELSE ur.direccion
                END AS conjunto_direccion
            FROM usuario_residencia ur
            LEFT JOIN condominio c
                ON c.codigo_condominio = ur.codigo_condominio
            LEFT JOIN urbanizacion u
                ON u.codigo_urbanizacion = ur.codigo_urbanizacion
            WHERE ur.codigo_usuario = :codigo_usuario
            ORDER BY ur.codigo_usuario_residencia DESC
            LIMIT 1
        ";

        try {
            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
            $st->execute();

            $row = $st->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return $this->residenciaVacia();
            }

            $tipo = strtolower(trim((string)($row['tipo_conjunto'] ?? '')));

            if (!in_array($tipo, ['urbanizacion', 'condominio'], true)) {
                $tipo = '';
            }

            $label = match ($tipo) {
                'urbanizacion' => 'Urbanización actual',
                'condominio' => 'Condominio actual',
                default => 'Comunidad actual',
            };

            return [
                'codigo_usuario_residencia' => (int)($row['codigo_usuario_residencia'] ?? 0),
                'tipo_conjunto' => $tipo,
                'codigo_condominio' => (int)($row['codigo_condominio'] ?? 0),
                'codigo_urbanizacion' => (int)($row['codigo_urbanizacion'] ?? 0),
                'conjunto_nombre' => (string)($row['conjunto_nombre'] ?? 'Tu comunidad'),
                'conjunto_label' => $label,
                'direccion' => (string)($row['conjunto_direccion'] ?? $row['direccion'] ?? ''),
            ];
        } catch (Throwable $e) {
            error_log('[EV][Dashboard][obtenerResidenciaActual] ' . $e->getMessage());

            return $this->residenciaVacia();
        }
    }

    private function residenciaVacia(): array
    {
        return [
            'codigo_usuario_residencia' => 0,
            'tipo_conjunto' => '',
            'codigo_condominio' => 0,
            'codigo_urbanizacion' => 0,
            'conjunto_nombre' => 'Tu comunidad',
            'conjunto_label' => 'Comunidad actual',
            'direccion' => '',
        ];
    }

    private function obtenerResumen(int $codigoUsuario): array
    {
        return [
            'compras_activas' => $this->contarComprasActivas($codigoUsuario),
            'ventas_pendientes' => $this->contarVentasPendientes($codigoUsuario),
            'calificaciones_pendientes' => $this->contarCalificacionesPendientes($codigoUsuario),
            'saldo_billetera' => $this->obtenerSaldoBilletera($codigoUsuario),
        ];
    }

    private function contarComprasActivas(int $codigoUsuario): int
    {
        $cerrados = $this->placeholders(self::ESTADOS_PEDIDO_CERRADO, 'ec');

        $sql = "
            SELECT COUNT(*) AS total
            FROM pedido p
            WHERE p.codigo_usuario_comprador = :codigo_usuario
              AND COALESCE(NULLIF(p.estado_actual, ''), p.estado, '') NOT IN ({$cerrados['sql']})
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $this->bindPlaceholders($st, $cerrados['params']);
        $st->execute();

        return (int)($st->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }

    private function contarVentasPendientes(int $codigoUsuario): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM pedido p
            WHERE p.codigo_usuario_vendedor = :codigo_usuario
              AND COALESCE(NULLIF(p.estado_actual, ''), p.estado, '') IN ('pendiente_vendedor', 'cola_aceptada')
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();

        return (int)($st->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }

    private function contarCalificacionesPendientes(int $codigoUsuario): int
    {
        try {
            $sql = "
                UPDATE calificacion
                SET estado = 'vencida', updated_at = NOW()
                WHERE codigo_usuario_calificador = :codigo_usuario
                  AND estado = 'pendiente'
                  AND fecha_limite < NOW()
            ";

            $up = $this->dblink->prepare($sql);
            $up->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
            $up->execute();
        } catch (Throwable $e) {
            error_log('[EV][Dashboard][contarCalificacionesPendientes][actualizar] ' . $e->getMessage());
        }

        $sql = "
            SELECT COUNT(*) AS total
            FROM calificacion
            WHERE codigo_usuario_calificador = :codigo_usuario
              AND estado = 'pendiente'
              AND fecha_limite >= NOW()
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();

        return (int)($st->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }

    private function obtenerSaldoBilletera(int $codigoUsuario): float
    {
        $sql = "
            SELECT saldo_actual
            FROM billetera
            WHERE codigo_usuario = :codigo_usuario
            LIMIT 1
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();

        $saldo = $st->fetchColumn();

        return round((float)($saldo !== false ? $saldo : 0), 2);
    }

    private function obtenerActividadReciente(int $codigoUsuario): array
    {
        $items = $this->obtenerActividadNotificaciones($codigoUsuario);

        if ($items) {
            return $items;
        }

        return $this->obtenerActividadPedidos($codigoUsuario);
    }

    private function obtenerActividadNotificaciones(int $codigoUsuario): array
    {
        try {
            $sql = "
                SELECT
                    codigo_notificacion,
                    categoria,
                    subcategoria,
                    referencia_id,
                    titulo,
                    mensaje,
                    estado,
                    created_at
                FROM notificacion
                WHERE codigo_usuario = :codigo_usuario
                ORDER BY created_at DESC, codigo_notificacion DESC
                LIMIT 4
            ";

            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
            $st->execute();

            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $out = [];

            foreach ($rows as $row) {
                $categoria = strtolower(trim((string)($row['categoria'] ?? '')));
                $subcategoria = strtolower(trim((string)($row['subcategoria'] ?? '')));

                $out[] = [
                    'tipo' => $categoria !== '' ? $categoria : 'notificacion',
                    'subtipo' => $subcategoria,
                    'titulo' => (string)($row['titulo'] ?? 'Notificación'),
                    'detalle' => (string)($row['mensaje'] ?? ''),
                    'referencia_id' => (int)($row['referencia_id'] ?? 0),
                    'estado' => (string)($row['estado'] ?? ''),
                    'fecha' => (string)($row['created_at'] ?? ''),
                    'tiempo' => $this->tiempoRelativo((string)($row['created_at'] ?? '')),
                    'icono' => $this->iconoActividad($categoria, $subcategoria),
                    'color' => $this->colorActividad($categoria, $subcategoria),
                ];
            }

            return $out;
        } catch (Throwable $e) {
            error_log('[EV][Dashboard][obtenerActividadNotificaciones] ' . $e->getMessage());

            return [];
        }
    }

    private function obtenerActividadPedidos(int $codigoUsuario): array
    {
        try {
            $sql = "
                SELECT
                    p.codigo_pedido,
                    p.codigo_usuario_comprador,
                    p.codigo_usuario_vendedor,
                    COALESCE(NULLIF(p.estado_actual, ''), p.estado, '') AS estado_pedido,
                    COALESCE(p.updated_at, p.created_at) AS fecha,
                    pr.titulo AS titulo_publicacion
                FROM pedido p
                INNER JOIN producto pr
                    ON pr.codigo_producto = p.codigo_producto
                WHERE p.codigo_usuario_comprador = :codigo_usuario
                   OR p.codigo_usuario_vendedor = :codigo_usuario
                ORDER BY COALESCE(p.updated_at, p.created_at) DESC, p.codigo_pedido DESC
                LIMIT 4
            ";

            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
            $st->execute();

            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $out = [];

            foreach ($rows as $row) {
                $esComprador = (int)($row['codigo_usuario_comprador'] ?? 0) === $codigoUsuario;
                $estado = strtolower(trim((string)($row['estado_pedido'] ?? '')));
                $titulo = (string)($row['titulo_publicacion'] ?? 'Pedido');

                $out[] = [
                    'tipo' => 'pedido',
                    'subtipo' => $estado,
                    'titulo' => $this->tituloActividadPedido($estado, $esComprador),
                    'detalle' => $titulo,
                    'referencia_id' => (int)($row['codigo_pedido'] ?? 0),
                    'estado' => $estado,
                    'fecha' => (string)($row['fecha'] ?? ''),
                    'tiempo' => $this->tiempoRelativo((string)($row['fecha'] ?? '')),
                    'icono' => $this->iconoActividadPedido($estado, $esComprador),
                    'color' => $this->colorActividadPedido($estado),
                ];
            }

            return $out;
        } catch (Throwable $e) {
            error_log('[EV][Dashboard][obtenerActividadPedidos] ' . $e->getMessage());

            return [];
        }
    }


    private function obtenerNovedadesComunidad(array $residencia): array
    {
        $tipoConjunto = strtolower(trim((string)($residencia['tipo_conjunto'] ?? '')));
        $codigoCondominio = (int)($residencia['codigo_condominio'] ?? 0);
        $codigoUrbanizacion = (int)($residencia['codigo_urbanizacion'] ?? 0);

        if (!in_array($tipoConjunto, ['condominio', 'urbanizacion'], true)) {
            return $this->novedadesComunidadVacia(false);
        }

        if ($tipoConjunto === 'condominio' && $codigoCondominio <= 0) {
            return $this->novedadesComunidadVacia(false);
        }

        if ($tipoConjunto === 'urbanizacion' && $codigoUrbanizacion <= 0) {
            return $this->novedadesComunidadVacia(false);
        }

        $codigoComunidad = $tipoConjunto === 'urbanizacion'
            ? $codigoUrbanizacion
            : $codigoCondominio;

        $whereComunidad = $tipoConjunto === 'urbanizacion'
            ? "p.tipo_conjunto = :tipo_conjunto
               AND p.codigo_urbanizacion = :codigo_comunidad
               AND p.codigo_condominio IS NULL"
            : "p.tipo_conjunto = :tipo_conjunto
               AND p.codigo_condominio = :codigo_comunidad
               AND p.codigo_urbanizacion IS NULL";

        $whereVisible = "
            p.alcance = 'comunidad'
            AND p.estado = 'publicado'
            AND (p.fecha_expiracion IS NULL OR p.fecha_expiracion > NOW())
            AND {$whereComunidad}
        ";

        try {
            $sqlCounts = "
                SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN p.tipo_publicacion = 'comunicado' THEN 1 ELSE 0 END) AS comunicados,
                    SUM(CASE WHEN p.tipo_publicacion = 'noticia' THEN 1 ELSE 0 END) AS noticias,
                    SUM(CASE WHEN p.tipo_publicacion = 'evento' THEN 1 ELSE 0 END) AS eventos,
                    SUM(CASE WHEN p.destacado_dashboard = 1 THEN 1 ELSE 0 END) AS destacados
                FROM comunidad_publicacion p
                WHERE {$whereVisible}
            ";

            $stCounts = $this->dblink->prepare($sqlCounts);
            $stCounts->bindValue(':tipo_conjunto', $tipoConjunto, PDO::PARAM_STR);
            $stCounts->bindValue(':codigo_comunidad', $codigoComunidad, PDO::PARAM_INT);
            $stCounts->execute();

            $rowCounts = $stCounts->fetch(PDO::FETCH_ASSOC) ?: [];

            $counts = [
                'total' => (int)($rowCounts['total'] ?? 0),
                'comunicados' => (int)($rowCounts['comunicados'] ?? 0),
                'noticias' => (int)($rowCounts['noticias'] ?? 0),
                'eventos' => (int)($rowCounts['eventos'] ?? 0),
                'destacados' => (int)($rowCounts['destacados'] ?? 0),
            ];

            $sql = "
                SELECT
                    p.codigo_publicacion,
                    p.tipo_publicacion,
                    p.titulo,
                    p.resumen,
                    p.imagen_portada,
                    p.prioridad,
                    p.destacado_dashboard,
                    p.fecha_publicacion,
                    p.fecha_expiracion,
                    p.fecha_evento_inicio,
                    p.fecha_evento_fin,
                    p.ubicacion_evento
                FROM comunidad_publicacion p
                WHERE {$whereVisible}
                ORDER BY
                    p.fecha_publicacion DESC,
                    p.codigo_publicacion DESC
                LIMIT 3
            ";

            $st = $this->dblink->prepare($sql);
            $st->bindValue(':tipo_conjunto', $tipoConjunto, PDO::PARAM_STR);
            $st->bindValue(':codigo_comunidad', $codigoComunidad, PDO::PARAM_INT);
            $st->execute();

            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $items = [];

            foreach ($rows as $row) {
                $fechaPublicacion = (string)($row['fecha_publicacion'] ?? '');

                $items[] = [
                    'codigo_publicacion' => (int)($row['codigo_publicacion'] ?? 0),
                    'tipo_publicacion' => (string)($row['tipo_publicacion'] ?? 'comunicado'),
                    'tipo_label' => $this->labelTipoNovedad((string)($row['tipo_publicacion'] ?? '')),
                    'titulo' => (string)($row['titulo'] ?? ''),
                    'resumen' => (string)($row['resumen'] ?? ''),
                    'imagen_portada' => (string)($row['imagen_portada'] ?? ''),
                    'imagen_portada_url' => $this->urlImagen((string)($row['imagen_portada'] ?? '')),
                    'prioridad' => (string)($row['prioridad'] ?? 'normal'),
                    'destacado_dashboard' => (int)($row['destacado_dashboard'] ?? 0),
                    'fecha_publicacion' => $fechaPublicacion,
                    'fecha_label' => $this->fechaCortaNovedad($fechaPublicacion),
                    'tiempo' => $this->tiempoRelativo($fechaPublicacion),
                    'fecha_expiracion' => (string)($row['fecha_expiracion'] ?? ''),
                    'fecha_evento_inicio' => (string)($row['fecha_evento_inicio'] ?? ''),
                    'fecha_evento_fin' => (string)($row['fecha_evento_fin'] ?? ''),
                    'ubicacion_evento' => (string)($row['ubicacion_evento'] ?? ''),
                ];
            }

            return [
                'habilitado' => true,
                'hay_novedad' => !empty($items),
                'total_activos' => $counts['total'],
                'counts' => $counts,
                'items' => $items,
                'ultimo' => $items[0] ?? null,
            ];
        } catch (Throwable $e) {
            error_log('[EV][Dashboard][obtenerNovedadesComunidad] ' . $e->getMessage());

            return $this->novedadesComunidadVacia(true);
        }
    }

    private function novedadesComunidadVacia(bool $habilitado = false): array
    {
        return [
            'habilitado' => $habilitado,
            'hay_novedad' => false,
            'total_activos' => 0,
            'counts' => [
                'total' => 0,
                'comunicados' => 0,
                'noticias' => 0,
                'eventos' => 0,
                'destacados' => 0,
            ],
            'items' => [],
            'ultimo' => null,
        ];
    }

    private function labelTipoNovedad(string $tipo): string
    {
        return match (strtolower(trim($tipo))) {
            'noticia' => 'Noticia',
            'evento' => 'Evento',
            default => 'Comunicado',
        };
    }

    private function fechaCortaNovedad(string $fecha): string
    {
        $ts = strtotime($fecha);

        if ($ts === false) {
            return '';
        }

        return date('d/m/Y', $ts);
    }

    private function obtenerPublicacionesRecientes(int $codigoUsuario, array $residencia): array
    {
        $tipoConjunto = strtolower(trim((string)($residencia['tipo_conjunto'] ?? '')));
        $codigoCondominio = (int)($residencia['codigo_condominio'] ?? 0);
        $codigoUrbanizacion = (int)($residencia['codigo_urbanizacion'] ?? 0);

        if ($tipoConjunto === 'condominio' && $codigoCondominio <= 0) {
            return [];
        }

        if ($tipoConjunto === 'urbanizacion' && $codigoUrbanizacion <= 0) {
            return [];
        }

        if (!in_array($tipoConjunto, ['condominio', 'urbanizacion'], true)) {
            return [];
        }

        $whereResidencia = $tipoConjunto === 'urbanizacion'
            ? "p.tipo_conjunto_publicacion = 'urbanizacion' AND p.codigo_urbanizacion_publicacion = :codigo_conjunto"
            : "p.tipo_conjunto_publicacion = 'condominio' AND p.codigo_condominio_publicacion = :codigo_conjunto";

        $codigoConjunto = $tipoConjunto === 'urbanizacion'
            ? $codigoUrbanizacion
            : $codigoCondominio;

        $sql = "
            SELECT
                p.codigo_producto,
                p.codigo_usuario,
                p.tipo_publicacion,
                p.titulo,
                p.descripcion,
                p.precio,
                p.imagen_portada,
                p.created_at,
                TRIM(COALESCE(u.nombre, 'Vecino')) AS nombre_vendedor,
                COALESCE(ROUND(AVG(c.puntaje), 1), 0) AS reputacion_promedio,
                COUNT(c.codigo_calificacion) AS reputacion_total
            FROM producto p
            INNER JOIN usuario u
                ON u.codigo_usuario = p.codigo_usuario
            LEFT JOIN calificacion c
                ON c.codigo_usuario_calificado = p.codigo_usuario
               AND c.rol_calificado = 'vendedor'
               AND c.estado = 'enviada'
               AND c.puntaje IS NOT NULL
            WHERE p.visible = 1
              AND p.estado_residencial_publicacion = 'activa'
              AND {$whereResidencia}
              AND p.codigo_usuario <> :codigo_usuario
              AND (
                    p.tipo_publicacion = 'servicio'
                    OR COALESCE(u.disponibilidad_pedidos, 0) = 1
                  )
            GROUP BY
                p.codigo_producto,
                p.codigo_usuario,
                p.tipo_publicacion,
                p.titulo,
                p.descripcion,
                p.precio,
                p.imagen_portada,
                p.created_at,
                u.nombre
            ORDER BY p.destacado DESC, p.es_destacado DESC, p.created_at DESC
            LIMIT 5
        ";

        try {
            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_conjunto', $codigoConjunto, PDO::PARAM_INT);
            $st->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
            $st->execute();

            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $out = [];

            foreach ($rows as $row) {
                $promedio = round((float)($row['reputacion_promedio'] ?? 0), 1);
                $total = (int)($row['reputacion_total'] ?? 0);

                $out[] = [
                    'codigo_producto' => (int)($row['codigo_producto'] ?? 0),
                    'codigo_usuario_vendedor' => (int)($row['codigo_usuario'] ?? 0),
                    'tipo_publicacion' => (string)($row['tipo_publicacion'] ?? 'producto'),
                    'titulo' => (string)($row['titulo'] ?? ''),
                    'descripcion' => (string)($row['descripcion'] ?? ''),
                    'precio' => round((float)($row['precio'] ?? 0), 2),
                    'imagen_portada' => (string)($row['imagen_portada'] ?? ''),
                    'imagen_portada_url' => $this->urlImagen((string)($row['imagen_portada'] ?? '')),
                    'nombre_vendedor' => (string)($row['nombre_vendedor'] ?? 'Vecino'),
                    'reputacion_promedio' => $promedio,
                    'reputacion_total' => $total,
                    'reputacion_texto' => $total >= 5
                        ? number_format($promedio, 1) . ' (' . $total . ')'
                        : 'Nuevo vendedor',
                ];
            }

            return $out;
        } catch (Throwable $e) {
            error_log('[EV][Dashboard][obtenerPublicacionesRecientes] ' . $e->getMessage());

            return [];
        }
    }

    private function urlImagen(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return rtrim(BASE_URL, '/') . '/resources/images/no-image-ev.png';
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
    }

    private function tituloActividadPedido(string $estado, bool $esComprador): string
    {
        return match ($estado) {
            'pendiente_vendedor' => $esComprador
                ? 'Solicitud enviada'
                : 'Nueva solicitud recibida',

            'cola_aceptada' => 'Solicitud en cola',
            'en_preparacion' => 'Pedido en preparación',
            'listo_para_entrega' => 'Pedido listo para entrega',
            'despachando' => 'Pedido despachándose',
            'en_camino' => 'Pedido en camino',
            'en_punto_entrega' => 'Pedido en punto de entrega',
            'entregado_vendedor' => 'Pedido entregado por vendedor',
            'entrega_confirmada_comprador' => 'Pedido finalizado',

            'rechazado_vendedor' => $esComprador
                ? 'Solicitud rechazada por vendedor'
                : 'Solicitud rechazada',

            'sin_respuesta_vendedor' => $esComprador
                ? 'Solicitud sin respuesta del vendedor'
                : 'Solicitud vencida sin respuesta',

            'cancelado_comprador' => $esComprador
                ? 'Cancelaste el pedido'
                : 'Pedido cancelado por comprador',

            'cancelado_vendedor' => $esComprador
                ? 'Pedido cancelado por vendedor'
                : 'Cancelaste el pedido',

            'cancelado_sistema' => 'Pedido cancelado automáticamente',
            'cancelado_soporte' => 'Pedido cancelado por soporte',
            'cancelado' => 'Pedido cancelado',

            default => 'Movimiento de pedido',
        };
    }

    private function iconoActividadPedido(string $estado, bool $esComprador): string
    {
        return match ($estado) {
            'pendiente_vendedor' => $esComprador ? 'bi-send-check' : 'bi-cart-plus',
            'cola_aceptada' => 'bi-hourglass-split',
            'en_preparacion' => 'bi-box-seam',
            'listo_para_entrega' => 'bi-bag-check',
            'despachando', 'en_camino' => 'bi-truck',
            'en_punto_entrega' => 'bi-geo-alt',
            'entregado_vendedor', 'entrega_confirmada_comprador' => 'bi-check-circle',
            'rechazado_vendedor' => 'bi-x-octagon',
            'sin_respuesta_vendedor' => 'bi-clock-history',
            'cancelado_comprador',
            'cancelado_vendedor',
            'cancelado_sistema',
            'cancelado_soporte',
            'cancelado' => 'bi-x-circle',
            default => $esComprador ? 'bi-bag-check' : 'bi-cart-check',
        };
    }

    private function colorActividadPedido(string $estado): string
    {
        return match ($estado) {
            'pendiente_vendedor',
            'cola_aceptada',
            'sin_respuesta_vendedor' => 'naranja',

            'en_preparacion',
            'listo_para_entrega',
            'despachando',
            'en_camino',
            'en_punto_entrega' => 'azul',

            'entregado_vendedor',
            'entrega_confirmada_comprador' => 'verde',

            'rechazado_vendedor' => 'rojo',

            'cancelado_comprador',
            'cancelado_vendedor',
            'cancelado_sistema',
            'cancelado_soporte',
            'cancelado' => 'gris',

            default => 'verde',
        };
    }

    private function iconoActividad(string $categoria, string $subcategoria): string
    {
        $clave = strtolower(trim($categoria . ' ' . $subcategoria));

        if (str_contains($clave, 'cancelado') || str_contains($clave, 'cancelacion')) {
            return 'bi-x-circle';
        }

        if (str_contains($clave, 'rechazado') || str_contains($clave, 'rechazo')) {
            return 'bi-x-octagon';
        }

        if (str_contains($clave, 'sin_respuesta')) {
            return 'bi-clock-history';
        }

        if (
            str_contains($clave, 'entrega_confirmada') ||
            str_contains($clave, 'entregado')
        ) {
            return 'bi-check-circle';
        }

        if (
            str_contains($clave, 'en_camino') ||
            str_contains($clave, 'despachando')
        ) {
            return 'bi-truck';
        }

        if (str_contains($clave, 'en_punto_entrega')) {
            return 'bi-geo-alt';
        }

        if ($categoria === 'pedido') {
            return 'bi-bag-check';
        }

        if ($categoria === 'calificacion') {
            return 'bi-star';
        }

        if ($categoria === 'residencia') {
            return 'bi-house-check';
        }

        if ($subcategoria === 'nueva_solicitud' || str_contains($subcategoria, 'solicitud')) {
            return 'bi-cart-plus';
        }

        return 'bi-bell';
    }

    private function colorActividad(string $categoria, string $subcategoria): string
    {
        $clave = strtolower(trim($categoria . ' ' . $subcategoria));

        if (str_contains($clave, 'cancelado') || str_contains($clave, 'cancelacion')) {
            return 'gris';
        }

        if (str_contains($clave, 'rechazado') || str_contains($clave, 'rechazo')) {
            return 'rojo';
        }

        if (str_contains($clave, 'sin_respuesta')) {
            return 'naranja';
        }

        if (
            str_contains($clave, 'entrega_confirmada') ||
            str_contains($clave, 'entregado')
        ) {
            return 'verde';
        }

        if (
            str_contains($clave, 'en_preparacion') ||
            str_contains($clave, 'listo_para_entrega') ||
            str_contains($clave, 'despachando') ||
            str_contains($clave, 'en_camino') ||
            str_contains($clave, 'en_punto_entrega')
        ) {
            return 'azul';
        }

        if ($categoria === 'calificacion') {
            return 'morado';
        }

        if ($categoria === 'residencia') {
            return 'azul';
        }

        if ($subcategoria === 'nueva_solicitud' || str_contains($subcategoria, 'solicitud')) {
            return 'naranja';
        }

        return 'verde';
    }

    private function tiempoRelativo(string $fecha): string
    {
        $ts = strtotime($fecha);

        if ($ts === false) {
            return '';
        }

        $diff = max(0, time() - $ts);

        if ($diff < 60) {
            return 'Hace un momento';
        }

        $min = (int)floor($diff / 60);

        if ($min < 60) {
            return 'Hace ' . $min . ' min';
        }

        $horas = (int)floor($min / 60);

        if ($horas < 24) {
            return 'Hace ' . $horas . ' h';
        }

        $dias = (int)floor($horas / 24);

        if ($dias < 30) {
            return 'Hace ' . $dias . ' día' . ($dias === 1 ? '' : 's');
        }

        return date('d/m/Y', $ts);
    }

    private function placeholders(array $values, string $prefix): array
    {
        $sql = [];
        $params = [];

        foreach (array_values($values) as $i => $value) {
            $key = ':' . $prefix . $i;
            $sql[] = $key;
            $params[$key] = (string)$value;
        }

        return [
            'sql' => implode(',', $sql),
            'params' => $params,
        ];
    }

    private function bindPlaceholders(PDOStatement $st, array $params): void
    {
        foreach ($params as $key => $value) {
            $st->bindValue($key, $value, PDO::PARAM_STR);
        }
    }
}