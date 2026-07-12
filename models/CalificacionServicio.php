<?php
// models/CalificacionServicio.php
// Punto 11 EV: calificación bidireccional e independiente para servicios.

declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class CalificacionServicio extends Conexion
{
    private const DIAS_PLAZO = 7;

    private const ETIQUETAS_PROVEEDOR = [
        'Calidad del trabajo',
        'Puntualidad',
        'Buena comunicación',
        'Cumplió lo acordado',
        'Buena atención',
        'Calidad aceptable',
        'Comunicación mejorable',
        'Puntualidad mejorable',
        'Cumplimiento parcial',
        'Atención correcta',
        'Calidad por mejorar',
        'Impuntualidad',
        'Mala comunicación',
        'No cumplió lo acordado',
        'Atención por mejorar',
    ];

    private const ETIQUETAS_COMPRADOR = [
        'Puntual',
        'Buena comunicación',
        'Coordinación clara',
        'Cumplió lo acordado',
        'Trato respetuoso',
        'Coordinación aceptable',
        'Comunicación mejorable',
        'Puntualidad mejorable',
        'Indicaciones poco claras',
        'Trato correcto',
        'Impuntualidad',
        'Mala comunicación',
        'No cumplió lo acordado',
        'Trato inadecuado',
    ];

    private function actualizarVencidas(?int $codigoUsuario = null): void
    {
        $sql = "
            UPDATE calificacion_servicio
            SET estado = 'vencida', updated_at = CURRENT_TIMESTAMP
            WHERE estado = 'pendiente'
              AND fecha_limite < NOW()
        ";

        if ($codigoUsuario !== null && $codigoUsuario > 0) {
            $sql .= ' AND codigo_usuario_calificador = :usuario';
        }

        $st = $this->dblink->prepare($sql);
        if ($codigoUsuario !== null && $codigoUsuario > 0) {
            $st->bindValue(':usuario', $codigoUsuario, PDO::PARAM_INT);
        }
        $st->execute();
    }

    private function obtenerSolicitudCompletada(int $codigoSolicitud, bool $forUpdate = false): ?array
    {
        if ($codigoSolicitud <= 0) {
            return null;
        }

        $lock = $forUpdate ? ' FOR UPDATE' : '';
        $sql = "
            SELECT
                ss.codigo_solicitud_servicio,
                ss.codigo_usuario_solicitante,
                ss.codigo_usuario_proveedor,
                ss.estado,
                ss.fecha_confirmacion_solicitante,
                p.titulo AS titulo_servicio,
                us.nombre AS nombre_comprador,
                up.nombre AS nombre_proveedor
            FROM solicitud_servicio ss
            INNER JOIN producto p ON p.codigo_producto = ss.codigo_producto
            INNER JOIN usuario us ON us.codigo_usuario = ss.codigo_usuario_solicitante
            INNER JOIN usuario up ON up.codigo_usuario = ss.codigo_usuario_proveedor
            WHERE ss.codigo_solicitud_servicio = :solicitud
              AND ss.estado = 'servicio_confirmado_solicitante'
            LIMIT 1{$lock}
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function asegurarPendientesParaSolicitud(int $codigoSolicitud): array
    {
        if ($codigoSolicitud <= 0) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'Solicitud de servicio inválida.'];
        }

        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudCompletada($codigoSolicitud, true);

            if (!$solicitud) {
                $this->dblink->rollBack();
                return [
                    'ok' => false,
                    'error' => 'SERVICIO_NO_COMPLETADO',
                    'mensaje' => 'La calificación se habilita cuando el servicio está completado.',
                ];
            }

            $comprador = (int)$solicitud['codigo_usuario_solicitante'];
            $proveedor = (int)$solicitud['codigo_usuario_proveedor'];
            $habilitacion = (string)($solicitud['fecha_confirmacion_solicitante'] ?? '');
            if ($habilitacion === '') {
                $habilitacion = date('Y-m-d H:i:s');
            }

            $fechaBase = new DateTimeImmutable($habilitacion, new DateTimeZone('America/Lima'));
            $limite = $fechaBase->modify('+' . self::DIAS_PLAZO . ' days')->format('Y-m-d H:i:s');

            $sql = "
                INSERT IGNORE INTO calificacion_servicio
                (
                    codigo_solicitud_servicio,
                    codigo_usuario_calificador,
                    codigo_usuario_calificado,
                    rol_calificador,
                    rol_calificado,
                    estado,
                    fecha_habilitacion,
                    fecha_limite
                )
                VALUES
                (:solicitud1, :comprador1, :proveedor1, 'comprador', 'proveedor', 'pendiente', :habilitacion1, :limite1),
                (:solicitud2, :proveedor2, :comprador2, 'proveedor', 'comprador', 'pendiente', :habilitacion2, :limite2)
            ";

            $st = $this->dblink->prepare($sql);
            $st->bindValue(':solicitud1', $codigoSolicitud, PDO::PARAM_INT);
            $st->bindValue(':comprador1', $comprador, PDO::PARAM_INT);
            $st->bindValue(':proveedor1', $proveedor, PDO::PARAM_INT);
            $st->bindValue(':solicitud2', $codigoSolicitud, PDO::PARAM_INT);
            $st->bindValue(':proveedor2', $proveedor, PDO::PARAM_INT);
            $st->bindValue(':comprador2', $comprador, PDO::PARAM_INT);
            $st->bindValue(':habilitacion1', $fechaBase->format('Y-m-d H:i:s'), PDO::PARAM_STR);
            $st->bindValue(':limite1', $limite, PDO::PARAM_STR);
            $st->bindValue(':habilitacion2', $fechaBase->format('Y-m-d H:i:s'), PDO::PARAM_STR);
            $st->bindValue(':limite2', $limite, PDO::PARAM_STR);
            $st->execute();

            $this->dblink->commit();

            return [
                'ok' => true,
                'mensaje' => 'Las calificaciones del servicio quedaron habilitadas.',
                'data' => ['codigo_solicitud_servicio' => $codigoSolicitud],
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            error_log('[EV][CalificacionServicio][asegurarPendientesParaSolicitud] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_HABILITAR_CALIFICACIONES', 'mensaje' => 'No se pudieron habilitar las calificaciones del servicio.'];
        }
    }

    private function obtenerEtiquetas(int $codigoCalificacion): array
    {
        $st = $this->dblink->prepare("SELECT etiqueta FROM calificacion_servicio_etiqueta WHERE codigo_calificacion_servicio = :id ORDER BY codigo_calificacion_servicio_etiqueta");
        $st->bindValue(':id', $codigoCalificacion, PDO::PARAM_INT);
        $st->execute();
        return array_values(array_map(static fn(array $row): string => (string)$row['etiqueta'], $st->fetchAll(PDO::FETCH_ASSOC) ?: []));
    }

    private function formatear(array $row): array
    {
        $rolCalificado = (string)($row['rol_calificado'] ?? '');
        $codigo = (int)($row['codigo_calificacion_servicio'] ?? 0);

        return [
            'codigo_calificacion_servicio' => $codigo,
            'codigo_solicitud_servicio' => (int)($row['codigo_solicitud_servicio'] ?? 0),
            'codigo_usuario_calificador' => (int)($row['codigo_usuario_calificador'] ?? 0),
            'codigo_usuario_calificado' => (int)($row['codigo_usuario_calificado'] ?? 0),
            'rol_calificador' => (string)($row['rol_calificador'] ?? ''),
            'rol_calificado' => $rolCalificado,
            'puntaje' => $row['puntaje'] !== null ? (int)$row['puntaje'] : null,
            'comentario' => (string)($row['comentario'] ?? ''),
            'estado' => (string)($row['estado'] ?? ''),
            'fecha_habilitacion' => $row['fecha_habilitacion'] ?? null,
            'fecha_limite' => $row['fecha_limite'] ?? null,
            'fecha_envio' => $row['fecha_envio'] ?? null,
            'titulo_servicio' => (string)($row['titulo_servicio'] ?? 'Servicio'),
            'nombre_comprador' => (string)($row['nombre_comprador'] ?? 'Vecino'),
            'nombre_proveedor' => (string)($row['nombre_proveedor'] ?? 'Vecino'),
            'nombre_calificado' => $rolCalificado === 'proveedor'
                ? (string)($row['nombre_proveedor'] ?? 'Vecino')
                : (string)($row['nombre_comprador'] ?? 'Vecino'),
            'etiquetas' => $codigo > 0 ? $this->obtenerEtiquetas($codigo) : [],
        ];
    }

    private function consultaBase(): string
    {
        return "
            SELECT
                cs.*,
                p.titulo AS titulo_servicio,
                us.nombre AS nombre_comprador,
                up.nombre AS nombre_proveedor
            FROM calificacion_servicio cs
            INNER JOIN solicitud_servicio ss ON ss.codigo_solicitud_servicio = cs.codigo_solicitud_servicio
            INNER JOIN producto p ON p.codigo_producto = ss.codigo_producto
            INNER JOIN usuario us ON us.codigo_usuario = ss.codigo_usuario_solicitante
            INNER JOIN usuario up ON up.codigo_usuario = ss.codigo_usuario_proveedor
        ";
    }

    public function listarPendientesUsuario(int $codigoUsuario): array
    {
        if ($codigoUsuario <= 0) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'Usuario inválido.'];
        }

        try {
            $this->actualizarVencidas($codigoUsuario);
            $sql = $this->consultaBase() . "
                WHERE cs.codigo_usuario_calificador = :usuario
                  AND cs.estado = 'pendiente'
                ORDER BY cs.fecha_habilitacion DESC
            ";
            $st = $this->dblink->prepare($sql);
            $st->bindValue(':usuario', $codigoUsuario, PDO::PARAM_INT);
            $st->execute();

            return [
                'ok' => true,
                'data' => array_map(fn(array $row): array => $this->formatear($row), $st->fetchAll(PDO::FETCH_ASSOC) ?: []),
            ];
        } catch (Throwable $e) {
            error_log('[EV][CalificacionServicio][listarPendientesUsuario] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_LISTAR_CALIFICACIONES_SERVICIO', 'mensaje' => 'No se pudieron obtener las calificaciones pendientes.'];
        }
    }

    public function obtenerPorSolicitudUsuario(int $codigoSolicitud, int $codigoUsuario): array
    {
        if ($codigoSolicitud <= 0 || $codigoUsuario <= 0) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'Parámetros inválidos.'];
        }

        try {
            $this->actualizarVencidas($codigoUsuario);
            $sql = $this->consultaBase() . "
                WHERE cs.codigo_solicitud_servicio = :solicitud
                  AND cs.codigo_usuario_calificador = :usuario
                LIMIT 1
            ";
            $st = $this->dblink->prepare($sql);
            $st->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
            $st->bindValue(':usuario', $codigoUsuario, PDO::PARAM_INT);
            $st->execute();
            $row = $st->fetch(PDO::FETCH_ASSOC);

            return ['ok' => true, 'data' => $row ? $this->formatear($row) : null];
        } catch (Throwable $e) {
            error_log('[EV][CalificacionServicio][obtenerPorSolicitudUsuario] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_OBTENER_CALIFICACION_SERVICIO', 'mensaje' => 'No se pudo obtener la calificación del servicio.'];
        }
    }

    private function etiquetasPermitidas(string $rolCalificado): array
    {
        return $rolCalificado === 'comprador'
            ? self::ETIQUETAS_COMPRADOR
            : self::ETIQUETAS_PROVEEDOR;
    }

    public function enviar(
        int $codigoCalificacion,
        int $codigoUsuario,
        int $puntaje,
        array $etiquetas,
        string $comentario
    ): array {
        if ($codigoCalificacion <= 0 || $codigoUsuario <= 0) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'Calificación inválida.'];
        }
        if ($puntaje < 1 || $puntaje > 5) {
            return ['ok' => false, 'error' => 'PUNTAJE_INVALIDO', 'mensaje' => 'Selecciona una calificación de 1 a 5 estrellas.'];
        }

        $comentario = trim($comentario);
        if (mb_strlen($comentario, 'UTF-8') > 1500) {
            return ['ok' => false, 'error' => 'COMENTARIO_DEMASIADO_LARGO', 'mensaje' => 'El comentario no puede superar 1500 caracteres.'];
        }
        if ($puntaje <= 2 && mb_strlen($comentario, 'UTF-8') < 8) {
            return ['ok' => false, 'error' => 'COMENTARIO_REQUERIDO', 'mensaje' => 'Para una calificación de 1 o 2 estrellas, describe brevemente qué ocurrió.'];
        }

        try {
            $this->dblink->beginTransaction();
            $this->actualizarVencidas($codigoUsuario);

            $st = $this->dblink->prepare("SELECT * FROM calificacion_servicio WHERE codigo_calificacion_servicio = :id AND codigo_usuario_calificador = :usuario LIMIT 1 FOR UPDATE");
            $st->bindValue(':id', $codigoCalificacion, PDO::PARAM_INT);
            $st->bindValue(':usuario', $codigoUsuario, PDO::PARAM_INT);
            $st->execute();
            $row = $st->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'CALIFICACION_NO_ENCONTRADA', 'mensaje' => 'La calificación no existe o no te pertenece.'];
            }
            if ((string)$row['estado'] !== 'pendiente') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'CALIFICACION_NO_DISPONIBLE', 'mensaje' => 'Esta calificación ya no está disponible.'];
            }

            $permitidas = array_flip($this->etiquetasPermitidas((string)$row['rol_calificado']));
            $limpias = [];
            foreach ($etiquetas as $etiqueta) {
                $etiqueta = trim((string)$etiqueta);
                if ($etiqueta !== '' && isset($permitidas[$etiqueta])) {
                    $limpias[$etiqueta] = $etiqueta;
                }
            }

            $up = $this->dblink->prepare("
                UPDATE calificacion_servicio
                SET puntaje = :puntaje,
                    comentario = :comentario,
                    estado = 'enviada',
                    fecha_envio = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_calificacion_servicio = :id
            ");
            $up->bindValue(':puntaje', $puntaje, PDO::PARAM_INT);
            $up->bindValue(':comentario', $comentario !== '' ? $comentario : null, $comentario !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $up->bindValue(':id', $codigoCalificacion, PDO::PARAM_INT);
            $up->execute();

            $del = $this->dblink->prepare('DELETE FROM calificacion_servicio_etiqueta WHERE codigo_calificacion_servicio = :id');
            $del->bindValue(':id', $codigoCalificacion, PDO::PARAM_INT);
            $del->execute();

            if ($limpias) {
                $ins = $this->dblink->prepare('INSERT INTO calificacion_servicio_etiqueta (codigo_calificacion_servicio, etiqueta) VALUES (:id, :etiqueta)');
                foreach ($limpias as $etiqueta) {
                    $ins->bindValue(':id', $codigoCalificacion, PDO::PARAM_INT);
                    $ins->bindValue(':etiqueta', $etiqueta, PDO::PARAM_STR);
                    $ins->execute();
                }
            }

            $this->dblink->commit();

            return [
                'ok' => true,
                'mensaje' => 'Tu calificación del servicio fue registrada.',
                'data' => [
                    'codigo_calificacion_servicio' => $codigoCalificacion,
                    'puntaje' => $puntaje,
                    'etiquetas' => array_values($limpias),
                ],
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            error_log('[EV][CalificacionServicio][enviar] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_ENVIAR_CALIFICACION_SERVICIO', 'mensaje' => 'No se pudo registrar la calificación del servicio.'];
        }
    }

    public function obtenerResumenUsuario(int $codigoUsuario): array
    {
        if ($codigoUsuario <= 0) {
            return ['ok' => true, 'data' => ['promedio' => null, 'total' => 0]];
        }

        try {
            $st = $this->dblink->prepare("
                SELECT ROUND(AVG(puntaje), 2) AS promedio, COUNT(*) AS total
                FROM calificacion_servicio
                WHERE codigo_usuario_calificado = :usuario
                  AND estado = 'enviada'
                  AND puntaje BETWEEN 1 AND 5
            ");
            $st->bindValue(':usuario', $codigoUsuario, PDO::PARAM_INT);
            $st->execute();
            $row = $st->fetch(PDO::FETCH_ASSOC) ?: [];

            return [
                'ok' => true,
                'data' => [
                    'promedio' => $row['promedio'] !== null ? (float)$row['promedio'] : null,
                    'total' => (int)($row['total'] ?? 0),
                ],
            ];
        } catch (Throwable $e) {
            error_log('[EV][CalificacionServicio][obtenerResumenUsuario] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_REPUTACION_SERVICIO', 'mensaje' => 'No se pudo obtener la reputación de servicios.'];
        }
    }
}
