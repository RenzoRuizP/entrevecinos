<?php
// models/SoporteRecargas.php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class SoporteRecargas extends Conexion
{
    private const ESTADOS_VALIDOS = ['pendiente','observada','aprobada','rechazada'];

    public function listar(array $filtros): array
    {
        $estado  = trim((string)($filtros['estado'] ?? ''));
        $q       = trim((string)($filtros['q'] ?? ''));
        $page    = (int)($filtros['page'] ?? 1);
        $perPage = (int)($filtros['per_page'] ?? 10);

        if ($page < 1) $page = 1;
        if ($perPage < 5) $perPage = 5;
        if ($perPage > 50) $perPage = 50;

        if ($estado !== '' && !in_array($estado, self::ESTADOS_VALIDOS, true)) {
            return ['ok' => false, 'error' => 'VALIDATION', 'mensaje' => 'Estado inválido'];
        }

        $where  = [];
        $params = [];

        if ($estado !== '') {
            $where[] = "r.estado = :estado";
            $params[':estado'] = $estado;
        }

        if ($q !== '') {
            $where[] = "(r.id_operacion LIKE :q OR u.email LIKE :q OR u.nombre LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
        $offset   = ($page - 1) * $perPage;

        // Total
        $sqlCount = "
            SELECT COUNT(*)
            FROM recarga_saldo r
            INNER JOIN usuario u ON u.codigo_usuario = r.codigo_usuario
            {$whereSql}
        ";
        $stc = $this->dblink->prepare($sqlCount);
        foreach ($params as $k => $v) $stc->bindValue($k, $v);
        $stc->execute();
        $total = (int)$stc->fetchColumn();

        // Items
        $sql = "
            SELECT
                r.codigo_recarga,
                r.codigo_usuario,
                u.nombre AS usuario_nombre,
                u.email  AS usuario_email,
                r.monto,
                r.metodo,
                r.id_operacion,
                r.comprobante_path,
                r.estado,
                r.comentario_soporte,
                r.codigo_soporte,
                r.fecha_revision,
                r.fecha_creacion
            FROM recarga_saldo r
            INNER JOIN usuario u ON u.codigo_usuario = r.codigo_usuario
            {$whereSql}
            ORDER BY
                CASE r.estado
                    WHEN 'pendiente' THEN 1
                    WHEN 'observada' THEN 2
                    WHEN 'rechazada' THEN 3
                    WHEN 'aprobada'  THEN 4
                    ELSE 5
                END,
                r.fecha_creacion DESC
            LIMIT :limit OFFSET :offset
        ";
        $st = $this->dblink->prepare($sql);
        foreach ($params as $k => $v) $st->bindValue($k, $v);
        $st->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st->execute();
        $items = $st->fetchAll(PDO::FETCH_ASSOC);

        return [
            'ok' => true,
            'data' => [
                'items' => $items,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => (int)ceil($total / $perPage),
                ]
            ]
        ];
    }

    public function actualizarEstado(int $codigoRecarga, string $nuevoEstado, ?string $comentario, int $codigoSoporte): array
    {
        if ($codigoRecarga <= 0) {
            return ['ok' => false, 'error' => 'VALIDATION', 'mensaje' => 'Código de recarga inválido'];
        }
        $nuevoEstado = trim($nuevoEstado);
        if (!in_array($nuevoEstado, self::ESTADOS_VALIDOS, true)) {
            return ['ok' => false, 'error' => 'VALIDATION', 'mensaje' => 'Estado inválido'];
        }
        if ($codigoSoporte <= 0) {
            return ['ok' => false, 'error' => 'UNAUTHORIZED', 'mensaje' => 'Sesión inválida'];
        }

        try {
            $this->dblink->beginTransaction();

            // 1) Recarga FOR UPDATE
            $st = $this->dblink->prepare("
                SELECT codigo_recarga, codigo_usuario, monto, estado
                FROM recarga_saldo
                WHERE codigo_recarga = :id
                FOR UPDATE
            ");
            $st->execute([':id' => $codigoRecarga]);
            $rec = $st->fetch(PDO::FETCH_ASSOC);

            if (!$rec) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'NOT_FOUND', 'mensaje' => 'Recarga no encontrada'];
            }

            $codigoUsuario = (int)$rec['codigo_usuario'];
            $monto         = (float)$rec['monto'];
            $estadoActual  = (string)$rec['estado'];

            // 2) Billetera FOR UPDATE (o crear)
            $stB = $this->dblink->prepare("
                SELECT codigo_billetera, saldo_actual
                FROM billetera
                WHERE codigo_usuario = :u
                FOR UPDATE
            ");
            $stB->execute([':u' => $codigoUsuario]);
            $bil = $stB->fetch(PDO::FETCH_ASSOC);

            if (!$bil) {
                $insB = $this->dblink->prepare("
                    INSERT INTO billetera (codigo_usuario, saldo_actual, estado)
                    VALUES (:u, 0.00, 1)
                ");
                $insB->execute([':u' => $codigoUsuario]);

                $stB->execute([':u' => $codigoUsuario]);
                $bil = $stB->fetch(PDO::FETCH_ASSOC);
            }

            $saldoActual = (float)($bil['saldo_actual'] ?? 0);
            $saldoNuevo  = $saldoActual;

            $cambioEstado = ($nuevoEstado !== $estadoActual);

            // 3) Regla reversible (evita duplicar saldo)
            if ($cambioEstado) {
                if ($estadoActual === 'aprobada' && $nuevoEstado !== 'aprobada') {
                    $saldoNuevo = $saldoActual - $monto;
                    if ($saldoNuevo < -0.00001) {
                        $this->dblink->rollBack();
                        return [
                            'ok' => false,
                            'error' => 'WALLET_INCONSISTENT',
                            'mensaje' => 'No se puede revertir: saldo insuficiente para restar la recarga'
                        ];
                    }
                }

                if ($estadoActual !== 'aprobada' && $nuevoEstado === 'aprobada') {
                    $saldoNuevo = $saldoActual + $monto;
                }
            }

            if (abs($saldoNuevo - $saldoActual) > 0.00001) {
                $updB = $this->dblink->prepare("
                    UPDATE billetera
                    SET saldo_actual = :s
                    WHERE codigo_usuario = :u
                ");
                $updB->execute([
                    ':s' => number_format($saldoNuevo, 2, '.', ''),
                    ':u' => $codigoUsuario
                ]);
            }

            // 4) Update recarga
            $updR = $this->dblink->prepare("
                UPDATE recarga_saldo
                SET
                    estado = :estado,
                    comentario_soporte = :comentario,
                    codigo_soporte = :soporte,
                    fecha_revision = NOW()
                WHERE codigo_recarga = :id
            ");
            $updR->execute([
                ':estado'     => $nuevoEstado,
                ':comentario' => ($comentario !== null && trim($comentario) !== '' ? $comentario : null),
                ':soporte'    => $codigoSoporte,
                ':id'         => $codigoRecarga
            ]);

            $this->dblink->commit();

            // 5) Respuesta final
            $out = $this->dblink->prepare("
                SELECT
                    r.codigo_recarga,
                    r.codigo_usuario,
                    u.nombre AS usuario_nombre,
                    u.email  AS usuario_email,
                    r.monto,
                    r.metodo,
                    r.id_operacion,
                    r.comprobante_path,
                    r.estado,
                    r.comentario_soporte,
                    r.codigo_soporte,
                    r.fecha_revision,
                    r.fecha_creacion,
                    b.saldo_actual
                FROM recarga_saldo r
                INNER JOIN usuario u ON u.codigo_usuario = r.codigo_usuario
                INNER JOIN billetera b ON b.codigo_usuario = r.codigo_usuario
                WHERE r.codigo_recarga = :id
                LIMIT 1
            ");
            $out->execute([':id' => $codigoRecarga]);
            $row = $out->fetch(PDO::FETCH_ASSOC);

            return ['ok' => true, 'data' => $row];
        } catch (\Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][SoporteRecargas::actualizarEstado] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'SERVER_ERROR', 'mensaje' => 'Error interno al actualizar estado'];
        }
    }
}
