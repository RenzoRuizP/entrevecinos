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

            $input = $_POST;
            if (empty($input)) {
                $raw = file_get_contents('php://input');
                $input = json_decode($raw, true) ?: [];
            }

            $codigoProducto       = (int)($input['codigo_producto'] ?? 0);
            $cantidad             = (int)($input['cantidad'] ?? 0);
            $tipoEntrega          = trim((string)($input['tipo_entrega'] ?? 'inmediata'));
            $fechaHoraProgramada  = $input['fecha_hora_programada'] ?? null;
            $direccionEntrega     = trim((string)($input['direccion_entrega'] ?? ''));
            $mensajeComprador     = trim((string)($input['mensaje_comprador'] ?? ''));

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
                'mensaje_comprador'        => $mensajeComprador
            ]);

            if (!$resultado['ok']) {
                $error = (string)($resultado['error'] ?? 'ERROR_REGISTRAR_PEDIDO');
                $mensaje = (string)($resultado['mensaje'] ?? 'No se pudo registrar el pedido.');

                $status = match ($error) {
                    'SIN_RESIDENCIA_ACTIVA',
                    'VENDEDOR_NO_DISPONIBLE',
                    'PUBLICACION_FUERA_DE_CONJUNTO',
                    'PRODUCTO_NO_APROBADO',
                    'PUBLICACION_NO_VIGENTE',
                    'VENDEDOR_NO_HABILITADO',
                    'PRODUCTO_PROPIO' => 409,

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

                $this->json($status, $payload);
                return;
            }

            $this->json(201, [
                'ok'      => true,
                'mensaje' => 'Solicitud de pedido registrada correctamente.',
                'data'    => $resultado['data']
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

            $baseUrl = rtrim(BASE_URL, '/');
            foreach ($pedidos as &$p) {
                $ruta = trim((string)($p['imagen_portada'] ?? ''));
                $p['imagen_portada_url'] = $ruta !== ''
                    ? $baseUrl . '/' . ltrim($ruta, '/')
                    : '';
            }
            unset($p);

            $this->json(200, [
                'ok'      => true,
                'pedidos' => $pedidos
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
}