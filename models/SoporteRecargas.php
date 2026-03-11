<?php
// models/SoporteRecargas.php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class SoporteRecargas extends Conexion
{
    private const ESTADOS_VALIDOS = ['pendiente','observada','aprobada','rechazada'];

    public function listar(array $filtros): array
    {
        $estado = strtolower(trim((string)($filtros['estado'] ?? 'pendiente')));
        $rango  = (string)($filtros['rango'] ?? '7');
        $q      = trim((string)($filtros['q'] ?? ''));
        $page   = (int)($filtros['page'] ?? 1);
        $size   = (int)($filtros['size'] ?? 10);

        if ($page < 1) $page = 1;
        if ($size < 5) $size = 5;
        if ($size > 50) $size = 50;

        if ($estado !== '' && !in_array($estado, self::ESTADOS_VALIDOS, true)) {
            return ['ok' => false, 'error' => 'VALIDATION', 'mensaje' => 'Estado inválido'];
        }

        $where = " WHERE r.estado = :estado ";
        $params = [':estado' => $estado];

        if ($rango === 'hoy') {
            $where .= " AND DATE(r.fecha_creacion) = CURDATE() ";
        } else {
            $dias = (int)$rango;
            if ($dias > 0) {
                $where .= " AND r.fecha_creacion >= (NOW() - INTERVAL {$dias} DAY) ";
            }
        }

        if ($q !== '') {
            $where .= " AND (
                u.nombre LIKE :q OR
                u.documento LIKE :q OR
                r.id_operacion LIKE :q OR
                u.email LIKE :q
            ) ";
            $params[':q'] = "%{$q}%";
        }

        $offset = ($page - 1) * $size;

        $sqlCount = "
            SELECT COUNT(*)
            FROM recarga_saldo r
            INNER JOIN usuario u ON u.codigo_usuario = r.codigo_usuario
            {$where}
        ";
        $stc = $this->dblink->prepare($sqlCount);
        foreach ($params as $k => $v) $stc->bindValue($k, $v);
        $stc->execute();
        $total = (int)$stc->fetchColumn();

        $sqlPend = "SELECT COUNT(*) FROM recarga_saldo WHERE estado='pendiente'";
        $pendientes = (int)$this->dblink->query($sqlPend)->fetchColumn();

        $sql = "
            SELECT
                r.codigo_recarga AS id,
                DATE_FORMAT(r.fecha_creacion, '%d/%m/%Y') AS fecha,
                DATE_FORMAT(r.fecha_creacion, '%h:%i %p') AS hora,

                u.nombre AS usuario_nombre,
                u.documento AS dni,

                r.monto,
                r.metodo,
                r.id_operacion,
                r.estado,
                r.comprobante_path,
                r.comentario_soporte,
                r.reenviada_usuario,

                r.codigo_usuario,
                r.codigo_soporte,
                r.fecha_revision,
                r.fecha_creacion

            FROM recarga_saldo r
            INNER JOIN usuario u ON u.codigo_usuario = r.codigo_usuario
            {$where}
            ORDER BY r.fecha_creacion DESC, r.codigo_recarga DESC
            LIMIT :lim OFFSET :off
        ";
        $st = $this->dblink->prepare($sql);
        foreach ($params as $k => $v) $st->bindValue($k, $v);
        $st->bindValue(':lim', $size, PDO::PARAM_INT);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();
        $items = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'ok' => true,
            'data' => [
                'items' => $items,
                'total' => $total,
                'page' => $page,
                'size' => $size,
                'pendientes' => $pendientes,
            ]
        ];
    }

    public function actualizarEstado(int $codigoRecarga, string $nuevoEstado, ?string $comentario, int $codigoSoporte): array
    {
        if ($codigoRecarga <= 0) {
            return ['ok' => false, 'error' => 'VALIDATION', 'mensaje' => 'Código de recarga inválido'];
        }

        $nuevoEstado = trim(strtolower($nuevoEstado));
        if (!in_array($nuevoEstado, self::ESTADOS_VALIDOS, true)) {
            return ['ok' => false, 'error' => 'VALIDATION', 'mensaje' => 'Estado inválido'];
        }

        if ($codigoSoporte <= 0) {
            return ['ok' => false, 'error' => 'UNAUTHORIZED', 'mensaje' => 'Sesión inválida'];
        }

        if (($nuevoEstado === 'observada' || $nuevoEstado === 'rechazada')) {
            if ($comentario === null || mb_strlen(trim($comentario)) < 3) {
                return ['ok' => false, 'error' => 'VALIDATION', 'mensaje' => 'Debes ingresar un comentario (mín. 3 caracteres).'];
            }
        }

        try {
            $this->dblink->beginTransaction();

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

            $reenviadaUsuario = ($nuevoEstado === 'observada' || $nuevoEstado === 'rechazada') ? 0 : null;

            if ($reenviadaUsuario === 0) {
                $updR = $this->dblink->prepare("
                    UPDATE recarga_saldo
                    SET
                        estado = :estado,
                        comentario_soporte = :comentario,
                        codigo_soporte = :soporte,
                        fecha_revision = NOW(),
                        reenviada_usuario = 0
                    WHERE codigo_recarga = :id
                ");
            } else {
                $updR = $this->dblink->prepare("
                    UPDATE recarga_saldo
                    SET
                        estado = :estado,
                        comentario_soporte = :comentario,
                        codigo_soporte = :soporte,
                        fecha_revision = NOW()
                    WHERE codigo_recarga = :id
                ");
            }

            $updR->execute([
                ':estado'     => $nuevoEstado,
                ':comentario' => ($comentario !== null && trim($comentario) !== '' ? $comentario : null),
                ':soporte'    => $codigoSoporte,
                ':id'         => $codigoRecarga
            ]);

            $this->dblink->commit();

            $out = $this->dblink->prepare("
                SELECT
                    r.codigo_recarga AS id,
                    r.codigo_usuario,
                    u.nombre AS usuario_nombre,
                    u.email  AS usuario_email,
                    u.documento AS dni,
                    r.monto,
                    r.metodo,
                    r.id_operacion,
                    r.comprobante_path,
                    r.estado,
                    r.comentario_soporte,
                    r.codigo_soporte,
                    r.fecha_revision,
                    r.fecha_creacion,
                    r.reenviada_usuario,
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