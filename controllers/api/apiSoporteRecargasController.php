<?php
// controllers/api/apiSoporteRecargasController.php

require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/RecargaSaldo.php';
require_once __DIR__ . '/../../models/Billetera.php';

class apiSoporteRecargasController
{
    private function obtenerUsuarioAuth(): ?array
    {
        $token = $_COOKIE['auth_token'] ?? null;
        if (!$token) return null;

        $usuario = SesionJWT::verificarToken($token);
        return is_array($usuario) ? $usuario : null;
    }

    public function listar()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $usuarioAuth = $this->obtenerUsuarioAuth();
            if (!$usuarioAuth || empty($usuarioAuth['codigo_usuario'])) {
                http_response_code(401);
                echo json_encode([
                    'ok' => false,
                    'error' => 'USUARIO_NO_ENCONTRADO',
                    'mensaje' => 'No se pudo identificar al usuario. Vuelve a iniciar sesión.'
                ]);
                return;
            }

            $filtros = [
                'estado' => $_GET['estado'] ?? 'pendiente',
                'rango'  => $_GET['rango'] ?? '7',
                'q'      => $_GET['q'] ?? '',
                'page'   => $_GET['page'] ?? 1,
                'size'   => $_GET['size'] ?? 10,
            ];

            $m = new RecargaSaldo();
            $data = $m->listarSoporte($filtros);

            echo json_encode([
                'ok' => true,
                'data' => $data
            ]);
            return;

        } catch (Throwable $e) {
            error_log('[EV][apiSoporteRecargasController::listar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'error' => 'ERROR_SERVIDOR',
                'mensaje' => 'Ocurrió un error al listar recargas.',
                'detalle' => $e->getMessage()
            ]);
            return;
        }
    }

    public function actualizarEstado($id)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $usuarioAuth = $this->obtenerUsuarioAuth();
            if (!$usuarioAuth || empty($usuarioAuth['codigo_usuario'])) {
                http_response_code(401);
                echo json_encode([
                    'ok' => false,
                    'error' => 'NO_AUTORIZADO',
                    'mensaje' => 'Tu sesión ha expirado. Vuelve a iniciar sesión.'
                ]);
                return;
            }

            $codigoSoporte = (int)$usuarioAuth['codigo_usuario'];
            $codigoRecarga = (int)$id;

            $estado = strtolower(trim($_POST['estado'] ?? ''));
            $comentario = trim($_POST['comentario'] ?? '');

            $permitidos = ['pendiente', 'observada', 'aprobada', 'rechazada'];
            if (!in_array($estado, $permitidos, true)) {
                http_response_code(400);
                echo json_encode([
                    'ok' => false,
                    'error' => 'ESTADO_INVALIDO',
                    'mensaje' => 'Estado inválido.'
                ]);
                return;
            }

            if (($estado === 'observada' || $estado === 'rechazada') && mb_strlen($comentario) < 3) {
                http_response_code(400);
                echo json_encode([
                    'ok' => false,
                    'error' => 'COMENTARIO_REQUERIDO',
                    'mensaje' => 'Debes ingresar un comentario para Observada o Rechazada.'
                ]);
                return;
            }

            $recargaModel = new RecargaSaldo();
            $rec = $recargaModel->obtenerPorId($codigoRecarga);

            if (!$rec) {
                http_response_code(404);
                echo json_encode([
                    'ok' => false,
                    'error' => 'RECARGA_NO_ENCONTRADA',
                    'mensaje' => 'No se encontró la recarga.'
                ]);
                return;
            }

            $estadoActual = strtolower($rec['estado'] ?? 'pendiente');

            // Si ya está aprobada y vuelven a aprobar, no repetir acreditación.
            if ($estado === 'aprobada' && $estadoActual === 'aprobada') {
                echo json_encode([
                    'ok' => true,
                    'mensaje' => 'La recarga ya estaba aprobada. No se realizó ningún cambio.'
                ]);
                return;
            }

            // 1) Actualizar estado
            $ok = $recargaModel->actualizarEstado(
                $codigoRecarga,
                $estado,
                $comentario !== '' ? $comentario : null,
                $codigoSoporte
            );

            if (!$ok) {
                http_response_code(500);
                echo json_encode([
                    'ok' => false,
                    'error' => 'NO_SE_PUDO_ACTUALIZAR',
                    'mensaje' => 'No se pudo actualizar el estado.'
                ]);
                return;
            }

            // 2) Si APROBADA => acreditar billetera
            if ($estado === 'aprobada') {
                $codigoUsuario = (int)$rec['codigo_usuario'];
                $monto = (float)$rec['monto'];
                $metodo = strtoupper((string)($rec['metodo'] ?? 'YAPE')); // yape/plin => YAPE/PLIN

                $billeteraModel = new Billetera();

                // Blindaje: evitar doble acreditación por reintentos/aprobaciones repetidas
                if (method_exists($billeteraModel, 'yaFueAcreditadaRecarga')) {
                    if ($billeteraModel->yaFueAcreditadaRecarga($codigoUsuario, $codigoRecarga)) {
                        echo json_encode([
                            'ok' => true,
                            'mensaje' => 'Estado aprobado. El saldo ya había sido acreditado previamente.'
                        ]);
                        return;
                    }
                }

                $res = $billeteraModel->acreditarPorRecargaManual(
                    $codigoUsuario,
                    $monto,
                    $codigoRecarga,
                    $metodo,
                    false,
                    null
                );

                if (empty($res['ok'])) {
                    // OJO: el estado ya quedó aprobado. Registramos el fallo para auditoría.
                    error_log('[EV][RECARGA][APROBAR] Falló acreditación billetera. recarga=' . $codigoRecarga . ' usuario=' . $codigoUsuario . ' resp=' . json_encode($res));
                    http_response_code(500);
                    echo json_encode([
                        'ok' => false,
                        'error' => 'ACREDITACION_FALLIDA',
                        'mensaje' => $res['mensaje'] ?? 'Se aprobó la recarga pero no se pudo acreditar el saldo. Revisa logs.'
                    ]);
                    return;
                }
            }

            echo json_encode([
                'ok' => true,
                'mensaje' => 'Estado actualizado correctamente.'
            ]);
            return;

        } catch (Throwable $e) {
            error_log('[EV][apiSoporteRecargasController::actualizarEstado] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'error' => 'ERROR_SERVIDOR',
                'mensaje' => 'Ocurrió un error al actualizar el estado.',
                'detalle' => $e->getMessage()
            ]);
            return;
        }
    }
}
