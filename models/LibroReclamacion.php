<?php
// models/LibroReclamacion.php

declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class LibroReclamacion extends Conexion
{
    private const ESTADOS = ['registrado', 'en_revision', 'respondido', 'cerrado'];

    public function resumen(): array
    {
        try {
            $sql = "
                SELECT
                    SUM(estado = 'registrado') AS registrados,
                    SUM(estado = 'en_revision') AS en_revision,
                    SUM(estado = 'respondido') AS respondidos,
                    SUM(estado = 'cerrado') AS cerrados,
                    COUNT(*) AS total,
                    SUM(DATE(fecha_registro) = CURDATE()) AS recibidos_hoy
                FROM libro_reclamacion
            ";
            $row = $this->dblink->query($sql)->fetch(PDO::FETCH_ASSOC) ?: [];
            return ['ok' => true, 'data' => [
                'registrados' => (int)($row['registrados'] ?? 0),
                'en_revision' => (int)($row['en_revision'] ?? 0),
                'respondidos' => (int)($row['respondidos'] ?? 0),
                'cerrados' => (int)($row['cerrados'] ?? 0),
                'total' => (int)($row['total'] ?? 0),
                'recibidos_hoy' => (int)($row['recibidos_hoy'] ?? 0),
            ]];
        } catch (Throwable $e) {
            error_log('[EV][LibroReclamacion][resumen] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_BD', 'mensaje' => 'No se pudo obtener el resumen del Libro de Reclamaciones.'];
        }
    }

    public function listar(array $filtros): array
    {
        try {
            $estado = strtolower(trim((string)($filtros['estado'] ?? 'pendientes')));
            $tipo = strtolower(trim((string)($filtros['tipo'] ?? 'all')));
            $buscar = trim((string)($filtros['buscar'] ?? ''));
            $page = max(1, (int)($filtros['page'] ?? 1));
            $size = min(50, max(10, (int)($filtros['size'] ?? 20)));
            $offset = ($page - 1) * $size;

            $where = ['1=1'];
            $params = [];

            if ($estado === 'pendientes') {
                $where[] = "lr.estado IN ('registrado','en_revision')";
            } elseif (in_array($estado, self::ESTADOS, true)) {
                $where[] = 'lr.estado = :estado';
                $params[':estado'] = $estado;
            }

            if (in_array($tipo, ['reclamo', 'queja'], true)) {
                $where[] = 'lr.tipo_registro = :tipo';
                $params[':tipo'] = $tipo;
            }

            if ($buscar !== '') {
                $where[] = "(
                    lr.numero_hoja LIKE :buscar
                    OR lr.consumidor_nombres LIKE :buscar
                    OR lr.consumidor_apellidos LIKE :buscar
                    OR lr.numero_documento LIKE :buscar
                    OR lr.correo LIKE :buscar
                    OR lr.descripcion_bien LIKE :buscar
                )";
                $params[':buscar'] = '%' . $buscar . '%';
            }

            $whereSql = implode(' AND ', $where);
            $stCount = $this->dblink->prepare("SELECT COUNT(*) FROM libro_reclamacion lr WHERE {$whereSql}");
            $stCount->execute($params);
            $total = (int)$stCount->fetchColumn();

            $sql = "
                SELECT
                    lr.codigo_libro_reclamacion,
                    lr.numero_hoja,
                    lr.fecha_registro,
                    lr.consumidor_nombres,
                    lr.consumidor_apellidos,
                    lr.numero_documento,
                    lr.telefono,
                    lr.correo,
                    lr.tipo_bien,
                    lr.descripcion_bien,
                    lr.monto_reclamado,
                    lr.tipo_registro,
                    lr.detalle,
                    lr.pedido_concreto,
                    lr.estado,
                    lr.fecha_respuesta,
                    lr.updated_at
                FROM libro_reclamacion lr
                WHERE {$whereSql}
                ORDER BY
                    FIELD(lr.estado, 'registrado','en_revision','respondido','cerrado'),
                    lr.fecha_registro ASC,
                    lr.codigo_libro_reclamacion ASC
                LIMIT {$size} OFFSET {$offset}
            ";
            $st = $this->dblink->prepare($sql);
            $st->execute($params);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

            return [
                'ok' => true,
                'data' => $rows,
                'pagination' => [
                    'page' => $page,
                    'size' => $size,
                    'total' => $total,
                    'pages' => max(1, (int)ceil($total / $size)),
                ],
            ];
        } catch (Throwable $e) {
            error_log('[EV][LibroReclamacion][listar] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_BD', 'mensaje' => 'No se pudo cargar la bandeja del Libro de Reclamaciones.'];
        }
    }

    public function detalle(int $codigo): array
    {
        if ($codigo <= 0) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'El código del registro no es válido.'];
        }

        try {
            $st = $this->dblink->prepare('SELECT * FROM libro_reclamacion WHERE codigo_libro_reclamacion = :codigo LIMIT 1');
            $st->execute([':codigo' => $codigo]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return ['ok' => false, 'error' => 'NO_ENCONTRADO', 'mensaje' => 'No se encontró el reclamo o queja.'];
            }

            $stHist = $this->dblink->prepare(
                'SELECT codigo_historial, evento, estado_anterior, estado_nuevo, detalle, actor, fecha_evento
                 FROM libro_reclamacion_historial
                 WHERE codigo_libro_reclamacion = :codigo
                 ORDER BY fecha_evento ASC, codigo_historial ASC'
            );
            $stHist->execute([':codigo' => $codigo]);

            return ['ok' => true, 'data' => [
                'registro' => $row,
                'historial' => $stHist->fetchAll(PDO::FETCH_ASSOC) ?: [],
            ]];
        } catch (Throwable $e) {
            error_log('[EV][LibroReclamacion][detalle] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_BD', 'mensaje' => 'No se pudo abrir el registro.'];
        }
    }

    public function atender(
        int $codigo,
        int $codigoUsuario,
        string $estadoNuevo,
        string $respuesta,
        string $medioRespuesta
    ): array {
        $estadoNuevo = strtolower(trim($estadoNuevo));
        $respuesta = trim($respuesta);
        $medioRespuesta = strtolower(trim($medioRespuesta));

        if ($codigo <= 0 || $codigoUsuario <= 0 || !in_array($estadoNuevo, self::ESTADOS, true)) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'Los datos de atención no son válidos.'];
        }
        if (in_array($estadoNuevo, ['respondido', 'cerrado'], true) && mb_strlen($respuesta) < 10) {
            return ['ok' => false, 'error' => 'RESPUESTA_REQUERIDA', 'mensaje' => 'Escribe una respuesta de al menos 10 caracteres.'];
        }
        if (!in_array($medioRespuesta, ['correo', 'telefono', 'domicilio', 'otro'], true)) {
            $medioRespuesta = 'correo';
        }
        if (mb_strlen($respuesta) > 10000) {
            return ['ok' => false, 'error' => 'RESPUESTA_MUY_LARGA', 'mensaje' => 'La respuesta supera el tamaño permitido.'];
        }

        $this->dblink->beginTransaction();
        try {
            $st = $this->dblink->prepare(
                'SELECT * FROM libro_reclamacion WHERE codigo_libro_reclamacion = :codigo FOR UPDATE'
            );
            $st->execute([':codigo' => $codigo]);
            $actual = $st->fetch(PDO::FETCH_ASSOC);
            if (!$actual) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'NO_ENCONTRADO', 'mensaje' => 'No se encontró el reclamo o queja.'];
            }

            $estadoAnterior = (string)$actual['estado'];
            $set = [
                'estado = :estado',
                'responsable_atencion = :responsable',
                'medio_respuesta = :medio',
                'updated_at = CURRENT_TIMESTAMP',
            ];
            $params = [
                ':estado' => $estadoNuevo,
                ':responsable' => 'usuario:' . $codigoUsuario,
                ':medio' => $medioRespuesta,
                ':codigo' => $codigo,
            ];

            if ($respuesta !== '') {
                $set[] = 'respuesta_publica = :respuesta';
                $params[':respuesta'] = $respuesta;
            }
            if (in_array($estadoNuevo, ['respondido', 'cerrado'], true)) {
                $set[] = 'fecha_respuesta = COALESCE(fecha_respuesta, CURRENT_TIMESTAMP)';
            }
            if ($estadoNuevo === 'cerrado') {
                $set[] = 'fecha_cierre = CURRENT_TIMESTAMP';
            } else {
                $set[] = 'fecha_cierre = NULL';
            }

            $sql = 'UPDATE libro_reclamacion SET ' . implode(', ', $set) . ' WHERE codigo_libro_reclamacion = :codigo';
            $stUpdate = $this->dblink->prepare($sql);
            $stUpdate->execute($params);

            $evento = $estadoNuevo === 'en_revision'
                ? 'atencion_iniciada'
                : ($estadoNuevo === 'cerrado' ? 'caso_cerrado' : ($estadoNuevo === 'respondido' ? 'respuesta_registrada' : 'estado_actualizado'));

            $stHist = $this->dblink->prepare(
                'INSERT INTO libro_reclamacion_historial (
                    codigo_libro_reclamacion, evento, estado_anterior, estado_nuevo,
                    detalle, actor, fecha_evento
                 ) VALUES (
                    :codigo, :evento, :anterior, :nuevo,
                    :detalle, :actor, CURRENT_TIMESTAMP
                 )'
            );
            $stHist->execute([
                ':codigo' => $codigo,
                ':evento' => $evento,
                ':anterior' => $estadoAnterior,
                ':nuevo' => $estadoNuevo,
                ':detalle' => $respuesta !== '' ? mb_substr($respuesta, 0, 1000) : 'Estado actualizado por soporte.',
                ':actor' => 'usuario:' . $codigoUsuario,
            ]);

            $this->dblink->commit();

            return ['ok' => true, 'data' => [
                'codigo_libro_reclamacion' => $codigo,
                'numero_hoja' => (string)$actual['numero_hoja'],
                'correo' => (string)$actual['correo'],
                'consumidor' => trim((string)$actual['consumidor_nombres'] . ' ' . (string)$actual['consumidor_apellidos']),
                'estado' => $estadoNuevo,
                'respuesta' => $respuesta,
            ], 'mensaje' => 'La atención fue registrada correctamente.'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            error_log('[EV][LibroReclamacion][atender] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_BD', 'mensaje' => 'No se pudo registrar la atención.'];
        }
    }
}
