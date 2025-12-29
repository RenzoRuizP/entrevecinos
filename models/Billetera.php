<?php
// models/Billetera.php

require_once __DIR__ . '/../Config/EnvConfig.php';
require_once __DIR__ . '/../database/Conexion.php';

class Billetera extends Conexion
{
    /**
     * Obtiene la billetera del usuario. Si no existe, la crea con saldo 0.
     */
    public function obtenerOBilleteraPorUsuario(int $codigoUsuario): array
    {
        // 1) Buscar billetera existente
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

        // 2) Si no existe, crearla
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

    /**
     * Devuelve el saldo actual de la billetera del usuario.
     */
    public function obtenerSaldoActual(int $codigoUsuario): float
    {
        $billetera = $this->obtenerOBilleteraPorUsuario($codigoUsuario);
        return (float)($billetera['saldo_actual'] ?? 0);
    }

    /**
     * Lista los movimientos de la billetera del usuario ordenados por fecha desc.
     */
    public function listarMovimientos(int $codigoUsuario): array
    {
        $sql = "
            SELECT
                m.codigo_movimiento,
                m.codigo_billetera,
                m.tipo_movimiento,         -- 'C' (crédito) / 'D' (débito)
                m.monto,
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

    /**
     * Debita un monto por destacar una publicación.
     * Retorna:
     *  - ['ok' => true, 'saldo_actual' => float]  si todo OK
     *  - ['ok' => false, 'codigo' => 'SALDO_INSUFICIENTE', 'mensaje' => ...]
     */
    public function debitarPorPublicacionDestacada(
        int $codigoUsuario,
        int $codigoPublicacion,
        float $monto = 1.00
    ): array {
        try {
            $this->dblink->beginTransaction();

            // Bloquear la billetera del usuario
            $sql = "
                SELECT
                    b.codigo_billetera,
                    b.saldo_actual
                FROM billetera b
                WHERE b.codigo_usuario = :codigo_usuario
                FOR UPDATE
            ";
            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
            $stmt->execute();
            $billetera = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$billetera) {
                // Crear billetera en cero si no existe
                $sqlInsert = "
                    INSERT INTO billetera (codigo_usuario, saldo_actual)
                    VALUES (:codigo_usuario, 0.00)
                ";
                $stmtInsert = $this->dblink->prepare($sqlInsert);
                $stmtInsert->bindParam(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
                $stmtInsert->execute();

                $codigoBilletera = (int)$this->dblink->lastInsertId();
                $saldoActual = 0.00;
            } else {
                $codigoBilletera = (int)$billetera['codigo_billetera'];
                $saldoActual     = (float)$billetera['saldo_actual'];
            }

            if ($saldoActual < $monto) {
                $this->dblink->rollBack();
                return [
                    'ok'      => false,
                    'codigo'  => 'SALDO_INSUFICIENTE',
                    'mensaje' => 'Tu billetera no tiene saldo suficiente.'
                ];
            }

            $nuevoSaldo = $saldoActual - $monto;

            // Insertar movimiento (débito)
            $sqlMov = "
                INSERT INTO billetera_movimiento
                    (codigo_billetera,
                     tipo_movimiento,
                     monto,
                     saldo_despues,
                     descripcion,
                     origen,
                     codigo_referencia)
                VALUES
                    (:codigo_billetera,
                     'D',
                     :monto,
                     :saldo_despues,
                     :descripcion,
                     :origen,
                     :codigo_referencia)
            ";
            $desc = 'Destacar publicación';
            $origen = 'PUBLICACION_DESTACADA';

            $stmtMov = $this->dblink->prepare($sqlMov);
            $stmtMov->bindParam(':codigo_billetera',   $codigoBilletera,   PDO::PARAM_INT);
            $stmtMov->bindParam(':monto',              $monto);
            $stmtMov->bindParam(':saldo_despues',      $nuevoSaldo);
            $stmtMov->bindParam(':descripcion',        $desc,              PDO::PARAM_STR);
            $stmtMov->bindParam(':origen',             $origen,            PDO::PARAM_STR);
            $stmtMov->bindParam(':codigo_referencia',  $codigoPublicacion, PDO::PARAM_INT);
            $stmtMov->execute();

            // Actualizar saldo de billetera
            $sqlUpd = "
                UPDATE billetera
                SET saldo_actual = :saldo_actual
                WHERE codigo_billetera = :codigo_billetera
            ";
            $stmtUpd = $this->dblink->prepare($sqlUpd);
            $stmtUpd->bindParam(':saldo_actual',     $nuevoSaldo);
            $stmtUpd->bindParam(':codigo_billetera', $codigoBilletera, PDO::PARAM_INT);
            $stmtUpd->execute();

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

    /**
     * NUEVO: Debita un monto por destacar un PRODUCTO.
     * Retorna:
     *  - ['ok' => true, 'saldo_actual' => float]  si todo OK
     *  - ['ok' => false, 'codigo' => 'SALDO_INSUFICIENTE', 'mensaje' => ...]
     */
    public function debitarPorProductoDestacado(
        int $codigoUsuario,
        int $codigoProducto,
        float $monto = 1.00
    ): array {
        try {
            $this->dblink->beginTransaction();

            // Bloquear billetera del usuario
            $sql = "
                SELECT
                    b.codigo_billetera,
                    b.saldo_actual
                FROM billetera b
                WHERE b.codigo_usuario = :codigo_usuario
                FOR UPDATE
            ";
            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
            $stmt->execute();
            $billetera = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$billetera) {
                // Crear billetera en cero si no existe
                $sqlInsert = "
                    INSERT INTO billetera (codigo_usuario, saldo_actual)
                    VALUES (:codigo_usuario, 0.00)
                ";
                $stmtInsert = $this->dblink->prepare($sqlInsert);
                $stmtInsert->bindParam(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
                $stmtInsert->execute();

                $codigoBilletera = (int)$this->dblink->lastInsertId();
                $saldoActual = 0.00;
            } else {
                $codigoBilletera = (int)$billetera['codigo_billetera'];
                $saldoActual     = (float)$billetera['saldo_actual'];
            }

            if ($saldoActual < $monto) {
                $this->dblink->rollBack();
                return [
                    'ok'      => false,
                    'codigo'  => 'SALDO_INSUFICIENTE',
                    'mensaje' => 'Tu billetera no tiene saldo suficiente.'
                ];
            }

            $nuevoSaldo = $saldoActual - $monto;

            // Insertar movimiento (débito)
            $sqlMov = "
                INSERT INTO billetera_movimiento
                    (codigo_billetera,
                     tipo_movimiento,
                     monto,
                     saldo_despues,
                     descripcion,
                     origen,
                     codigo_referencia)
                VALUES
                    (:codigo_billetera,
                     'D',
                     :monto,
                     :saldo_despues,
                     :descripcion,
                     :origen,
                     :codigo_referencia)
            ";
            $desc   = 'Destacar producto';
            $origen = 'PRODUCTO_DESTACADO';

            $stmtMov = $this->dblink->prepare($sqlMov);
            $stmtMov->bindParam(':codigo_billetera',  $codigoBilletera, PDO::PARAM_INT);
            $stmtMov->bindParam(':monto',             $monto);
            $stmtMov->bindParam(':saldo_despues',     $nuevoSaldo);
            $stmtMov->bindParam(':descripcion',       $desc, PDO::PARAM_STR);
            $stmtMov->bindParam(':origen',            $origen, PDO::PARAM_STR);
            $stmtMov->bindParam(':codigo_referencia', $codigoProducto, PDO::PARAM_INT);
            $stmtMov->execute();

            // Actualizar saldo de billetera
            $sqlUpd = "
                UPDATE billetera
                SET saldo_actual = :saldo_actual
                WHERE codigo_billetera = :codigo_billetera
            ";
            $stmtUpd = $this->dblink->prepare($sqlUpd);
            $stmtUpd->bindParam(':saldo_actual',     $nuevoSaldo);
            $stmtUpd->bindParam(':codigo_billetera', $codigoBilletera, PDO::PARAM_INT);
            $stmtUpd->execute();

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

    /**
     * Acredita saldo por recarga (crédito) de forma transaccional.
     * - Bloquea billetera (FOR UPDATE)
     * - Inserta movimiento con saldo_antes/saldo_despues
     * - Actualiza saldo billetera
     *
     * $codigoReferencia: normalmente codigo_recarga
    */
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

            // Bloquear billetera del usuario
            $sql = "
                SELECT b.codigo_billetera, b.saldo_actual
                FROM billetera b
                WHERE b.codigo_usuario = :codigo_usuario
                FOR UPDATE
            ";
            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
            $stmt->execute();
            $billetera = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$billetera) {
                // Crear billetera si no existe
                $sqlInsert = "INSERT INTO billetera (codigo_usuario, saldo_actual) VALUES (:codigo_usuario, 0.00)";
                $stmtInsert = $this->dblink->prepare($sqlInsert);
                $stmtInsert->bindParam(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
                $stmtInsert->execute();
                $codigoBilletera = (int)$this->dblink->lastInsertId();
                $saldoAntes = 0.00;
            } else {
                $codigoBilletera = (int)$billetera['codigo_billetera'];
                $saldoAntes = (float)$billetera['saldo_actual'];
            }

            $saldoDespues = $saldoAntes + $monto;

            // Insert movimiento (crédito)
            $sqlMov = "
                INSERT INTO billetera_movimiento
                    (codigo_billetera, tipo_movimiento, monto, saldo_antes, saldo_despues,
                    descripcion, origen, codigo_referencia, es_promocional, fecha_expira)
                VALUES
                    (:codigo_billetera, 'C', :monto, :saldo_antes, :saldo_despues,
                    :descripcion, :origen, :codigo_referencia, :es_promocional, :fecha_expira)
            ";
            $descripcion = "Recarga manual ({$metodo})";
            $origen = "RECARGA_MANUAL";

            $stmtMov = $this->dblink->prepare($sqlMov);
            $stmtMov->bindParam(':codigo_billetera', $codigoBilletera, PDO::PARAM_INT);
            $stmtMov->bindParam(':monto', $monto);
            $stmtMov->bindParam(':saldo_antes', $saldoAntes);
            $stmtMov->bindParam(':saldo_despues', $saldoDespues);
            $stmtMov->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $stmtMov->bindParam(':origen', $origen, PDO::PARAM_STR);
            $stmtMov->bindParam(':codigo_referencia', $codigoReferencia, PDO::PARAM_INT);
            $stmtMov->bindValue(':es_promocional', $esPromocional ? 1 : 0, PDO::PARAM_INT);
            $stmtMov->bindValue(':fecha_expira', $fechaExpira, PDO::PARAM_STR);
            $stmtMov->execute();

            // Actualizar saldo billetera
            $sqlUpd = "UPDATE billetera SET saldo_actual = :saldo_actual WHERE codigo_billetera = :codigo_billetera";
            $stmtUpd = $this->dblink->prepare($sqlUpd);
            $stmtUpd->bindParam(':saldo_actual', $saldoDespues);
            $stmtUpd->bindParam(':codigo_billetera', $codigoBilletera, PDO::PARAM_INT);
            $stmtUpd->execute();

            $this->dblink->commit();

            return ['ok' => true, 'saldo_actual' => $saldoDespues];

        } catch (Exception $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            throw $e;
        }
    }

    // =========================================================
    // NUEVO: Blindaje anti doble acreditación (NO elimina nada)
    // =========================================================
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
}
