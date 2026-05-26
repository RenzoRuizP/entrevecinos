<?php
// models/Calificacion.php

declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class Calificacion extends Conexion
{
    private const DIAS_PLAZO_CALIFICACION = 7;

    private const ETIQUETAS_VENDEDOR = [
        'Buena atención',
        'Entrega rápida',
        'Producto conforme',
        'Comunicación clara',
        'Lo recomiendo',
    ];

    private const ETIQUETAS_COMPRADOR = [
        'Confirmación sin problemas',
        'Puntual',
        'Comunicación clara',
        'Trato respetuoso',
        'Lo recomiendo',
    ];

    private function actualizarVencidas(?int $codigoUsuario = null): void
    {
        $sql = "
            UPDATE calificacion
            SET estado = 'vencida', updated_at = NOW()
            WHERE estado = 'pendiente'
              AND fecha_limite < NOW()
        ";

        if ($codigoUsuario !== null && $codigoUsuario > 0) {
            $sql .= " AND codigo_usuario_calificador = :codigo_usuario";
        }

        $st = $this->dblink->prepare($sql);

        if ($codigoUsuario !== null && $codigoUsuario > 0) {
            $st->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        }

        $st->execute();
    }

    private function obtenerPedidoCompletado(int $codigoPedido): ?array
    {
        if ($codigoPedido <= 0) return null;

        $sql = "
            SELECT
                p.codigo_pedido,
                p.codigo_producto,
                p.codigo_usuario_comprador,
                p.codigo_usuario_vendedor,
                p.estado_actual,
                p.estado,
                p.entrega_confirmada_comprador,
                p.fecha_confirmacion_entrega,
                p.fecha_cierre,
                pr.titulo AS titulo_publicacion,
                TRIM(COALESCE(uc.nombre, '')) AS nombre_comprador,
                TRIM(COALESCE(uv.nombre, '')) AS nombre_vendedor
            FROM pedido p
            INNER JOIN producto pr ON pr.codigo_producto = p.codigo_producto
            INNER JOIN usuario uc ON uc.codigo_usuario = p.codigo_usuario_comprador
            INNER JOIN usuario uv ON uv.codigo_usuario = p.codigo_usuario_vendedor
            WHERE p.codigo_pedido = :codigo_pedido
              AND p.estado_actual = 'entrega_confirmada_comprador'
            LIMIT 1
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function formatear(?array $row): ?array
    {
        if (!$row) return null;

        $rolCalificado = (string)($row['rol_calificado'] ?? '');
        $estado = (string)($row['estado'] ?? '');
        $fechaLimite = (string)($row['fecha_limite'] ?? '');

        return [
            'codigo_calificacion' => (int)($row['codigo_calificacion'] ?? 0),
            'codigo_pedido' => (int)($row['codigo_pedido'] ?? 0),
            'codigo_usuario_calificador' => (int)($row['codigo_usuario_calificador'] ?? 0),
            'codigo_usuario_calificado' => (int)($row['codigo_usuario_calificado'] ?? 0),
            'rol_calificador' => (string)($row['rol_calificador'] ?? ''),
            'rol_calificado' => $rolCalificado,
            'tipo_calificacion' => (string)($row['tipo_calificacion'] ?? ''),
            'puntaje' => isset($row['puntaje']) ? (int)$row['puntaje'] : null,
            'comentario' => (string)($row['comentario'] ?? ''),
            'estado' => $estado,
            'fecha_limite' => $fechaLimite,
            'fecha_envio' => $row['fecha_envio'] ?? null,
            'titulo_publicacion' => (string)($row['titulo_publicacion'] ?? ''),
            'nombre_comprador' => (string)($row['nombre_comprador'] ?? ''),
            'nombre_vendedor' => (string)($row['nombre_vendedor'] ?? ''),
            'nombre_calificado' => $rolCalificado === 'vendedor'
                ? (string)($row['nombre_vendedor'] ?? 'Vecino')
                : (string)($row['nombre_comprador'] ?? 'Vecino'),
            'puede_calificar' => ($estado === 'pendiente' && $fechaLimite !== '') ? 1 : 0,
        ];
    }

    private function selectCalificacionBase(): string
    {
        return "
            SELECT
                c.*,
                pr.titulo AS titulo_publicacion,
                TRIM(COALESCE(uc.nombre, '')) AS nombre_comprador,
                TRIM(COALESCE(uv.nombre, '')) AS nombre_vendedor
            FROM calificacion c
            INNER JOIN pedido p ON p.codigo_pedido = c.codigo_pedido
            INNER JOIN producto pr ON pr.codigo_producto = p.codigo_producto
            INNER JOIN usuario uc ON uc.codigo_usuario = p.codigo_usuario_comprador
            INNER JOIN usuario uv ON uv.codigo_usuario = p.codigo_usuario_vendedor
        ";
    }

    public function generarPendientesPorPedido(int $codigoPedido): array
    {
        try {
            $pedido = $this->obtenerPedidoCompletado($codigoPedido);

            if (!$pedido) {
                return [
                    'ok' => false,
                    'error' => 'PEDIDO_NO_COMPLETADO',
                    'mensaje' => 'El pedido aún no está completado para calificar.'
                ];
            }

            $codigoComprador = (int)$pedido['codigo_usuario_comprador'];
            $codigoVendedor = (int)$pedido['codigo_usuario_vendedor'];

            if ($codigoComprador <= 0 || $codigoVendedor <= 0 || $codigoComprador === $codigoVendedor) {
                return [
                    'ok' => false,
                    'error' => 'USUARIOS_INVALIDOS',
                    'mensaje' => 'No se pudo generar la calificación para este pedido.'
                ];
            }

            $fechaLimiteSql = "DATE_ADD(NOW(), INTERVAL " . self::DIAS_PLAZO_CALIFICACION . " DAY)";

            $sql = "
                INSERT INTO calificacion
                (
                    codigo_pedido,
                    codigo_usuario_calificador,
                    codigo_usuario_calificado,
                    rol_calificador,
                    rol_calificado,
                    tipo_calificacion,
                    fecha_habilitacion,
                    fecha_limite,
                    estado
                )
                VALUES
                (
                    :codigo_pedido_cv,
                    :codigo_comprador_cv,
                    :codigo_vendedor_cv,
                    'comprador',
                    'vendedor',
                    'comprador_a_vendedor',
                    NOW(),
                    {$fechaLimiteSql},
                    'pendiente'
                ),
                (
                    :codigo_pedido_vc,
                    :codigo_vendedor_vc,
                    :codigo_comprador_vc,
                    'vendedor',
                    'comprador',
                    'vendedor_a_comprador',
                    NOW(),
                    {$fechaLimiteSql},
                    'pendiente'
                )
                ON DUPLICATE KEY UPDATE
                    updated_at = NOW()
            ";

            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_pedido_cv', $codigoPedido, PDO::PARAM_INT);
            $st->bindValue(':codigo_comprador_cv', $codigoComprador, PDO::PARAM_INT);
            $st->bindValue(':codigo_vendedor_cv', $codigoVendedor, PDO::PARAM_INT);
            $st->bindValue(':codigo_pedido_vc', $codigoPedido, PDO::PARAM_INT);
            $st->bindValue(':codigo_vendedor_vc', $codigoVendedor, PDO::PARAM_INT);
            $st->bindValue(':codigo_comprador_vc', $codigoComprador, PDO::PARAM_INT);
            $st->execute();

            return [
                'ok' => true,
                'data' => [
                    'comprador' => $this->obtenerPendientePorPedidoUsuario($codigoPedido, $codigoComprador),
                    'vendedor' => $this->obtenerPendientePorPedidoUsuario($codigoPedido, $codigoVendedor),
                ]
            ];
        } catch (Throwable $e) {
            error_log('[EV][Calificacion][generarPendientesPorPedido] ' . $e->getMessage());
            return [
                'ok' => false,
                'error' => 'ERROR_GENERAR_CALIFICACIONES',
                'mensaje' => 'No se pudieron generar las calificaciones pendientes.'
            ];
        }
    }

    public function obtenerPendientePorPedidoUsuario(int $codigoPedido, int $codigoUsuario): ?array
    {
        try {
            $this->actualizarVencidas($codigoUsuario);

            $sql = $this->selectCalificacionBase() . "
                WHERE c.codigo_pedido = :codigo_pedido
                  AND c.codigo_usuario_calificador = :codigo_usuario
                  AND c.estado = 'pendiente'
                  AND c.fecha_limite >= NOW()
                LIMIT 1
            ";

            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
            $st->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
            $st->execute();

            $row = $st->fetch(PDO::FETCH_ASSOC);
            return $this->formatear($row ?: null);
        } catch (Throwable $e) {
            error_log('[EV][Calificacion][obtenerPendientePorPedidoUsuario] ' . $e->getMessage());
            return null;
        }
    }

    public function listarPendientesUsuario(int $codigoUsuario): array
    {
        try {
            if ($codigoUsuario <= 0) {
                return ['ok' => false, 'error' => 'USUARIO_INVALIDO', 'mensaje' => 'Usuario inválido.'];
            }

            $this->actualizarVencidas($codigoUsuario);

            $sql = $this->selectCalificacionBase() . "
                WHERE c.codigo_usuario_calificador = :codigo_usuario
                  AND c.estado = 'pendiente'
                  AND c.fecha_limite >= NOW()
                ORDER BY c.fecha_habilitacion DESC, c.codigo_calificacion DESC
            ";

            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
            $st->execute();

            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $items = [];

            foreach ($rows as $row) {
                $items[] = $this->formatear($row);
            }

            return ['ok' => true, 'data' => $items];
        } catch (Throwable $e) {
            error_log('[EV][Calificacion][listarPendientesUsuario] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_LISTAR_CALIFICACIONES', 'mensaje' => 'No se pudieron listar las calificaciones pendientes.'];
        }
    }

    public function adjuntarPendientesADataPedidos(array $data, int $codigoUsuario): array
    {
        try {
            if ($codigoUsuario <= 0) return $data;

            $ids = [];
            foreach (['pendientes', 'en_proceso', 'finalizados'] as $grupo) {
                if (!isset($data[$grupo]) || !is_array($data[$grupo])) continue;
                foreach ($data[$grupo] as $item) {
                    $id = (int)($item['codigo_pedido'] ?? 0);
                    if ($id > 0) $ids[$id] = $id;
                }
            }

            if (!$ids) return $data;

            $this->actualizarVencidas($codigoUsuario);

            $placeholders = [];
            $params = [':codigo_usuario' => $codigoUsuario];
            $i = 0;
            foreach (array_values($ids) as $id) {
                $key = ':p' . $i;
                $placeholders[] = $key;
                $params[$key] = $id;
                $i++;
            }

            $sql = $this->selectCalificacionBase() . "
                WHERE c.codigo_usuario_calificador = :codigo_usuario
                  AND c.estado = 'pendiente'
                  AND c.fecha_limite >= NOW()
                  AND c.codigo_pedido IN (" . implode(',', $placeholders) . ")
            ";

            $st = $this->dblink->prepare($sql);
            foreach ($params as $key => $value) {
                $st->bindValue($key, (int)$value, PDO::PARAM_INT);
            }
            $st->execute();

            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $map = [];
            foreach ($rows as $row) {
                $map[(int)$row['codigo_pedido']] = $this->formatear($row);
            }

            foreach (['pendientes', 'en_proceso', 'finalizados'] as $grupo) {
                if (!isset($data[$grupo]) || !is_array($data[$grupo])) continue;
                foreach ($data[$grupo] as &$item) {
                    if (!is_array($item)) continue;
                    $id = (int)($item['codigo_pedido'] ?? 0);
                    $pendiente = $map[$id] ?? null;
                    $item['calificacion_pendiente'] = $pendiente;
                    $item['puede_calificar'] = $pendiente ? 1 : 0;
                    $item['codigo_calificacion_pendiente'] = $pendiente ? (int)$pendiente['codigo_calificacion'] : 0;
                }
                unset($item);
            }

            return $data;
        } catch (Throwable $e) {
            error_log('[EV][Calificacion][adjuntarPendientesADataPedidos] ' . $e->getMessage());
            return $data;
        }
    }

    public function enviarCalificacion(
        int $codigoCalificacion,
        int $codigoUsuarioCalificador,
        int $puntaje,
        array $etiquetas,
        string $comentario,
        bool $reportarSoporte = false
    ): array {
        try {
            if ($codigoCalificacion <= 0 || $codigoUsuarioCalificador <= 0) {
                return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'Parámetros inválidos.'];
            }

            if ($puntaje < 1 || $puntaje > 5) {
                return ['ok' => false, 'error' => 'PUNTAJE_INVALIDO', 'mensaje' => 'Selecciona una calificación del 1 al 5.'];
            }

            $comentario = trim($comentario);
            if (mb_strlen($comentario, 'UTF-8') > 800) {
                $comentario = mb_substr($comentario, 0, 800, 'UTF-8');
            }

            $this->dblink->beginTransaction();

            $sql = $this->selectCalificacionBase() . "
                WHERE c.codigo_calificacion = :codigo_calificacion
                  AND c.codigo_usuario_calificador = :codigo_usuario
                LIMIT 1
                FOR UPDATE
            ";

            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_calificacion', $codigoCalificacion, PDO::PARAM_INT);
            $st->bindValue(':codigo_usuario', $codigoUsuarioCalificador, PDO::PARAM_INT);
            $st->execute();

            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'CALIFICACION_NO_ENCONTRADA', 'mensaje' => 'No se encontró la calificación pendiente.'];
            }

            if ((string)$row['estado'] !== 'pendiente') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'CALIFICACION_NO_DISPONIBLE', 'mensaje' => 'Esta calificación ya no está disponible.'];
            }

            $fechaLimite = strtotime((string)$row['fecha_limite']);
            if ($fechaLimite !== false && $fechaLimite < time()) {
                $upVencida = $this->dblink->prepare("UPDATE calificacion SET estado = 'vencida', updated_at = NOW() WHERE codigo_calificacion = :codigo_calificacion");
                $upVencida->bindValue(':codigo_calificacion', $codigoCalificacion, PDO::PARAM_INT);
                $upVencida->execute();

                $this->dblink->commit();
                return ['ok' => false, 'error' => 'CALIFICACION_VENCIDA', 'mensaje' => 'El plazo para calificar este pedido ya venció.'];
            }

            $up = $this->dblink->prepare("
                UPDATE calificacion
                SET puntaje = :puntaje,
                    comentario = :comentario,
                    estado = 'enviada',
                    fecha_envio = NOW(),
                    updated_at = NOW()
                WHERE codigo_calificacion = :codigo_calificacion
            ");
            $up->bindValue(':puntaje', $puntaje, PDO::PARAM_INT);
            $up->bindValue(':comentario', $comentario !== '' ? $comentario : null, $comentario !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $up->bindValue(':codigo_calificacion', $codigoCalificacion, PDO::PARAM_INT);
            $up->execute();

            $this->guardarEtiquetas($codigoCalificacion, $this->normalizarEtiquetas($etiquetas, (string)$row['rol_calificado']));

            if ($reportarSoporte && $puntaje <= 2) {
                $this->registrarReporteInterno($row, $codigoUsuarioCalificador, $comentario);
            }

            $this->dblink->commit();

            $enviada = $this->obtenerPorIdUsuario($codigoCalificacion, $codigoUsuarioCalificador);

            return [
                'ok' => true,
                'mensaje' => 'Tu calificación fue registrada correctamente.',
                'data' => $enviada
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            error_log('[EV][Calificacion][enviarCalificacion] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_ENVIAR_CALIFICACION', 'mensaje' => 'No se pudo registrar la calificación.'];
        }
    }

    private function obtenerPorIdUsuario(int $codigoCalificacion, int $codigoUsuario): ?array
    {
        $sql = $this->selectCalificacionBase() . "
            WHERE c.codigo_calificacion = :codigo_calificacion
              AND c.codigo_usuario_calificador = :codigo_usuario
            LIMIT 1
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_calificacion', $codigoCalificacion, PDO::PARAM_INT);
        $st->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $this->formatear($row ?: null);
    }

    private function normalizarEtiquetas(array $etiquetas, string $rolCalificado): array
    {
        $permitidas = $rolCalificado === 'vendedor' ? self::ETIQUETAS_VENDEDOR : self::ETIQUETAS_COMPRADOR;
        $permitidasMap = array_fill_keys($permitidas, true);

        $out = [];
        foreach ($etiquetas as $etiqueta) {
            $txt = trim((string)$etiqueta);
            if ($txt === '' || !isset($permitidasMap[$txt])) continue;
            $out[$txt] = $txt;
        }

        return array_values($out);
    }

    private function guardarEtiquetas(int $codigoCalificacion, array $etiquetas): void
    {
        $del = $this->dblink->prepare("DELETE FROM calificacion_etiqueta WHERE codigo_calificacion = :codigo_calificacion");
        $del->bindValue(':codigo_calificacion', $codigoCalificacion, PDO::PARAM_INT);
        $del->execute();

        if (!$etiquetas) return;

        $ins = $this->dblink->prepare("
            INSERT INTO calificacion_etiqueta (codigo_calificacion, etiqueta)
            VALUES (:codigo_calificacion, :etiqueta)
        ");

        foreach ($etiquetas as $etiqueta) {
            $ins->bindValue(':codigo_calificacion', $codigoCalificacion, PDO::PARAM_INT);
            $ins->bindValue(':etiqueta', mb_substr($etiqueta, 0, 80, 'UTF-8'), PDO::PARAM_STR);
            $ins->execute();
        }
    }

    private function registrarReporteInterno(array $calificacionRow, int $codigoUsuarioReporta, string $detalle): void
    {
        $codigoCalificacion = (int)($calificacionRow['codigo_calificacion'] ?? 0);
        $codigoPedido = (int)($calificacionRow['codigo_pedido'] ?? 0);
        $codigoUsuarioReportado = (int)($calificacionRow['codigo_usuario_calificado'] ?? 0);

        if ($codigoCalificacion <= 0 || $codigoPedido <= 0 || $codigoUsuarioReporta <= 0 || $codigoUsuarioReportado <= 0) return;

        $sql = "
            INSERT INTO calificacion_reporte
            (
                codigo_calificacion,
                codigo_pedido,
                codigo_usuario_reporta,
                codigo_usuario_reportado,
                motivo,
                detalle,
                estado
            )
            VALUES
            (
                :codigo_calificacion,
                :codigo_pedido,
                :codigo_usuario_reporta,
                :codigo_usuario_reportado,
                'experiencia_mala',
                :detalle,
                'pendiente'
            )
            ON DUPLICATE KEY UPDATE
                detalle = VALUES(detalle),
                estado = IF(estado IN ('resuelto', 'descartado'), estado, 'pendiente'),
                updated_at = NOW()
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_calificacion', $codigoCalificacion, PDO::PARAM_INT);
        $st->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
        $st->bindValue(':codigo_usuario_reporta', $codigoUsuarioReporta, PDO::PARAM_INT);
        $st->bindValue(':codigo_usuario_reportado', $codigoUsuarioReportado, PDO::PARAM_INT);
        $st->bindValue(':detalle', $detalle !== '' ? mb_substr($detalle, 0, 800, 'UTF-8') : null, $detalle !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $st->execute();
    }

    public function obtenerResumenVendedor(int $codigoUsuarioVendedor): array
    {
        try {
            if ($codigoUsuarioVendedor <= 0) {
                return ['ok' => false, 'error' => 'VENDEDOR_INVALIDO', 'mensaje' => 'Vendedor inválido.'];
            }

            $sql = "
                SELECT
                    COUNT(*) AS total,
                    COALESCE(ROUND(AVG(puntaje), 2), 0) AS promedio
                FROM calificacion
                WHERE codigo_usuario_calificado = :codigo_usuario
                  AND rol_calificado = 'vendedor'
                  AND estado = 'enviada'
                  AND puntaje IS NOT NULL
            ";

            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_usuario', $codigoUsuarioVendedor, PDO::PARAM_INT);
            $st->execute();

            $row = $st->fetch(PDO::FETCH_ASSOC) ?: [];
            $total = (int)($row['total'] ?? 0);
            $promedio = round((float)($row['promedio'] ?? 0), 2);

            return [
                'ok' => true,
                'data' => [
                    'codigo_usuario_vendedor' => $codigoUsuarioVendedor,
                    'total_calificaciones' => $total,
                    'promedio' => $promedio,
                    'texto' => $total >= 5 ? number_format($promedio, 1) . ' · ' . $total . ' ventas' : 'Nuevo vendedor'
                ]
            ];
        } catch (Throwable $e) {
            error_log('[EV][Calificacion][obtenerResumenVendedor] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_RESUMEN_VENDEDOR', 'mensaje' => 'No se pudo obtener el resumen del vendedor.'];
        }
    }
}
