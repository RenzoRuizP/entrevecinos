<?php
// controllers/api/apiRecargaSaldoController.php

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/RecargaSaldo.php';

class apiRecargaSaldoController
{
    public function registrar()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $usuarioAuth = $this->obtenerUsuarioAuth();

            if (!$usuarioAuth || empty($usuarioAuth['codigo_usuario'])) {
                http_response_code(401);
                echo json_encode([
                    'ok'      => false,
                    'error'   => 'USUARIO_NO_ENCONTRADO',
                    'mensaje' => 'No se pudo identificar al usuario. Vuelve a iniciar sesión.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $metodo = strtolower(trim((string)($_POST['recarga_tipo'] ?? '')));
            $monto  = (float)($_POST['recarga_monto'] ?? 0);
            $idOp   = trim((string)($_POST['recarga_operacion'] ?? ''));

            if (!in_array($metodo, ['yape', 'plin'], true)) {
                http_response_code(422);
                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Tipo de billetera inválido (Yape o Plin).'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if ($monto <= 0) {
                http_response_code(422);
                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Ingresa un monto válido mayor a 0.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if (strlen($idOp) < 4) {
                http_response_code(422);
                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Ingresa un ID de operación válido (mínimo 4 caracteres).'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if (!isset($_FILES['recarga_imagen']) || !is_array($_FILES['recarga_imagen']) || (int)$_FILES['recarga_imagen']['error'] !== UPLOAD_ERR_OK) {
                http_response_code(422);
                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Sube una imagen del comprobante (jpg/png).'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $file = $_FILES['recarga_imagen'];

            $maxBytes = 3 * 1024 * 1024;
            if ((int)$file['size'] <= 0 || (int)$file['size'] > $maxBytes) {
                http_response_code(422);
                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'El comprobante debe pesar máximo 3MB.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file((string)$file['tmp_name']) ?: '';

            $ext = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                default      => ''
            };

            if ($ext === '') {
                http_response_code(422);
                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Formato no permitido. Sube una imagen JPG o PNG.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $model = new RecargaSaldo();
            $codigoUsuario = (int)$usuarioAuth['codigo_usuario'];

            if ($model->existeOperacionParaUsuario($codigoUsuario, $metodo, $idOp)) {
                http_response_code(409);
                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Ya registraste una recarga con ese ID de operación. Verifica e intenta con otro.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $dir = __DIR__ . '/../../resources/images/recargas';
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }

            $nombreSeguro = 'recarga_' . $codigoUsuario . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $rutaAbs = $dir . '/' . $nombreSeguro;

            if (!move_uploaded_file((string)$file['tmp_name'], $rutaAbs)) {
                http_response_code(500);
                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'No se pudo guardar el comprobante.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $rutaRel = 'resources/images/recargas/' . $nombreSeguro;

            $codigoRecarga = $model->registrarRecarga(
                $codigoUsuario,
                $monto,
                $metodo,
                $idOp,
                $rutaRel
            );

            echo json_encode([
                'ok'      => true,
                'id'      => $codigoRecarga,
                'estado'  => 'pendiente',
                'mensaje' => 'Recarga registrada. Quedará pendiente de validación por Soporte.'
            ], JSON_UNESCAPED_UNICODE);
            return;

        } catch (Throwable $e) {
            error_log('[EV][apiRecargaSaldoController::registrar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'ok'      => false,
                'mensaje' => 'Error interno al registrar la recarga.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
    }

    // ✅ GET /api/recargas/mis
    public function mis()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $usuarioAuth = $this->obtenerUsuarioAuth();

            if (!$usuarioAuth || empty($usuarioAuth['codigo_usuario'])) {
                http_response_code(401);
                echo json_encode([
                    'ok' => false,
                    'error' => 'UNAUTHORIZED',
                    'mensaje' => 'Sesión inválida.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $codigoUsuario = (int)$usuarioAuth['codigo_usuario'];
            $limit = (int)($_GET['limit'] ?? 20);
            if ($limit < 1) $limit = 20;
            if ($limit > 50) $limit = 50;

            $model = new RecargaSaldo();
            $items = $model->listarMisRecargas($codigoUsuario, $limit);

            echo json_encode([
                'ok' => true,
                'data' => $items
            ], JSON_UNESCAPED_UNICODE);
            return;

        } catch (Throwable $e) {
            error_log('[EV][apiRecargaSaldoController::mis] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'Error interno al listar recargas.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
    }

    // =========================================================
    // Helpers
    // =========================================================
    private function obtenerUsuarioAuth(): ?array
    {
        $token = $_COOKIE['auth_token'] ?? null;
        if (!$token) return null;

        $payload = SesionJWT::verificarToken($token);
        if (!$payload || !is_array($payload)) return null;

        // ✅ FUENTE DE VERDAD: token
        $codigoUsuario = 0;
        if (!empty($payload['codigo_usuario'])) {
            $codigoUsuario = (int)$payload['codigo_usuario'];
        } elseif (!empty($payload['codigoUsuario'])) {
            $codigoUsuario = (int)$payload['codigoUsuario'];
        } elseif (!empty($payload['usuario']['codigo_usuario'])) {
            $codigoUsuario = (int)$payload['usuario']['codigo_usuario'];
        }

        $email = '';
        if (!empty($payload['email'])) {
            $email = trim((string)$payload['email']);
        } elseif (!empty($payload['sub'])) {
            $email = trim((string)$payload['sub']);
        } elseif (!empty($payload['usuario']['email'])) {
            $email = trim((string)$payload['usuario']['email']);
        }

        // Si el token ya trae codigo_usuario, con eso basta
        if ($codigoUsuario > 0) {
            return [
                'codigo_usuario' => $codigoUsuario,
                'email' => $email
            ];
        }

        // Fallback: enriquecer con User.php solo si hay email
        if ($email !== '') {
            try {
                $u = new User();
                $datos = $u->DatosUsuario($email);

                if ($datos && is_array($datos)) {
                    if (empty($datos['codigo_usuario']) && !empty($datos['id_usuario'])) {
                        $datos['codigo_usuario'] = (int)$datos['id_usuario'];
                    } elseif (empty($datos['codigo_usuario']) && !empty($datos['codigoUsuario'])) {
                        $datos['codigo_usuario'] = (int)$datos['codigoUsuario'];
                    } elseif (empty($datos['codigo_usuario']) && !empty($datos['codigo'])) {
                        $datos['codigo_usuario'] = (int)$datos['codigo'];
                    }

                    if (!empty($datos['codigo_usuario'])) {
                        if (empty($datos['email'])) $datos['email'] = $email;
                        return $datos;
                    }
                }
            } catch (Throwable $e) {
                error_log('[EV][apiRecargaSaldoController::obtenerUsuarioAuth] ' . $e->getMessage());
            }
        }

        return null;
    }
}