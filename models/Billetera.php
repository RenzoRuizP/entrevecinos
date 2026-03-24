<?php
// models/Billetera.php

require_once __DIR__ . '/../Config/EnvConfig.php';
require_once __DIR__ . '/../database/Conexion.php';

class Billetera extends Conexion
{
    private function obtenerOBilleteraBloqueada(int $codigoUsuario): array
    {
        $sql = "
            SELECT
                b.codigo_billetera,
                b.codigo_usuario,
                b.saldo_actual
            FROM billetera b
            WHERE b.codigo_usuario = :codigo_usuario
            FOR UPDATE
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return [
                'codigo_billetera' => (int)$row['codigo_billetera'],
                'codigo_usuario'   => (int)$row['codigo_usuario'],
                'saldo_actual'     => (float)$row['saldo_actual']
            ];
        }

        $sqlInsert = "
            INSERT INTO billetera (codigo_usuario, saldo_actual)
            VALUES (:codigo_usuario, 0.00)
        ";
        $stmtInsert = $this->dblink->prepare($sqlInsert);
        $stmtInsert->bindParam(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmtInsert->execute();

        return [
            'codigo_billetera' => (int)$this->dblink->lastInsertId(),
            'codigo_usuario'   => $codigoUsuario,
            'saldo_actual'     => 0.00
        ];
    }

    private function registrarMovimiento(
        int $codigoBilletera,
        string $tipoMovimiento,
        float $monto,
        float $saldoAntes,
        float $saldoDespues,
        string $descripcion,
        string $origen,
        ?int $codigoReferencia = null,
        int $esPromocional = 0,
        ?string $fechaExpira = null
    ): void {
        $sqlMov = "
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
                :es_promocional,
                :fecha_expira
            )
        ";

        $stmtMov = $this->dblink->prepare($sqlMov);
        $stmtMov->bindParam(':codigo_billetera', $codigoBilletera, PDO::PARAM_INT);
        $stmtMov->bindParam(':tipo_movimiento', $tipoMovimiento, PDO::PARAM_STR);
        $stmtMov->bindParam(':monto', $monto);
        $stmtMov->bindParam(':saldo_antes', $saldoAntes);
        $stmtMov->bindParam(':saldo_despues', $saldoDespues);
        $stmtMov->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmtMov->bindParam(':origen', $origen, PDO::PARAM_STR);

        if ($codigoReferencia !== null) {
            $stmtMov->bindParam(':codigo_referencia', $codigoReferencia, PDO::PARAM_INT);
        } else {
            $stmtMov->bindValue(':codigo_referencia', null, PDO::PARAM_NULL);
        }

        $stmtMov->bindValue(':es_promocional', $esPromocional, PDO::PARAM_INT);
        $stmtMov->bindValue(':fecha_expira', $fechaExpira, $fechaExpira !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmtMov->execute();
    }

    private function actualizarSaldoBilletera(int $codigoBilletera, float $nuevoSaldo): void
    {
        $sqlUpd = "
            UPDATE billetera
            SET saldo_actual = :saldo_actual
            WHERE codigo_billetera = :codigo_billetera
        ";
        $stmtUpd = $this->dblink->prepare($sqlUpd);
        $stmtUpd->bindParam(':saldo_actual', $nuevoSaldo);
        $stmtUpd->bindParam(':codigo_billetera', $codigoBilletera, PDO::PARAM_INT);
        $stmtUpd->execute();
    }

    /**
     * Obtiene la billetera del usuario. Si no existe, la crea con saldo 0.
     */
    public function obtenerOBilleteraPorUsuario(int $codigoUsuario): array
    {
        $sql = "
            SELECT
                b.codigo_billetera,
                b.codigo_usuario,
                b.saldo_actual
            FROM billetera b
            WHERE b.codigo_usuario = :codigo_usuario
            LIMIT 1
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row;
        }

        $sqlInsert = "
            INSERT INTO billetera (codigo_usuario, saldo_actual)
            VALUES (:codigo_usuario, 0.00)
        ";
        $stmtInsert = $this->dblink->prepare($sqlInsert);
        $stmtInsert->bindParam(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmtInsert->execute();

        $codigoBilletera = (int)$this->dblink->lastInsertId();

        return [
            'codigo_billetera' => $codigoBilletera,
            'codigo_usuario'   => $codigoUsuario,
            'saldo_actual'     => 0.00
        ];
    }

    public function obtenerSaldoActual(int $codigoUsuario): float
    {
        $billetera = $this->obtenerOBilleteraPorUsuario($codigoUsuario);
        return (float)($billetera['saldo_actual'] ?? 0);
    }

    public function listarMovimientos(int $codigoUsuario): array
    {
        $sql = "
            SELECT
                m.codigo_movimiento,
                m.codigo_billetera,
                m.tipo_movimiento,
                m.monto,
                m.saldo_antes,
                m.saldo_despues,
                m.descripcion,
                m.origen,
                m.codigo_referencia,
                DATE_FORMAT(m.fecha_movimiento, '%d/%m/%Y %H:%i') AS fecha
            FROM billetera_movimiento m
            INNER JOIN billetera b
                ON b.codigo_billetera = m.codigo_billetera
            WHERE b.codigo_usuario = :codigo_usuario
            ORDER BY m.fecha_movimiento DESC, m.codigo_movimiento DESC
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows ?: [];
    }

    public function debitarPorPublicacionDestacada(
        int $codigoUsuario,
        int $codigoPublicacion,
        float $monto = 1.00
    ): array {
        try {
            $this->dblink->beginTransaction();

            $billetera = $this->obtenerOBilleteraBloqueada($codigoUsuario);
            $codigoBilletera = (int)$billetera['codigo_billetera'];
            $saldoActual = (float)$billetera['saldo_actual'];

            if ($saldoActual < $monto) {
                $this->dblink->rollBack();
                return [
                    'ok'      => false,
                    'codigo'  => 'SALDO_INSUFICIENTE',
                    'mensaje' => 'Tu billetera no tiene saldo suficiente.'
                ];
            }

            $nuevoSaldo = round($saldoActual - $monto, 2);

            $this->registrarMovimiento(
                $codigoBilletera,
                'D',
                $monto,
                $saldoActual,
                $nuevoSaldo,
                'Destacar publicación',
                'PUBLICACION_DESTACADA',
                $codigoPublicacion
            );

            $this->actualizarSaldoBilletera($codigoBilletera, $nuevoSaldo);

            $this->dblink->commit();

            return [
                'ok'           => true,
                'saldo_actual' => $nuevoSaldo
            ];

        } catch (Exception $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            throw $e;
        }
    }

    public function debitarPorProductoDestacado(
        int $codigoUsuario,
        int $codigoProducto,
        float $monto = 1.00
    ): array {
        try {
            $this->dblink->beginTransaction();

            $billetera = $this->obtenerOBilleteraBloqueada($codigoUsuario);
            $codigoBilletera = (int)$billetera['codigo_billetera'];
            $saldoActual = (float)$billetera['saldo_actual'];

            if ($saldoActual < $monto) {
                $this->dblink->rollBack();
                return [
                    'ok'      => false,
                    'codigo'  => 'SALDO_INSUFICIENTE',
                    'mensaje' => 'Tu billetera no tiene saldo suficiente.'
                ];
            }

            $nuevoSaldo = round($saldoActual - $monto, 2);

            $this->registrarMovimiento(
                $codigoBilletera,
                'D',
                $monto,
                $saldoActual,
                $nuevoSaldo,
                'Destacar producto',
                'PRODUCTO_DESTACADO',
                $codigoProducto
            );

            $this->actualizarSaldoBilletera($codigoBilletera, $nuevoSaldo);

            $this->dblink->commit();

            return [
                'ok'           => true,
                'saldo_actual' => $nuevoSaldo
            ];

        } catch (Exception $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            throw $e;
        }
    }

    public function acreditarPorRecargaManual(
        int $codigoUsuario,
        float $monto,
        int $codigoReferencia,
        string $metodo = 'YAPE',
        bool $esPromocional = false,
        ?string $fechaExpira = null
    ): array {
        try {
            if ($monto <= 0) {
                return ['ok' => false, 'mensaje' => 'Monto inválido.'];
            }

            $this->dblink->beginTransaction();

            $billetera = $this->obtenerOBilleteraBloqueada($codigoUsuario);
            $codigoBilletera = (int)$billetera['codigo_billetera'];
            $saldoAntes = (float)$billetera['saldo_actual'];
            $saldoDespues = round($saldoAntes + $monto, 2);

            $descripcion = "Recarga manual ({$metodo})";
            $origen = "RECARGA_MANUAL";

            $this->registrarMovimiento(
                $codigoBilletera,
                'C',
                $monto,
                $saldoAntes,
                $saldoDespues,
                $descripcion,
                $origen,
                $codigoReferencia,
                $esPromocional ? 1 : 0,
                $fechaExpira
            );

            $this->actualizarSaldoBilletera($codigoBilletera, $saldoDespues);

            $this->dblink->commit();

            return ['ok' => true, 'saldo_actual' => $saldoDespues];

        } catch (Exception $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            throw $e;
        }
    }

    public function yaFueAcreditadaRecarga(int $codigoUsuario, int $codigoRecarga): bool
    {
        $b = $this->obtenerOBilleteraPorUsuario($codigoUsuario);
        $codigoBilletera = (int)($b['codigo_billetera'] ?? 0);
        if ($codigoBilletera <= 0) return false;

        $sql = "
            SELECT 1
            FROM billetera_movimiento
            WHERE codigo_billetera = :codigo_billetera
              AND origen = 'RECARGA_MANUAL'
              AND codigo_referencia = :codigo_recarga
            LIMIT 1
        ";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':codigo_billetera', $codigoBilletera, PDO::PARAM_INT);
        $stmt->bindParam(':codigo_recarga', $codigoRecarga, PDO::PARAM_INT);
        $stmt->execute();

        return (bool)$stmt->fetchColumn();
    }

    public function aplicarBonoBienvenida(int $codigoUsuario, float $monto = 15.00): array
    {
        try {
            if ($codigoUsuario <= 0) {
                return ['ok' => false, 'mensaje' => 'Usuario inválido.'];
            }
            if ($monto <= 0) {
                return ['ok' => false, 'mensaje' => 'Monto inválido.'];
            }

            $this->dblink->beginTransaction();

            $billetera = $this->obtenerOBilleteraBloqueada($codigoUsuario);
            $codigoBilletera = (int)$billetera['codigo_billetera'];
            $saldoAntes = (float)$billetera['saldo_actual'];

            $sqlYa = "
                SELECT 1
                FROM billetera_movimiento
                WHERE codigo_billetera = :codigo_billetera
                  AND tipo_movimiento = 'C'
                  AND origen = 'BONO_BIENVENIDA'
                  AND es_promocional = 1
                  AND codigo_referencia = :codigo_usuario
                LIMIT 1
            ";
            $stmtYa = $this->dblink->prepare($sqlYa);
            $stmtYa->bindParam(':codigo_billetera', $codigoBilletera, PDO::PARAM_INT);
            $stmtYa->bindParam(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
            $stmtYa->execute();

            if ($stmtYa->fetchColumn()) {
                $this->dblink->commit();
                return [
                    'ok' => true,
                    'aplicado' => false,
                    'mensaje' => 'El bono de bienvenida ya estaba aplicado.'
                ];
            }

            $saldoDespues = round($saldoAntes + $monto, 2);
            $descripcion = 'Bono de bienvenida por aprobación de cuenta';
            $origen = 'BONO_BIENVENIDA';

            $this->registrarMovimiento(
                $codigoBilletera,
                'C',
                $monto,
                $saldoAntes,
                $saldoDespues,
                $descripcion,
                $origen,
                $codigoUsuario,
                1,
                null
            );

            $this->actualizarSaldoBilletera($codigoBilletera, $saldoDespues);

            $this->dblink->commit();

            return [
                'ok' => true,
                'aplicado' => true,
                'saldo_actual' => $saldoDespues,
                'mensaje' => 'Bono de bienvenida aplicado.'
            ];
        } catch (Exception $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][Billetera::aplicarBonoBienvenida] ' . $e->getMessage());
            return [
                'ok' => false,
                'mensaje' => 'No se pudo aplicar el bono de bienvenida.',
                'error' => $e->getMessage()
            ];
        }
    }
}