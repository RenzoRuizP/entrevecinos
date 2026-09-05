<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';
require_once __DIR__ . '/Calificacion.php';
require_once __DIR__ . '/Notificacion.php';
require_once __DIR__ . '/ConfiguracionPlataforma.php';

class Pedido extends Conexion
{
    private const SEGUNDOS_CANCELACION = 120;
    private const SEGUNDOS_TIMEOUT = 240;
    private const MINUTOS_GRACIA_FECHA_PROGRAMADA = 1;
    private const SEGUNDOS_RECOJO = 360;
    private const PENALIDAD_CANCELACION_COMPRADOR = 1.00;
    private const COMISION_EV_PORCENTAJE_FALLBACK = 0.10;


    // =========================================================
    // REGLAS EV 2.0: comisiones, penalidades, recojo y alertas
    // =========================================================

    private function esProductoPreparadoPedido(array $pedido): bool
    {
        return (int)($pedido['requiere_preparacion'] ?? 0) === 1;
    }

    private function pedidoFueAceptado(array $pedido): bool
    {
        $fechaAceptacion = trim((string)($pedido['fecha_aceptacion'] ?? ''));
        if ($fechaAceptacion !== '') {
            return true;
        }

        // No toda fila en fase "pedido" fue aceptada: rechazo, timeout y una
        // cancelación durante la espera también cierran la solicitud en esa fase.
        return in_array((string)($pedido['estado_actual'] ?? ''), [
            'en_preparacion',
            'despachando',
            'listo_para_entrega',
            'en_camino',
            'en_punto_entrega',
            'entregado_vendedor',
            'entrega_confirmada_comprador',
            'cancelado_vendedor'
        ], true);
    }

    private function baseComisionPedido(array $pedido): float
    {
        $cantidad = max(1, (int)($pedido['cantidad'] ?? 1));
        $unitario = (float)($pedido['costo_unitario'] ?? 0);

        if ($unitario <= 0) {
            $unitario = (float)($pedido['precio_unitario'] ?? 0);
        }

        if ($unitario > 0) {
            return round($unitario * $cantidad, 2);
        }

        $total = (float)($pedido['total'] ?? $pedido['monto_total'] ?? 0);
        $penalidad = (float)($pedido['penalidad_comprador_monto'] ?? 0);

        return max(0.00, round($total - $penalidad, 2));
    }

    /**
     * Resuelve el alcance comercial de una publicación de producto. La misma
     * fuente se usa para aplicar comisiones y débitos de billetera, evitando
     * que una configuración específica de condominio o urbanización quede
     * reemplazada por la regla global.
     */
    private function obtenerAlcanceConfiguracionProducto(int $codigoProducto, array $contexto = []): array
    {
        $publicacion = $contexto;
        $tieneAlcance = trim((string)($publicacion['tipo_conjunto_publicacion'] ?? '')) !== '';

        if (!$tieneAlcance && $codigoProducto > 0) {
            $sql = "
                SELECT
                    tipo_conjunto_publicacion,
                    codigo_condominio_publicacion,
                    codigo_urbanizacion_publicacion
                FROM producto
                WHERE codigo_producto = :codigo_producto
                LIMIT 1
            ";
            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_producto', $codigoProducto, PDO::PARAM_INT);
            $st->execute();
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (is_array($row)) {
                $publicacion = array_merge($publicacion, $row);
            }
        }

        return (new ConfiguracionPlataforma())->obtenerAlcancePublicacion($publicacion);
    }

    private function obtenerReglaMonetizacionProducto(string $clave, int $codigoProducto, array $contexto = []): array
    {
        $alcance = $this->obtenerAlcanceConfiguracionProducto($codigoProducto, $contexto);
        return (new ConfiguracionPlataforma())->obtenerMonetizacionPorAlcance(
            $clave,
            (string)$alcance['tipo_alcance'],
            (int)$alcance['codigo_alcance']
        );
    }

    private function obtenerPorcentajeComisionProducto(array $pedido): float
    {
        try {
            $regla = $this->obtenerReglaMonetizacionProducto(
                ConfiguracionPlataforma::MON_COMISION_PRODUCTO,
                (int)($pedido['codigo_producto'] ?? 0),
                $pedido
            );
            $porcentaje = (float)($regla['valor_decimal'] ?? (self::COMISION_EV_PORCENTAJE_FALLBACK * 100));
            return max(0.0, min(100.0, $porcentaje)) / 100;
        } catch (Throwable $e) {
            error_log('[EV][Pedido][obtenerPorcentajeComisionProducto] ' . $e->getMessage());
            return self::COMISION_EV_PORCENTAJE_FALLBACK;
        }
    }

    private function actualizarEstadoComisionPedido(int $codigoPedido, float $monto, bool $aplicada, bool $pendiente): void
    {
        $sql = "
            UPDATE pedido
            SET
                comision_ev_monto = :monto,
                comision_ev_aplicada = :aplicada,
                comision_ev_pendiente = :pendiente
            WHERE codigo_pedido = :codigo_pedido
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':monto', round(max(0.0, $monto), 2));
        $st->bindValue(':aplicada', $aplicada ? 1 : 0, PDO::PARAM_INT);
        $st->bindValue(':pendiente', $pendiente ? 1 : 0, PDO::PARAM_INT);
        $st->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
        $st->execute();
    }

    private function obtenerConceptoMovimiento(string $origen, string $descripcion): string
    {
        $concepto = trim($origen);
        if ($concepto === '') {
            $concepto = trim($descripcion);
        }
        if ($concepto === '') {
            $concepto = 'Movimiento de billetera';
        }
        return mb_substr($concepto, 0, 150, 'UTF-8');
    }

    private function obtenerPenalidadPendienteComprador(int $codigoUsuarioComprador): float
    {
        if ($codigoUsuarioComprador <= 0) return 0.00;

        $sql = "
            SELECT COALESCE(SUM(monto), 0)
            FROM usuario_penalidad
            WHERE codigo_usuario = :codigo_usuario
              AND estado = 'pendiente'
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario', $codigoUsuarioComprador, PDO::PARAM_INT);
        $st->execute();

        return round((float)$st->fetchColumn(), 2);
    }

    private function reservarPenalidadesPendientesParaPedido(int $codigoUsuarioComprador, int $codigoPedido): float
    {
        if ($codigoUsuarioComprador <= 0 || $codigoPedido <= 0) return 0.00;

        $total = $this->obtenerPenalidadPendienteComprador($codigoUsuarioComprador);
        if ($total <= 0) return 0.00;

        $sql = "
            UPDATE usuario_penalidad
            SET estado = 'reservada', codigo_pedido_aplicado = :codigo_pedido, updated_at = NOW()
            WHERE codigo_usuario = :codigo_usuario
              AND estado = 'pendiente'
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
        $st->bindValue(':codigo_usuario', $codigoUsuarioComprador, PDO::PARAM_INT);
        $st->execute();

        return $total;
    }

    private function liberarPenalidadesReservadasPedido(int $codigoPedido): void
    {
        if ($codigoPedido <= 0) return;

        $sql = "
            UPDATE usuario_penalidad
            SET estado = 'pendiente', codigo_pedido_aplicado = NULL, updated_at = NOW()
            WHERE codigo_pedido_aplicado = :codigo_pedido
              AND estado = 'reservada'
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
        $st->execute();
    }

    private function aplicarPenalidadesReservadasPedido(int $codigoPedido): void
    {
        if ($codigoPedido <= 0) return;

        $sql = "
            UPDATE usuario_penalidad
            SET estado = 'aplicada', fecha_aplicacion = NOW(), updated_at = NOW()
            WHERE codigo_pedido_aplicado = :codigo_pedido
              AND estado = 'reservada'
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
        $st->execute();
    }

    private function registrarPenalidadCompradorPendiente(
        int $codigoUsuarioComprador,
        int $codigoPedidoOrigen,
        string $motivoClave,
        string $descripcion
    ): void {
        if ($codigoUsuarioComprador <= 0 || $codigoPedidoOrigen <= 0) return;

        $sqlExiste = "
            SELECT 1
            FROM usuario_penalidad
            WHERE codigo_usuario = :codigo_usuario
              AND codigo_pedido_origen = :codigo_pedido_origen
              AND estado IN ('pendiente', 'reservada', 'aplicada')
            LIMIT 1
        ";
        $stExiste = $this->dblink->prepare($sqlExiste);
        $stExiste->bindValue(':codigo_usuario', $codigoUsuarioComprador, PDO::PARAM_INT);
        $stExiste->bindValue(':codigo_pedido_origen', $codigoPedidoOrigen, PDO::PARAM_INT);
        $stExiste->execute();

        if ($stExiste->fetchColumn()) return;

        $sql = "
            INSERT INTO usuario_penalidad
            (
                codigo_usuario,
                codigo_pedido_origen,
                monto,
                estado,
                motivo_clave,
                descripcion
            )
            VALUES
            (
                :codigo_usuario,
                :codigo_pedido_origen,
                :monto,
                'pendiente',
                :motivo_clave,
                :descripcion
            )
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario', $codigoUsuarioComprador, PDO::PARAM_INT);
        $st->bindValue(':codigo_pedido_origen', $codigoPedidoOrigen, PDO::PARAM_INT);
        $st->bindValue(':monto', self::PENALIDAD_CANCELACION_COMPRADOR);
        $st->bindValue(':motivo_clave', mb_substr($motivoClave, 0, 80, 'UTF-8'), PDO::PARAM_STR);
        $st->bindValue(':descripcion', mb_substr($descripcion, 0, 255, 'UTF-8'), PDO::PARAM_STR);
        $st->execute();
    }

    private function registrarComisionPendienteVendedor(int $codigoPedido, int $codigoUsuarioVendedor, float $monto): void
    {
        if ($codigoPedido <= 0 || $codigoUsuarioVendedor <= 0 || $monto <= 0) return;

        $sql = "
            INSERT INTO vendedor_comision_ev
            (
                codigo_pedido,
                codigo_usuario_vendedor,
                monto,
                estado
            )
            VALUES
            (
                :codigo_pedido,
                :codigo_usuario_vendedor,
                :monto,
                'pendiente'
            )
            ON DUPLICATE KEY UPDATE
                monto = VALUES(monto),
                estado = IF(estado = 'cobrada', estado, 'pendiente'),
                updated_at = NOW()
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
        $st->bindValue(':codigo_usuario_vendedor', $codigoUsuarioVendedor, PDO::PARAM_INT);
        $st->bindValue(':monto', $monto);
        $st->execute();
    }

    private function aplicarComisionEVPorAceptacion(array $pedido): void
    {
        $codigoPedido = (int)($pedido['codigo_pedido'] ?? 0);
        $codigoVendedor = (int)($pedido['codigo_usuario_vendedor'] ?? 0);

        if ($codigoPedido <= 0 || $codigoVendedor <= 0) return;

        if ((int)($pedido['comision_ev_aplicada'] ?? 0) === 1 || (int)($pedido['comision_ev_pendiente'] ?? 0) === 1) {
            return;
        }

        $base = $this->baseComisionPedido($pedido);
        $porcentaje = $this->obtenerPorcentajeComisionProducto($pedido);
        $monto = round($base * $porcentaje, 2);

        // Una comisión configurada en 0 % queda registrada como resuelta y no
        // genera movimientos ni deudas pendientes para el vendedor.
        if ($monto <= 0 || $porcentaje <= 0) {
            $this->actualizarEstadoComisionPedido($codigoPedido, 0.0, true, false);
            return;
        }

        // Si la billetera no está operativa en la comunidad del vendedor, la
        // comisión se conserva como pendiente en lugar de efectuar un débito
        // oculto sobre un módulo deshabilitado.
        $estadoBilleteraVendedor = (new ConfiguracionPlataforma())->obtenerEstadoBilleteraUsuario($codigoVendedor);
        if (!(bool)($estadoBilleteraVendedor['billetera_disponible'] ?? false)) {
            $this->registrarComisionPendienteVendedor($codigoPedido, $codigoVendedor, $monto);
            $this->actualizarEstadoComisionPedido($codigoPedido, $monto, false, true);
            return;
        }

        $billetera = $this->obtenerOBilleteraBloqueada($codigoVendedor);
        $codigoBilletera = (int)$billetera['codigo_billetera'];
        $saldoAntes = (float)$billetera['saldo_actual'];

        if ($saldoAntes >= $monto) {
            $saldoDespues = round($saldoAntes - $monto, 2);

            $this->registrarMovimientoBilletera(
                $codigoBilletera,
                'D',
                $monto,
                $saldoAntes,
                $saldoDespues,
                'Comisión EV por pedido aceptado',
                'COMISION_EV_PEDIDO',
                $codigoPedido
            );
            $this->actualizarSaldoBilletera($codigoBilletera, $saldoDespues);

            $this->actualizarEstadoComisionPedido($codigoPedido, $monto, true, false);
            return;
        }

        $this->registrarComisionPendienteVendedor($codigoPedido, $codigoVendedor, $monto);

        $this->actualizarEstadoComisionPedido($codigoPedido, $monto, false, true);
    }

    private function textoEstadoPedidoEV(string $estado): string
    {
        return match ($estado) {
            'pendiente_vendedor' => 'Pendiente de atención',
            'en_preparacion' => 'En preparación',
            'despachando' => 'Despachando',
            'listo_para_entrega' => 'Listo para entrega',
            'en_camino' => 'En camino',
            'en_punto_entrega' => 'En punto de recojo',
            'entregado_vendedor' => 'Entregado por vendedor',
            'entrega_confirmada_comprador' => 'Entrega confirmada',
            'cancelado_comprador' => 'Cancelado por comprador',
            'cancelado_vendedor' => 'Cancelado por vendedor',
            'rechazado_vendedor' => 'Rechazado por vendedor',
            'sin_respuesta_vendedor' => 'Sin respuesta del vendedor',
            default => 'Estado actualizado',
        };
    }

    private function mensajeEstadoCompradorEV(string $estado, array $pedido): string
    {
        $titulo = trim((string)($pedido['titulo_producto'] ?? 'tu pedido'));
        $montoDebitado = round((float)($pedido['monto_descontado_billetera'] ?? 0), 2);
        $devolucionAplicada = (int)($pedido['devolucion_billetera_aplicada'] ?? 0) === 1;
        $notaDevolucion = $montoDebitado > 0 && $devolucionAplicada
            ? ' EV devolvió S/ ' . number_format($montoDebitado, 2, '.', '') . ' a tu billetera.'
            : '';

        return match ($estado) {
            'en_preparacion' => "El vendedor aceptó tu pedido de {$titulo} y ya está en preparación.",
            'despachando' => "El vendedor aceptó tu pedido de {$titulo} y está despachándolo.",
            'listo_para_entrega' => "Tu pedido de {$titulo} ya está listo para entrega.",
            'en_camino' => "Tu pedido de {$titulo} ya va en camino.",
            'en_punto_entrega' => "Tu pedido de {$titulo} llegó al punto de recojo. Recuerda que tienes 6 minutos para recibirlo.",
            'entregado_vendedor' => "El vendedor marcó tu pedido de {$titulo} como entregado. Confirma la recepción cuando corresponda.",
            'cancelado_vendedor' => (string)($pedido['motivo_estado'] ?? 'El vendedor canceló el pedido.') . $notaDevolucion,
            'rechazado_vendedor' => (string)($pedido['motivo_estado'] ?? 'El vendedor rechazó tu solicitud.') . $notaDevolucion,
            'sin_respuesta_vendedor' => 'El vendedor no respondió dentro del tiempo esperado.' . $notaDevolucion,
            default => (string)($pedido['motivo_estado'] ?? 'Tu pedido cambió de estado.'),
        };
    }

    private function registrarNotificacionPedido(
        int $codigoUsuario,
        string $subcategoria,
        int $codigoPedido,
        string $titulo,
        string $mensaje,
        array $payload = []
    ): void {
        if ($codigoUsuario <= 0 || $codigoPedido <= 0) return;

        try {
            $payload['codigo_pedido'] = $codigoPedido;
            $notificacion = new Notificacion($this->dblink);
            $notificacion->crearOActualizarNoLeida([
                'codigo_usuario' => $codigoUsuario,
                'categoria' => Notificacion::CAT_PEDIDO,
                'subcategoria' => $subcategoria,
                'referencia_id' => $codigoPedido,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'payload' => $payload,
            ]);
        } catch (Throwable $e) {
            error_log('[EV][Pedido::registrarNotificacionPedido] ' . $e->getMessage());
        }
    }

    private function registrarAlertaAvanceComprador(array $pedido, string $estadoNuevo): void
    {
        $codigoComprador = (int)($pedido['codigo_usuario_comprador'] ?? 0);
        $codigoPedido = (int)($pedido['codigo_pedido'] ?? 0);
        $estadoNuevo = strtolower(trim($estadoNuevo));

        if ($codigoComprador <= 0 || $codigoPedido <= 0 || $estadoNuevo === '') {
            return;
        }

        $titulo = $this->textoEstadoPedidoEV($estadoNuevo);
        $mensaje = $this->mensajeEstadoCompradorEV($estadoNuevo, $pedido);

        /*
        * EV:
        * Antes se usaba siempre "avance_estado".
        * Eso impedía crear una nueva notificación si el comprador aún tenía
        * una notificación no leída del mismo pedido.
        *
        * Ahora cada estado tiene su propia subcategoría:
        * avance_estado_en_camino
        * avance_estado_en_punto_entrega
        * avance_estado_entregado_vendedor
        */
        $subcategoria = 'avance_estado_' . preg_replace('/[^a-z0-9_]/', '_', $estadoNuevo);
        $subcategoria = mb_substr($subcategoria, 0, 50, 'UTF-8');

        $this->registrarNotificacionPedido(
            $codigoComprador,
            $subcategoria,
            $codigoPedido,
            $titulo,
            $mensaje,
            [
                'subcategoria_base' => 'avance_estado',
                'estado_actual' => $estadoNuevo,
                'rol_destino' => 'comprador',
                'ruta' => '/mis-pedidos-comprador',
                'titulo_producto' => (string)($pedido['titulo_producto'] ?? '')
            ]
        );
    }

    private function registrarAlertaPedidoVendedor(array $pedido, string $subcategoria, string $titulo, string $mensaje): void
    {
        $codigoVendedor = (int)($pedido['codigo_usuario_vendedor'] ?? 0);
        $codigoPedido = (int)($pedido['codigo_pedido'] ?? 0);
        if ($codigoVendedor <= 0 || $codigoPedido <= 0) return;

        $this->registrarNotificacionPedido(
            $codigoVendedor,
            $subcategoria,
            $codigoPedido,
            $titulo,
            $mensaje,
            [
                'rol_destino' => 'vendedor',
                'ruta' => '/mis-pedidos-vendedor',
                'titulo_producto' => (string)($pedido['titulo_producto'] ?? '')
            ]
        );
    }

    private function segundosRecojoRestantes(array $pedido): int
    {
        $estado = (string)($pedido['estado_actual'] ?? '');
        if ($estado !== 'en_punto_entrega') return 0;

        $limite = trim((string)($pedido['fecha_limite_recojo'] ?? ''));
        if ($limite === '') return 0;

        try {
            // EV: comparar contra NOW() de MySQL/MariaDB evita desfases de zona horaria
            // entre PHP (UTC) y la BD/local (Lima). Si se usa time(), el sistema puede
            // considerar vencidos los 6 minutos inmediatamente.
            $st = $this->dblink->prepare("SELECT GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), :fecha_limite))");
            $st->bindValue(':fecha_limite', $limite, PDO::PARAM_STR);
            $st->execute();
            return max(0, (int)$st->fetchColumn());
        } catch (Throwable $e) {
            error_log('[EV][Pedido][segundosRecojoRestantes] ' . $e->getMessage());

            $ts = strtotime($limite);
            if ($ts === false) return 0;

            return max(0, $ts - time());
        }
    }

    private function puedeVendedorCancelarPorNoRecojo(array $pedido): bool
    {
        return (string)($pedido['estado_actual'] ?? '') === 'en_punto_entrega'
            && trim((string)($pedido['fecha_limite_recojo'] ?? '')) !== ''
            && $this->segundosRecojoRestantes($pedido) <= 0;
    }

    private function puedeVendedorCancelarPedido(array $pedido): bool
    {
        if ((string)($pedido['fase'] ?? '') !== 'pedido') {
            return false;
        }

        return in_array(
            (string)($pedido['estado_actual'] ?? ''),
            [
                'en_preparacion',
                'despachando',
                'listo_para_entrega',
                'en_camino',
                'en_punto_entrega',
            ],
            true
        );
    }

    private function motivoCancelacionAtribuibleComprador(string $clave): bool
    {
        return in_array(
            trim($clave),
            [
                'comprador_no_se_presento',
                'comprador_no_responde',
                'comprador_rechazo_recepcion',
                'no_se_pudo_concretar',
            ],
            true
        );
    }


    private function asegurarVentanaRecojoSiFalta(array $pedido): array
    {
        $codigoPedido = (int)($pedido['codigo_pedido'] ?? 0);
        $estado = (string)($pedido['estado_actual'] ?? '');
        $limite = trim((string)($pedido['fecha_limite_recojo'] ?? ''));

        if ($codigoPedido <= 0 || $estado !== 'en_punto_entrega' || $limite !== '') {
            return $pedido;
        }

        try {
            $sql = "
                UPDATE pedido
                SET
                    fecha_punto_recojo = COALESCE(fecha_punto_recojo, NOW()),
                    fecha_limite_recojo = DATE_ADD(COALESCE(fecha_punto_recojo, NOW()), INTERVAL " . self::SEGUNDOS_RECOJO . " SECOND)
                WHERE codigo_pedido = :codigo_pedido
                  AND estado_actual = 'en_punto_entrega'
                  AND fecha_limite_recojo IS NULL
            ";
            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
            $st->execute();

            $stSel = $this->dblink->prepare("SELECT fecha_punto_recojo, fecha_limite_recojo FROM pedido WHERE codigo_pedido = :codigo_pedido LIMIT 1");
            $stSel->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
            $stSel->execute();
            $row = $stSel->fetch(PDO::FETCH_ASSOC) ?: [];

            if (!empty($row['fecha_punto_recojo'])) {
                $pedido['fecha_punto_recojo'] = $row['fecha_punto_recojo'];
            }
            if (!empty($row['fecha_limite_recojo'])) {
                $pedido['fecha_limite_recojo'] = $row['fecha_limite_recojo'];
            }
        } catch (Throwable $e) {
            error_log('[EV][Pedido][asegurarVentanaRecojoSiFalta] ' . $e->getMessage());
        }

        return $pedido;
    }

    private function motivoCancelacionVendedorTexto(string $clave, string $detalle = ''): string
    {
        $base = match ($clave) {
            'sin_stock' => 'El vendedor canceló el pedido porque ya no cuenta con disponibilidad del producto.',
            'no_puede_preparar' => 'El vendedor canceló el pedido porque no puede completar la preparación.',
            'problema_entrega' => 'El vendedor canceló el pedido por un inconveniente para realizar la entrega.',
            'error_publicacion' => 'El vendedor canceló el pedido por un error en la publicación o disponibilidad informada.',
            'otro_vendedor' => 'El vendedor canceló el pedido por otro motivo.',
            'comprador_no_se_presento' => 'El comprador no se presentó en el punto de recojo.',
            'comprador_no_responde' => 'El comprador no respondió para concretar la entrega.',
            'comprador_rechazo_recepcion' => 'El comprador rechazó recibir el pedido.',
            'no_se_pudo_concretar' => 'No se pudo concretar la entrega.',
            'otro' => 'El vendedor canceló el pedido por otro motivo.',
            default => 'El vendedor canceló el pedido.',
        };

        $detalle = trim($detalle);
        return $detalle !== '' ? $base . ' Detalle: ' . $detalle : $base;
    }

    public function listarAlertasPedidoUsuario(int $codigoUsuario): array
    {
        try {
            if ($codigoUsuario <= 0) {
                return ['ok' => false, 'error' => 'USUARIO_INVALIDO', 'mensaje' => 'Usuario inválido.'];
            }

            // EV UX: si hay varias alertas pendientes del mismo pedido, solo se debe mostrar
            // la más reciente. Las anteriores se marcan como leídas para evitar modales duplicados
            // o alertas desfasadas, por ejemplo: en_punto_entrega seguido de entregado_vendedor.
            $subcategoriasModal = [
                'avance_estado_en_punto_entrega',
                'avance_estado_entregado_vendedor',
                'avance_estado_rechazado_vendedor',
                'avance_estado_cancelado_vendedor',
                'avance_estado_sin_respuesta_vendedor'
            ];

            $placeholders = implode(',', array_fill(0, count($subcategoriasModal), '?'));

            $sqlLimpiarObsoletas = "
                UPDATE notificacion n
                INNER JOIN (
                    SELECT referencia_id, MAX(codigo_notificacion) AS max_id
                    FROM notificacion
                    WHERE codigo_usuario = ?
                      AND categoria = 'pedido'
                      AND estado = 'no_leida'
                      AND referencia_id IS NOT NULL
                      AND subcategoria IN ({$placeholders})
                    GROUP BY referencia_id
                ) ult ON ult.referencia_id = n.referencia_id
                SET n.estado = 'leida', n.read_at = NOW()
                WHERE n.codigo_usuario = ?
                  AND n.categoria = 'pedido'
                  AND n.estado = 'no_leida'
                  AND n.subcategoria IN ({$placeholders})
                  AND n.codigo_notificacion < ult.max_id
            ";

            $paramsLimpiar = array_merge(
                [$codigoUsuario],
                $subcategoriasModal,
                [$codigoUsuario],
                $subcategoriasModal
            );

            $stLimpiar = $this->dblink->prepare($sqlLimpiarObsoletas);
            foreach ($paramsLimpiar as $i => $value) {
                $stLimpiar->bindValue($i + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stLimpiar->execute();

            $sql = "
                SELECT
                    codigo_notificacion,
                    subcategoria,
                    referencia_id,
                    titulo,
                    mensaje,
                    payload_json,
                    DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') AS fecha
                FROM notificacion
                WHERE codigo_usuario = ?
                  AND categoria = 'pedido'
                  AND estado = 'no_leida'
                  AND subcategoria IN ({$placeholders})
                ORDER BY codigo_notificacion DESC
                LIMIT 5
            ";

            $params = array_merge([$codigoUsuario], $subcategoriasModal);
            $st = $this->dblink->prepare($sql);
            foreach ($params as $i => $value) {
                $st->bindValue($i + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $st->execute();
            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($rows as &$row) {
                $payload = json_decode((string)($row['payload_json'] ?? '{}'), true);
                $row['payload'] = is_array($payload) ? $payload : [];
                unset($row['payload_json']);
            }
            unset($row);

            return ['ok' => true, 'data' => $rows];
        } catch (Throwable $e) {
            error_log('[EV][Pedido][listarAlertasPedidoUsuario] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_ALERTAS_PEDIDO', 'mensaje' => 'No se pudieron obtener las alertas de pedido.'];
        }
    }

    public function marcarAlertaPedidoLeida(int $codigoUsuario, int $codigoNotificacion): array
    {
        try {
            if ($codigoUsuario <= 0 || $codigoNotificacion <= 0) {
                return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'Parámetros inválidos.'];
            }

            $sql = "
                UPDATE notificacion
                SET estado = 'leida', read_at = NOW()
                WHERE codigo_notificacion = :codigo_notificacion
                  AND codigo_usuario = :codigo_usuario
                  AND categoria = 'pedido'
            ";
            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_notificacion', $codigoNotificacion, PDO::PARAM_INT);
            $st->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
            $st->execute();

            return ['ok' => true];
        } catch (Throwable $e) {
            error_log('[EV][Pedido][marcarAlertaPedidoLeida] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_MARCAR_ALERTA', 'mensaje' => 'No se pudo marcar la alerta como leída.'];
        }
    }

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
                $warningCount = is_array($errores) ? (int)($errores['warning_count'] ?? 0) : 0;
                $errorCount   = is_array($errores) ? (int)($errores['error_count'] ?? 0) : 0;

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
        $maximo = (clone $ahora)->modify('+7 days');

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
                'mensaje' => 'La fecha programada no puede superar 1 semana desde ahora.'
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

        $ins = $this->dblink->prepare("
            INSERT INTO billetera (codigo_usuario, saldo_actual)
            VALUES (:codigo_usuario, 0.00)
        ");
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
        $concepto = $this->obtenerConceptoMovimiento($origen, $descripcion);

        $sql = "
            INSERT INTO billetera_movimiento
            (
                codigo_billetera,
                tipo_movimiento,
                concepto,
                monto,
                saldo_antes,
                saldo_despues,
                saldo_anterior,
                saldo_posterior,
                descripcion,
                origen,
                codigo_referencia,
                referencia_tipo,
                referencia_id,
                es_promocional,
                fecha_expira
            )
            VALUES
            (
                :codigo_billetera,
                :tipo_movimiento,
                :concepto,
                :monto,
                :saldo_antes,
                :saldo_despues,
                :saldo_anterior,
                :saldo_posterior,
                :descripcion,
                :origen,
                :codigo_referencia,
                :referencia_tipo,
                :referencia_id,
                :es_promocional,
                :fecha_expira
            )
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_billetera', $codigoBilletera, PDO::PARAM_INT);
        $st->bindValue(':tipo_movimiento', $tipoMovimiento, PDO::PARAM_STR);
        $st->bindValue(':concepto', $concepto, PDO::PARAM_STR);
        $st->bindValue(':monto', $monto);
        $st->bindValue(':saldo_antes', $saldoAntes);
        $st->bindValue(':saldo_despues', $saldoDespues);
        $st->bindValue(':saldo_anterior', $saldoAntes);
        $st->bindValue(':saldo_posterior', $saldoDespues);
        $st->bindValue(':descripcion', mb_substr($descripcion, 0, 180, 'UTF-8'), PDO::PARAM_STR);
        $st->bindValue(':origen', $origen, PDO::PARAM_STR);

        if ($codigoReferencia !== null) {
            $st->bindValue(':codigo_referencia', $codigoReferencia, PDO::PARAM_INT);
            $st->bindValue(':referencia_tipo', $origen, PDO::PARAM_STR);
            $st->bindValue(':referencia_id', $codigoReferencia, PDO::PARAM_INT);
        } else {
            $st->bindValue(':codigo_referencia', null, PDO::PARAM_NULL);
            $st->bindValue(':referencia_tipo', null, PDO::PARAM_NULL);
            $st->bindValue(':referencia_id', null, PDO::PARAM_NULL);
        }

        $st->bindValue(':es_promocional', $esPromocional, PDO::PARAM_INT);
        $st->bindValue(':fecha_expira', $fechaExpira, $fechaExpira !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $st->execute();
    }


    private function actualizarSaldoBilletera(int $codigoBilletera, float $nuevoSaldo): void
    {
        $sql = "
            UPDATE billetera
            SET saldo_actual = :saldo_actual,
                saldo = :saldo
            WHERE codigo_billetera = :codigo_billetera
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':saldo_actual', $nuevoSaldo);
        $st->bindValue(':saldo', $nuevoSaldo);
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
                'ok'              => false,
                'saldo_actual'    => $saldoAntes,
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

        $up = $this->dblink->prepare("
            UPDATE pedido
            SET
                monto_descontado_billetera = :monto,
                descuento_billetera_aplicado = 1
            WHERE codigo_pedido = :codigo_pedido
        ");
        $up->bindValue(':monto', $monto);
        $up->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
        $up->execute();

        return [
            'ok'              => true,
            'saldo_actual'    => $saldoDespues,
            'monto_requerido' => $monto
        ];
    }

    private function descripcionDevolucionBilletera(string $contexto, string $tituloProducto): string
    {
        $tituloLimpio = $this->limpiarTituloParaMovimiento($tituloProducto);

        return match ($contexto) {
            'cancelado_comprador'     => 'Devolución por cancelación de solicitud: ' . $tituloLimpio,
            'rechazado_vendedor'      => 'Devolución por rechazo de solicitud: ' . $tituloLimpio,
            'cancelado_vendedor'      => 'Devolución por cancelación del vendedor: ' . $tituloLimpio,
            'sin_respuesta_vendedor'  => 'Devolución por solicitud sin respuesta del vendedor: ' . $tituloLimpio,
            default                   => 'Devolución por solicitud no concretada: ' . $tituloLimpio,
        };
    }

    /**
     * Comprueba si el pedido ya tiene una devolución registrada en la billetera.
     * Se contemplan ambos códigos de origen utilizados históricamente por EV para
     * evitar una doble acreditación en ambientes que hayan pasado por versiones
     * anteriores del módulo.
     */
    private function existeMovimientoDevolucionPedido(int $codigoBilletera, int $codigoPedido): bool
    {
        if ($codigoBilletera <= 0 || $codigoPedido <= 0) {
            return false;
        }

        $sql = "
            SELECT 1
            FROM billetera_movimiento
            WHERE codigo_billetera = :codigo_billetera
              AND tipo_movimiento = 'C'
              AND codigo_referencia = :codigo_pedido
              AND origen IN ('PEDIDO_SOLICITUD_DEVOLUCION', 'DEVOLUCION_PEDIDO_SOLICITUD')
            LIMIT 1
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_billetera', $codigoBilletera, PDO::PARAM_INT);
        $st->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
        $st->execute();

        return (bool)$st->fetchColumn();
    }

    /**
     * Devuelve el importe realmente debitado por una solicitud de producto
     * preparado. La operación se ejecuta dentro de la misma transacción del
     * pedido y sobre la billetera bloqueada con FOR UPDATE.
     *
     * Reglas EV:
     * - rechazo del vendedor      => devolución 100 %;
     * - cancelación del vendedor  => devolución 100 %;
     * - timeout del vendedor      => devolución 100 %;
     * - cancelación del comprador durante la ventana habilitada (>= 2 min y
     *   antes del timeout)         => devolución 100 %;
     * - nunca se acredita dos veces el mismo pedido.
     */
    private function devolverBilleteraSiCorresponde(array $pedido, string $contexto): void
    {
        $codigoPedido = (int)($pedido['codigo_pedido'] ?? 0);
        $codigoUsuarioComprador = (int)($pedido['codigo_usuario_comprador'] ?? 0);
        $descuentoAplicado = (int)($pedido['descuento_billetera_aplicado'] ?? 0) === 1;
        $devolucionAplicada = (int)($pedido['devolucion_billetera_aplicada'] ?? 0) === 1;
        $monto = round((float)($pedido['monto_descontado_billetera'] ?? 0), 2);
        $sinReembolso = (int)($pedido['sin_reembolso'] ?? 0) === 1;

        // Si la causa depende del vendedor o del timeout del sistema, el comprador
        // siempre recupera el dinero realmente debitado, incluso si el producto ya
        // había sido aceptado o estaba en preparación.
        $reembolsoObligatorio = in_array(
            $contexto,
            ['rechazado_vendedor', 'cancelado_vendedor', 'sin_respuesta_vendedor'],
            true
        );

        if ($sinReembolso && !$reembolsoObligatorio) {
            return;
        }

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

        // Defensa adicional de idempotencia: si por una versión anterior el
        // movimiento existe pero la bandera del pedido quedó desincronizada, no se
        // vuelve a acreditar el saldo; solo se repara la bandera del pedido.
        if ($this->existeMovimientoDevolucionPedido($codigoBilletera, $codigoPedido)) {
            $up = $this->dblink->prepare("
                UPDATE pedido
                SET devolucion_billetera_aplicada = 1
                WHERE codigo_pedido = :codigo_pedido
            ");
            $up->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
            $up->execute();
            return;
        }

        $saldoAntes = (float)$billetera['saldo_actual'];
        $saldoDespues = round($saldoAntes + $monto, 2);

        $this->registrarMovimientoBilletera(
            $codigoBilletera,
            'C',
            $monto,
            $saldoAntes,
            $saldoDespues,
            $this->descripcionDevolucionBilletera($contexto, (string)($pedido['titulo_producto'] ?? 'producto')),
            'PEDIDO_SOLICITUD_DEVOLUCION',
            $codigoPedido
        );

        $this->actualizarSaldoBilletera($codigoBilletera, $saldoDespues);

        $up = $this->dblink->prepare("
            UPDATE pedido
            SET devolucion_billetera_aplicada = 1
            WHERE codigo_pedido = :codigo_pedido
        ");
        $up->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
        $up->execute();
    }


    private function existeAcreditacionVendedorPedido(int $codigoBilletera, int $codigoPedido): bool
    {
        if ($codigoBilletera <= 0 || $codigoPedido <= 0) return false;

        $st = $this->dblink->prepare("
            SELECT 1
            FROM billetera_movimiento
            WHERE codigo_billetera = :codigo_billetera
              AND tipo_movimiento = 'C'
              AND codigo_referencia = :codigo_pedido
              AND origen = 'VENTA_PREPARADA_ACREDITADA'
            LIMIT 1
        ");
        $st->bindValue(':codigo_billetera', $codigoBilletera, PDO::PARAM_INT);
        $st->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
        $st->execute();
        return (bool)$st->fetchColumn();
    }

    /**
     * Acredita al vendedor únicamente cuando un pedido preparado, pagado
     * previamente desde la billetera del comprador, termina correctamente.
     * La operación es idempotente por pedido y ocurre dentro de la misma
     * transacción de la confirmación de entrega.
     */
    private function acreditarVendedorPorPedidoPreparado(array $pedido): void
    {
        $codigoPedido = (int)($pedido['codigo_pedido'] ?? 0);
        $codigoVendedor = (int)($pedido['codigo_usuario_vendedor'] ?? 0);
        $requierePreparacion = (int)($pedido['requiere_preparacion'] ?? 0) === 1;
        $debitoAplicado = (int)($pedido['descuento_billetera_aplicado'] ?? 0) === 1;
        $devolucionAplicada = (int)($pedido['devolucion_billetera_aplicada'] ?? 0) === 1;
        $yaAcreditado = (int)($pedido['acreditacion_vendedor_aplicada'] ?? 0) === 1;
        $monto = round((float)($pedido['monto_descontado_billetera'] ?? 0), 2);

        if (
            $codigoPedido <= 0 ||
            $codigoVendedor <= 0 ||
            !$requierePreparacion ||
            !$debitoAplicado ||
            $devolucionAplicada ||
            $monto <= 0
        ) {
            return;
        }

        $billetera = $this->obtenerOBilleteraBloqueada($codigoVendedor);
        $codigoBilletera = (int)$billetera['codigo_billetera'];

        if ($yaAcreditado || $this->existeAcreditacionVendedorPedido($codigoBilletera, $codigoPedido)) {
            $up = $this->dblink->prepare("
                UPDATE pedido
                SET acreditacion_vendedor_aplicada = 1,
                    monto_acreditado_vendedor = :monto
                WHERE codigo_pedido = :codigo_pedido
            ");
            $up->bindValue(':monto', $monto);
            $up->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
            $up->execute();
            return;
        }

        $saldoAntes = round((float)$billetera['saldo_actual'], 2);
        $saldoDespues = round($saldoAntes + $monto, 2);
        $titulo = $this->limpiarTituloParaMovimiento((string)($pedido['titulo_producto'] ?? 'producto'));

        $this->registrarMovimientoBilletera(
            $codigoBilletera,
            'C',
            $monto,
            $saldoAntes,
            $saldoDespues,
            'Ingreso por venta de producto preparado: ' . $titulo,
            'VENTA_PREPARADA_ACREDITADA',
            $codigoPedido
        );
        $this->actualizarSaldoBilletera($codigoBilletera, $saldoDespues);

        $up = $this->dblink->prepare("
            UPDATE pedido
            SET acreditacion_vendedor_aplicada = 1,
                monto_acreditado_vendedor = :monto
            WHERE codigo_pedido = :codigo_pedido
        ");
        $up->bindValue(':monto', $monto);
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

            $up = $this->dblink->prepare("
                UPDATE pedido
                SET
                    fase = 'pedido',
                    estado_actual = 'sin_respuesta_vendedor',
                    estado = 'sin_respuesta_vendedor',
                    motivo_estado = :motivo_estado,
                    fecha_cierre = NOW(),
                    fecha_limite_respuesta = NULL
                WHERE codigo_pedido = :codigo_pedido
            ");
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

            $this->liberarPenalidadesReservadasPedido($codigoPedido);
            $this->devolverBilleteraSiCorresponde($pedido, 'sin_respuesta_vendedor');
            $pedidoActualizado = $this->obtenerPedidoPorId($codigoPedido, false) ?: $pedido;
            $pedidoActualizado['motivo_estado'] = $motivoEstado;
            $this->registrarAlertaAvanceComprador($pedidoActualizado, 'sin_respuesta_vendedor');
            $this->dblink->commit();
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }

            error_log('[EV][Pedido][marcarSolicitudSinRespuestaVencida] ' . $e->getMessage());
        }
    }


    private function solicitudPendienteVencida(array $pedido): bool
    {
        if (
            (string)($pedido['fase'] ?? '') !== 'solicitud'
            || (string)($pedido['estado_actual'] ?? '') !== 'pendiente_vendedor'
        ) {
            return false;
        }

        $fechaLimite = trim((string)($pedido['fecha_limite_respuesta'] ?? ''));
        if ($fechaLimite === '') {
            return false;
        }

        $tsLimite = strtotime($fechaLimite);
        return $tsLimite !== false && $tsLimite <= time();
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
                $tsInicioVentana = $tsLimite - self::SEGUNDOS_TIMEOUT;
                return max(0, (int)(time() - $tsInicioVentana));
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

        return 0;
    }

    private function puedeCancelarSolicitudSegunRegla(array $pedido): bool
    {
        $estado = (string)($pedido['estado_actual'] ?? '');

        if ($estado !== 'pendiente_vendedor') {
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
        $motivoEstado = trim((string)($pedido['motivo_estado'] ?? ''));

        $mensajeEstado = match ($estado) {
            'pendiente_vendedor' =>
                'Tu solicitud está esperando respuesta del vendedor.',

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
            'requiere_preparacion'                  => (int)($pedido['requiere_preparacion'] ?? 0),
            'monto_descontado_billetera'            => (float)($pedido['monto_descontado_billetera'] ?? 0),
            'descuento_billetera_aplicado'          => (int)($pedido['descuento_billetera_aplicado'] ?? 0),
            'devolucion_billetera_aplicada'         => (int)($pedido['devolucion_billetera_aplicada'] ?? 0),
            'fecha_limite_respuesta'                => $pedido['fecha_limite_respuesta'] ?? null,
            'fecha_cancelacion'                     => $pedido['fecha_cancelacion'] ?? null,
            'fecha_cierre'                          => $pedido['fecha_cierre'] ?? null,
            'created_at'                            => $pedido['created_at'] ?? null,
            'puede_cancelar'                        => $puedeCancelar,
            'segundos_restantes'                    => $segundosRestantes,
            'segundos_para_cancelar_restantes'      => $segundosParaCancelarRestantes,
            'metodo_pago'                           => (string)($pedido['metodo_pago'] ?? ''),
            'penalidad_comprador_monto'             => (float)($pedido['penalidad_comprador_monto'] ?? 0),
            'penalidad_comprador_aplicada'          => (int)($pedido['penalidad_comprador_aplicada'] ?? 0),
            'comision_ev_monto'                     => (float)($pedido['comision_ev_monto'] ?? 0),
            'comision_ev_aplicada'                  => (int)($pedido['comision_ev_aplicada'] ?? 0),
            'comision_ev_pendiente'                 => (int)($pedido['comision_ev_pendiente'] ?? 0),
            'fecha_punto_recojo'                    => $pedido['fecha_punto_recojo'] ?? null,
            'fecha_limite_recojo'                   => $pedido['fecha_limite_recojo'] ?? null,
            'segundos_recojo_restantes'             => $this->segundosRecojoRestantes($pedido),
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
    // REGISTRO DE SOLICITUD CON ATENCIÓN CONCURRENTE
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
        $metodoPago             = strtolower(trim((string)($data['metodo_pago'] ?? 'efectivo')));

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
        $subtotalProducto = round($costoUnitario * $cantidad, 2);
        $requierePrep   = (int)($producto['requiere_preparacion'] ?? 0);
        $codigoVendedor = (int)($producto['codigo_usuario_vendedor'] ?? 0);

        // Regla funcional EV: todo producto que requiere preparación se paga
        // desde la billetera al momento de registrar la solicitud. No depende
        // de una regla de monetización opcional: si no hay saldo suficiente,
        // la transacción completa se revierte y la solicitud no se registra.
        $usarBilleteraPreparado = ($requierePrep === 1);
        $metodoPago = $usarBilleteraPreparado ? 'billetera' : 'efectivo';
        $penalidadReservada = 0.00;
        $total = $subtotalProducto;

        try {
            $this->dblink->beginTransaction();

            // Flujo concurrente: cada solicitud es independiente. La existencia
            // de otros pedidos del vendedor no altera el estado ni la ventana
            // de respuesta de esta solicitud.
            $fase = 'solicitud';
            $estadoActual = 'pendiente_vendedor';

            $fechaLimite = (new DateTime('now', $this->obtenerTimezoneAplicacion()))
                ->modify('+' . self::SEGUNDOS_TIMEOUT . ' seconds')
                ->format('Y-m-d H:i:s');

            $motivoEstado = 'Solicitud registrada y disponible para atención del vendedor.';

            // Penalidad pendiente: solo se reserva y suma al siguiente pedido no preparado en efectivo.
            if ($requierePrep === 0 && $metodoPago === 'efectivo') {
                $penalidadReservada = $this->obtenerPenalidadPendienteComprador($codigoUsuarioComprador);
                $total = round($subtotalProducto + $penalidadReservada, 2);
            }

            $sql = "
                INSERT INTO pedido
                (
                    codigo_producto,
                    codigo_usuario_comprador,
                    codigo_usuario_vendedor,
                    fase,
                    estado_actual,
                    estado,
                    cantidad,
                    costo_unitario,
                    precio_unitario,
                    total,
                    monto_total,
                    tipo_entrega,
                    fecha_hora_programada,
                    fecha_programada,
                    direccion_entrega,
                    mensaje_comprador,
                    motivo_estado,
                    requiere_preparacion,
                    metodo_pago,
                    penalidad_comprador_monto,
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
                    :estado,
                    :cantidad,
                    :costo_unitario,
                    :precio_unitario,
                    :total,
                    :monto_total,
                    :tipo_entrega,
                    :fecha_hora_programada,
                    :fecha_programada,
                    :direccion_entrega,
                    :mensaje_comprador,
                    :motivo_estado,
                    :requiere_preparacion,
                    :metodo_pago,
                    :penalidad_comprador_monto,
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
            $st->bindValue(':estado', $estadoActual, PDO::PARAM_STR);
            $st->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);
            $st->bindValue(':costo_unitario', $costoUnitario);
            $st->bindValue(':precio_unitario', $costoUnitario);
            $st->bindValue(':total', $total);
            $st->bindValue(':monto_total', $total);
            $st->bindValue(':tipo_entrega', $tipoEntrega, PDO::PARAM_STR);

            if ($fechaProgramadaMySql !== null) {
                $st->bindValue(':fecha_hora_programada', $fechaProgramadaMySql, PDO::PARAM_STR);
                $st->bindValue(':fecha_programada', $fechaProgramadaMySql, PDO::PARAM_STR);
            } else {
                $st->bindValue(':fecha_hora_programada', null, PDO::PARAM_NULL);
                $st->bindValue(':fecha_programada', null, PDO::PARAM_NULL);
            }

            $st->bindValue(':direccion_entrega', $direccionEntrega, PDO::PARAM_STR);

            if ($mensajeComprador !== '') {
                $st->bindValue(':mensaje_comprador', $mensajeComprador, PDO::PARAM_STR);
            } else {
                $st->bindValue(':mensaje_comprador', null, PDO::PARAM_NULL);
            }

            $st->bindValue(':motivo_estado', $motivoEstado, PDO::PARAM_STR);
            $st->bindValue(':requiere_preparacion', $requierePrep, PDO::PARAM_INT);
            $st->bindValue(':metodo_pago', $metodoPago, PDO::PARAM_STR);
            $st->bindValue(':penalidad_comprador_monto', $penalidadReservada);

            if ($fechaLimite !== null) {
                $st->bindValue(':fecha_limite_respuesta', $fechaLimite, PDO::PARAM_STR);
            } else {
                $st->bindValue(':fecha_limite_respuesta', null, PDO::PARAM_NULL);
            }

            $st->execute();

            $codigoPedido = (int)$this->dblink->lastInsertId();

            if ($penalidadReservada > 0) {
                $this->reservarPenalidadesPendientesParaPedido($codigoUsuarioComprador, $codigoPedido);
            }

            if ($usarBilleteraPreparado) {
                $debito = $this->debitarBilleteraPorSolicitudPreparada(
                    $codigoUsuarioComprador,
                    $codigoPedido,
                    $subtotalProducto,
                    (string)($producto['titulo'] ?? 'producto')
                );

                if (!$debito['ok']) {
                    $this->dblink->rollBack();

                    return [
                        'ok'              => false,
                        'error'           => 'SALDO_INSUFICIENTE_BILLETERA',
                        'mensaje'         => 'No tienes saldo suficiente en tu billetera para este producto con preparación.',
                        'saldo_actual'    => (float)($debito['saldo_actual'] ?? 0),
                        'monto_requerido' => (float)($debito['monto_requerido'] ?? $subtotalProducto)
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

            $pedidoInsertado = $this->obtenerSolicitudComprador($codigoPedido, $codigoUsuarioComprador, false);

            $pedidoParaNotificar = $pedidoInsertado ?: [
                'codigo_pedido' => $codigoPedido,
                'codigo_usuario_vendedor' => $codigoVendedor,
                'titulo_producto' => (string)($producto['titulo'] ?? 'producto'),
            ];
            $this->registrarAlertaPedidoVendedor(
                $pedidoParaNotificar,
                'nueva_solicitud',
                'Nueva solicitud de producto',
                'Tienes una nueva solicitud de “' . (string)($pedidoParaNotificar['titulo_producto'] ?? 'producto') . '” lista para atender.'
            );

            $this->dblink->commit();

            return [
                'ok'   => true,
                'data' => $this->construirDataEstadoSolicitud($pedidoInsertado ?: [
                    'codigo_pedido'                 => $codigoPedido,
                    'codigo_producto'               => $codigoProducto,
                    'titulo_producto'               => (string)($producto['titulo'] ?? ''),
                    'fase'                          => $fase,
                    'estado_actual'                 => $estadoActual,
                    'motivo_estado'                 => $motivoEstado,
                    'requiere_preparacion'          => $requierePrep,
                    'metodo_pago'                   => $metodoPago,
                    'penalidad_comprador_monto'     => $penalidadReservada,
                    'monto_descontado_billetera'    => $usarBilleteraPreparado ? $subtotalProducto : 0,
                    'descuento_billetera_aplicado'  => $usarBilleteraPreparado ? 1 : 0,
                    'devolucion_billetera_aplicada' => 0,
                    'fecha_limite_respuesta'        => $fechaLimite,
                    'created_at'                    => date('Y-m-d H:i:s')
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
        // Si la ventana absoluta de 4 minutos ya venció, este sincronizador cierra
        // primero la solicitud como sin_respuesta_vendedor y procesa la devolución.
        $this->sincronizarSolicitudesVencidas(null, $codigoUsuarioComprador, $codigoPedido);

        try {
            $this->dblink->beginTransaction();

            // FOR UPDATE serializa la carrera comprador-cancela vs vendedor-acepta.
            // Solo la primera operación que obtenga el bloqueo podrá cambiar el estado.
            $pedido = $this->obtenerSolicitudComprador($codigoPedido, $codigoUsuarioComprador, true);

            if (!$pedido) {
                $this->dblink->rollBack();

                return [
                    'ok'      => false,
                    'error'   => 'PEDIDO_NO_ENCONTRADO',
                    'mensaje' => 'No se encontró la solicitud o pedido.'
                ];
            }

            if ($this->solicitudPendienteVencida($pedido)) {
                $this->dblink->rollBack();
                $this->marcarSolicitudSinRespuestaVencida($codigoPedido);
                $pedidoVencido = $this->obtenerSolicitudComprador($codigoPedido, $codigoUsuarioComprador, false);

                return [
                    'ok'      => false,
                    'error'   => 'ESTADO_NO_CANCELABLE',
                    'mensaje' => 'El tiempo de respuesta del vendedor ya venció. EV cerró la solicitud y procesó la devolución correspondiente.',
                    'data'    => $pedidoVencido ? $this->construirDataEstadoSolicitud($pedidoVencido) : null
                ];
            }

            $estadoAnterior = (string)$pedido['estado_actual'];
            $faseAnterior = (string)$pedido['fase'];
            $motivo = trim($motivo);
            $esPreparado = $this->esProductoPreparadoPedido($pedido);
            $esPedidoAceptado = $this->pedidoFueAceptado($pedido);

            $penalidadNueva = 0.00;
            $cancelable = false;
            $motivoHistorial = 'cancelacion_solicitud';

            // Ventana de espera de una solicitud, para productos preparados y no
            // preparados: 0:00-1:59 no se puede cancelar; desde 2:00 sí se puede,
            // mientras el vendedor conserve su ventana de respuesta hasta 4:00.
            if ($faseAnterior === 'solicitud' && $estadoAnterior === 'pendiente_vendedor') {
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

                $cancelable = true;
                $motivoHistorial = 'cancelacion_solicitud_por_demora_vendedor';
                $this->liberarPenalidadesReservadasPedido($codigoPedido);
            }

            if ($esPedidoAceptado && $faseAnterior === 'pedido') {
                // Una vez que el vendedor acepta un producto preparado, el comprador
                // ya no puede cancelarlo de forma voluntaria. Si el vendedor cancela,
                // rechaza o la operación no se concreta por su responsabilidad, la
                // devolución se procesa por el flujo correspondiente del vendedor.
                if ($esPreparado) {
                    $this->dblink->rollBack();

                    return [
                        'ok'      => false,
                        'error'   => 'CANCELACION_NO_PERMITIDA_PRODUCTO_PREPARADO',
                        'mensaje' => 'El vendedor ya aceptó este producto preparado. La cancelación por parte del comprador ya no está disponible.',
                        'data'    => $this->construirDataEstadoSolicitud($pedido)
                    ];
                }

                // Producto no preparado: sin penalidad solo en despachando.
                if ($estadoAnterior === 'despachando') {
                    $cancelable = true;
                    $motivoHistorial = 'cancelacion_pedido_sin_penalidad';
                } elseif (in_array($estadoAnterior, ['listo_para_entrega', 'en_camino', 'en_punto_entrega'], true)) {
                    $cancelable = true;
                    $penalidadNueva = self::PENALIDAD_CANCELACION_COMPRADOR;
                    $motivoHistorial = 'cancelacion_pedido_con_penalidad';
                }
            }

            if (!$cancelable) {
                $this->dblink->rollBack();

                return [
                    'ok'      => false,
                    'error'   => 'ESTADO_NO_CANCELABLE',
                    'mensaje' => 'El pedido ya no se puede cancelar desde este estado.',
                    'data'    => $this->construirDataEstadoSolicitud($pedido)
                ];
            }

            if ($motivo === '') {
                $motivo = 'Pedido cancelado por el comprador.';
            }

            $motivoEstado = $penalidadNueva > 0
                ? $motivo . ' Se aplicará una penalidad de S/ 1.00 en el siguiente pedido.'
                : $motivo;

            $up = $this->dblink->prepare("
                UPDATE pedido
                SET
                    fase = 'pedido',
                    estado_actual = 'cancelado_comprador',
                    estado = 'cancelado_comprador',
                    motivo_estado = :motivo_estado,
                    motivo_cancelacion = :motivo_cancelacion,
                    cancelado_por = 'comprador',
                    penalidad_comprador_aplicada = CASE WHEN :penalidad > 0 THEN 1 ELSE penalidad_comprador_aplicada END,
                    fecha_cancelacion = NOW(),
                    fecha_cierre = NOW(),
                    fecha_limite_respuesta = NULL,
                    oculto_comprador = 1,
                    oculto_vendedor = 1
                WHERE codigo_pedido = :codigo_pedido
            ");
            $up->bindValue(':motivo_estado', $motivoEstado, PDO::PARAM_STR);
            $up->bindValue(':motivo_cancelacion', mb_substr($motivo, 0, 255, 'UTF-8'), PDO::PARAM_STR);
            $up->bindValue(':penalidad', $penalidadNueva);
            $up->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
            $up->execute();

            $this->registrarHistorialEstado(
                (int)$pedido['codigo_pedido'],
                $faseAnterior,
                $estadoAnterior,
                'pedido',
                'cancelado_comprador',
                $codigoUsuarioComprador,
                'comprador',
                $motivoHistorial,
                $motivoEstado
            );

            if ($penalidadNueva > 0) {
                $this->registrarPenalidadCompradorPendiente(
                    $codigoUsuarioComprador,
                    $codigoPedido,
                    'cancelacion_comprador_fuera_despacho',
                    'Penalidad por cancelar o no recibir un pedido no preparado fuera del estado despachando.'
                );
            }

            // Si el producto preparado había debitado la billetera, una cancelación
            // habilitada por demora del vendedor (>= 2 min y < 4 min) devuelve el
            // 100 % del monto realmente debitado y registra el movimiento.
            $this->devolverBilleteraSiCorresponde($pedido, 'cancelado_comprador');
            $this->registrarAlertaPedidoVendedor(
                $pedido,
                'cancelado_por_comprador',
                'Pedido cancelado por comprador',
                $motivoEstado
            );

            $pedidoActualizado = $this->obtenerSolicitudComprador($codigoPedido, $codigoUsuarioComprador, false);

            $this->dblink->commit();

            $data = $this->construirDataEstadoSolicitud($pedidoActualizado ?: $pedido);
            $data['penalidad_generada'] = $penalidadNueva;
            $data['mensaje_oculto'] = 'El pedido fue cancelado y ya no aparecerá en tus pedidos activos.';

            return [
                'ok'   => true,
                'data' => $data
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
              AND p.estado_actual = 'pendiente_vendedor'
            ORDER BY p.codigo_pedido ASC
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
                  AND p.estado_actual = 'pendiente_vendedor'
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

            return [
                'ok'   => true,
                'data' => $this->construirDataEstadoSolicitud($pedido)
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

        if ($tipo === 'programada' && !empty($fechaProgramada)) {
            return 'Programado';
        }

        return 'Inmediato';
    }

    private function formatearPedidoVendedor(array $r): array
    {
        $r = $this->asegurarVentanaRecojoSiFalta($r);
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
            'tiempo_restante_segundos'       => $tiempoRestante,
            'segundos_recojo_restantes'      => $this->segundosRecojoRestantes($r),
            'puede_cancelar_vendedor'        => $this->puedeVendedorCancelarPorNoRecojo($r) ? 1 : 0,
            'puede_cancelar_pedido'          => $this->puedeVendedorCancelarPedido($r) ? 1 : 0,
            'metodo_pago'                    => (string)($r['metodo_pago'] ?? ''),
            'penalidad_comprador_monto'      => (string)($r['penalidad_comprador_monto'] ?? '0.00'),
            'comision_ev_monto'              => (string)($r['comision_ev_monto'] ?? '0.00'),
            'comision_ev_aplicada'           => (int)($r['comision_ev_aplicada'] ?? 0),
            'comision_ev_pendiente'          => (int)($r['comision_ev_pendiente'] ?? 0),
            'fecha_punto_recojo'             => $r['fecha_punto_recojo'] ?? null,
            'fecha_limite_recojo'            => $r['fecha_limite_recojo'] ?? null
        ];
    }

    private function determinarGrupoPedidoVendedor(array $pedido): string
    {
        $estado = (string)($pedido['estado_actual'] ?? '');

        if ($estado === 'pendiente_vendedor') {
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

    /**
     * Endpoint liviano para polling global.
     * No pinta toda la vista ni carga todos los pedidos.
     * Devuelve solicitudes nuevas pendientes de decisión del vendedor.
     */
    public function listarNuevasSolicitudesVendedor(int $codigoUsuarioVendedor, int $sinceId = 0, int $limit = 10): array
    {
        try {
            $codigoUsuarioVendedor = max(0, $codigoUsuarioVendedor);
            $sinceId = max(0, $sinceId);
            $limit = max(1, min(25, $limit));

            if ($codigoUsuarioVendedor <= 0) {
                return [
                    'ok'      => false,
                    'error'   => 'VENDEDOR_INVALIDO',
                    'mensaje' => 'No se pudo identificar al vendedor.'
                ];
            }

            // Sincroniza solicitudes vencidas antes de entregar novedades al vendedor.
            $this->sincronizarSolicitudesVencidas($codigoUsuarioVendedor, null, null);

            $sqlMax = "
                SELECT COALESCE(MAX(p.codigo_pedido), 0)
                FROM pedido p
                WHERE p.codigo_usuario_vendedor = :codigo_usuario_vendedor
                  AND p.fase = 'solicitud'
                  AND p.estado_actual = 'pendiente_vendedor'
                  AND (
                        p.fecha_limite_respuesta IS NULL
                        OR p.fecha_limite_respuesta > NOW()
                  )
            ";

            $stMax = $this->dblink->prepare($sqlMax);
            $stMax->bindValue(':codigo_usuario_vendedor', $codigoUsuarioVendedor, PDO::PARAM_INT);
            $stMax->execute();

            $maxIdActual = (int)$stMax->fetchColumn();

            $sql = "
                SELECT
                    p.codigo_pedido,
                    p.codigo_producto,
                    p.codigo_usuario_comprador,
                    p.codigo_usuario_vendedor,
                    p.fase,
                    p.estado_actual,
                    p.cantidad,
                    p.costo_unitario,
                    p.total,
                    p.tipo_entrega,
                    p.fecha_hora_programada,
                    p.direccion_entrega,
                    p.mensaje_comprador,
                    p.motivo_estado,
                    p.requiere_preparacion,
                    p.monto_descontado_billetera,
                    p.descuento_billetera_aplicado,
                    p.devolucion_billetera_aplicada,
                    p.fecha_limite_respuesta,
                    p.created_at,
                    pr.titulo AS titulo_publicacion,
                    pr.titulo AS titulo_producto,
                    pr.imagen_portada,
                    TRIM(COALESCE(u.nombre, '')) AS nombre_vecino,
                    TRIM(COALESCE(u.nombre, '')) AS nombre_comprador
                FROM pedido p
                INNER JOIN producto pr ON pr.codigo_producto = p.codigo_producto
                INNER JOIN usuario u ON u.codigo_usuario = p.codigo_usuario_comprador
                WHERE p.codigo_usuario_vendedor = :codigo_usuario_vendedor
                  AND p.fase = 'solicitud'
                  AND p.estado_actual = 'pendiente_vendedor'
                  AND p.codigo_pedido > :since_id
                  AND (
                        p.fecha_limite_respuesta IS NULL
                        OR p.fecha_limite_respuesta > NOW()
                  )
                ORDER BY p.codigo_pedido ASC
                LIMIT {$limit}
            ";

            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_usuario_vendedor', $codigoUsuarioVendedor, PDO::PARAM_INT);
            $st->bindValue(':since_id', $sinceId, PDO::PARAM_INT);
            $st->execute();

            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $items = [];

            foreach ($rows as $row) {
                $items[] = $this->formatearPedidoVendedor($row);
            }

            return [
                'ok'   => true,
                'data' => [
                    'items'  => $items,
                    'max_id' => $maxIdActual
                ]
            ];
        } catch (Throwable $e) {
            error_log('[EV][Pedido][listarNuevasSolicitudesVendedor] ' . $e->getMessage());

            return [
                'ok'      => false,
                'error'   => 'ERROR_LISTAR_NUEVAS_SOLICITUDES',
                'mensaje' => 'No se pudieron obtener las nuevas solicitudes del vendedor.'
            ];
        }
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
                  AND COALESCE(p.oculto_vendedor, 0) = 0
                ORDER BY
                    CASE
                        WHEN p.estado_actual = 'pendiente_vendedor' THEN 1
                        WHEN p.estado_actual IN (
                            'en_preparacion',
                            'despachando',
                            'listo_para_entrega',
                            'en_camino',
                            'en_punto_entrega',
                            'entregado_vendedor'
                        ) THEN 2
                        ELSE 3
                    END,
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

            $pedido = $this->obtenerPedidoVendedor($codigoPedido, $codigoUsuarioVendedor, true);

            if (!$pedido) {
                $this->dblink->rollBack();

                return [
                    'ok'      => false,
                    'error'   => 'PEDIDO_NO_ENCONTRADO',
                    'mensaje' => 'No se encontró el pedido.'
                ];
            }

            if ($this->solicitudPendienteVencida($pedido)) {
                $this->dblink->rollBack();
                $this->marcarSolicitudSinRespuestaVencida($codigoPedido);

                return [
                    'ok'      => false,
                    'error'   => 'ESTADO_NO_ACEPTABLE',
                    'mensaje' => 'La solicitud venció por falta de respuesta y ya no puede aceptarse.'
                ];
            }

            if ((string)$pedido['fase'] !== 'solicitud' || (string)$pedido['estado_actual'] !== 'pendiente_vendedor') {
                $this->dblink->rollBack();

                return [
                    'ok'      => false,
                    'error'   => 'ESTADO_NO_ACEPTABLE',
                    'mensaje' => 'La solicitud ya no se puede aceptar.'
                ];
            }

            $siguienteEstado = ((int)$pedido['requiere_preparacion'] === 1)
                ? 'en_preparacion'
                : 'despachando';

            $this->aplicarComisionEVPorAceptacion($pedido);
            $this->aplicarPenalidadesReservadasPedido($codigoPedido);

            $up = $this->dblink->prepare("
                UPDATE pedido
                SET
                    fase = 'pedido',
                    estado_actual = :estado_actual,
                    estado = :estado_sync,
                    motivo_estado = 'Pedido aceptado por el vendedor.',
                    fecha_aceptacion = NOW(),
                    fecha_limite_respuesta = NULL
                WHERE codigo_pedido = :codigo_pedido
            ");
            $up->bindValue(':estado_actual', $siguienteEstado, PDO::PARAM_STR);
            $up->bindValue(':estado_sync', $siguienteEstado, PDO::PARAM_STR);
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

            $pedidoParaAlerta = $pedido;
            $pedidoParaAlerta['estado_actual'] = $siguienteEstado;
            $pedidoParaAlerta['fase'] = 'pedido';
            $this->registrarAlertaAvanceComprador($pedidoParaAlerta, $siguienteEstado);

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

            error_log('[EV][Pedido][aceptarSolicitudPorVendedor] ' . $e->getMessage());

            return [
                'ok'      => false,
                'error'   => 'ERROR_ACEPTAR_SOLICITUD',
                'mensaje' => 'No se pudo aceptar la solicitud.'
            ];
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

            if ($this->solicitudPendienteVencida($pedido)) {
                $this->dblink->rollBack();
                $this->marcarSolicitudSinRespuestaVencida($codigoPedido);

                return [
                    'ok'      => false,
                    'error'   => 'ESTADO_NO_RECHAZABLE',
                    'mensaje' => 'La solicitud venció por falta de respuesta y ya fue cerrada por EV.'
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

            $up = $this->dblink->prepare("
                UPDATE pedido
                SET
                    fase = 'pedido',
                    estado_actual = 'rechazado_vendedor',
                    estado = 'rechazado_vendedor',
                    motivo_estado = :motivo_estado,
                    motivo_rechazo = :motivo_rechazo,
                    fecha_rechazo = NOW(),
                    fecha_cierre = NOW(),
                    fecha_limite_respuesta = NULL
                WHERE codigo_pedido = :codigo_pedido
            ");
            $up->bindValue(':motivo_estado', $motivo, PDO::PARAM_STR);
            $up->bindValue(':motivo_rechazo', mb_substr($motivo, 0, 255, 'UTF-8'), PDO::PARAM_STR);
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

            $this->liberarPenalidadesReservadasPedido($codigoPedido);
            $this->devolverBilleteraSiCorresponde($pedido, 'rechazado_vendedor');

            $pedidoActualizado = $this->obtenerPedidoVendedor($codigoPedido, $codigoUsuarioVendedor, false);
            $pedidoParaAlerta = $pedidoActualizado ?: $pedido;
            $pedidoParaAlerta['motivo_estado'] = $motivo;
            $this->registrarAlertaAvanceComprador($pedidoParaAlerta, 'rechazado_vendedor');

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
                'en_preparacion'     => ['listo_para_entrega', 'cancelado_vendedor'],
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
            'en_punto_entrega'             => 'El pedido llegó al punto de recojo.',
            'entregado_vendedor'           => 'El vendedor marcó el pedido como entregado.',
            'cancelado_vendedor'           => 'El vendedor canceló el pedido.',
            'entrega_confirmada_comprador' => 'El comprador confirmó la entrega.',
            default                        => 'Estado actualizado.'
        };
    }


    public function actualizarEstadoPedidoPorVendedor(
        int $codigoPedido,
        int $codigoUsuarioVendedor,
        string $nuevoEstado,
        string $motivoCancelacionClave = '',
        string $motivoCancelacionDetalle = ''
    ): array {
        try {
            $nuevoEstado = trim($nuevoEstado);

            if ($nuevoEstado === '') {
                return [
                    'ok'      => false,
                    'error'   => 'ESTADO_NO_ACTUALIZABLE',
                    'mensaje' => 'Debes indicar el nuevo estado.'
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

            $estadoActual = (string)$pedido['estado_actual'];
            $faseActual = (string)$pedido['fase'];
            $requierePreparacion = ((int)$pedido['requiere_preparacion'] === 1);

            if ($faseActual !== 'pedido') {
                $this->dblink->rollBack();

                return [
                    'ok'      => false,
                    'error'   => 'ESTADO_NO_ACTUALIZABLE',
                    'mensaje' => 'Este pedido aún no está en una fase actualizable por el vendedor.'
                ];
            }

            $transiciones = $this->obtenerMapaTransicionesVendedor($requierePreparacion);

            if (!isset($transiciones[$estadoActual]) || !in_array($nuevoEstado, $transiciones[$estadoActual], true)) {
                $this->dblink->rollBack();

                return [
                    'ok'      => false,
                    'error'   => 'TRANSICION_INVALIDA',
                    'mensaje' => 'La transición de estado no es válida.'
                ];
            }

            $cerrar = ($nuevoEstado === 'cancelado_vendedor') ? 1 : 0;
            $motivoEstado = $this->motivoEstadoPorNuevoEstado($nuevoEstado);
            $sinReembolso = 0;
            $canceladoPor = null;
            $motivoCancelacionSql = null;
            $motivoCancelacionClaveSql = null;

            if ($nuevoEstado === 'cancelado_vendedor') {
                if (!$this->puedeVendedorCancelarPedido($pedido)) {
                    $this->dblink->rollBack();

                    return [
                        'ok'      => false,
                        'error'   => 'CANCELACION_VENDEDOR_NO_PERMITIDA',
                        'mensaje' => 'Este pedido ya no puede ser cancelado por el vendedor.'
                    ];
                }

                $motivoCancelacionClave = trim($motivoCancelacionClave);
                if ($motivoCancelacionClave === '') {
                    $this->dblink->rollBack();

                    return [
                        'ok'      => false,
                        'error'   => 'MOTIVO_CANCELACION_REQUERIDO',
                        'mensaje' => 'Debes seleccionar el motivo de cancelación.'
                    ];
                }

                if (
                    $this->motivoCancelacionAtribuibleComprador($motivoCancelacionClave)
                    && !$this->puedeVendedorCancelarPorNoRecojo($pedido)
                ) {
                    $this->dblink->rollBack();

                    return [
                        'ok'      => false,
                        'error'   => 'RECOJO_AUN_NO_VENCIDO',
                        'mensaje' => 'Los motivos atribuibles al comprador se habilitan cuando vence el tiempo de recepción en el punto de entrega.'
                    ];
                }

                $motivoEstado = $this->motivoCancelacionVendedorTexto($motivoCancelacionClave, $motivoCancelacionDetalle);
                $sinReembolso = 0; // Cancelación del vendedor: si hubo débito, corresponde devolución total.
                $canceladoPor = 'vendedor';
                $motivoCancelacionSql = mb_substr($motivoEstado, 0, 255, 'UTF-8');
                $motivoCancelacionClaveSql = mb_substr($motivoCancelacionClave, 0, 80, 'UTF-8');
            }

            $fechaPuntoRecojoSql = ($nuevoEstado === 'en_punto_entrega') ? 'NOW()' : 'fecha_punto_recojo';
            $fechaLimiteRecojoSql = ($nuevoEstado === 'en_punto_entrega')
                ? "DATE_ADD(NOW(), INTERVAL " . self::SEGUNDOS_RECOJO . " SECOND)"
                : 'fecha_limite_recojo';

            $up = $this->dblink->prepare("
                UPDATE pedido
                SET
                    estado_actual = :estado_actual,
                    estado = :estado_sync,
                    motivo_estado = :motivo_estado,
                    fecha_punto_recojo = {$fechaPuntoRecojoSql},
                    fecha_limite_recojo = {$fechaLimiteRecojoSql},
                    fecha_cierre = CASE WHEN :cerrar = 1 THEN NOW() ELSE fecha_cierre END,
                    cancelado_por = CASE WHEN :cancelado_por_is_null = 1 THEN cancelado_por ELSE :cancelado_por_value END,
                    motivo_cancelacion = CASE WHEN :motivo_cancelacion_is_null = 1 THEN motivo_cancelacion ELSE :motivo_cancelacion_value END,
                    motivo_cancelacion_clave = CASE WHEN :motivo_cancelacion_clave_is_null = 1 THEN motivo_cancelacion_clave ELSE :motivo_cancelacion_clave_value END,
                    sin_reembolso = CASE WHEN :es_cancelacion_vendedor = 1 THEN 0 ELSE sin_reembolso END
                WHERE codigo_pedido = :codigo_pedido
            ");
            $up->bindValue(':estado_actual', $nuevoEstado, PDO::PARAM_STR);
            $up->bindValue(':estado_sync', $nuevoEstado, PDO::PARAM_STR);
            $up->bindValue(':motivo_estado', $motivoEstado, PDO::PARAM_STR);
            $up->bindValue(':cerrar', $cerrar, PDO::PARAM_INT);
            $up->bindValue(':cancelado_por_is_null', $canceladoPor === null ? 1 : 0, PDO::PARAM_INT);
            $up->bindValue(':cancelado_por_value', $canceladoPor, $canceladoPor !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $up->bindValue(':motivo_cancelacion_is_null', $motivoCancelacionSql === null ? 1 : 0, PDO::PARAM_INT);
            $up->bindValue(':motivo_cancelacion_value', $motivoCancelacionSql, $motivoCancelacionSql !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $up->bindValue(':motivo_cancelacion_clave_is_null', $motivoCancelacionClaveSql === null ? 1 : 0, PDO::PARAM_INT);
            $up->bindValue(':motivo_cancelacion_clave_value', $motivoCancelacionClaveSql, $motivoCancelacionClaveSql !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $up->bindValue(':es_cancelacion_vendedor', $nuevoEstado === 'cancelado_vendedor' ? 1 : 0, PDO::PARAM_INT);
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
                $nuevoEstado === 'cancelado_vendedor'
                    ? ($this->motivoCancelacionAtribuibleComprador($motivoCancelacionClave)
                        ? 'cancelacion_por_no_recojo'
                        : 'cancelacion_vendedor')
                    : 'actualizacion_estado_pedido',
                $motivoEstado
            );

            if ($nuevoEstado === 'cancelado_vendedor') {
                if (
                    !$requierePreparacion
                    && $this->motivoCancelacionAtribuibleComprador($motivoCancelacionClave)
                ) {
                    $this->registrarPenalidadCompradorPendiente(
                        (int)$pedido['codigo_usuario_comprador'],
                        $codigoPedido,
                        'comprador_no_recoge',
                        'Penalidad por no recoger o no recibir un pedido no preparado.'
                    );
                }

                $this->devolverBilleteraSiCorresponde(
                    array_merge($pedido, ['sin_reembolso' => $sinReembolso]),
                    'cancelado_vendedor'
                );
            }

            $pedidoActualizado = $this->obtenerPedidoVendedor($codigoPedido, $codigoUsuarioVendedor, false);

            $pedidoParaAlerta = $pedidoActualizado ?: $pedido;
            $pedidoParaAlerta['estado_actual'] = $nuevoEstado;
            $pedidoParaAlerta['motivo_estado'] = $motivoEstado;
            $this->registrarAlertaAvanceComprador($pedidoParaAlerta, $nuevoEstado);

            $this->dblink->commit();

            return [
                'ok'   => true,
                'data' => $pedidoActualizado ? $this->formatearPedidoVendedor($pedidoActualizado) : null
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }

            error_log('[EV][Pedido][actualizarEstadoPedidoPorVendedor] ' . $e->getMessage());

            return [
                'ok'      => false,
                'error'   => 'ERROR_ACTUALIZAR_ESTADO_PEDIDO',
                'mensaje' => 'No se pudo actualizar el estado del pedido.'
            ];
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
                  AND COALESCE(p.oculto_comprador, 0) = 0
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
                $row = $this->asegurarVentanaRecojoSiFalta($row);
                $estado = (string)($row['estado_actual'] ?? '');
                $fase   = (string)($row['fase'] ?? '');

                $rowEstado = $row;
                $rowEstado['titulo_producto'] = (string)($row['titulo_publicacion'] ?? '');

                $estadoData = $this->construirDataEstadoSolicitud($rowEstado);

                $item = [
                    'codigo_pedido'                    => (int)$row['codigo_pedido'],
                    'codigo_producto'                  => (int)$row['codigo_producto'],
                    'titulo_publicacion'               => (string)($row['titulo_publicacion'] ?? ''),
                    'titulo_producto'                  => (string)($row['titulo_publicacion'] ?? ''),
                    'nombre_vendedor'                  => (string)($row['nombre_vendedor'] ?? 'Vecino'),
                    'nombre_vecino'                    => (string)($row['nombre_vendedor'] ?? 'Vecino'),
                    'imagen_portada'                   => (string)($row['imagen_portada'] ?? ''),
                    'fase'                             => $fase,
                    'estado_actual'                    => $estado,
                    'motivo_estado'                    => (string)($row['motivo_estado'] ?? ''),
                    'mensaje_estado'                   => (string)($estadoData['mensaje_estado'] ?? ''),
                    'cantidad'                         => (int)($row['cantidad'] ?? 0),
                    'precio_unitario'                  => (string)($row['costo_unitario'] ?? '0.00'),
                    'monto_total'                      => (string)($row['total'] ?? '0.00'),
                    'tipo_entrega'                     => ((string)($row['tipo_entrega'] ?? '') === 'programada' && !empty($row['fecha_hora_programada']))
                        ? 'Programada'
                        : 'Inmediata',
                    'tipo_entrega_raw'                 => (string)($row['tipo_entrega'] ?? 'inmediata'),
                    'fecha_hora_programada'            => $row['fecha_hora_programada'],
                    'direccion_entrega'                => (string)($row['direccion_entrega'] ?? ''),
                    'mensaje_comprador'                => (string)($row['mensaje_comprador'] ?? ''),
                    'fecha_hora'                       => $row['created_at'] ?? null,
                    'created_at'                       => $row['created_at'] ?? null,
                    'fecha_limite_respuesta'           => $row['fecha_limite_respuesta'] ?? null,
                    'requiere_preparacion'             => (int)($row['requiere_preparacion'] ?? 0),
                    'descuento_billetera_aplicado'     => (int)($row['descuento_billetera_aplicado'] ?? 0),
                    'devolucion_billetera_aplicada'    => (int)($row['devolucion_billetera_aplicada'] ?? 0),
                    'monto_descontado_billetera'       => (string)($row['monto_descontado_billetera'] ?? '0.00'),
                    'puede_cancelar'                   => (int)($estadoData['puede_cancelar'] ?? 0),
                    'finalizado'                       => (int)($estadoData['finalizado'] ?? 0),
                    'segundos_restantes'               => (int)($estadoData['segundos_restantes'] ?? 0),
                    'segundos_para_cancelar_restantes' => (int)($estadoData['segundos_para_cancelar_restantes'] ?? 0),
                    'segundos_recojo_restantes'        => (int)($estadoData['segundos_recojo_restantes'] ?? 0),
                    'metodo_pago'                      => (string)($row['metodo_pago'] ?? ''),
                    'penalidad_comprador_monto'        => (string)($row['penalidad_comprador_monto'] ?? '0.00'),
                    'comision_ev_monto'                => (string)($row['comision_ev_monto'] ?? '0.00'),
                ];

                if ($fase === 'solicitud' && $estado === 'pendiente_vendedor') {
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

            $up = $this->dblink->prepare("
                UPDATE pedido
                SET
                    estado_actual = 'entrega_confirmada_comprador',
                    estado = 'entrega_confirmada_comprador',
                    entrega_confirmada_comprador = 1,
                    motivo_estado = 'El comprador confirmó la entrega del pedido.',
                    fecha_confirmacion_entrega = NOW(),
                    fecha_cierre = NOW()
                WHERE codigo_pedido = :codigo_pedido
            ");
            $up->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
            $up->execute();

            // En el piloto EV no se descuenta comisión al vendedor. El importe
            // efectivamente debitado al comprador se acredita completo al cierre
            // satisfactorio del pedido preparado.
            $this->acreditarVendedorPorPedidoPreparado($pedido);

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

            $this->dblink->commit();

            $calificacionPendiente = null;

            try {
                $calificacionModel = new Calificacion();
                $generadas = $calificacionModel->generarPendientesPorPedido($codigoPedido);

                if (($generadas['ok'] ?? false) === true) {
                    $calificacionPendiente = $generadas['data']['comprador'] ?? null;
                }
            } catch (Throwable $e) {
                // La confirmación de entrega no debe fallar si el módulo de calificaciones
                // tiene un problema operativo. Se registra el error y el pedido queda cerrado.
                error_log('[EV][Pedido][confirmarEntregaPorComprador][calificacion] ' . $e->getMessage());
            }

            return [
                'ok'   => true,
                'data' => [
                    'codigo_pedido' => $codigoPedido,
                    'estado_actual' => 'entrega_confirmada_comprador',
                    'calificacion_pendiente' => $calificacionPendiente
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

    public function reportarNoEntregadoPorComprador(int $codigoPedido, int $codigoUsuarioComprador): array
    {
        try {
            $this->dblink->beginTransaction();

            $pedido = $this->obtenerSolicitudComprador($codigoPedido, $codigoUsuarioComprador, true);

            if (!$pedido) {
                $this->dblink->rollBack();
                return [
                    'ok' => false,
                    'error' => 'PEDIDO_NO_ENCONTRADO',
                    'mensaje' => 'No se encontró el pedido.'
                ];
            }

            if ((string)$pedido['estado_actual'] !== 'entregado_vendedor') {
                $this->dblink->rollBack();
                return [
                    'ok' => false,
                    'error' => 'ESTADO_NO_REPORTABLE',
                    'mensaje' => 'Este pedido ya no se encuentra pendiente de confirmación de entrega.'
                ];
            }

            $detalle = 'El comprador indicó que no recibió el pedido marcado como entregado. Se mostró Ayuda EV para contacto con soporte.';

            $up = $this->dblink->prepare("
                UPDATE pedido
                SET motivo_estado = :motivo_estado
                WHERE codigo_pedido = :codigo_pedido
            ");
            $up->bindValue(':motivo_estado', $detalle, PDO::PARAM_STR);
            $up->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_INT);
            $up->execute();

            $this->registrarHistorialEstado(
                $codigoPedido,
                (string)$pedido['fase'],
                (string)$pedido['estado_actual'],
                (string)$pedido['fase'],
                (string)$pedido['estado_actual'],
                $codigoUsuarioComprador,
                'comprador',
                'comprador_reporta_no_entregado',
                $detalle
            );

            $this->dblink->commit();

            $calificacionPendiente = null;
            try {
                $calificacionModel = new Calificacion();
                $generada = $calificacionModel->generarPendienteCompradorPorPedidoNoEntregado(
                    $codigoPedido,
                    $codigoUsuarioComprador
                );
                if (($generada['ok'] ?? false) === true) {
                    $calificacionPendiente = $generada['data'] ?? null;
                }
            } catch (Throwable $e) {
                error_log('[EV][Pedido][reportarNoEntregadoPorComprador][calificacion] ' . $e->getMessage());
            }

            return [
                'ok' => true,
                'data' => [
                    'codigo_pedido' => $codigoPedido,
                    'estado_actual' => 'entregado_vendedor',
                    'no_entregado_reportado' => 1,
                    'calificacion_pendiente' => $calificacionPendiente
                ]
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }

            error_log('[EV][Pedido][reportarNoEntregadoPorComprador] ' . $e->getMessage());
            return [
                'ok' => false,
                'error' => 'ERROR_REPORTAR_NO_ENTREGADO',
                'mensaje' => 'No se pudo registrar que el pedido no fue entregado.'
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
            return [
                'ok'      => false,
                'error'   => 'PRODUCTO_NO_ENCONTRADO',
                'mensaje' => 'La publicación ya no está disponible.'
            ];
        }

        $codigoVendedor = (int)($row['codigo_usuario_vendedor'] ?? 0);

        if ($codigoVendedor === $codigoUsuarioComprador) {
            return [
                'ok'      => false,
                'error'   => 'PRODUCTO_PROPIO',
                'mensaje' => 'No puedes solicitar un pedido sobre tu propia publicación.'
            ];
        }

        if ((int)($row['visible'] ?? 0) !== 2) {
            return [
                'ok'      => false,
                'error'   => 'PRODUCTO_NO_APROBADO',
                'mensaje' => 'La publicación ya no está disponible para pedidos.'
            ];
        }

        if ((string)($row['estado_residencial_publicacion'] ?? '') !== 'activa') {
            return [
                'ok'      => false,
                'error'   => 'PUBLICACION_NO_VIGENTE',
                'mensaje' => 'La publicación ya no pertenece a una residencia activa.'
            ];
        }

        if ((int)($row['estado_vendedor'] ?? 0) !== 2) {
            return [
                'ok'      => false,
                'error'   => 'VENDEDOR_NO_HABILITADO',
                'mensaje' => 'El vendedor no se encuentra habilitado en este momento.'
            ];
        }

        if ((int)($row['disponibilidad_pedidos_vendedor'] ?? 0) !== 1) {
            return [
                'ok'      => false,
                'error'   => 'VENDEDOR_NO_DISPONIBLE',
                'mensaje' => 'El vendedor no está disponible para recibir pedidos en este momento.'
            ];
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
                'codigo_producto'         => (int)$row['codigo_producto'],
                'titulo'                  => (string)($row['titulo'] ?? ''),
                'descripcion'             => (string)($row['descripcion'] ?? ''),
                'precio'                  => (float)($row['precio'] ?? 0),
                'codigo_usuario_vendedor' => $codigoVendedor,
                'nombre_vendedor'         => (string)($row['nombre_vendedor'] ?? 'Vecino'),
                'codigo_tipo'             => (int)($row['codigo_tipo'] ?? 0),
                'codigo_categoria'        => (int)($row['codigo_categoria'] ?? 0),
                'tipo_nombre'             => (string)($row['tipo_nombre'] ?? ''),
                'categoria_nombre'        => (string)($row['categoria_nombre'] ?? ''),
                'imagen_portada'                    => (string)($row['imagen_portada'] ?? ''),
                'tipo_conjunto_publicacion'          => (string)($row['tipo_conjunto_publicacion'] ?? ''),
                'codigo_condominio_publicacion'      => (int)($row['codigo_condominio_publicacion'] ?? 0),
                'codigo_urbanizacion_publicacion'    => (int)($row['codigo_urbanizacion_publicacion'] ?? 0),
                'requiere_preparacion'               => ((string)($row['tipo_atencion_producto'] ?? '') === 'requiere_preparacion') ? 1 : 0
            ]
        ];
    }
}
