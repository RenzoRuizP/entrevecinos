<?php
// models/SoporteRecargas.php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';
require_once __DIR__ . '/Notificacion.php';

final class SoporteRecargas extends Conexion
{
    private const ESTADOS_VALIDOS = ['pendiente', 'observada', 'aprobada', 'rechazada'];

    private function obtenerOBilleteraBloqueada(int $codigoUsuario): array
    {
        $stB = $this->dblink->prepare("
            SELECT codigo_billetera, codigo_usuario, saldo_actual
            FROM billetera
            WHERE codigo_usuario = :u
            FOR UPDATE
        ");
        $stB->execute([':u' => $codigoUsuario]);
        $bil = $stB->fetch(PDO::FETCH_ASSOC);

        if ($bil) {
            return [
                'codigo_billetera' => (int)$bil['codigo_billetera'],
                'codigo_usuario'   => (int)$bil['codigo_usuario'],
                'saldo_actual'     => (float)$bil['saldo_actual'],
            ];
        }

        $insB = $this->dblink->prepare("
            INSERT INTO billetera (codigo_usuario, saldo_actual, estado)
            VALUES (:u, 0.00, 1)
        ");
        $insB->execute([':u' => $codigoUsuario]);

        $stB->execute([':u' => $codigoUsuario]);
        $bil = $stB->fetch(PDO::FETCH_ASSOC);

        return [
            'codigo_billetera' => (int)($bil['codigo_billetera'] ?? 0),
            'codigo_usuario'   => (int)($bil['codigo_usuario'] ?? $codigoUsuario),
            'saldo_actual'     => (float)($bil['saldo_actual'] ?? 0),
        ];
    }

    private function contarMovimientosRecarga(int $codigoBilletera, string $origen, int $codigoRecarga): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM billetera_movimiento
            WHERE codigo_billetera = :b
              AND origen = :o
              AND codigo_referencia = :r
        ";
        $st = $this->dblink->prepare($sql);
        $st->execute([
            ':b' => $codigoBilletera,
            ':o' => $origen,
            ':r' => $codigoRecarga
        ]);
        return (int)$st->fetchColumn();
    }

    private function registrarMovimiento(
        int $codigoBilletera,
        string $tipoMovimiento,
        float $monto,
        float $saldoAntes,
        float $saldoDespues,
        string $descripcion,
        string $origen,
        ?int $codigoReferencia = null
    ): void {
        $sql = "
            INSERT INTO billetera_movimiento
            (
                codigo_billetera,
                tipo_movimiento,
                monto,
                saldo_antes,
                saldo_despues,
                descripcion,
                origen,
                codigo_referencia,
                es_promocional,
                fecha_expira
            )
            VALUES
            (
                :codigo_billetera,
                :tipo_movimiento,
                :monto,
                :saldo_antes,
                :saldo_despues,
                :descripcion,
                :origen,
                :codigo_referencia,
                0,
                NULL
            )
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_billetera', $codigoBilletera, PDO::PARAM_INT);
        $st->bindValue(':tipo_movimiento', $tipoMovimiento, PDO::PARAM_STR);
        $st->bindValue(':monto', $monto);
        $st->bindValue(':saldo_antes', $saldoAntes);
        $st->bindValue(':saldo_despues', $saldoDespues);
        $st->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
        $st->bindValue(':origen', $origen, PDO::PARAM_STR);

        if ($codigoReferencia !== null) {
            $st->bindValue(':codigo_referencia', $codigoReferencia, PDO::PARAM_INT);
        } else {
            $st->bindValue(':codigo_referencia', null, PDO::PARAM_NULL);
        }

        $st->execute();
    }

    private function actualizarSaldoBilletera(int $codigoBilletera, float $nuevoSaldo): void
    {
        $updB = $this->dblink->prepare("
            UPDATE billetera
            SET saldo_actual = :s
            WHERE codigo_billetera = :b
        ");
        $updB->execute([
            ':s' => number_format($nuevoSaldo, 2, '.', ''),
            ':b' => $codigoBilletera
        ]);
    }

    /**
     * Deja la billetera en el estado correcto según si la recarga debe estar aprobada o no.
     * Usa historial de movimientos para no duplicar créditos/débitos.
     */
    private function sincronizarBilleteraPorRecarga(
        int $codigoBilletera,
        float $saldoActual,
        int $codigoRecarga,
        float $monto,
        string $metodo,
        bool $debeEstarAprobada
    ): array {
        $creditsOriginal = $this->contarMovimientosRecarga($codigoBilletera, 'RECARGA_MANUAL', $codigoRecarga);
        $creditsReaplica = $this->contarMovimientosRecarga($codigoBilletera, 'RECARGA_MANUAL_REACTIVADA', $codigoRecarga);
        $debitsReversa   = $this->contarMovimientosRecarga($codigoBilletera, 'RECARGA_MANUAL_REVERSA', $codigoRecarga);

        $creditosTotales = $creditsOriginal + $creditsReaplica;
        $netoAplicado    = ($creditosTotales > $debitsReversa);

        $saldoNuevo = $saldoActual;

        if ($debeEstarAprobada) {
            if (!$netoAplicado) {
                $origen = ($creditosTotales === 0) ? 'RECARGA_MANUAL' : 'RECARGA_MANUAL_REACTIVADA';
                $descripcion = ($origen === 'RECARGA_MANUAL')
                    ? "Recarga manual (" . strtoupper($metodo) . ")"
                    : "Reactivación de recarga (" . strtoupper($metodo) . ")";

                $saldoNuevo = round($saldoActual + $monto, 2);

                $this->registrarMovimiento(
                    $codigoBilletera,
                    'C',
                    $monto,
                    $saldoActual,
                    $saldoNuevo,
                    $descripcion,
                    $origen,
                    $codigoRecarga
                );

                $this->actualizarSaldoBilletera($codigoBilletera, $saldoNuevo);
            }

            return [
                'ok' => true,
                'saldo_nuevo' => $saldoNuevo
            ];
        }

        if ($netoAplicado) {
            $saldoNuevo = round($saldoActual - $monto, 2);

            if ($saldoNuevo < -0.00001) {
                return [
                    'ok' => false,
                    'error' => 'WALLET_INCONSISTENT',
                    'mensaje' => 'No se puede revertir: saldo insuficiente para restar la recarga'
                ];
            }

            $this->registrarMovimiento(
                $codigoBilletera,
                'D',
                $monto,
                $saldoActual,
                $saldoNuevo,
                'Reversa de recarga manual',
                'RECARGA_MANUAL_REVERSA',
                $codigoRecarga
            );

            $this->actualizarSaldoBilletera($codigoBilletera, $saldoNuevo);
        }

        return [
            'ok' => true,
            'saldo_nuevo' => $saldoNuevo
        ];
    }

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
                SELECT codigo_recarga, codigo_usuario, monto, metodo, estado
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
            $metodo        = (string)$rec['metodo'];
            $estadoActual  = (string)$rec['estado'];

            $bil = $this->obtenerOBilleteraBloqueada($codigoUsuario);
            $codigoBilletera = (int)$bil['codigo_billetera'];
            $saldoActual = (float)$bil['saldo_actual'];

            $sync = $this->sincronizarBilleteraPorRecarga(
                $codigoBilletera,
                $saldoActual,
                $codigoRecarga,
                $monto,
                $metodo,
                $nuevoEstado === 'aprobada'
            );

            if (!($sync['ok'] ?? false)) {
                $this->dblink->rollBack();
                return $sync;
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

            if ($estadoActual !== $nuevoEstado && in_array($nuevoEstado, ['observada', 'aprobada', 'rechazada'], true)) {
                try {
                    $titulos = [
                        'observada' => 'Tu recarga fue observada',
                        'aprobada' => 'Tu recarga fue aprobada',
                        'rechazada' => 'Tu recarga fue rechazada',
                    ];
                    $mensajes = [
                        'observada' => trim((string)$comentario) !== ''
                            ? trim((string)$comentario)
                            : 'Revisa el detalle de tu recarga y vuelve a enviar la información solicitada.',
                        'aprobada' => 'Se acreditaron S/ ' . number_format($monto, 2) . ' en tu billetera EV.',
                        'rechazada' => trim((string)$comentario) !== ''
                            ? trim((string)$comentario)
                            : 'La recarga no pudo ser validada por soporte.',
                    ];

                    $notif = new Notificacion($this->dblink);
                    $notif->crearOActualizarNoLeida([
                        'codigo_usuario' => $codigoUsuario,
                        'categoria' => Notificacion::CAT_BILLETERA,
                        'subcategoria' => 'recarga_' . $nuevoEstado,
                        'referencia_id' => $codigoRecarga,
                        'titulo' => $titulos[$nuevoEstado],
                        'mensaje' => $mensajes[$nuevoEstado],
                        'payload' => [
                            'codigo_recarga' => $codigoRecarga,
                            'monto' => $monto,
                            'metodo' => $metodo,
                            'estado' => $nuevoEstado,
                            'comentario_soporte' => trim((string)$comentario),
                            'saldo_actualizado' => (float)($sync['saldo_nuevo'] ?? $saldoActual),
                            'ruta' => '/billetera',
                        ],
                    ]);
                } catch (Throwable $eNotif) {
                    error_log('[EV][SoporteRecargas::actualizarEstado][notificacion] ' . $eNotif->getMessage());
                }
            }

            if ($estadoActual !== $nuevoEstado && in_array($nuevoEstado, ['observada', 'aprobada', 'rechazada'], true)) {
                try {
                    $notifEquipo = new Notificacion($this->dblink);
                    $notifEquipo->marcarLeidasPorReferenciaRoles(
                        [1, 3],
                        Notificacion::CAT_BILLETERA,
                        $codigoRecarga,
                        'recarga_pendiente_soporte'
                    );
                } catch (Throwable $eNotifEquipo) {
                    error_log('[EV][SoporteRecargas::actualizarEstado][resolver_notificacion_soporte] ' . $eNotifEquipo->getMessage());
                }
            }

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
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][SoporteRecargas::actualizarEstado] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'SERVER_ERROR', 'mensaje' => 'Error interno al actualizar estado'];
        }
    }
}