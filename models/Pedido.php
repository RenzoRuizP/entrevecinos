<?php
require_once __DIR__ . '/../database/Conexion.php';

class Pedido extends Conexion
{
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
        if (!$row) {
            return null;
        }

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
            INNER JOIN usuario u
                ON u.codigo_usuario = p.codigo_usuario
            LEFT JOIN tipo t
                ON t.codigo_tipo = p.codigo_tipo
            LEFT JOIN categoria c
                ON c.codigo_categoria = p.codigo_categoria
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
        if ($codigoVendedor <= 0) {
            return [
                'ok'      => false,
                'error'   => 'VENDEDOR_INVALIDO',
                'mensaje' => 'No se pudo identificar al vendedor.'
            ];
        }

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

        $tipoConjuntoPublicacion = strtolower(trim((string)($row['tipo_conjunto_publicacion'] ?? '')));
        $codigoCondominioPub     = (int)($row['codigo_condominio_publicacion'] ?? 0);
        $codigoUrbanizacionPub   = (int)($row['codigo_urbanizacion_publicacion'] ?? 0);

        $mismoConjunto = false;

        if (
            $resComprador['tipo_conjunto'] === 'condominio' &&
            $tipoConjuntoPublicacion === 'condominio' &&
            (int)$resComprador['codigo_condominio'] === $codigoCondominioPub
        ) {
            $mismoConjunto = true;
        }

        if (
            $resComprador['tipo_conjunto'] === 'urbanizacion' &&
            $tipoConjuntoPublicacion === 'urbanizacion' &&
            (int)$resComprador['codigo_urbanizacion'] === $codigoUrbanizacionPub
        ) {
            $mismoConjunto = true;
        }

        if (!$mismoConjunto) {
            return [
                'ok'      => false,
                'error'   => 'PUBLICACION_FUERA_DE_CONJUNTO',
                'mensaje' => 'La publicación ya no pertenece a tu conjunto residencial.'
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

    public function registrarSolicitud(array $data): array
    {
        $codigoProducto         = (int)($data['codigo_producto'] ?? 0);
        $codigoUsuarioComprador = (int)($data['codigo_usuario_comprador'] ?? 0);
        $cantidad               = (int)($data['cantidad'] ?? 0);
        $tipoEntrega            = (string)($data['tipo_entrega'] ?? 'inmediata');
        $fechaHoraProgramada    = $data['fecha_hora_programada'] ?? null;
        $direccionEntrega       = trim((string)($data['direccion_entrega'] ?? ''));
        $mensajeComprador       = trim((string)($data['mensaje_comprador'] ?? ''));

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

            try {
                $dt = new DateTime((string)$fechaHoraProgramada);
                $ahora = new DateTime('now');
                $maximo = (clone $ahora)->modify('+2 days');

                if ($dt < $ahora) {
                    return [
                        'ok'      => false,
                        'error'   => 'FECHA_PROGRAMADA_INVALIDA',
                        'mensaje' => 'La fecha programada no puede ser menor al momento actual.'
                    ];
                }

                if ($dt > $maximo) {
                    return [
                        'ok'      => false,
                        'error'   => 'FECHA_PROGRAMADA_FUERA_DE_RANGO',
                        'mensaje' => 'La fecha programada no puede superar 2 días desde ahora.'
                    ];
                }

                $fechaProgramadaMySql = $dt->format('Y-m-d H:i:s');
            } catch (Throwable $e) {
                return [
                    'ok'      => false,
                    'error'   => 'FECHA_PROGRAMADA_INVALIDA',
                    'mensaje' => 'La fecha programada no tiene un formato válido.'
                ];
            }
        }

        $costoUnitario = (float)$producto['precio'];
        $total         = round($costoUnitario * $cantidad, 2);
        $requierePrep  = (int)($producto['requiere_preparacion'] ?? 0);

        $fase          = 'solicitud';
        $estadoActual  = 'pendiente_vendedor';

        $fechaLimite = (new DateTime('now'))->modify('+5 minutes')->format('Y-m-d H:i:s');

        try {
            $this->dblink->beginTransaction();

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
                    motivo_estado,
                    requiere_preparacion,
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
                    :motivo_estado,
                    :requiere_preparacion,
                    :fecha_limite_respuesta
                )
            ";

            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_producto', $codigoProducto, PDO::PARAM_INT);
            $st->bindValue(':codigo_usuario_comprador', $codigoUsuarioComprador, PDO::PARAM_INT);
            $st->bindValue(':codigo_usuario_vendedor', (int)$producto['codigo_usuario_vendedor'], PDO::PARAM_INT);
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

            $st->bindValue(':motivo_estado', 'Solicitud registrada por comprador.', PDO::PARAM_STR);
            $st->bindValue(':requiere_preparacion', $requierePrep, PDO::PARAM_INT);
            $st->bindValue(':fecha_limite_respuesta', $fechaLimite, PDO::PARAM_STR);
            $st->execute();

            $codigoPedido = (int)$this->dblink->lastInsertId();

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
                    NULL,
                    NULL,
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
            $stHist->bindValue(':fase_nueva', $fase, PDO::PARAM_STR);
            $stHist->bindValue(':estado_nuevo', $estadoActual, PDO::PARAM_STR);
            $stHist->bindValue(':codigo_usuario_actor', $codigoUsuarioComprador, PDO::PARAM_INT);
            $stHist->bindValue(':rol_actor', 'comprador', PDO::PARAM_STR);
            $stHist->bindValue(':motivo', 'registro_solicitud', PDO::PARAM_STR);

            if ($mensajeComprador !== '') {
                $stHist->bindValue(':observacion', $mensajeComprador, PDO::PARAM_STR);
            } else {
                $stHist->bindValue(':observacion', null, PDO::PARAM_NULL);
            }

            $stHist->execute();

            $this->dblink->commit();

            return [
                'ok'   => true,
                'data' => [
                    'codigo_pedido'           => $codigoPedido,
                    'codigo_producto'         => $codigoProducto,
                    'titulo_producto'         => $producto['titulo'],
                    'cantidad'                => $cantidad,
                    'total'                   => $total,
                    'tipo_entrega'            => $tipoEntrega,
                    'fecha_hora_programada'   => $fechaProgramadaMySql,
                    'fecha_limite_respuesta'  => $fechaLimite,
                    'estado_actual'           => $estadoActual,
                    'fase'                    => $fase
                ]
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

    public function listarPedidosEntrantes(int $codigoUsuarioVendedor): array
    {
        $sql = "
            SELECT
                p.codigo_pedido,
                p.codigo_producto,
                p.estado_actual,
                p.fase,
                p.cantidad,
                p.costo_unitario,
                p.total,
                p.tipo_entrega,
                p.fecha_hora_programada,
                p.direccion_entrega,
                p.mensaje_comprador,
                p.fecha_limite_respuesta,
                p.created_at,

                pr.titulo AS titulo_publicacion,
                pr.imagen_portada,

                TRIM(COALESCE(u.nombre, '')) AS nombre_vecino
            FROM pedido p
            INNER JOIN producto pr
                ON pr.codigo_producto = p.codigo_producto
            INNER JOIN usuario u
                ON u.codigo_usuario = p.codigo_usuario_comprador
            WHERE p.codigo_usuario_vendedor = :v
              AND p.fase = 'solicitud'
              AND p.estado_actual = 'pendiente_vendedor'
            ORDER BY p.created_at DESC, p.codigo_pedido DESC
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':v', $codigoUsuarioVendedor, PDO::PARAM_INT);
        $st->execute();

        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        if (!$rows) {
            return [];
        }

        $out = [];
        $ahora = new DateTime('now');

        foreach ($rows as $r) {
            $segundosRestantes = null;

            if (!empty($r['fecha_limite_respuesta'])) {
                try {
                    $limite = new DateTime((string)$r['fecha_limite_respuesta']);
                    $segundosRestantes = max(0, $limite->getTimestamp() - $ahora->getTimestamp());
                } catch (Throwable $e) {
                    $segundosRestantes = null;
                }
            }

            $out[] = [
                'id_pedido'                 => (int)$r['codigo_pedido'],
                'codigo_producto'           => (int)$r['codigo_producto'],
                'titulo_publicacion'        => (string)($r['titulo_publicacion'] ?? ''),
                'nombre_vecino'             => (string)($r['nombre_vecino'] ?? 'Vecino'),
                'fecha_hora'                => !empty($r['created_at']) ? date('d/m/Y H:i', strtotime((string)$r['created_at'])) : '',
                'monto_total'               => (string)($r['total'] ?? '0.00'),
                'cantidad'                  => (int)($r['cantidad'] ?? 0),
                'precio_unitario'           => (string)($r['costo_unitario'] ?? '0.00'),
                'tipo_entrega'              => (string)($r['tipo_entrega'] ?? 'inmediata'),
                'fecha_hora_programada'     => $r['fecha_hora_programada'],
                'direccion_entrega'         => (string)($r['direccion_entrega'] ?? ''),
                'mensaje_comprador'         => (string)($r['mensaje_comprador'] ?? ''),
                'estado_actual'             => (string)($r['estado_actual'] ?? ''),
                'fase'                      => (string)($r['fase'] ?? ''),
                'fecha_limite_respuesta'    => $r['fecha_limite_respuesta'],
                'tiempo_restante_segundos'  => $segundosRestantes,
                'imagen_portada'            => (string)($r['imagen_portada'] ?? '')
            ];
        }

        return $out;
    }
}