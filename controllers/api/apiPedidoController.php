<?php
require_once __DIR__ . '/../../models/Pedido.php';

class apiPedidoController
{
    private function json(int $statusCode, array $payload): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function obtenerUsuarioAuth(): int
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        $codigoUsuario = (int)($auth['codigo_usuario'] ?? 0);

        if ($codigoUsuario <= 0) {
            $this->json(401, [
                'ok'      => false,
                'error'   => 'UNAUTHORIZED',
                'mensaje' => 'Tu sesión no es válida.'
            ]);
            exit;
        }

        return $codigoUsuario;
    }

    private function leerInput(): array
    {
        $input = $_POST;
        if (!empty($input)) {
            return is_array($input) ? $input : [];
        }

        $raw = file_get_contents('php://input');
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $json = json_decode($raw, true);
        return is_array($json) ? $json : [];
    }

    private function construirUrlImagen(?string $ruta): string
    {
        $ruta = trim((string)$ruta);
        if ($ruta === '') {
            return '';
        }

        return rtrim(BASE_URL, '/') . '/' . ltrim($ruta, '/');
    }

    private function agregarUrlImagenAItem(array $item): array
    {
        $item['imagen_portada_url'] = $this->construirUrlImagen($item['imagen_portada'] ?? '');
        return $item;
    }

    private function agregarUrlImagenAGrupos(array $data): array
    {
        foreach (['pendientes', 'en_proceso', 'finalizados'] as $grupo) {
            if (!isset($data[$grupo]) || !is_array($data[$grupo])) {
                continue;
            }

            foreach ($data[$grupo] as &$item) {
                if (is_array($item)) {
                    $item = $this->agregarUrlImagenAItem($item);
                }
            }
            unset($item);
        }

        return $data;
    }

    // =========================================================
    // COMPRADOR
    // =========================================================
    public function registrarPedido(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, [
                'ok'      => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        try {
            $codigoUsuarioComprador = $this->obtenerUsuarioAuth();
            $input = $this->leerInput();

            $codigoProducto      = (int)($input['codigo_producto'] ?? 0);
            $cantidad            = (int)($input['cantidad'] ?? 0);
            $tipoEntrega         = trim((string)($input['tipo_entrega'] ?? 'inmediata'));
            $fechaHoraProgramada = $input['fecha_hora_programada'] ?? null;
            $direccionEntrega    = trim((string)($input['direccion_entrega'] ?? ''));
            $mensajeComprador    = trim((string)($input['mensaje_comprador'] ?? ''));
            $aceptaCola          = (int)($input['acepta_cola'] ?? 0);

            if ($codigoProducto <= 0) {
                $this->json(400, [
                    'ok'      => false,
                    'mensaje' => 'Debes seleccionar una publicación válida.'
                ]);
                return;
            }

            if ($cantidad <= 0) {
                $this->json(400, [
                    'ok'      => false,
                    'mensaje' => 'La cantidad debe ser mayor a 0.'
                ]);
                return;
            }

            if ($direccionEntrega === '') {
                $this->json(400, [
                    'ok'      => false,
                    'mensaje' => 'Debes ingresar la dirección de entrega.'
                ]);
                return;
            }

            $model = new Pedido();
            $resultado = $model->registrarSolicitud([
                'codigo_producto'          => $codigoProducto,
                'codigo_usuario_comprador' => $codigoUsuarioComprador,
                'cantidad'                 => $cantidad,
                'tipo_entrega'             => $tipoEntrega,
                'fecha_hora_programada'    => $fechaHoraProgramada,
                'direccion_entrega'        => $direccionEntrega,
                'mensaje_comprador'        => $mensajeComprador,
                'acepta_cola'              => $aceptaCola
            ]);

            if (!$resultado['ok']) {
                $error = (string)($resultado['error'] ?? 'ERROR_REGISTRAR_PEDIDO');
                $mensaje = (string)($resultado['mensaje'] ?? 'No se pudo registrar el pedido.');

                $status = match ($error) {
                    'SIN_RESIDENCIA_ACTIVA',
                    'VENDEDOR_NO_DISPONIBLE',
                    'PUBLICACION_FUERA_DE_RESIDENCIA',
                    'PRODUCTO_NO_APROBADO',
                    'PUBLICACION_NO_VIGENTE',
                    'VENDEDOR_NO_HABILITADO',
                    'PRODUCTO_PROPIO',
                    'SALDO_INSUFICIENTE_BILLETERA' => 409,

                    'CANTIDAD_INVALIDA',
                    'DIRECCION_REQUERIDA',
                    'FECHA_PROGRAMADA_REQUERIDA',
                    'FECHA_PROGRAMADA_INVALIDA',
                    'FECHA_PROGRAMADA_FUERA_DE_RANGO',
                    'PARAMETROS_INVALIDOS' => 400,

                    'PRODUCTO_NO_ENCONTRADO' => 404,

                    default => 500
                };

                $payload = [
                    'ok'      => false,
                    'error'   => $error,
                    'mensaje' => $mensaje
                ];

                if ($error === 'SIN_RESIDENCIA_ACTIVA') {
                    $payload['redirect'] = rtrim(BASE_URL, '/') . '/mi-perfil';
                }

                if ($error === 'SALDO_INSUFICIENTE_BILLETERA') {
                    $payload['saldo_actual'] = (float)($resultado['saldo_actual'] ?? 0);
                    $payload['monto_requerido'] = (float)($resultado['monto_requerido'] ?? 0);
                    $payload['redirect'] = rtrim(BASE_URL, '/') . '/billetera';
                }

                $this->json($status, $payload);
                return;
            }

            $data = is_array($resultado['data'] ?? null) ? $resultado['data'] : [];

            $this->json(201, [
                'ok'      => true,
                'mensaje' => 'Solicitud de pedido registrada correctamente.',
                'data'    => $data
            ]);
            return;
        } catch (Throwable $e) {
            error_log('[EV][apiPedidoController][registrarPedido] ' . $e->getMessage());

            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Ocurrió un error al registrar la solicitud de pedido.'
            ]);
            return;
        }
    }

    public function confirmarCola($codigoPedido): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, [
                'ok'      => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        try {
            $codigoUsuarioComprador = $this->obtenerUsuarioAuth();
            $codigoPedido = (int)$codigoPedido;

            if ($codigoPedido <= 0) {
                $this->json(400, [
                    'ok'      => false,
                    'mensaje' => 'Código de pedido inválido.'
                ]);
                return;
            }

            $model = new Pedido();
            $resultado = $model->confirmarColaPorComprador($codigoPedido, $codigoUsuarioComprador);

            if (!$resultado['ok']) {
                $error = (string)($resultado['error'] ?? 'ERROR_CONFIRMAR_COLA');
                $mensaje = (string)($resultado['mensaje'] ?? 'No se pudo confirmar la cola.');

                $status = match ($error) {
                    'PEDIDO_NO_ENCONTRADO' => 404,
                    'ESTADO_NO_CONFIRMABLE' => 409,
                    default => 500
                };

                $this->json($status, [
                    'ok'      => false,
                    'error'   => $error,
                    'mensaje' => $mensaje,
                    'data'    => $resultado['data'] ?? null
                ]);
                return;
            }

            $this->json(200, [
                'ok'      => true,
                'mensaje' => 'Aceptaste continuar en la cola.',
                'data'    => $resultado['data'] ?? null
            ]);
            return;
        } catch (Throwable $e) {
            error_log('[EV][apiPedidoController][confirmarCola] ' . $e->getMessage());

            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'No se pudo confirmar la cola.'
            ]);
            return;
        }
    }

    public function obtenerEstadoSolicitud($codigoPedido): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(405, [
                'ok'      => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        try {
            $codigoUsuarioComprador = $this->obtenerUsuarioAuth();
            $codigoPedido = (int)$codigoPedido;

            if ($codigoPedido <= 0) {
                $this->json(400, [
                    'ok'      => false,
                    'mensaje' => 'Código de pedido inválido.'
                ]);
                return;
            }

            $model = new Pedido();
            $resultado = $model->obtenerEstadoSolicitudParaComprador($codigoPedido, $codigoUsuarioComprador);

            if (!$resultado['ok']) {
                $error = (string)($resultado['error'] ?? 'ERROR_OBTENER_ESTADO');
                $mensaje = (string)($resultado['mensaje'] ?? 'No se pudo obtener el estado de la solicitud.');

                $status = match ($error) {
                    'PEDIDO_NO_ENCONTRADO' => 404,
                    default => 500
                };

                $this->json($status, [
                    'ok'      => false,
                    'error'   => $error,
                    'mensaje' => $mensaje
                ]);
                return;
            }

            $this->json(200, [
                'ok'   => true,
                'data' => $resultado['data']
            ]);
            return;
        } catch (Throwable $e) {
            error_log('[EV][apiPedidoController][obtenerEstadoSolicitud] ' . $e->getMessage());

            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'No se pudo consultar el estado de la solicitud.'
            ]);
            return;
        }
    }

    public function cancelarSolicitud($codigoPedido): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, [
                'ok'      => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        try {
            $codigoUsuarioComprador = $this->obtenerUsuarioAuth();
            $codigoPedido = (int)$codigoPedido;
            $input = $this->leerInput();

            if ($codigoPedido <= 0) {
                $this->json(400, [
                    'ok'      => false,
                    'mensaje' => 'Código de pedido inválido.'
                ]);
                return;
            }

            $motivo = trim((string)($input['motivo_cancelacion'] ?? $input['motivo'] ?? ''));
            if ($motivo === '') {
                $motivo = 'Solicitud cancelada por el comprador.';
            }

            $model = new Pedido();
            $resultado = $model->cancelarSolicitudPorComprador($codigoPedido, $codigoUsuarioComprador, $motivo);

            if (!$resultado['ok']) {
                $error = (string)($resultado['error'] ?? 'ERROR_CANCELAR_SOLICITUD');
                $mensaje = (string)($resultado['mensaje'] ?? 'No se pudo cancelar la solicitud.');

                $status = match ($error) {
                    'PEDIDO_NO_ENCONTRADO' => 404,
                    'ESTADO_NO_CANCELABLE',
                    'CANCELACION_AUN_NO_DISPONIBLE' => 409,
                    default => 500
                };

                $this->json($status, [
                    'ok'      => false,
                    'error'   => $error,
                    'mensaje' => $mensaje,
                    'data'    => $resultado['data'] ?? null
                ]);
                return;
            }

            $this->json(200, [
                'ok'      => true,
                'mensaje' => 'Tu solicitud fue cancelada correctamente.',
                'data'    => $resultado['data']
            ]);
            return;
        } catch (Throwable $e) {
            error_log('[EV][apiPedidoController][cancelarSolicitud] ' . $e->getMessage());

            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'No se pudo cancelar la solicitud.'
            ]);
            return;
        }
    }

    public function listarPedidos(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(405, [
                'ok'      => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();

            $model = new Pedido();
            $pedidos = $model->listarPedidosEntrantes($codigoUsuario);

            foreach ($pedidos as &$p) {
                if (is_array($p)) {
                    $p = $this->agregarUrlImagenAItem($p);
                }
            }
            unset($p);

            $this->json(200, [
                'ok'   => true,
                'data' => $pedidos
            ]);
            return;
        } catch (Throwable $e) {
            error_log('[EV][apiPedidoController][listarPedidos] ' . $e->getMessage());

            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'No se pudieron obtener los pedidos entrantes.'
            ]);
            return;
        }
    }

    public function obtenerSolicitudActiva(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(405, [
                'ok'      => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        try {
            $codigoUsuarioComprador = $this->obtenerUsuarioAuth();

            $model = new Pedido();
            $resultado = $model->obtenerSolicitudActivaComprador($codigoUsuarioComprador);

            if (!$resultado['ok']) {
                $error = (string)($resultado['error'] ?? 'ERROR_OBTENER_SOLICITUD_ACTIVA');
                $mensaje = (string)($resultado['mensaje'] ?? 'No se pudo obtener la solicitud activa.');

                $this->json(500, [
                    'ok'      => false,
                    'error'   => $error,
                    'mensaje' => $mensaje
                ]);
                return;
            }

            $this->json(200, [
                'ok'   => true,
                'data' => $resultado['data'] ?? null
            ]);
            return;
        } catch (Throwable $e) {
            error_log('[EV][apiPedidoController][obtenerSolicitudActiva] ' . $e->getMessage());

            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'No se pudo obtener la solicitud activa.'
            ]);
            return;
        }
    }

    // =========================================================
    // VENDEDOR - MIS PEDIDOS
    // =========================================================
    public function listarMisPedidos(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(405, [
                'ok'      => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        try {
            $codigoUsuarioVendedor = $this->obtenerUsuarioAuth();

            $model = new Pedido();
            $resultado = $model->listarMisPedidosVendedor($codigoUsuarioVendedor);

            if (!$resultado['ok']) {
                $this->json(500, [
                    'ok'      => false,
                    'error'   => (string)($resultado['error'] ?? 'ERROR_LISTAR_MIS_PEDIDOS'),
                    'mensaje' => (string)($resultado['mensaje'] ?? 'No se pudo obtener la lista de pedidos.')
                ]);
                return;
            }

            $data = is_array($resultado['data'] ?? null) ? $resultado['data'] : [];
            $data = $this->agregarUrlImagenAGrupos($data);

            $this->json(200, [
                'ok'   => true,
                'data' => $data
            ]);
            return;
        } catch (Throwable $e) {
            error_log('[EV][apiPedidoController][listarMisPedidos] ' . $e->getMessage());

            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'No se pudo obtener el módulo Mis pedidos.'
            ]);
            return;
        }
    }

    public function aceptarSolicitud($codigoPedido): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, [
                'ok'      => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        try {
            $codigoUsuarioVendedor = $this->obtenerUsuarioAuth();
            $codigoPedido = (int)$codigoPedido;

            if ($codigoPedido <= 0) {
                $this->json(400, [
                    'ok'      => false,
                    'mensaje' => 'Código de pedido inválido.'
                ]);
                return;
            }

            $model = new Pedido();
            $resultado = $model->aceptarSolicitudPorVendedor($codigoPedido, $codigoUsuarioVendedor);

            if (!$resultado['ok']) {
                $error = (string)($resultado['error'] ?? 'ERROR_ACEPTAR_SOLICITUD');
                $mensaje = (string)($resultado['mensaje'] ?? 'No se pudo aceptar la solicitud.');

                $status = match ($error) {
                    'PEDIDO_NO_ENCONTRADO' => 404,
                    'ESTADO_NO_ACEPTABLE',
                    'VENDEDOR_CON_TURNO_ACTIVO' => 409,
                    default => 500
                };

                $this->json($status, [
                    'ok'      => false,
                    'error'   => $error,
                    'mensaje' => $mensaje,
                    'data'    => $resultado['data'] ?? null
                ]);
                return;
            }

            $data = $resultado['data'] ?? null;
            if (is_array($data)) {
                $data = $this->agregarUrlImagenAItem($data);
            }

            $this->json(200, [
                'ok'      => true,
                'mensaje' => 'Solicitud aceptada correctamente.',
                'data'    => $data
            ]);
            return;
        } catch (Throwable $e) {
            error_log('[EV][apiPedidoController][aceptarSolicitud] ' . $e->getMessage());

            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Ocurrió un error al aceptar la solicitud.'
            ]);
            return;
        }
    }

    public function rechazarSolicitud($codigoPedido): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, [
                'ok'      => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        try {
            $codigoUsuarioVendedor = $this->obtenerUsuarioAuth();
            $codigoPedido = (int)$codigoPedido;
            $input = $this->leerInput();

            if ($codigoPedido <= 0) {
                $this->json(400, [
                    'ok'      => false,
                    'mensaje' => 'Código de pedido inválido.'
                ]);
                return;
            }

            $motivo = trim((string)($input['motivo'] ?? $input['motivo_rechazo'] ?? ''));
            if ($motivo === '') {
                $this->json(400, [
                    'ok'      => false,
                    'error'   => 'MOTIVO_REQUERIDO',
                    'mensaje' => 'Debes indicar el motivo de rechazo.'
                ]);
                return;
            }

            $model = new Pedido();
            $resultado = $model->rechazarSolicitudPorVendedor($codigoPedido, $codigoUsuarioVendedor, $motivo);

            if (!$resultado['ok']) {
                $error = (string)($resultado['error'] ?? 'ERROR_RECHAZAR_SOLICITUD');
                $mensaje = (string)($resultado['mensaje'] ?? 'No se pudo rechazar la solicitud.');

                $status = match ($error) {
                    'PEDIDO_NO_ENCONTRADO' => 404,
                    'ESTADO_NO_RECHAZABLE' => 409,
                    'MOTIVO_REQUERIDO' => 400,
                    default => 500
                };

                $this->json($status, [
                    'ok'      => false,
                    'error'   => $error,
                    'mensaje' => $mensaje,
                    'data'    => $resultado['data'] ?? null
                ]);
                return;
            }

            $data = $resultado['data'] ?? null;
            if (is_array($data)) {
                $data = $this->agregarUrlImagenAItem($data);
            }

            $this->json(200, [
                'ok'      => true,
                'mensaje' => 'Solicitud rechazada correctamente.',
                'data'    => $data
            ]);
            return;
        } catch (Throwable $e) {
            error_log('[EV][apiPedidoController][rechazarSolicitud] ' . $e->getMessage());

            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Ocurrió un error al rechazar la solicitud.'
            ]);
            return;
        }
    }

    public function actualizarEstadoPedido($codigoPedido): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, [
                'ok'      => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        try {
            $codigoUsuarioVendedor = $this->obtenerUsuarioAuth();
            $codigoPedido = (int)$codigoPedido;
            $input = $this->leerInput();

            if ($codigoPedido <= 0) {
                $this->json(400, [
                    'ok'      => false,
                    'mensaje' => 'Código de pedido inválido.'
                ]);
                return;
            }

            $nuevoEstado = trim((string)($input['nuevo_estado'] ?? $input['estado'] ?? ''));
            if ($nuevoEstado === '') {
                $this->json(400, [
                    'ok'      => false,
                    'error'   => 'ESTADO_REQUERIDO',
                    'mensaje' => 'Debes indicar el nuevo estado.'
                ]);
                return;
            }

            $model = new Pedido();
            $resultado = $model->actualizarEstadoPedidoPorVendedor($codigoPedido, $codigoUsuarioVendedor, $nuevoEstado);

            if (!$resultado['ok']) {
                $error = (string)($resultado['error'] ?? 'ERROR_ACTUALIZAR_ESTADO_PEDIDO');
                $mensaje = (string)($resultado['mensaje'] ?? 'No se pudo actualizar el estado del pedido.');

                $status = match ($error) {
                    'PEDIDO_NO_ENCONTRADO' => 404,
                    'ESTADO_NO_ACTUALIZABLE',
                    'TRANSICION_INVALIDA' => 409,
                    default => 500
                };

                $this->json($status, [
                    'ok'      => false,
                    'error'   => $error,
                    'mensaje' => $mensaje,
                    'data'    => $resultado['data'] ?? null
                ]);
                return;
            }

            $data = $resultado['data'] ?? null;
            if (is_array($data)) {
                $data = $this->agregarUrlImagenAItem($data);
            }

            $this->json(200, [
                'ok'      => true,
                'mensaje' => 'Estado del pedido actualizado correctamente.',
                'data'    => $data
            ]);
            return;
        } catch (Throwable $e) {
            error_log('[EV][apiPedidoController][actualizarEstadoPedido] ' . $e->getMessage());

            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Ocurrió un error al actualizar el estado del pedido.'
            ]);
            return;
        }
    }

    public function listarMisPedidosComprador(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(405, [
                'ok'      => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        try {
            $codigoUsuarioComprador = $this->obtenerUsuarioAuth();

            $model = new Pedido();
            $resultado = $model->listarMisPedidosComprador($codigoUsuarioComprador);

            if (!$resultado['ok']) {
                $this->json(500, [
                    'ok'      => false,
                    'error'   => (string)($resultado['error'] ?? 'ERROR_LISTAR_MIS_PEDIDOS_COMPRADOR'),
                    'mensaje' => (string)($resultado['mensaje'] ?? 'No se pudo obtener la lista de pedidos del comprador.')
                ]);
                return;
            }

            $data = is_array($resultado['data'] ?? null) ? $resultado['data'] : [];
            $data = $this->agregarUrlImagenAGrupos($data);

            $this->json(200, [
                'ok'   => true,
                'data' => $data
            ]);
            return;
        } catch (Throwable $e) {
            error_log('[EV][apiPedidoController][listarMisPedidosComprador] ' . $e->getMessage());

            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'No se pudo obtener el módulo Mis pedidos del comprador.'
            ]);
            return;
        }
    }

    public function confirmarEntrega($codigoPedido): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, [
                'ok'      => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        try {
            $codigoUsuarioComprador = $this->obtenerUsuarioAuth();
            $codigoPedido = (int)$codigoPedido;

            if ($codigoPedido <= 0) {
                $this->json(400, [
                    'ok'      => false,
                    'mensaje' => 'Código de pedido inválido.'
                ]);
                return;
            }

            $model = new Pedido();
            $resultado = $model->confirmarEntregaPorComprador($codigoPedido, $codigoUsuarioComprador);

            if (!$resultado['ok']) {
                $error = (string)($resultado['error'] ?? 'ERROR_CONFIRMAR_ENTREGA');
                $mensaje = (string)($resultado['mensaje'] ?? 'No se pudo confirmar la entrega.');

                $status = match ($error) {
                    'PEDIDO_NO_ENCONTRADO' => 404,
                    'ESTADO_NO_CONFIRMABLE' => 409,
                    default => 500
                };

                $this->json($status, [
                    'ok'      => false,
                    'error'   => $error,
                    'mensaje' => $mensaje
                ]);
                return;
            }

            $this->json(200, [
                'ok'      => true,
                'mensaje' => 'La entrega fue confirmada correctamente.',
                'data'    => $resultado['data'] ?? null
            ]);
            return;
        } catch (Throwable $e) {
            error_log('[EV][apiPedidoController][confirmarEntrega] ' . $e->getMessage());

            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'No se pudo confirmar la entrega.'
            ]);
            return;
        }
    }
}
