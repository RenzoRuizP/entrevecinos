<?php
require_once __DIR__ . '/../database/Conexion.php';

class Pedido extends Conexion
{
    private const SEGUNDOS_CANCELACION = 120;
    private const SEGUNDOS_TIMEOUT = 240;
    private const MINUTOS_GRACIA_FECHA_PROGRAMADA = 1;

    // =========================================================
    // HELPERS GENERALES
    // =========================================================

    private function obtenerResidenciaActivaUsuario(int $codigoUsuario): ?array
    {
        $sql = "
            SELECT
                codigo_usuario_residencia,
                tipo_conjunto,
                codigo_condominio,
                codigo_urbanizacion,
                direccion
            FROM usuario_residencia
            WHERE codigo_usuario = :u
            ORDER BY codigo_usuario_residencia DESC
            LIMIT 1
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        $tipo = strtolower(trim((string)($row['tipo_conjunto'] ?? '')));
        $cond = (int)($row['codigo_condominio'] ?? 0);
        $urb  = (int)($row['codigo_urbanizacion'] ?? 0);

        if ($tipo === 'condominio' && $cond > 0) {
            return [
                'codigo_usuario_residencia' => (int)$row['codigo_usuario_residencia'],
                'tipo_conjunto'             => 'condominio',
                'codigo_condominio'         => $cond,
                'codigo_urbanizacion'       => null,
                'direccion'                 => (string)($row['direccion'] ?? '')
            ];
        }

        if ($tipo === 'urbanizacion' && $urb > 0) {
            return [
                'codigo_usuario_residencia' => (int)$row['codigo_usuario_residencia'],
                'tipo_conjunto'             => 'urbanizacion',
                'codigo_condominio'         => null,
                'codigo_urbanizacion'       => $urb,
                'direccion'                 => (string)($row['direccion'] ?? '')
            ];
        }

        return null;
    }

    private function coincideResidenciaPublicacionConComprador(array $resComprador, array $rowProducto): bool
    {
        $tipoPub = strtolower(trim((string)($rowProducto['tipo_conjunto_publicacion'] ?? '')));
        $codCondPub = (int)($rowProducto['codigo_condominio_publicacion'] ?? 0);
        $codUrbPub  = (int)($rowProducto['codigo_urbanizacion_publicacion'] ?? 0);

        $tipoComprador = strtolower(trim((string)($resComprador['tipo_conjunto'] ?? '')));
        $codCondComprador = (int)($resComprador['codigo_condominio'] ?? 0);
        $codUrbComprador  = (int)($resComprador['codigo_urbanizacion'] ?? 0);

        if ($tipoPub === 'condominio' && $tipoComprador === 'condominio') {
            return $codCondPub > 0 && $codCondComprador > 0 && $codCondPub === $codCondComprador;
        }

        if ($tipoPub === 'urbanizacion' && $tipoComprador === 'urbanizacion') {
            return $codUrbPub > 0 && $codUrbComprador > 0 && $codUrbPub === $codUrbComprador;
        }

        return false;
    }

    private function limpiarTituloParaMovimiento(string $titulo): string
    {
        $titulo = trim((string)preg_replace('/\s+/u', ' ', $titulo));
        if ($titulo === '') return 'producto';

        if (mb_strlen($titulo, 'UTF-8') > 70) {
            $titulo = mb_substr($titulo, 0, 67, 'UTF-8') . '...';
        }

        return $titulo;
    }

    private function obtenerTimezoneAplicacion(): DateTimeZone
    {
        try {
            $tzName = (string)date_default_timezone_get();
            return new DateTimeZone($tzName !== '' ? $tzName : 'America/Lima');
        } catch (Throwable $e) {
            return new DateTimeZone('America/Lima');
        }
    }

    private function parsearFechaProgramadaUsuario($valor): ?DateTime
    {
        $raw = trim((string)$valor);
        if ($raw === '') {
            return null;
        }

        $tz = $this->obtenerTimezoneAplicacion();
        $raw = str_replace('T', ' ', $raw);
        $formatos = [
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'd/m/Y H:i:s',
            'd/m/Y H:i',
        ];

        foreach ($formatos as $formato) {
            $dt = DateTime::createFromFormat($formato, $raw, $tz);
            if ($dt instanceof DateTime) {
                $errores = DateTime::getLastErrors();
                $warningCount = (int)($errores['warning_count'] ?? 0);
                $errorCount   = (int)($errores['error_count'] ?? 0);

                if ($warningCount === 0 && $errorCount === 0) {
                    return $dt;
                }
            }
        }

        try {
            return new DateTime($raw, $tz);
        } catch (Throwable $e) {
            return null;
        }
    }

    private function validarFechaProgramada(?DateTime $fechaProgramada): array
    {
        if (!$fechaProgramada) {
            return [
                'ok'      => false,
                'error'   => 'FECHA_PROGRAMADA_INVALIDA',
                'mensaje' => 'La fecha programada no tiene un formato válido.'
            ];
        }

        $tz = $this->obtenerTimezoneAplicacion();
        $ahora = new DateTime('now', $tz);
        $minimaPermitida = (clone $ahora)->modify('-' . self::MINUTOS_GRACIA_FECHA_PROGRAMADA . ' minute');
        $maximo = (clone $ahora)->modify('+2 days');

        if ($fechaProgramada < $minimaPermitida) {
            return [
                'ok'      => false,
                'error'   => 'FECHA_PROGRAMADA_INVALIDA',
                'mensaje' => 'La fecha programada no puede ser menor al momento actual.'
            ];
        }

        if ($fechaProgramada > $maximo) {
            return [
                'ok'      => false,
                'error'   => 'FECHA_PROGRAMADA_FUERA_DE_RANGO',
                'mensaje' => 'La fecha programada no puede superar 2 días desde ahora.'
            ];
        }

        return [
            'ok'    => true,
            'fecha' => $fechaProgramada->format('Y-m-d H:i:s')
        ];
    }

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

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return [
                'codigo_billetera' => (int)$row['codigo_billetera'],
                'codigo_usuario'   => (int)$row['codigo_usuario'],
                'saldo_actual'     => (float)$row['saldo_actual']
            ];
        }

        $ins = $this->dblink->prepare("\n            INSERT INTO billetera (codigo_usuario, saldo_actual)\n            VALUES (:codigo_usuario, 0.00)\n        ");
        $ins->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $ins->execute();

        return [
            'codigo_billetera' => (int)$this->dblink->lastInsertId(),
            'codigo_usuario'   => $codigoUsuario,
            'saldo_actual'     => 0.00
        ];
    }

    private function registrarMovimientoBilletera(
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
                :es_promocional,
                :fecha_expira
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

        $st->bindValue(':es_promocional', $esPromocional, PDO::PARAM_INT);
        $st->bindValue(':fecha_expira', $fechaExpira, $fechaExpira !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $st->execute();
    }

    private function actualizarSaldoBilletera(int $codigoBilletera, float $nuevoSaldo): void
    {
        $sql = "
            UPDATE billetera
            SET saldo_actual = :saldo_actual
            WHERE codigo_billetera = :codigo_billetera
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':saldo_actual', $nuevoSaldo);
        $st->bindValue(':codigo_billetera', $codigoBilletera, PDO::PARAM_INT);
        $st->execute();
    }

    private function debitarBilleteraPorSolicitudPreparada(
        int $codigoUsuarioComprador,
        int $codigoPedido,
        float $monto,
        string $tituloProducto
    ): array {
        $monto = round((float)$monto, 2);
        if ($monto <= 0) {
            return ['ok' => true, 'saldo_actual' => 0.00];
        }

        $billetera = $this->obtenerOBilleteraBloqueada($codigoUsuarioComprador);
        $codigoBilletera = (int)$billetera['codigo_billetera'];
        $saldoAntes = (float)$billetera['saldo_actual'];

        if ($saldoAntes < $monto) {
            return [
                'ok' => false,
                'saldo_actual' => $saldoAntes,
                'monto_requerido' => $monto
            ];
        }

        $saldoDespues = round($saldoAntes - $monto, 2);
        $tituloLimpio = $this->limpiarTituloParaMovimiento($tituloProducto);

        $this->registrarMovimientoBilletera(
            $codigoBilletera,
            'D',
            $monto,
            $saldoAntes,
            $saldoDespues,
            'Débito por solicitud de producto: ' . $tituloLimpio,
            'PEDIDO_SOLICITUD_PREPARADA',
            $codigoPedido
        );

        $this->actualizarSaldoBilletera($codigoBilletera, $saldoDespues);

        $up = $this->dblink->prepare("\n            UPDATE pedido\n            SET\n                monto_descontado_billetera = :monto,\n                descuento_billetera_aplicado = 1\n            WHERE codigo_pedido = :codigo_pedido\n        ");
        $up->bindValue(':monto', $monto);
        $up->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
        $up->execute();

        return [
            'ok' => true,
            'saldo_actual' => $saldoDespues,
            'monto_requerido' => $monto
        ];
    }

    private function descripcionDevolucionBilletera(string $contexto, string $tituloProducto): string
    {
        $tituloLimpio = $this->limpiarTituloParaMovimiento($tituloProducto);

        return match ($contexto) {
            'cancelado_comprador' => 'Devolución por cancelación de solicitud: ' . $tituloLimpio,
            'rechazado_vendedor' => 'Devolución por rechazo de solicitud: ' . $tituloLimpio,
            'cancelado_vendedor' => 'Devolución por cancelación del vendedor: ' . $tituloLimpio,
            'sin_respuesta_vendedor' => 'Devolución por solicitud sin respuesta del vendedor: ' . $tituloLimpio,
            default => 'Devolución por solicitud no concretada: ' . $tituloLimpio,
        };
    }

    private function devolverBilleteraSiCorresponde(array $pedido, string $contexto): void
    {
        $codigoPedido = (int)($pedido['codigo_pedido'] ?? 0);
        $codigoUsuarioComprador = (int)($pedido['codigo_usuario_comprador'] ?? 0);
        $descuentoAplicado = (int)($pedido['descuento_billetera_aplicado'] ?? 0) === 1;
        $devolucionAplicada = (int)($pedido['devolucion_billetera_aplicada'] ?? 0) === 1;
        $monto = round((float)($pedido['monto_descontado_billetera'] ?? 0), 2);

        if (
            $codigoPedido <= 0 ||
            $codigoUsuarioComprador <= 0 ||
            !$descuentoAplicado ||
            $devolucionAplicada ||
            $monto <= 0
        ) {
            return;
        }

        $billetera = $this->obtenerOBilleteraBloqueada($codigoUsuarioComprador);
        $codigoBilletera = (int)$billetera['codigo_billetera'];
        $saldoAntes = (float)$billetera['saldo_actual'];
        $saldoDespues = round($saldoAntes + $monto, 2);

        $this->registrarMovimientoBilletera(
            $codigoBilletera,
            'C',
            $monto,
            $saldoAntes,
            $saldoDespues,
            $this->descripcionDevolucionBilletera($contexto, (string)($pedido['titulo_producto'] ?? 'producto')),
            'DEVOLUCION_PEDIDO_SOLICITUD',
            $codigoPedido
        );

        $this->actualizarSaldoBilletera($codigoBilletera, $saldoDespues);

        $up = $this->dblink->prepare("\n            UPDATE pedido\n            SET devolucion_billetera_aplicada = 1\n            WHERE codigo_pedido = :codigo_pedido\n        ");
        $up->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
        $up->execute();
    }

    private function registrarHistorialEstado(
        int $codigoPedido,
        ?string $faseAnterior,
        ?string $estadoAnterior,
        string $faseNueva,
        string $estadoNuevo,
        ?int $codigoUsuarioActor,
        ?string $rolActor,
        ?string $motivo,
        ?string $observacion
    ): void {
        $sqlHist = "
            INSERT INTO pedido_historial_estado
            (
                codigo_pedido,
                fase_anterior,
                estado_anterior,
                fase_nueva,
                estado_nuevo,
                codigo_usuario_actor,
                rol_actor,
                motivo,
                observacion
            )
            VALUES
            (
                :codigo_pedido,
                :fase_anterior,
                :estado_anterior,
                :fase_nueva,
                :estado_nuevo,
                :codigo_usuario_actor,
                :rol_actor,
                :motivo,
                :observacion
            )
        ";

        $stHist = $this->dblink->prepare($sqlHist);
        $stHist->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
        $stHist->bindValue(':fase_anterior', $faseAnterior, $faseAnterior !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stHist->bindValue(':estado_anterior', $estadoAnterior, $estadoAnterior !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stHist->bindValue(':fase_nueva', $faseNueva, PDO::PARAM_STR);
        $stHist->bindValue(':estado_nuevo', $estadoNuevo, PDO::PARAM_STR);

        if ($codigoUsuarioActor !== null && $codigoUsuarioActor > 0) {
            $stHist->bindValue(':codigo_usuario_actor', $codigoUsuarioActor, PDO::PARAM_INT);
        } else {
            $stHist->bindValue(':codigo_usuario_actor', null, PDO::PARAM_NULL);
        }

        $stHist->bindValue(':rol_actor', $rolActor, $rolActor !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stHist->bindValue(':motivo', $motivo, $motivo !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stHist->bindValue(':observacion', $observacion, $observacion !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stHist->execute();
    }

    private function obtenerPedidoPorId(int $codigoPedido, bool $forUpdate = false): ?array
    {
        $forUpdateSql = $forUpdate ? ' FOR UPDATE' : '';

        $sql = "
            SELECT
                p.*, 
                pr.titulo AS titulo_producto,
                pr.imagen_portada,
                TRIM(COALESCE(uc.nombre, '')) AS nombre_comprador,
                TRIM(COALESCE(uv.nombre, '')) AS nombre_vendedor
            FROM pedido p
            INNER JOIN producto pr ON pr.codigo_producto = p.codigo_producto
            INNER JOIN usuario uc ON uc.codigo_usuario = p.codigo_usuario_comprador
            INNER JOIN usuario uv ON uv.codigo_usuario = p.codigo_usuario_vendedor
            WHERE p.codigo_pedido = :codigo_pedido
            LIMIT 1
            {$forUpdateSql}
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function obtenerSolicitudComprador(int $codigoPedido, int $codigoUsuarioComprador, bool $forUpdate = false): ?array
    {
        $forUpdateSql = $forUpdate ? ' FOR UPDATE' : '';

        $sql = "
            SELECT
                p.*, 
                pr.titulo AS titulo_producto
            FROM pedido p
            INNER JOIN producto pr ON pr.codigo_producto = p.codigo_producto
            WHERE p.codigo_pedido = :codigo_pedido
              AND p.codigo_usuario_comprador = :codigo_usuario_comprador
            LIMIT 1
            {$forUpdateSql}
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
        $st->bindValue(':codigo_usuario_comprador', $codigoUsuarioComprador, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function obtenerPedidoVendedor(int $codigoPedido, int $codigoUsuarioVendedor, bool $forUpdate = false): ?array
    {
        $forUpdateSql = $forUpdate ? ' FOR UPDATE' : '';

        $sql = "
            SELECT
                p.*, 
                pr.titulo AS titulo_producto,
                pr.imagen_portada,
                TRIM(COALESCE(u.nombre, '')) AS nombre_comprador
            FROM pedido p
            INNER JOIN producto pr ON pr.codigo_producto = p.codigo_producto
            INNER JOIN usuario u ON u.codigo_usuario = p.codigo_usuario_comprador
            WHERE p.codigo_pedido = :codigo_pedido
              AND p.codigo_usuario_vendedor = :codigo_usuario_vendedor
            LIMIT 1
            {$forUpdateSql}
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
        $st->bindValue(':codigo_usuario_vendedor', $codigoUsuarioVendedor, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function bloquearPedidosActivosVendedor(int $codigoUsuarioVendedor): void
    {
        $sql = "
            SELECT codigo_pedido
            FROM pedido
            WHERE codigo_usuario_vendedor = :codigo_usuario_vendedor
            AND (
                    (
                        fase = 'solicitud'
                        AND estado_actual IN (
                            'pendiente_vendedor',
                            'cola_pendiente_confirmacion',
                            'cola_aceptada'
                        )
                    )
                    OR
                    (
                        fase = 'pedido'
                        AND estado_actual IN (
                            'en_preparacion',
                            'despachando',
                            'listo_para_entrega',
                            'en_camino',
                            'en_punto_entrega',
                            'entregado_vendedor'
                        )
                    )
            )
            ORDER BY
                CASE
                    WHEN fase = 'solicitud' AND estado_actual = 'pendiente_vendedor' THEN 1
                    WHEN fase = 'pedido' THEN 2
                    WHEN fase = 'solicitud' AND estado_actual = 'cola_pendiente_confirmacion' THEN 3
                    WHEN fase = 'solicitud' AND estado_actual = 'cola_aceptada' THEN 4
                    ELSE 5
                END,
                posicion_cola ASC,
                codigo_pedido ASC
            FOR UPDATE
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario_vendedor', $codigoUsuarioVendedor, PDO::PARAM_INT);
        $st->execute();
        $st->fetchAll(PDO::FETCH_ASSOC);
    }

    private function contarSolicitudesActivasPrevias(int $codigoUsuarioVendedor): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM pedido
            WHERE codigo_usuario_vendedor = :codigo_usuario_vendedor
            AND (
                    (
                        fase = 'solicitud'
                        AND estado_actual IN (
                            'pendiente_vendedor',
                            'cola_pendiente_confirmacion',
                            'cola_aceptada'
                        )
                    )
                    OR
                    (
                        fase = 'pedido'
                        AND estado_actual IN (
                            'en_preparacion',
                            'despachando',
                            'listo_para_entrega',
                            'en_camino',
                            'en_punto_entrega',
                            'entregado_vendedor'
                        )
                    )
            )
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario_vendedor', $codigoUsuarioVendedor, PDO::PARAM_INT);
        $st->execute();

        return (int)$st->fetchColumn();
    }

    private function vendedorTieneTurnoActivo(int $codigoUsuarioVendedor): bool
    {
        $sql = "
            SELECT 1
            FROM pedido
            WHERE codigo_usuario_vendedor = :codigo_usuario_vendedor
            AND (
                    (
                        fase = 'solicitud'
                        AND estado_actual = 'pendiente_vendedor'
                    )
                    OR
                    (
                        fase = 'pedido'
                        AND estado_actual IN (
                            'en_preparacion',
                            'despachando',
                            'listo_para_entrega',
                            'en_camino',
                            'en_punto_entrega',
                            'entregado_vendedor'
                        )
                    )
            )
            LIMIT 1
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario_vendedor', $codigoUsuarioVendedor, PDO::PARAM_INT);
        $st->execute();

        return (bool)$st->fetchColumn();
    }

    private function vendedorTieneOtroTurnoActivo(int $codigoUsuarioVendedor, int $codigoPedidoExcluir = 0): bool
    {
        $sql = "
            SELECT 1
            FROM pedido
            WHERE codigo_usuario_vendedor = :codigo_usuario_vendedor
              AND codigo_pedido <> :codigo_pedido_excluir
              AND (
                    (
                        fase = 'solicitud'
                        AND estado_actual = 'pendiente_vendedor'
                    )
                    OR
                    (
                        fase = 'pedido'
                        AND estado_actual IN (
                            'en_preparacion',
                            'despachando',
                            'listo_para_entrega',
                            'en_camino',
                            'en_punto_entrega',
                            'entregado_vendedor'
                        )
                    )
              )
            LIMIT 1
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario_vendedor', $codigoUsuarioVendedor, PDO::PARAM_INT);
        $st->bindValue(':codigo_pedido_excluir', $codigoPedidoExcluir, PDO::PARAM_INT);
        $st->execute();

        return (bool)$st->fetchColumn();
    }

    private function obtenerSiguientePendienteEnCola(int $codigoUsuarioVendedor): ?array
    {
        $sql = "
            SELECT
                p.*, 
                pr.titulo AS titulo_producto
            FROM pedido p
            INNER JOIN producto pr
                ON pr.codigo_producto = p.codigo_producto
            WHERE p.codigo_usuario_vendedor = :codigo_usuario_vendedor
            AND p.fase = 'solicitud'
            AND p.estado_actual = 'cola_aceptada'
            ORDER BY p.posicion_cola ASC, p.codigo_pedido ASC
            LIMIT 1
            FOR UPDATE
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario_vendedor', $codigoUsuarioVendedor, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function recalcularColaVendedor(int $codigoUsuarioVendedor): void
    {
        $sql = "
            SELECT
                p.codigo_pedido
            FROM pedido p
            WHERE p.codigo_usuario_vendedor = :codigo_usuario_vendedor
            AND p.fase = 'solicitud'
            AND p.estado_actual = 'cola_aceptada'
            ORDER BY p.posicion_cola ASC, p.codigo_pedido ASC
            FOR UPDATE
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario_vendedor', $codigoUsuarioVendedor, PDO::PARAM_INT);
        $st->execute();

        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $posicion = 2;

        foreach ($rows as $row) {
            $codigoPedido = (int)($row['codigo_pedido'] ?? 0);
            if ($codigoPedido <= 0) {
                continue;
            }

            $up = $this->dblink->prepare("\n                UPDATE pedido\n                SET posicion_cola = :posicion_cola\n                WHERE codigo_pedido = :codigo_pedido\n            ");
            $up->bindValue(':posicion_cola', $posicion, PDO::PARAM_INT);
            $up->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
            $up->execute();

            $posicion++;
        }
    }

    private function moverSiguienteColaAPendiente(int $codigoUsuarioVendedor): void
    {
        if ($this->vendedorTieneTurnoActivo($codigoUsuarioVendedor)) {
            $this->recalcularColaVendedor($codigoUsuarioVendedor);
            return;
        }

        $siguiente = $this->obtenerSiguientePendienteEnCola($codigoUsuarioVendedor);
        if (!$siguiente) {
            $this->recalcularColaVendedor($codigoUsuarioVendedor);
            return;
        }

        $codigoPedido = (int)($siguiente['codigo_pedido'] ?? 0);
        if ($codigoPedido <= 0) {
            $this->recalcularColaVendedor($codigoUsuarioVendedor);
            return;
        }

        $nuevoLimite = (new DateTime('now', $this->obtenerTimezoneAplicacion()))
            ->modify('+' . self::SEGUNDOS_TIMEOUT . ' seconds')
            ->format('Y-m-d H:i:s');

        $up = $this->dblink->prepare("\n            UPDATE pedido\n            SET\n                estado_actual = 'pendiente_vendedor',\n                motivo_estado = 'Tu solicitud pasó al turno de atención del vendedor.',\n                fecha_limite_respuesta = :fecha_limite_respuesta,\n                posicion_cola = 1\n            WHERE codigo_pedido = :codigo_pedido\n        ");
        $up->bindValue(':fecha_limite_respuesta', $nuevoLimite, PDO::PARAM_STR);
        $up->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
        $up->execute();

        $this->registrarHistorialEstado(
            $codigoPedido,
            (string)($siguiente['fase'] ?? 'solicitud'),
            (string)($siguiente['estado_actual'] ?? 'cola_aceptada'),
            (string)($siguiente['fase'] ?? 'solicitud'),
            'pendiente_vendedor',
            null,
            'sistema',
            'avance_cola',
            'La solicitud pasó de la cola a la atención del vendedor.'
        );

        $this->recalcularColaVendedor($codigoUsuarioVendedor);
    }

    private function sincronizarSolicitudesVencidas(
        ?int $codigoUsuarioVendedor = null,
        ?int $codigoUsuarioComprador = null,
        ?int $codigoPedido = null
    ): void {
        $where = [
            "fase = 'solicitud'",
            "estado_actual = 'pendiente_vendedor'",
            "fecha_limite_respuesta IS NOT NULL",
            "fecha_limite_respuesta <= NOW()"
        ];
        $params = [];

        if ($codigoUsuarioVendedor !== null && $codigoUsuarioVendedor > 0) {
            $where[] = 'codigo_usuario_vendedor = :codigo_usuario_vendedor';
            $params[':codigo_usuario_vendedor'] = $codigoUsuarioVendedor;
        }

        if ($codigoUsuarioComprador !== null && $codigoUsuarioComprador > 0) {
            $where[] = 'codigo_usuario_comprador = :codigo_usuario_comprador';
            $params[':codigo_usuario_comprador'] = $codigoUsuarioComprador;
        }

        if ($codigoPedido !== null && $codigoPedido > 0) {
            $where[] = 'codigo_pedido = :codigo_pedido';
            $params[':codigo_pedido'] = $codigoPedido;
        }

        $sql = "
            SELECT codigo_pedido
            FROM pedido
            WHERE " . implode(' AND ', $where) . "
            ORDER BY codigo_pedido ASC
        ";

        $st = $this->dblink->prepare($sql);
        foreach ($params as $key => $value) {
            $st->bindValue($key, $value, PDO::PARAM_INT);
        }
        $st->execute();

        $ids = $st->fetchAll(PDO::FETCH_COLUMN) ?: [];
        foreach ($ids as $id) {
            $this->marcarSolicitudSinRespuestaVencida((int)$id);
        }
    }

    private function marcarSolicitudSinRespuestaVencida(int $codigoPedido): void
    {
        if ($codigoPedido <= 0) {
            return;
        }

        try {
            $this->dblink->beginTransaction();

            $pedido = $this->obtenerPedidoPorId($codigoPedido, true);
            if (!$pedido) {
                $this->dblink->rollBack();
                return;
            }

            $estadoActual = (string)($pedido['estado_actual'] ?? '');
            $faseActual = (string)($pedido['fase'] ?? '');
            $fechaLimite = trim((string)($pedido['fecha_limite_respuesta'] ?? ''));
            $tsLimite = $fechaLimite !== '' ? strtotime($fechaLimite) : false;

            if (
                $faseActual !== 'solicitud' ||
                $estadoActual !== 'pendiente_vendedor' ||
                $tsLimite === false ||
                $tsLimite > time()
            ) {
                $this->dblink->rollBack();
                return;
            }

            $motivoEstado = 'El vendedor no respondió dentro del tiempo esperado.';

            $up = $this->dblink->prepare("\n                UPDATE pedido\n                SET\n                    fase = 'pedido',\n                    estado_actual = 'sin_respuesta_vendedor',\n                    motivo_estado = :motivo_estado,\n                    fecha_cierre = NOW(),\n                    fecha_limite_respuesta = NULL,\n                    posicion_cola = 0\n                WHERE codigo_pedido = :codigo_pedido\n            ");
            $up->bindValue(':motivo_estado', $motivoEstado, PDO::PARAM_STR);
            $up->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
            $up->execute();

            $this->registrarHistorialEstado(
                $codigoPedido,
                $faseActual,
                $estadoActual,
                'pedido',
                'sin_respuesta_vendedor',
                null,
                'sistema',
                'timeout_respuesta_vendedor',
                'La solicitud venció sin respuesta del vendedor.'
            );

            $this->devolverBilleteraSiCorresponde($pedido, 'sin_respuesta_vendedor');
            $this->moverSiguienteColaAPendiente((int)$pedido['codigo_usuario_vendedor']);

            $this->dblink->commit();
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            error_log('[EV][Pedido][marcarSolicitudSinRespuestaVencida] ' . $e->getMessage());
        }
    }

    private function obtenerSegundosRestantesRespuestaSolicitud(array $pedido): int
    {
        if ((string)($pedido['estado_actual'] ?? '') !== 'pendiente_vendedor') {
            return 0;
        }

        $fechaLimite = trim((string)($pedido['fecha_limite_respuesta'] ?? ''));
        if ($fechaLimite === '') {
            return 0;
        }

        $tsLimite = strtotime($fechaLimite);
        if ($tsLimite === false) {
            return 0;
        }

        return max(0, (int)($tsLimite - time()));
    }

    private function obtenerSegundosTranscurridosVentanaCancelacion(array $pedido): int
    {
        $estado = (string)($pedido['estado_actual'] ?? '');

        if ($estado !== 'pendiente_vendedor') {
            return 0;
        }

        $fechaLimite = trim((string)($pedido['fecha_limite_respuesta'] ?? ''));
        if ($fechaLimite !== '') {
            $tsLimite = strtotime($fechaLimite);
            if ($tsLimite !== false) {
                $tsInicioTurno = $tsLimite - self::SEGUNDOS_TIMEOUT;
                return max(0, (int)(time() - $tsInicioTurno));
            }
        }

        $createdAt = trim((string)($pedido['created_at'] ?? ''));
        if ($createdAt !== '') {
            $tsCreated = strtotime($createdAt);
            if ($tsCreated !== false) {
                return max(0, (int)(time() - $tsCreated));
            }
        }

        return 0;
    }

    private function obtenerSegundosParaCancelarSolicitud(array $pedido): int
    {
        $estado = (string)($pedido['estado_actual'] ?? '');

        if ($estado === 'pendiente_vendedor') {
            $segundosTranscurridos = $this->obtenerSegundosTranscurridosVentanaCancelacion($pedido);
            return max(0, self::SEGUNDOS_CANCELACION - $segundosTranscurridos);
        }

        if (in_array($estado, ['cola_pendiente_confirmacion', 'cola_aceptada'], true)) {
            return 0;
        }

        return 0;
    }

    private function puedeCancelarSolicitudSegunRegla(array $pedido): bool
    {
        $estado = (string)($pedido['estado_actual'] ?? '');

        if (!in_array($estado, ['pendiente_vendedor', 'cola_pendiente_confirmacion', 'cola_aceptada'], true)) {
            return false;
        }

        if ($estado === 'pendiente_vendedor') {
            return $this->obtenerSegundosParaCancelarSolicitud($pedido) <= 0;
        }

        return true;
    }

    private function construirDataEstadoSolicitud(array $pedido): array
    {
        $estado = (string)($pedido['estado_actual'] ?? '');
        $posicionCola = (int)($pedido['posicion_cola'] ?? 0);
        $motivoEstado = trim((string)($pedido['motivo_estado'] ?? ''));

        $mensajeEstado = match ($estado) {
            'pendiente_vendedor' =>
                'Tu solicitud está esperando respuesta del vendedor.',

            'cola_pendiente_confirmacion' =>
                ($posicionCola > 1)
                    ? "Tu solicitud quedó registrada y ocuparía la posición {$posicionCola} en la cola de atención. ¿Deseas continuar en espera?"
                    : 'El vendedor tiene otros pedidos en atención. Debes confirmar si deseas ingresar a la cola.',

            'cola_aceptada' =>
                ($posicionCola > 1)
                    ? "Tu solicitud está en cola y actualmente ocupa la posición {$posicionCola}. Cuando el vendedor termine el turno actual, pasará a revisión."
                    : 'Tu solicitud está en cola y avanzará cuando el vendedor termine el turno actual.',

            'cancelado_comprador' =>
                $motivoEstado !== ''
                    ? $motivoEstado
                    : 'Cancelaste la solicitud antes de que fuera atendida.',

            'rechazado_vendedor' =>
                $motivoEstado !== ''
                    ? 'El vecino no aceptó su pedido. Motivo: ' . $motivoEstado
                    : 'El vecino no aceptó su pedido.',

            'sin_respuesta_vendedor' =>
                $motivoEstado !== ''
                    ? $motivoEstado
                    : 'El vendedor no respondió a tiempo.',

            default =>
                $motivoEstado !== ''
                    ? $motivoEstado
                    : 'Estado actualizado.'
        };

        $segundosRestantes = $this->obtenerSegundosRestantesRespuestaSolicitud($pedido);
        $segundosParaCancelarRestantes = $this->obtenerSegundosParaCancelarSolicitud($pedido);
        $puedeCancelar = $this->puedeCancelarSolicitudSegunRegla($pedido) ? 1 : 0;

        return [
            'codigo_pedido'                         => (int)($pedido['codigo_pedido'] ?? 0),
            'codigo_producto'                       => (int)($pedido['codigo_producto'] ?? 0),
            'titulo_producto'                       => (string)($pedido['titulo_producto'] ?? ''),
            'fase'                                  => (string)($pedido['fase'] ?? ''),
            'estado_actual'                         => $estado,
            'motivo_estado'                         => $motivoEstado,
            'mensaje_estado'                        => $mensajeEstado,
            'posicion_cola'                         => $posicionCola,
            'requiere_preparacion'                  => (int)($pedido['requiere_preparacion'] ?? 0),
            'monto_descontado_billetera'            => (float)($pedido['monto_descontado_billetera'] ?? 0),
            'descuento_billetera_aplicado'          => (int)($pedido['descuento_billetera_aplicado'] ?? 0),
            'devolucion_billetera_aplicada'         => (int)($pedido['devolucion_billetera_aplicada'] ?? 0),
            'fecha_limite_respuesta'                => $pedido['fecha_limite_respuesta'] ?? null,
            'fecha_cancelacion'                     => $pedido['fecha_cancelacion'] ?? null,
            'fecha_cierre'                          => $pedido['fecha_cierre'] ?? null,
            'created_at'                            => $pedido['created_at'] ?? null,
            'puede_confirmar_cola'                  => $estado === 'cola_pendiente_confirmacion' ? 1 : 0,
            'puede_cancelar'                        => $puedeCancelar,
            'segundos_restantes'                    => $segundosRestantes,
            'segundos_para_cancelar_restantes'      => $segundosParaCancelarRestantes,
            'finalizado'                            => in_array($estado, [
                'cancelado_comprador',
                'sin_respuesta_vendedor',
                'rechazado_vendedor',
                'cancelado_vendedor',
                'entrega_confirmada_comprador'
            ], true) ? 1 : 0
        ];
    }

    // =========================================================
    // REGISTRO CON COLA
    // =========================================================
    public function registrarSolicitud(array $data): array
    {
        $codigoProducto         = (int)($data['codigo_producto'] ?? 0);
        $codigoUsuarioComprador = (int)($data['codigo_usuario_comprador'] ?? 0);
        $cantidad               = (int)($data['cantidad'] ?? 0);
        $tipoEntrega            = (string)($data['tipo_entrega'] ?? 'inmediata');
        $fechaHoraProgramada    = $data['fecha_hora_programada'] ?? null;
        $direccionEntrega       = trim((string)($data['direccion_entrega'] ?? ''));
        $mensajeComprador       = trim((string)($data['mensaje_comprador'] ?? ''));
        $aceptaCola             = (int)($data['acepta_cola'] ?? 0);

        if ($codigoProducto <= 0 || $codigoUsuarioComprador <= 0) {
            return [
                'ok'      => false,
                'error'   => 'PARAMETROS_INVALIDOS',
                'mensaje' => 'No se pudo registrar la solicitud.'
            ];
        }

        $validacion = $this->validarProductoParaSolicitud($codigoProducto, $codigoUsuarioComprador);
        if (!$validacion['ok']) {
            return $validacion;
        }

        $producto = $validacion['data'];

        if ($cantidad <= 0) {
            return [
                'ok'      => false,
                'error'   => 'CANTIDAD_INVALIDA',
                'mensaje' => 'La cantidad debe ser mayor a 0.'
            ];
        }

        if ($direccionEntrega === '') {
            return [
                'ok'      => false,
                'error'   => 'DIRECCION_REQUERIDA',
                'mensaje' => 'Debes ingresar la dirección de entrega.'
            ];
        }

        $tipoEntrega = strtolower(trim($tipoEntrega));
        if (!in_array($tipoEntrega, ['inmediata', 'programada'], true)) {
            $tipoEntrega = 'inmediata';
        }

        $fechaProgramadaMySql = null;
        if ($tipoEntrega === 'programada') {
            if (!$fechaHoraProgramada) {
                return [
                    'ok'      => false,
                    'error'   => 'FECHA_PROGRAMADA_REQUERIDA',
                    'mensaje' => 'Debes seleccionar la fecha y hora programada.'
                ];
            }

            $fechaProgramada = $this->parsearFechaProgramadaUsuario($fechaHoraProgramada);
            $validacionFechaProgramada = $this->validarFechaProgramada($fechaProgramada);

            if (!$validacionFechaProgramada['ok']) {
                return $validacionFechaProgramada;
            }

            $fechaProgramadaMySql = (string)$validacionFechaProgramada['fecha'];
        }

        $costoUnitario  = (float)($producto['precio'] ?? 0);
        $total          = round($costoUnitario * $cantidad, 2);
        $requierePrep   = (int)($producto['requiere_preparacion'] ?? 0);
        $codigoVendedor = (int)($producto['codigo_usuario_vendedor'] ?? 0);

        try {
            $this->dblink->beginTransaction();

            $this->bloquearPedidosActivosVendedor($codigoVendedor);

            $cargaActiva = $this->contarSolicitudesActivasPrevias($codigoVendedor);

            $fase = 'solicitud';
            $estadoActual = 'pendiente_vendedor';
            $posicionCola = 1;
            $fechaLimite = (new DateTime('now', $this->obtenerTimezoneAplicacion()))
                ->modify('+' . self::SEGUNDOS_TIMEOUT . ' seconds')
                ->format('Y-m-d H:i:s');
            $motivoEstado = 'Solicitud registrada por comprador.';

            if ($cargaActiva > 0) {
                $posicionCola = $cargaActiva + 1;
                $fechaLimite = null;

                if ($aceptaCola === 1) {
                    $estadoActual = 'cola_aceptada';
                    $motivoEstado = "El comprador aceptó ingresar a la cola. Posición actual: {$posicionCola}.";
                } else {
                    $estadoActual = 'cola_pendiente_confirmacion';
                    $motivoEstado = "Tu solicitud quedó registrada y ocuparía la posición {$posicionCola} en la cola de atención. ¿Deseas continuar en espera?";
                }
            }

            $sql = "
                INSERT INTO pedido
                (
                    codigo_producto,
                    codigo_usuario_comprador,
                    codigo_usuario_vendedor,
                    fase,
                    estado_actual,
                    cantidad,
                    costo_unitario,
                    total,
                    tipo_entrega,
                    fecha_hora_programada,
                    direccion_entrega,
                    mensaje_comprador,
                    posicion_cola,
                    motivo_estado,
                    requiere_preparacion,
                    monto_descontado_billetera,
                    descuento_billetera_aplicado,
                    devolucion_billetera_aplicada,
                    fecha_limite_respuesta
                )
                VALUES
                (
                    :codigo_producto,
                    :codigo_usuario_comprador,
                    :codigo_usuario_vendedor,
                    :fase,
                    :estado_actual,
                    :cantidad,
                    :costo_unitario,
                    :total,
                    :tipo_entrega,
                    :fecha_hora_programada,
                    :direccion_entrega,
                    :mensaje_comprador,
                    :posicion_cola,
                    :motivo_estado,
                    :requiere_preparacion,
                    0.00,
                    0,
                    0,
                    :fecha_limite_respuesta
                )
            ";

            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_producto', $codigoProducto, PDO::PARAM_INT);
            $st->bindValue(':codigo_usuario_comprador', $codigoUsuarioComprador, PDO::PARAM_INT);
            $st->bindValue(':codigo_usuario_vendedor', $codigoVendedor, PDO::PARAM_INT);
            $st->bindValue(':fase', $fase, PDO::PARAM_STR);
            $st->bindValue(':estado_actual', $estadoActual, PDO::PARAM_STR);
            $st->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);
            $st->bindValue(':costo_unitario', $costoUnitario);
            $st->bindValue(':total', $total);
            $st->bindValue(':tipo_entrega', $tipoEntrega, PDO::PARAM_STR);

            if ($fechaProgramadaMySql !== null) {
                $st->bindValue(':fecha_hora_programada', $fechaProgramadaMySql, PDO::PARAM_STR);
            } else {
                $st->bindValue(':fecha_hora_programada', null, PDO::PARAM_NULL);
            }

            $st->bindValue(':direccion_entrega', $direccionEntrega, PDO::PARAM_STR);

            if ($mensajeComprador !== '') {
                $st->bindValue(':mensaje_comprador', $mensajeComprador, PDO::PARAM_STR);
            } else {
                $st->bindValue(':mensaje_comprador', null, PDO::PARAM_NULL);
            }

            $st->bindValue(':posicion_cola', $posicionCola, PDO::PARAM_INT);
            $st->bindValue(':motivo_estado', $motivoEstado, PDO::PARAM_STR);
            $st->bindValue(':requiere_preparacion', $requierePrep, PDO::PARAM_INT);

            if ($fechaLimite !== null) {
                $st->bindValue(':fecha_limite_respuesta', $fechaLimite, PDO::PARAM_STR);
            } else {
                $st->bindValue(':fecha_limite_respuesta', null, PDO::PARAM_NULL);
            }

            $st->execute();

            $codigoPedido = (int)$this->dblink->lastInsertId();

            if ($requierePrep === 1) {
                $debito = $this->debitarBilleteraPorSolicitudPreparada(
                    $codigoUsuarioComprador,
                    $codigoPedido,
                    $total,
                    (string)($producto['titulo'] ?? 'producto')
                );

                if (!$debito['ok']) {
                    $this->dblink->rollBack();
                    return [
                        'ok' => false,
                        'error' => 'SALDO_INSUFICIENTE_BILLETERA',
                        'mensaje' => 'No tienes saldo suficiente en tu billetera para este producto con preparación.',
                        'saldo_actual' => (float)($debito['saldo_actual'] ?? 0),
                        'monto_requerido' => (float)($debito['monto_requerido'] ?? $total)
                    ];
                }
            }

            $this->registrarHistorialEstado(
                $codigoPedido,
                null,
                null,
                $fase,
                $estadoActual,
                $codigoUsuarioComprador,
                'comprador',
                'registro_solicitud',
                ($mensajeComprador !== '') ? $mensajeComprador : $motivoEstado
            );

            $this->recalcularColaVendedor($codigoVendedor);

            $pedidoInsertado = $this->obtenerSolicitudComprador($codigoPedido, $codigoUsuarioComprador, false);

            $this->dblink->commit();

            return [
                'ok'   => true,
                'data' => $this->construirDataEstadoSolicitud($pedidoInsertado ?: [
                    'codigo_pedido' => $codigoPedido,
                    'codigo_producto' => $codigoProducto,
                    'titulo_producto' => (string)($producto['titulo'] ?? ''),
                    'fase' => $fase,
                    'estado_actual' => $estadoActual,
                    'motivo_estado' => $motivoEstado,
                    'posicion_cola' => $posicionCola,
                    'requiere_preparacion' => $requierePrep,
                    'monto_descontado_billetera' => $requierePrep === 1 ? $total : 0,
                    'descuento_billetera_aplicado' => $requierePrep === 1 ? 1 : 0,
                    'devolucion_billetera_aplicada' => 0,
                    'fecha_limite_respuesta' => $fechaLimite,
                    'created_at' => date('Y-m-d H:i:s')
                ])
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }

            error_log('[EV][Pedido][registrarSolicitud] ' . $e->getMessage());

            return [
                'ok'      => false,
                'error'   => 'ERROR_REGISTRAR_PEDIDO',
                'mensaje' => 'No se pudo registrar la solicitud de pedido.'
            ];
        }
    }

    public function confirmarColaPorComprador(int $codigoPedido, int $codigoUsuarioComprador): array
    {
        try {
            $this->dblink->beginTransaction();

            $pedido = $this->obtenerSolicitudComprador($codigoPedido, $codigoUsuarioComprador, true);

            if (!$pedido) {
                $this->dblink->rollBack();
                return [
                    'ok'      => false,
                    'error'   => 'PEDIDO_NO_ENCONTRADO',
                    'mensaje' => 'No se encontró la solicitud.'
                ];
            }

            if ((string)$pedido['estado_actual'] !== 'cola_pendiente_confirmacion') {
                $this->dblink->rollBack();
                return [
                    'ok'      => false,
                    'error'   => 'ESTADO_NO_CONFIRMABLE',
                    'mensaje' => 'Esta solicitud ya no está pendiente de confirmación de cola.',
                    'data'    => $this->construirDataEstadoSolicitud($pedido)
                ];
            }

            $posicionCola = max(2, (int)($pedido['posicion_cola'] ?? 0));
            $motivoEstado = "El comprador aceptó ingresar a la cola. Posición actual: {$posicionCola}.";

            $up = $this->dblink->prepare("\n                UPDATE pedido\n                SET\n                    estado_actual = 'cola_aceptada',\n                    motivo_estado = :motivo_estado\n                WHERE codigo_pedido = :codigo_pedido\n            ");
            $up->bindValue(':motivo_estado', $motivoEstado, PDO::PARAM_STR);
            $up->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
            $up->execute();

            $this->registrarHistorialEstado(
                $codigoPedido,
                (string)$pedido['fase'],
                (string)$pedido['estado_actual'],
                (string)$pedido['fase'],
                'cola_aceptada',
                $codigoUsuarioComprador,
                'comprador',
                'confirmacion_cola',
                'El comprador aceptó permanecer en la cola.'
            );

            $this->recalcularColaVendedor((int)$pedido['codigo_usuario_vendedor']);

            $pedidoActualizado = $this->obtenerSolicitudComprador($codigoPedido, $codigoUsuarioComprador, false);

            $this->dblink->commit();

            return [
                'ok'   => true,
                'data' => $this->construirDataEstadoSolicitud($pedidoActualizado ?: $pedido)
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }

            error_log('[EV][Pedido][confirmarColaPorComprador] ' . $e->getMessage());

            return [
                'ok'      => false,
                'error'   => 'ERROR_CONFIRMAR_COLA',
                'mensaje' => 'No se pudo confirmar la cola.'
            ];
        }
    }

    public function obtenerEstadoSolicitudParaComprador(int $codigoPedido, int $codigoUsuarioComprador): array
    {
        $this->sincronizarSolicitudesVencidas(null, $codigoUsuarioComprador, $codigoPedido);

        $pedido = $this->obtenerSolicitudComprador($codigoPedido, $codigoUsuarioComprador, false);

        if (!$pedido) {
            return [
                'ok'      => false,
                'error'   => 'PEDIDO_NO_ENCONTRADO',
                'mensaje' => 'No se encontró la solicitud.'
            ];
        }

        return [
            'ok'   => true,
            'data' => $this->construirDataEstadoSolicitud($pedido)
        ];
    }

    public function cancelarSolicitudPorComprador(int $codigoPedido, int $codigoUsuarioComprador, string $motivo): array
    {
        $this->sincronizarSolicitudesVencidas(null, $codigoUsuarioComprador, $codigoPedido);

        try {
            $this->dblink->beginTransaction();

            $pedido = $this->obtenerSolicitudComprador($codigoPedido, $codigoUsuarioComprador, true);

            if (!$pedido) {
                $this->dblink->rollBack();
                return [
                    'ok'      => false,
                    'error'   => 'PEDIDO_NO_ENCONTRADO',
                    'mensaje' => 'No se encontró la solicitud.'
                ];
            }

            $estadoAnterior = (string)$pedido['estado_actual'];
            $motivo = trim($motivo);

            if (!in_array($estadoAnterior, ['pendiente_vendedor', 'cola_pendiente_confirmacion', 'cola_aceptada'], true)) {
                $this->dblink->rollBack();
                return [
                    'ok'      => false,
                    'error'   => 'ESTADO_NO_CANCELABLE',
                    'mensaje' => 'La solicitud ya no se puede cancelar.',
                    'data'    => $this->construirDataEstadoSolicitud($pedido)
                ];
            }

            if ($estadoAnterior === 'pendiente_vendedor') {
                $segundosParaCancelarRestantes = $this->obtenerSegundosParaCancelarSolicitud($pedido);

                if ($segundosParaCancelarRestantes > 0) {
                    $this->dblink->rollBack();

                    $min = str_pad((string)floor($segundosParaCancelarRestantes / 60), 2, '0', STR_PAD_LEFT);
                    $sec = str_pad((string)($segundosParaCancelarRestantes % 60), 2, '0', STR_PAD_LEFT);

                    return [
                        'ok'      => false,
                        'error'   => 'CANCELACION_AUN_NO_DISPONIBLE',
                        'mensaje' => "Podrás cancelar esta solicitud cuando se cumplan 2 minutos de espera. Tiempo restante: {$min}:{$sec}.",
                        'data'    => $this->construirDataEstadoSolicitud($pedido)
                    ];
                }
            }

            if ($motivo === '') {
                $motivo = in_array($estadoAnterior, ['cola_pendiente_confirmacion', 'cola_aceptada'], true)
                    ? 'Ya no deseo continuar con el pedido.'
                    : 'Solicitud cancelada por el comprador.';
            }

            $motivoEstado = in_array($estadoAnterior, ['cola_pendiente_confirmacion', 'cola_aceptada'], true)
                ? 'El comprador decidió no continuar con la cola. Motivo: ' . $motivo
                : $motivo;

            $up = $this->dblink->prepare("\n                UPDATE pedido\n                SET\n                    estado_actual = 'cancelado_comprador',\n                    motivo_estado = :motivo_estado,\n                    fecha_cancelacion = NOW(),\n                    fecha_cierre = NOW(),\n                    posicion_cola = 0,\n                    fecha_limite_respuesta = NULL\n                WHERE codigo_pedido = :codigo_pedido\n            ");
            $up->bindValue(':motivo_estado', $motivoEstado, PDO::PARAM_STR);
            $up->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
            $up->execute();

            $this->registrarHistorialEstado(
                (int)$pedido['codigo_pedido'],
                (string)$pedido['fase'],
                $estadoAnterior,
                (string)$pedido['fase'],
                'cancelado_comprador',
                $codigoUsuarioComprador,
                'comprador',
                'cancelacion_solicitud',
                $motivo
            );

            $this->devolverBilleteraSiCorresponde($pedido, 'cancelado_comprador');

            if ($estadoAnterior === 'pendiente_vendedor') {
                $this->moverSiguienteColaAPendiente((int)$pedido['codigo_usuario_vendedor']);
            } else {
                $this->recalcularColaVendedor((int)$pedido['codigo_usuario_vendedor']);
            }

            $pedidoActualizado = $this->obtenerSolicitudComprador($codigoPedido, $codigoUsuarioComprador, false);

            $this->dblink->commit();

            return [
                'ok'   => true,
                'data' => $this->construirDataEstadoSolicitud($pedidoActualizado ?: $pedido)
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }

            error_log('[EV][Pedido][cancelarSolicitudPorComprador] ' . $e->getMessage());

            return [
                'ok'      => false,
                'error'   => 'ERROR_CANCELAR_SOLICITUD',
                'mensaje' => 'No se pudo cancelar la solicitud.'
            ];
        }
    }

    // =========================================================
    // LISTADOS
    // =========================================================
    public function listarPedidosEntrantes(int $codigoUsuarioVendedor): array
    {
        $this->sincronizarSolicitudesVencidas($codigoUsuarioVendedor, null, null);

        $sql = "
            SELECT
                p.*, 
                pr.titulo AS titulo_publicacion,
                pr.imagen_portada,
                TRIM(COALESCE(u.nombre, '')) AS nombre_vecino
            FROM pedido p
            INNER JOIN producto pr ON pr.codigo_producto = p.codigo_producto
            INNER JOIN usuario u ON u.codigo_usuario = p.codigo_usuario_comprador
            WHERE p.codigo_usuario_vendedor = :v
            AND p.fase = 'solicitud'
            AND p.estado_actual IN ('pendiente_vendedor', 'cola_aceptada')
            ORDER BY
                CASE
                    WHEN p.estado_actual = 'pendiente_vendedor' THEN 1
                    WHEN p.estado_actual = 'cola_aceptada' THEN 2
                    ELSE 3
                END,
                p.posicion_cola ASC,
                p.codigo_pedido ASC
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':v', $codigoUsuarioVendedor, PDO::PARAM_INT);
        $st->execute();

        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $out = [];

        foreach ($rows as $r) {
            $tiempoRestante = null;

            if (
                (string)($r['estado_actual'] ?? '') === 'pendiente_vendedor' &&
                !empty($r['fecha_limite_respuesta'])
            ) {
                $seg = strtotime((string)$r['fecha_limite_respuesta']) - time();
                $tiempoRestante = max(0, (int)$seg);
            }

            $out[] = [
                'codigo_pedido'                 => (int)$r['codigo_pedido'],
                'codigo_producto'               => (int)$r['codigo_producto'],
                'titulo_publicacion'            => (string)($r['titulo_publicacion'] ?? ''),
                'nombre_vecino'                 => (string)($r['nombre_vecino'] ?? 'Vecino'),
                'fecha_hora'                    => !empty($r['created_at']) ? date('d/m/Y H:i', strtotime((string)$r['created_at'])) : '',
                'monto_total'                   => (string)($r['total'] ?? '0.00'),
                'cantidad'                      => (int)($r['cantidad'] ?? 0),
                'precio_unitario'               => (string)($r['costo_unitario'] ?? '0.00'),
                'tipo_entrega'                  => (string)($r['tipo_entrega'] ?? 'inmediata'),
                'tipo_entrega_raw'              => (string)($r['tipo_entrega'] ?? 'inmediata'),
                'fecha_hora_programada'         => $r['fecha_hora_programada'],
                'direccion_entrega'             => (string)($r['direccion_entrega'] ?? ''),
                'mensaje_comprador'             => (string)($r['mensaje_comprador'] ?? ''),
                'motivo_estado'                 => (string)($r['motivo_estado'] ?? ''),
                'estado_actual'                 => (string)($r['estado_actual'] ?? ''),
                'fase'                          => (string)($r['fase'] ?? ''),
                'fecha_limite_respuesta'        => $r['fecha_limite_respuesta'],
                'imagen_portada'                => (string)($r['imagen_portada'] ?? ''),
                'requiere_preparacion'          => (int)($r['requiere_preparacion'] ?? 0),
                'monto_descontado_billetera'    => (string)($r['monto_descontado_billetera'] ?? '0.00'),
                'descuento_billetera_aplicado'  => (int)($r['descuento_billetera_aplicado'] ?? 0),
                'devolucion_billetera_aplicada' => (int)($r['devolucion_billetera_aplicada'] ?? 0),
                'posicion_cola'                 => (int)($r['posicion_cola'] ?? 0),
                'tiempo_restante_segundos'      => $tiempoRestante
            ];
        }

        return $out;
    }

    public function obtenerSolicitudActivaComprador(int $codigoUsuarioComprador): array
    {
        try {
            $this->sincronizarSolicitudesVencidas(null, $codigoUsuarioComprador, null);

            $sql = "
                SELECT
                    p.*, 
                    pr.titulo AS titulo_producto
                FROM pedido p
                INNER JOIN producto pr ON pr.codigo_producto = p.codigo_producto
                WHERE p.codigo_usuario_comprador = :codigo_usuario_comprador
                AND p.fase = 'solicitud'
                AND p.estado_actual IN ('pendiente_vendedor', 'cola_pendiente_confirmacion', 'cola_aceptada')
                ORDER BY p.codigo_pedido DESC
                LIMIT 1
            ";

            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_usuario_comprador', $codigoUsuarioComprador, PDO::PARAM_INT);
            $st->execute();

            $pedido = $st->fetch(PDO::FETCH_ASSOC);
            if (!$pedido) {
                return ['ok' => true, 'data' => null];
            }

            $data = $this->construirDataEstadoSolicitud($pedido);

            return [
                'ok'   => true,
                'data' => $data
            ];
        } catch (Throwable $e) {
            error_log('[EV][Pedido][obtenerSolicitudActivaComprador] ' . $e->getMessage());

            return [
                'ok'      => false,
                'error'   => 'ERROR_OBTENER_SOLICITUD_ACTIVA',
                'mensaje' => 'No se pudo obtener la solicitud activa del comprador.'
            ];
        }
    }

    // =========================================================
    // VENDEDOR
    // =========================================================
    private function normalizarTipoEntrega(?string $tipo, ?string $fechaProgramada): string
    {
        $tipo = strtolower(trim((string)$tipo));
        if ($tipo === 'programada' && !empty($fechaProgramada)) return 'Programado';
        return 'Inmediato';
    }

    private function formatearPedidoVendedor(array $r): array
    {
        $tiempoRestante = null;

        if (
            (string)($r['fase'] ?? '') === 'solicitud' &&
            (string)($r['estado_actual'] ?? '') === 'pendiente_vendedor' &&
            !empty($r['fecha_limite_respuesta'])
        ) {
            $tiempoRestante = max(
                0,
                (int)(strtotime((string)$r['fecha_limite_respuesta']) - time())
            );
        }

        $tituloPublicacion = (string)($r['titulo_publicacion'] ?? $r['titulo_producto'] ?? '');
        $nombreComprador   = (string)($r['nombre_comprador'] ?? $r['nombre_vecino'] ?? 'Vecino');

        return [
            'codigo_pedido'                  => (int)$r['codigo_pedido'],
            'codigo_producto'                => (int)$r['codigo_producto'],
            'titulo_publicacion'             => $tituloPublicacion,
            'titulo_producto'                => $tituloPublicacion,
            'nombre_vecino'                  => $nombreComprador,
            'nombre_comprador'               => $nombreComprador,
            'imagen_portada'                 => (string)($r['imagen_portada'] ?? ''),
            'fase'                           => (string)($r['fase'] ?? ''),
            'estado_actual'                  => (string)($r['estado_actual'] ?? ''),
            'motivo_estado'                  => (string)($r['motivo_estado'] ?? ''),
            'cantidad'                       => (int)($r['cantidad'] ?? 0),
            'precio_unitario'                => (string)($r['costo_unitario'] ?? '0.00'),
            'monto_total'                    => (string)($r['total'] ?? '0.00'),
            'tipo_entrega'                   => $this->normalizarTipoEntrega($r['tipo_entrega'] ?? null, $r['fecha_hora_programada'] ?? null),
            'tipo_entrega_raw'               => (string)($r['tipo_entrega'] ?? 'inmediata'),
            'fecha_hora_programada'          => $r['fecha_hora_programada'],
            'direccion_entrega'              => (string)($r['direccion_entrega'] ?? ''),
            'mensaje_comprador'              => (string)($r['mensaje_comprador'] ?? ''),
            'fecha_hora'                     => $r['created_at'] ?? null,
            'created_at'                     => $r['created_at'] ?? null,
            'fecha_limite_respuesta'         => $r['fecha_limite_respuesta'] ?? null,
            'requiere_preparacion'           => (int)($r['requiere_preparacion'] ?? 0),
            'descuento_billetera_aplicado'   => (int)($r['descuento_billetera_aplicado'] ?? 0),
            'devolucion_billetera_aplicada'  => (int)($r['devolucion_billetera_aplicada'] ?? 0),
            'monto_descontado_billetera'     => (string)($r['monto_descontado_billetera'] ?? '0.00'),
            'posicion_cola'                  => (int)($r['posicion_cola'] ?? 0),
            'tiempo_restante_segundos'       => $tiempoRestante
        ];
    }

    private function determinarGrupoPedidoVendedor(array $pedido): string
    {
        $estado = (string)($pedido['estado_actual'] ?? '');

        if (in_array($estado, [
            'pendiente_vendedor',
            'cola_aceptada'
        ], true)) {
            return 'pendientes';
        }

        if (in_array($estado, [
            'en_preparacion',
            'despachando',
            'listo_para_entrega',
            'en_camino',
            'en_punto_entrega',
            'entregado_vendedor'
        ], true)) {
            return 'en_proceso';
        }

        return 'finalizados';
    }

    public function listarMisPedidosVendedor(int $codigoUsuarioVendedor): array
    {
        try {
            $this->sincronizarSolicitudesVencidas($codigoUsuarioVendedor, null, null);

            $sql = "
                SELECT
                    p.*,
                    pr.titulo AS titulo_publicacion,
                    pr.imagen_portada,
                    TRIM(COALESCE(u.nombre, '')) AS nombre_comprador
                FROM pedido p
                INNER JOIN producto pr ON pr.codigo_producto = p.codigo_producto
                INNER JOIN usuario u ON u.codigo_usuario = p.codigo_usuario_comprador
                WHERE p.codigo_usuario_vendedor = :codigo_usuario_vendedor
                ORDER BY
                    CASE
                        WHEN p.estado_actual = 'pendiente_vendedor' THEN 1
                        WHEN p.estado_actual = 'cola_pendiente_confirmacion' THEN 2
                        WHEN p.estado_actual = 'cola_aceptada' THEN 3
                        WHEN p.estado_actual IN (
                            'en_preparacion',
                            'despachando',
                            'listo_para_entrega',
                            'en_camino',
                            'en_punto_entrega',
                            'entregado_vendedor'
                        ) THEN 4
                        ELSE 5
                    END,
                    CASE
                        WHEN p.estado_actual IN ('cola_aceptada', 'cola_pendiente_confirmacion') THEN p.posicion_cola
                        ELSE 0
                    END ASC,
                    p.codigo_pedido DESC
            ";

            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_usuario_vendedor', $codigoUsuarioVendedor, PDO::PARAM_INT);
            $st->execute();

            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $data = [
                'pendientes'  => [],
                'en_proceso'  => [],
                'finalizados' => []
            ];

            foreach ($rows as $row) {
                if ((string)($row['estado_actual'] ?? '') === 'cola_pendiente_confirmacion') {
                    continue;
                }

                $item = $this->formatearPedidoVendedor($row);
                $grupo = $this->determinarGrupoPedidoVendedor($item);
                $data[$grupo][] = $item;
            }

            return [
                'ok'   => true,
                'data' => $data
            ];
        } catch (Throwable $e) {
            error_log('[EV][Pedido][listarMisPedidosVendedor] ' . $e->getMessage());

            return [
                'ok'      => false,
                'error'   => 'ERROR_LISTAR_MIS_PEDIDOS',
                'mensaje' => 'No se pudo obtener la lista de pedidos del vendedor.'
            ];
        }
    }

    public function aceptarSolicitudPorVendedor(int $codigoPedido, int $codigoUsuarioVendedor): array
    {
        $this->sincronizarSolicitudesVencidas($codigoUsuarioVendedor, null, $codigoPedido);

        try {
            $this->dblink->beginTransaction();

            $this->bloquearPedidosActivosVendedor($codigoUsuarioVendedor);
            $pedido = $this->obtenerPedidoVendedor($codigoPedido, $codigoUsuarioVendedor, true);

            if (!$pedido) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'PEDIDO_NO_ENCONTRADO', 'mensaje' => 'No se encontró el pedido.'];
            }

            if ((string)$pedido['fase'] !== 'solicitud' || (string)$pedido['estado_actual'] !== 'pendiente_vendedor') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_ACEPTABLE', 'mensaje' => 'La solicitud ya no se puede aceptar.'];
            }

            if ($this->vendedorTieneOtroTurnoActivo($codigoUsuarioVendedor, $codigoPedido)) {
                $this->dblink->rollBack();
                return [
                    'ok' => false,
                    'error' => 'VENDEDOR_CON_TURNO_ACTIVO',
                    'mensaje' => 'Debes terminar el pedido actual antes de atender uno nuevo.'
                ];
            }

            $siguienteEstado = ((int)$pedido['requiere_preparacion'] === 1) ? 'en_preparacion' : 'despachando';

            $up = $this->dblink->prepare("\n                UPDATE pedido\n                SET\n                    fase = 'pedido',\n                    estado_actual = :estado_actual,\n                    motivo_estado = 'Pedido aceptado por el vendedor.',\n                    fecha_aceptacion = NOW(),\n                    posicion_cola = 0,\n                    fecha_limite_respuesta = NULL\n                WHERE codigo_pedido = :codigo_pedido\n            ");
            $up->bindValue(':estado_actual', $siguienteEstado, PDO::PARAM_STR);
            $up->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
            $up->execute();

            $this->registrarHistorialEstado(
                $codigoPedido,
                (string)$pedido['fase'],
                (string)$pedido['estado_actual'],
                'pedido',
                $siguienteEstado,
                $codigoUsuarioVendedor,
                'vendedor',
                'aceptacion_solicitud',
                'El vendedor aceptó la solicitud.'
            );

            $this->recalcularColaVendedor($codigoUsuarioVendedor);

            $pedidoActualizado = $this->obtenerPedidoVendedor($codigoPedido, $codigoUsuarioVendedor, false);

            $this->dblink->commit();

            return ['ok' => true, 'data' => $pedidoActualizado ? $this->formatearPedidoVendedor($pedidoActualizado) : null];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][Pedido][aceptarSolicitudPorVendedor] ' . $e->getMessage());

            return ['ok' => false, 'error' => 'ERROR_ACEPTAR_SOLICITUD', 'mensaje' => 'No se pudo aceptar la solicitud.'];
        }
    }

    public function rechazarSolicitudPorVendedor(int $codigoPedido, int $codigoUsuarioVendedor, string $motivo): array
    {
        $this->sincronizarSolicitudesVencidas($codigoUsuarioVendedor, null, $codigoPedido);

        try {
            $motivo = trim($motivo);
            if ($motivo === '') {
                return [
                    'ok'      => false,
                    'error'   => 'MOTIVO_REQUERIDO',
                    'mensaje' => 'Debes indicar el motivo de rechazo.'
                ];
            }

            $this->dblink->beginTransaction();

            $pedido = $this->obtenerPedidoVendedor($codigoPedido, $codigoUsuarioVendedor, true);

            if (!$pedido) {
                $this->dblink->rollBack();
                return [
                    'ok'      => false,
                    'error'   => 'PEDIDO_NO_ENCONTRADO',
                    'mensaje' => 'No se encontró el pedido.'
                ];
            }

            $estadoAnterior = (string)$pedido['estado_actual'];
            $faseAnterior   = (string)$pedido['fase'];

            if (
                $faseAnterior !== 'solicitud' ||
                !in_array($estadoAnterior, ['pendiente_vendedor'], true)
            ) {
                $this->dblink->rollBack();
                return [
                    'ok'      => false,
                    'error'   => 'ESTADO_NO_RECHAZABLE',
                    'mensaje' => 'La solicitud ya no se puede rechazar.'
                ];
            }

            $up = $this->dblink->prepare("\n                UPDATE pedido\n                SET\n                    fase = 'pedido',\n                    estado_actual = 'rechazado_vendedor',\n                    motivo_estado = :motivo_estado,\n                    fecha_rechazo = NOW(),\n                    fecha_cierre = NOW(),\n                    posicion_cola = 0,\n                    fecha_limite_respuesta = NULL\n                WHERE codigo_pedido = :codigo_pedido\n            ");
            $up->bindValue(':motivo_estado', $motivo, PDO::PARAM_STR);
            $up->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
            $up->execute();

            $this->registrarHistorialEstado(
                $codigoPedido,
                $faseAnterior,
                $estadoAnterior,
                'pedido',
                'rechazado_vendedor',
                $codigoUsuarioVendedor,
                'vendedor',
                'rechazo_solicitud',
                $motivo
            );

            $this->devolverBilleteraSiCorresponde($pedido, 'rechazado_vendedor');

            if ($estadoAnterior === 'pendiente_vendedor') {
                $this->moverSiguienteColaAPendiente($codigoUsuarioVendedor);
            } else {
                $this->recalcularColaVendedor($codigoUsuarioVendedor);
            }

            $pedidoActualizado = $this->obtenerPedidoVendedor($codigoPedido, $codigoUsuarioVendedor, false);

            $this->dblink->commit();

            return [
                'ok'   => true,
                'data' => $pedidoActualizado ? $this->formatearPedidoVendedor($pedidoActualizado) : null
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }

            error_log('[EV][Pedido][rechazarSolicitudPorVendedor] ' . $e->getMessage());

            return [
                'ok'      => false,
                'error'   => 'ERROR_RECHAZAR_SOLICITUD',
                'mensaje' => 'No se pudo rechazar la solicitud.'
            ];
        }
    }

    private function obtenerMapaTransicionesVendedor(bool $requierePreparacion): array
    {
        if ($requierePreparacion) {
            return [
                'en_preparacion'     => ['listo_para_entrega', 'en_camino', 'cancelado_vendedor'],
                'listo_para_entrega' => ['en_camino', 'en_punto_entrega', 'entregado_vendedor', 'cancelado_vendedor'],
                'en_camino'          => ['en_punto_entrega', 'entregado_vendedor', 'cancelado_vendedor'],
                'en_punto_entrega'   => ['entregado_vendedor', 'cancelado_vendedor'],
                'entregado_vendedor' => [],
            ];
        }

        return [
            'despachando'        => ['en_camino', 'en_punto_entrega', 'entregado_vendedor', 'cancelado_vendedor'],
            'en_camino'          => ['en_punto_entrega', 'entregado_vendedor', 'cancelado_vendedor'],
            'en_punto_entrega'   => ['entregado_vendedor', 'cancelado_vendedor'],
            'entregado_vendedor' => [],
        ];
    }

    private function motivoEstadoPorNuevoEstado(string $estado): string
    {
        return match ($estado) {
            'en_preparacion'               => 'El pedido está en preparación.',
            'despachando'                  => 'El pedido está siendo despachado.',
            'listo_para_entrega'           => 'El pedido está listo para entrega.',
            'en_camino'                    => 'El pedido va en camino.',
            'en_punto_entrega'             => 'El pedido llegó al punto de entrega.',
            'entregado_vendedor'           => 'El vendedor marcó el pedido como entregado.',
            'cancelado_vendedor'           => 'El vendedor canceló el pedido.',
            'entrega_confirmada_comprador' => 'El comprador confirmó la entrega.',
            default                        => 'Estado actualizado.'
        };
    }

    public function actualizarEstadoPedidoPorVendedor(int $codigoPedido, int $codigoUsuarioVendedor, string $nuevoEstado): array
    {
        try {
            $nuevoEstado = trim($nuevoEstado);
            if ($nuevoEstado === '') {
                return ['ok' => false, 'error' => 'ESTADO_NO_ACTUALIZABLE', 'mensaje' => 'Debes indicar el nuevo estado.'];
            }

            $this->dblink->beginTransaction();

            $pedido = $this->obtenerPedidoVendedor($codigoPedido, $codigoUsuarioVendedor, true);

            if (!$pedido) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'PEDIDO_NO_ENCONTRADO', 'mensaje' => 'No se encontró el pedido.'];
            }

            $estadoActual = (string)$pedido['estado_actual'];
            $faseActual = (string)$pedido['fase'];
            $requierePreparacion = ((int)$pedido['requiere_preparacion'] === 1);

            if ($faseActual !== 'pedido') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_ACTUALIZABLE', 'mensaje' => 'Este pedido aún no está en una fase actualizable por el vendedor.'];
            }

            $transiciones = $this->obtenerMapaTransicionesVendedor($requierePreparacion);

            if (!isset($transiciones[$estadoActual]) || !in_array($nuevoEstado, $transiciones[$estadoActual], true)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'TRANSICION_INVALIDA', 'mensaje' => 'La transición de estado no es válida.'];
            }

            $cerrar = ($nuevoEstado === 'cancelado_vendedor') ? 1 : 0;

            $up = $this->dblink->prepare("\n                UPDATE pedido\n                SET\n                    estado_actual = :estado_actual,\n                    motivo_estado = :motivo_estado,\n                    fecha_cierre = CASE WHEN :cerrar = 1 THEN NOW() ELSE fecha_cierre END\n                WHERE codigo_pedido = :codigo_pedido\n            ");
            $up->bindValue(':estado_actual', $nuevoEstado, PDO::PARAM_STR);
            $up->bindValue(':motivo_estado', $this->motivoEstadoPorNuevoEstado($nuevoEstado), PDO::PARAM_STR);
            $up->bindValue(':cerrar', $cerrar, PDO::PARAM_INT);
            $up->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
            $up->execute();

            $this->registrarHistorialEstado(
                $codigoPedido,
                $faseActual,
                $estadoActual,
                $faseActual,
                $nuevoEstado,
                $codigoUsuarioVendedor,
                'vendedor',
                'actualizacion_estado_pedido',
                $this->motivoEstadoPorNuevoEstado($nuevoEstado)
            );

            if ($nuevoEstado === 'cancelado_vendedor') {
                $this->devolverBilleteraSiCorresponde($pedido, 'cancelado_vendedor');
                $this->moverSiguienteColaAPendiente($codigoUsuarioVendedor);
            }

            $pedidoActualizado = $this->obtenerPedidoVendedor($codigoPedido, $codigoUsuarioVendedor, false);

            $this->dblink->commit();

            return ['ok' => true, 'data' => $pedidoActualizado ? $this->formatearPedidoVendedor($pedidoActualizado) : null];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][Pedido][actualizarEstadoPedidoPorVendedor] ' . $e->getMessage());

            return ['ok' => false, 'error' => 'ERROR_ACTUALIZAR_ESTADO_PEDIDO', 'mensaje' => 'No se pudo actualizar el estado del pedido.'];
        }
    }

    // =========================================================
    // COMPRADOR - MIS PEDIDOS
    // =========================================================
    public function listarMisPedidosComprador(int $codigoUsuarioComprador): array
    {
        try {
            $this->sincronizarSolicitudesVencidas(null, $codigoUsuarioComprador, null);

            $sql = "
                SELECT
                    p.*, 
                    pr.titulo AS titulo_publicacion,
                    pr.imagen_portada,
                    TRIM(COALESCE(u.nombre, '')) AS nombre_vendedor
                FROM pedido p
                INNER JOIN producto pr ON pr.codigo_producto = p.codigo_producto
                INNER JOIN usuario u ON u.codigo_usuario = p.codigo_usuario_vendedor
                WHERE p.codigo_usuario_comprador = :codigo_usuario_comprador
                ORDER BY p.codigo_pedido DESC
            ";

            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_usuario_comprador', $codigoUsuarioComprador, PDO::PARAM_INT);
            $st->execute();

            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $data = [
                'pendientes'  => [],
                'en_proceso'  => [],
                'finalizados' => []
            ];

            foreach ($rows as $row) {
                $estado = (string)($row['estado_actual'] ?? '');
                $fase   = (string)($row['fase'] ?? '');

                $rowEstado = $row;
                $rowEstado['titulo_producto'] = (string)($row['titulo_publicacion'] ?? '');

                $estadoData = $this->construirDataEstadoSolicitud($rowEstado);

                $item = [
                    'codigo_pedido'                 => (int)$row['codigo_pedido'],
                    'codigo_producto'               => (int)$row['codigo_producto'],
                    'titulo_publicacion'            => (string)($row['titulo_publicacion'] ?? ''),
                    'titulo_producto'               => (string)($row['titulo_publicacion'] ?? ''),
                    'nombre_vendedor'               => (string)($row['nombre_vendedor'] ?? 'Vecino'),
                    'nombre_vecino'                 => (string)($row['nombre_vendedor'] ?? 'Vecino'),
                    'imagen_portada'                => (string)($row['imagen_portada'] ?? ''),
                    'fase'                          => $fase,
                    'estado_actual'                 => $estado,
                    'motivo_estado'                 => (string)($row['motivo_estado'] ?? ''),
                    'mensaje_estado'                => (string)($estadoData['mensaje_estado'] ?? ''),
                    'cantidad'                      => (int)($row['cantidad'] ?? 0),
                    'precio_unitario'               => (string)($row['costo_unitario'] ?? '0.00'),
                    'monto_total'                   => (string)($row['total'] ?? '0.00'),
                    'tipo_entrega'                  => ((string)($row['tipo_entrega'] ?? '') === 'programada' && !empty($row['fecha_hora_programada']))
                        ? 'Programada'
                        : 'Inmediata',
                    'tipo_entrega_raw'              => (string)($row['tipo_entrega'] ?? 'inmediata'),
                    'fecha_hora_programada'         => $row['fecha_hora_programada'],
                    'direccion_entrega'             => (string)($row['direccion_entrega'] ?? ''),
                    'mensaje_comprador'             => (string)($row['mensaje_comprador'] ?? ''),
                    'fecha_hora'                    => $row['created_at'] ?? null,
                    'created_at'                    => $row['created_at'] ?? null,
                    'fecha_limite_respuesta'        => $row['fecha_limite_respuesta'] ?? null,
                    'requiere_preparacion'          => (int)($row['requiere_preparacion'] ?? 0),
                    'descuento_billetera_aplicado'  => (int)($row['descuento_billetera_aplicado'] ?? 0),
                    'devolucion_billetera_aplicada' => (int)($row['devolucion_billetera_aplicada'] ?? 0),
                    'monto_descontado_billetera'    => (string)($row['monto_descontado_billetera'] ?? '0.00'),
                    'posicion_cola'                 => (int)($row['posicion_cola'] ?? 0),
                    'puede_confirmar_cola'          => (int)($estadoData['puede_confirmar_cola'] ?? 0),
                    'puede_cancelar'                => (int)($estadoData['puede_cancelar'] ?? 0),
                    'finalizado'                    => (int)($estadoData['finalizado'] ?? 0),
                    'segundos_restantes'            => (int)($estadoData['segundos_restantes'] ?? 0),
                    'segundos_para_cancelar_restantes' => (int)($estadoData['segundos_para_cancelar_restantes'] ?? 0),
                ];

                if ($fase === 'solicitud' && in_array($estado, [
                    'pendiente_vendedor',
                    'cola_pendiente_confirmacion',
                    'cola_aceptada'
                ], true)) {
                    $data['pendientes'][] = $item;
                    continue;
                }

                if (in_array($estado, [
                    'en_preparacion',
                    'despachando',
                    'listo_para_entrega',
                    'en_camino',
                    'en_punto_entrega',
                    'entregado_vendedor'
                ], true)) {
                    $data['en_proceso'][] = $item;
                    continue;
                }

                $data['finalizados'][] = $item;
            }

            return [
                'ok'   => true,
                'data' => $data
            ];
        } catch (Throwable $e) {
            error_log('[EV][Pedido][listarMisPedidosComprador] ' . $e->getMessage());

            return [
                'ok'      => false,
                'error'   => 'ERROR_LISTAR_MIS_PEDIDOS_COMPRADOR',
                'mensaje' => 'No se pudo obtener la lista de pedidos del comprador.'
            ];
        }
    }

    public function confirmarEntregaPorComprador(int $codigoPedido, int $codigoUsuarioComprador): array
    {
        try {
            $this->dblink->beginTransaction();

            $pedido = $this->obtenerSolicitudComprador($codigoPedido, $codigoUsuarioComprador, true);

            if (!$pedido) {
                $this->dblink->rollBack();
                return [
                    'ok'      => false,
                    'error'   => 'PEDIDO_NO_ENCONTRADO',
                    'mensaje' => 'No se encontró el pedido.'
                ];
            }

            if ((string)$pedido['estado_actual'] !== 'entregado_vendedor') {
                $this->dblink->rollBack();
                return [
                    'ok'      => false,
                    'error'   => 'ESTADO_NO_CONFIRMABLE',
                    'mensaje' => 'Este pedido aún no puede confirmarse.'
                ];
            }

            $up = $this->dblink->prepare("\n                UPDATE pedido\n                SET\n                    estado_actual = 'entrega_confirmada_comprador',\n                    motivo_estado = 'El comprador confirmó la entrega del pedido.',\n                    fecha_cierre = NOW()\n                WHERE codigo_pedido = :codigo_pedido\n            ");
            $up->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
            $up->execute();

            $this->registrarHistorialEstado(
                $codigoPedido,
                (string)$pedido['fase'],
                (string)$pedido['estado_actual'],
                (string)$pedido['fase'],
                'entrega_confirmada_comprador',
                $codigoUsuarioComprador,
                'comprador',
                'confirmacion_entrega',
                'El comprador confirmó la entrega satisfactoria del pedido.'
            );

            $this->moverSiguienteColaAPendiente((int)$pedido['codigo_usuario_vendedor']);

            $this->dblink->commit();

            return [
                'ok'   => true,
                'data' => [
                    'codigo_pedido' => $codigoPedido,
                    'estado_actual' => 'entrega_confirmada_comprador'
                ]
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }

            error_log('[EV][Pedido][confirmarEntregaPorComprador] ' . $e->getMessage());

            return [
                'ok'      => false,
                'error'   => 'ERROR_CONFIRMAR_ENTREGA',
                'mensaje' => 'No se pudo confirmar la entrega del pedido.'
            ];
        }
    }

    // =========================================================
    // VALIDACIÓN PRODUCTO
    // =========================================================
    public function validarProductoParaSolicitud(int $codigoProducto, int $codigoUsuarioComprador): array
    {
        $resComprador = $this->obtenerResidenciaActivaUsuario($codigoUsuarioComprador);
        if (!$resComprador) {
            return [
                'ok'      => false,
                'error'   => 'SIN_RESIDENCIA_ACTIVA',
                'mensaje' => 'No tienes una residencia activa para solicitar pedidos.'
            ];
        }

        $sql = "
            SELECT
                p.codigo_producto,
                p.titulo,
                p.descripcion,
                p.precio,
                p.visible,
                p.codigo_usuario AS codigo_usuario_vendedor,
                p.codigo_tipo,
                p.codigo_categoria,
                p.tipo_atencion_producto,
                p.imagen_portada,
                p.tipo_conjunto_publicacion,
                p.codigo_condominio_publicacion,
                p.codigo_urbanizacion_publicacion,
                p.estado_residencial_publicacion,
                u.estado AS estado_vendedor,
                COALESCE(u.disponibilidad_pedidos, 0) AS disponibilidad_pedidos_vendedor,
                TRIM(COALESCE(u.nombre, '')) AS nombre_vendedor,
                t.nombre AS tipo_nombre,
                c.nombre AS categoria_nombre
            FROM producto p
            INNER JOIN usuario u ON u.codigo_usuario = p.codigo_usuario
            LEFT JOIN tipo t ON t.codigo_tipo = p.codigo_tipo
            LEFT JOIN categoria c ON c.codigo_categoria = p.codigo_categoria
            WHERE p.codigo_producto = :p
            LIMIT 1
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':p', $codigoProducto, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return ['ok' => false, 'error' => 'PRODUCTO_NO_ENCONTRADO', 'mensaje' => 'La publicación ya no está disponible.'];
        }

        $codigoVendedor = (int)($row['codigo_usuario_vendedor'] ?? 0);
        if ($codigoVendedor === $codigoUsuarioComprador) {
            return ['ok' => false, 'error' => 'PRODUCTO_PROPIO', 'mensaje' => 'No puedes solicitar un pedido sobre tu propia publicación.'];
        }

        if ((int)($row['visible'] ?? 0) !== 2) {
            return ['ok' => false, 'error' => 'PRODUCTO_NO_APROBADO', 'mensaje' => 'La publicación ya no está disponible para pedidos.'];
        }

        if ((string)($row['estado_residencial_publicacion'] ?? '') !== 'activa') {
            return ['ok' => false, 'error' => 'PUBLICACION_NO_VIGENTE', 'mensaje' => 'La publicación ya no pertenece a una residencia activa.'];
        }

        if ((int)($row['estado_vendedor'] ?? 0) !== 2) {
            return ['ok' => false, 'error' => 'VENDEDOR_NO_HABILITADO', 'mensaje' => 'El vendedor no se encuentra habilitado en este momento.'];
        }

        if ((int)($row['disponibilidad_pedidos_vendedor'] ?? 0) !== 1) {
            return ['ok' => false, 'error' => 'VENDEDOR_NO_DISPONIBLE', 'mensaje' => 'El vendedor no está disponible para recibir pedidos en este momento.'];
        }

        if (!$this->coincideResidenciaPublicacionConComprador($resComprador, $row)) {
            return [
                'ok'      => false,
                'error'   => 'PUBLICACION_FUERA_DE_RESIDENCIA',
                'mensaje' => 'La publicación ya no pertenece a tu condominio o urbanización activa.'
            ];
        }

        return [
            'ok'   => true,
            'data' => [
                'codigo_producto'          => (int)$row['codigo_producto'],
                'titulo'                   => (string)($row['titulo'] ?? ''),
                'descripcion'              => (string)($row['descripcion'] ?? ''),
                'precio'                   => (float)($row['precio'] ?? 0),
                'codigo_usuario_vendedor'  => $codigoVendedor,
                'nombre_vendedor'          => (string)($row['nombre_vendedor'] ?? 'Vecino'),
                'codigo_tipo'              => (int)($row['codigo_tipo'] ?? 0),
                'codigo_categoria'         => (int)($row['codigo_categoria'] ?? 0),
                'tipo_nombre'              => (string)($row['tipo_nombre'] ?? ''),
                'categoria_nombre'         => (string)($row['categoria_nombre'] ?? ''),
                'imagen_portada'           => (string)($row['imagen_portada'] ?? ''),
                'requiere_preparacion'     => ((string)($row['tipo_atencion_producto'] ?? '') === 'requiere_preparacion') ? 1 : 0
            ]
        ];
    }
}
