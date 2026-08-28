<?php
// models/Billetera.php
// Versión corregida EV 2.0: compatible con columna concepto y saldo/saldo_actual.
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

class Billetera extends Conexion
{
    private function obtenerOBilleteraBloqueada(int $codigoUsuario): array
    {
        $sql = "
            SELECT codigo_billetera, codigo_usuario, saldo_actual
            FROM billetera
            WHERE codigo_usuario = :codigo_usuario
            FOR UPDATE
        ";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return [
                'codigo_billetera' => (int)$row['codigo_billetera'],
                'codigo_usuario'   => (int)$row['codigo_usuario'],
                'saldo_actual'     => (float)$row['saldo_actual']
            ];
        }

        $sqlInsert = "INSERT INTO billetera (codigo_usuario, saldo, saldo_actual, estado) VALUES (:codigo_usuario, 0.00, 0.00, 1)";
        $stmtInsert = $this->dblink->prepare($sqlInsert);
        $stmtInsert->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmtInsert->execute();

        return [
            'codigo_billetera' => (int)$this->dblink->lastInsertId(),
            'codigo_usuario'   => $codigoUsuario,
            'saldo_actual'     => 0.00
        ];
    }

    private function conceptoMovimiento(string $origen, string $descripcion): string
    {
        $concepto = trim($origen) !== '' ? trim($origen) : trim($descripcion);
        if ($concepto === '') $concepto = 'Movimiento de billetera';
        return mb_substr($concepto, 0, 150, 'UTF-8');
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
                codigo_billetera, tipo_movimiento, concepto, monto,
                saldo_antes, saldo_despues, saldo_anterior, saldo_posterior,
                descripcion, origen, codigo_referencia, referencia_tipo, referencia_id,
                es_promocional, fecha_expira
            )
            VALUES
            (
                :codigo_billetera, :tipo_movimiento, :concepto, :monto,
                :saldo_antes, :saldo_despues, :saldo_anterior, :saldo_posterior,
                :descripcion, :origen, :codigo_referencia, :referencia_tipo, :referencia_id,
                :es_promocional, :fecha_expira
            )
        ";

        $stmtMov = $this->dblink->prepare($sqlMov);
        $stmtMov->bindValue(':codigo_billetera', $codigoBilletera, PDO::PARAM_INT);
        $stmtMov->bindValue(':tipo_movimiento', $tipoMovimiento, PDO::PARAM_STR);
        $stmtMov->bindValue(':concepto', $this->conceptoMovimiento($origen, $descripcion), PDO::PARAM_STR);
        $stmtMov->bindValue(':monto', round($monto, 2));
        $stmtMov->bindValue(':saldo_antes', round($saldoAntes, 2));
        $stmtMov->bindValue(':saldo_despues', round($saldoDespues, 2));
        $stmtMov->bindValue(':saldo_anterior', round($saldoAntes, 2));
        $stmtMov->bindValue(':saldo_posterior', round($saldoDespues, 2));
        $stmtMov->bindValue(':descripcion', mb_substr($descripcion, 0, 180, 'UTF-8'), PDO::PARAM_STR);
        $stmtMov->bindValue(':origen', $origen, PDO::PARAM_STR);

        if ($codigoReferencia !== null) {
            $stmtMov->bindValue(':codigo_referencia', $codigoReferencia, PDO::PARAM_INT);
            $stmtMov->bindValue(':referencia_tipo', $origen, PDO::PARAM_STR);
            $stmtMov->bindValue(':referencia_id', $codigoReferencia, PDO::PARAM_INT);
        } else {
            $stmtMov->bindValue(':codigo_referencia', null, PDO::PARAM_NULL);
            $stmtMov->bindValue(':referencia_tipo', null, PDO::PARAM_NULL);
            $stmtMov->bindValue(':referencia_id', null, PDO::PARAM_NULL);
        }

        $stmtMov->bindValue(':es_promocional', $esPromocional, PDO::PARAM_INT);
        $stmtMov->bindValue(':fecha_expira', $fechaExpira, $fechaExpira !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmtMov->execute();
    }

    private function actualizarSaldoBilletera(int $codigoBilletera, float $nuevoSaldo): void
    {
        $sqlUpd = "UPDATE billetera SET saldo_actual = :saldo_actual, saldo = :saldo WHERE codigo_billetera = :codigo_billetera";
        $stmtUpd = $this->dblink->prepare($sqlUpd);
        $stmtUpd->bindValue(':saldo_actual', round($nuevoSaldo, 2));
        $stmtUpd->bindValue(':saldo', round($nuevoSaldo, 2));
        $stmtUpd->bindValue(':codigo_billetera', $codigoBilletera, PDO::PARAM_INT);
        $stmtUpd->execute();
    }

    private function existeMovimientoPorOrigenYReferencia(int $codigoBilletera, string $tipoMovimiento, string $origen, int $codigoReferencia): bool
    {
        $sql = "
            SELECT 1 FROM billetera_movimiento
            WHERE codigo_billetera = :codigo_billetera
              AND tipo_movimiento = :tipo_movimiento
              AND origen = :origen
              AND codigo_referencia = :codigo_referencia
            LIMIT 1
        ";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':codigo_billetera', $codigoBilletera, PDO::PARAM_INT);
        $stmt->bindValue(':tipo_movimiento', $tipoMovimiento, PDO::PARAM_STR);
        $stmt->bindValue(':origen', $origen, PDO::PARAM_STR);
        $stmt->bindValue(':codigo_referencia', $codigoReferencia, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    private function limpiarTituloMovimiento(string $titulo): string
    {
        $titulo = trim((string)preg_replace('/\s+/u', ' ', $titulo));
        if ($titulo === '') return 'producto';
        return mb_strlen($titulo, 'UTF-8') > 70 ? mb_substr($titulo, 0, 67, 'UTF-8') . '...' : $titulo;
    }

    private function descripcionDevolucionPedido(string $motivoClave, string $tituloProducto): string
    {
        return match ($motivoClave) {
            'cancelacion_comprador' => "Devolución por cancelación de solicitud: {$tituloProducto}",
            'rechazo_vendedor' => "Devolución por solicitud rechazada: {$tituloProducto}",
            'sin_respuesta_vendedor' => "Devolución por solicitud sin respuesta del vendedor: {$tituloProducto}",
            'cancelacion_vendedor' => "Devolución por cancelación del vendedor: {$tituloProducto}",
            default => "Devolución por solicitud no concretada: {$tituloProducto}",
        };
    }

    public function obtenerOBilleteraPorUsuario(int $codigoUsuario): array
    {
        $sql = "SELECT codigo_billetera, codigo_usuario, saldo_actual FROM billetera WHERE codigo_usuario = :codigo_usuario LIMIT 1";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return [
                'codigo_billetera' => (int)$row['codigo_billetera'],
                'codigo_usuario'   => (int)$row['codigo_usuario'],
                'saldo_actual'     => (float)$row['saldo_actual'],
            ];
        }

        $sqlInsert = "INSERT INTO billetera (codigo_usuario, saldo, saldo_actual, estado) VALUES (:codigo_usuario, 0.00, 0.00, 1)";
        $stmtInsert = $this->dblink->prepare($sqlInsert);
        $stmtInsert->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmtInsert->execute();

        return ['codigo_billetera' => (int)$this->dblink->lastInsertId(), 'codigo_usuario' => $codigoUsuario, 'saldo_actual' => 0.00];
    }

    public function obtenerSaldoActual(int $codigoUsuario): float
    {
        $billetera = $this->obtenerOBilleteraPorUsuario($codigoUsuario);
        return (float)($billetera['saldo_actual'] ?? 0);
    }

    public function listarMovimientos(int $codigoUsuario): array
    {
        $sql = "
            SELECT m.codigo_movimiento, m.codigo_billetera, m.tipo_movimiento, m.monto,
                   m.saldo_antes, m.saldo_despues, m.descripcion, m.origen, m.codigo_referencia,
                   DATE_FORMAT(m.fecha_movimiento, '%d/%m/%Y %H:%i') AS fecha
            FROM billetera_movimiento m
            INNER JOIN billetera b ON b.codigo_billetera = m.codigo_billetera
            WHERE b.codigo_usuario = :codigo_usuario
            ORDER BY m.fecha_movimiento DESC, m.codigo_movimiento DESC
        ";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function debitarPorPublicacionDestacada(int $codigoUsuario, int $codigoPublicacion, float $monto = 1.00): array
    { return $this->debitarSimple($codigoUsuario, $codigoPublicacion, $monto, 'Destacar publicación', 'PUBLICACION_DESTACADA'); }

    public function debitarPorProductoDestacado(int $codigoUsuario, int $codigoProducto, float $monto = 1.00): array
    { return $this->debitarSimple($codigoUsuario, $codigoProducto, $monto, 'Destacar producto', 'PRODUCTO_DESTACADO'); }

    private function debitarSimple(int $codigoUsuario, int $codigoReferencia, float $monto, string $descripcion, string $origen): array
    {
        try {
            $this->dblink->beginTransaction();
            $b = $this->obtenerOBilleteraBloqueada($codigoUsuario);
            $saldo = (float)$b['saldo_actual'];
            if ($saldo < $monto) { $this->dblink->rollBack(); return ['ok'=>false,'codigo'=>'SALDO_INSUFICIENTE','mensaje'=>'Tu billetera no tiene saldo suficiente.']; }
            $nuevo = round($saldo - $monto, 2);
            $this->registrarMovimiento((int)$b['codigo_billetera'], 'D', $monto, $saldo, $nuevo, $descripcion, $origen, $codigoReferencia);
            $this->actualizarSaldoBilletera((int)$b['codigo_billetera'], $nuevo);
            $this->dblink->commit();
            return ['ok'=>true,'saldo_actual'=>$nuevo];
        } catch (Exception $e) { if ($this->dblink->inTransaction()) $this->dblink->rollBack(); throw $e; }
    }

    public function debitarPorSolicitudPreparada(int $codigoUsuario, int $codigoPedido, float $monto, string $tituloProducto = ''): array
    {
        $abrioTx = false;
        try {
            if ($codigoUsuario <= 0 || $codigoPedido <= 0 || $monto <= 0) return ['ok'=>false,'codigo'=>'PARAMETROS_INVALIDOS','mensaje'=>'No se pudo procesar el débito de la solicitud.'];
            if (!$this->dblink->inTransaction()) { $this->dblink->beginTransaction(); $abrioTx = true; }
            $b = $this->obtenerOBilleteraBloqueada($codigoUsuario);
            $saldo = (float)$b['saldo_actual'];
            $codigoB = (int)$b['codigo_billetera'];
            if ($this->existeMovimientoPorOrigenYReferencia($codigoB, 'D', 'PEDIDO_SOLICITUD_PREPARADA', $codigoPedido)) {
                if ($abrioTx) $this->dblink->commit();
                return ['ok'=>true,'ya_aplicado'=>true,'saldo_actual'=>$saldo,'codigo_billetera'=>$codigoB];
            }
            if ($saldo < $monto) { if ($abrioTx) $this->dblink->rollBack(); return ['ok'=>false,'codigo'=>'SALDO_INSUFICIENTE_BILLETERA','mensaje'=>'Tu billetera no tiene saldo suficiente para enviar esta solicitud.','saldo_actual'=>$saldo,'monto_requerido'=>round($monto,2)]; }
            $nuevo = round($saldo - $monto, 2);
            $this->registrarMovimiento($codigoB, 'D', round($monto,2), $saldo, $nuevo, 'Débito por solicitud de producto: '.$this->limpiarTituloMovimiento($tituloProducto), 'PEDIDO_SOLICITUD_PREPARADA', $codigoPedido);
            $this->actualizarSaldoBilletera($codigoB, $nuevo);
            if ($abrioTx) $this->dblink->commit();
            return ['ok'=>true,'saldo_actual'=>$nuevo,'saldo_anterior'=>$saldo,'monto_debitado'=>round($monto,2),'codigo_billetera'=>$codigoB,'ya_aplicado'=>false];
        } catch (Exception $e) { if ($abrioTx && $this->dblink->inTransaction()) $this->dblink->rollBack(); throw $e; }
    }

    public function devolverPorSolicitudNoConcretada(int $codigoUsuario, int $codigoPedido, float $monto, string $tituloProducto = '', string $motivoClave = 'no_concretada'): array
    {
        $abrioTx = false;
        try {
            if ($codigoUsuario <= 0 || $codigoPedido <= 0 || $monto <= 0) return ['ok'=>false,'codigo'=>'PARAMETROS_INVALIDOS','mensaje'=>'No se pudo procesar la devolución.'];
            if (!$this->dblink->inTransaction()) { $this->dblink->beginTransaction(); $abrioTx = true; }
            $b = $this->obtenerOBilleteraBloqueada($codigoUsuario);
            $saldo = (float)$b['saldo_actual'];
            $codigoB = (int)$b['codigo_billetera'];
            if (
                $this->existeMovimientoPorOrigenYReferencia($codigoB, 'C', 'PEDIDO_SOLICITUD_DEVOLUCION', $codigoPedido)
                || $this->existeMovimientoPorOrigenYReferencia($codigoB, 'C', 'DEVOLUCION_PEDIDO_SOLICITUD', $codigoPedido)
            ) {
                if ($abrioTx) $this->dblink->commit();
                return ['ok'=>true,'ya_aplicado'=>true,'saldo_actual'=>$saldo,'codigo_billetera'=>$codigoB];
            }
            $nuevo = round($saldo + $monto, 2);
            $this->registrarMovimiento($codigoB, 'C', round($monto,2), $saldo, $nuevo, $this->descripcionDevolucionPedido($motivoClave, $this->limpiarTituloMovimiento($tituloProducto)), 'PEDIDO_SOLICITUD_DEVOLUCION', $codigoPedido);
            $this->actualizarSaldoBilletera($codigoB, $nuevo);
            if ($abrioTx) $this->dblink->commit();
            return ['ok'=>true,'saldo_actual'=>$nuevo,'saldo_anterior'=>$saldo,'monto_devuelto'=>round($monto,2),'codigo_billetera'=>$codigoB,'ya_aplicado'=>false];
        } catch (Exception $e) { if ($abrioTx && $this->dblink->inTransaction()) $this->dblink->rollBack(); throw $e; }
    }

    public function acreditarPorRecargaManual(int $codigoUsuario, float $monto, int $codigoReferencia, string $metodo = 'YAPE', bool $esPromocional = false, ?string $fechaExpira = null): array
    {
        try {
            if ($monto <= 0) return ['ok'=>false,'mensaje'=>'Monto inválido.'];
            $this->dblink->beginTransaction();
            $b = $this->obtenerOBilleteraBloqueada($codigoUsuario);
            $saldo = (float)$b['saldo_actual'];
            $nuevo = round($saldo + $monto, 2);
            $this->registrarMovimiento((int)$b['codigo_billetera'], 'C', $monto, $saldo, $nuevo, "Recarga manual ({$metodo})", 'RECARGA_MANUAL', $codigoReferencia, $esPromocional ? 1 : 0, $fechaExpira);
            $this->actualizarSaldoBilletera((int)$b['codigo_billetera'], $nuevo);
            $this->dblink->commit();
            return ['ok'=>true,'saldo_actual'=>$nuevo];
        } catch (Exception $e) { if ($this->dblink->inTransaction()) $this->dblink->rollBack(); throw $e; }
    }

    public function yaFueAcreditadaRecarga(int $codigoUsuario, int $codigoRecarga): bool
    {
        $b = $this->obtenerOBilleteraPorUsuario($codigoUsuario);
        $sql = "SELECT 1 FROM billetera_movimiento WHERE codigo_billetera = :b AND origen = 'RECARGA_MANUAL' AND codigo_referencia = :r LIMIT 1";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':b', (int)$b['codigo_billetera'], PDO::PARAM_INT);
        $stmt->bindValue(':r', $codigoRecarga, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    public function aplicarBonoBienvenida(int $codigoUsuario, float $monto = 15.00): array
    {
        try {
            if ($codigoUsuario <= 0 || $monto <= 0) return ['ok'=>false,'mensaje'=>'Parámetros inválidos.'];
            $this->dblink->beginTransaction();
            $b = $this->obtenerOBilleteraBloqueada($codigoUsuario);
            $codigoB = (int)$b['codigo_billetera'];
            $saldo = (float)$b['saldo_actual'];
            if ($this->existeMovimientoPorOrigenYReferencia($codigoB, 'C', 'BONO_BIENVENIDA', $codigoUsuario)) {
                $this->dblink->commit();
                return ['ok'=>true,'aplicado'=>false,'mensaje'=>'El bono de bienvenida ya estaba aplicado.'];
            }
            $nuevo = round($saldo + $monto, 2);
            $this->registrarMovimiento($codigoB, 'C', $monto, $saldo, $nuevo, 'Bono de bienvenida por aprobación de cuenta', 'BONO_BIENVENIDA', $codigoUsuario, 1, null);
            $this->actualizarSaldoBilletera($codigoB, $nuevo);
            $this->dblink->commit();
            return ['ok'=>true,'aplicado'=>true,'saldo_actual'=>$nuevo,'mensaje'=>'Bono de bienvenida aplicado.'];
        } catch (Exception $e) { if ($this->dblink->inTransaction()) $this->dblink->rollBack(); error_log('[EV][Billetera::aplicarBonoBienvenida] '.$e->getMessage()); return ['ok'=>false,'mensaje'=>'No se pudo aplicar el bono de bienvenida.','error'=>$e->getMessage()]; }
    }
}
