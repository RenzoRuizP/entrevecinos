<?php
// controllers/api/apiRecargaSaldoController.php

require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/RecargaSaldo.php';

class apiRecargaSaldoController
{
    public function registrar()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            // 1) Usuario autenticado
            $usuarioAuth = $this->obtenerUsuarioAuth();

            // Si no hay usuario
            if (!$usuarioAuth) {
                http_response_code(401);
                echo json_encode([
                    'ok'      => false,
                    'error'   => 'USUARIO_NO_ENCONTRADO',
                    'mensaje' => 'No se pudo identificar al usuario. Vuelve a iniciar sesión.'
                ]);
                return;
            }

            // Si hay usuario pero no código (este es tu caso actual)
            if (empty($usuarioAuth['codigo_usuario'])) {
                http_response_code(401);
                echo json_encode([
                    'ok'      => false,
                    'error'   => 'CODIGO_USUARIO_NO_DISPONIBLE',
                    'mensaje' => 'Se encontró el usuario, pero no se obtuvo su código. Revisa el SELECT de DatosUsuario() para incluir codigo_usuario.'
                ]);
                return;
            }

            // 2) Campos del formulario (multipart/form-data)
            $metodo = strtolower(trim($_POST['recarga_tipo'] ?? ''));
            $monto  = (float)($_POST['recarga_monto'] ?? 0);
            $idOp   = trim($_POST['recarga_operacion'] ?? '');

            if (!in_array($metodo, ['yape', 'plin'], true)) {
                http_response_code(422);
                echo json_encode(['ok' => false, 'mensaje' => 'Tipo de billetera inválido (Yape o Plin).']);
                return;
            }
            if ($monto <= 0) {
                http_response_code(422);
                echo json_encode(['ok' => false, 'mensaje' => 'Ingresa un monto válido mayor a 0.']);
                return;
            }
            if (strlen($idOp) < 4) {
                http_response_code(422);
                echo json_encode(['ok' => false, 'mensaje' => 'Ingresa un ID de operación válido (mínimo 4 caracteres).']);
                return;
            }

            // 3) Archivo comprobante
            if (!isset($_FILES['recarga_imagen']) || $_FILES['recarga_imagen']['error'] !== UPLOAD_ERR_OK) {
                http_response_code(422);
                echo json_encode(['ok' => false, 'mensaje' => 'Sube una imagen del comprobante (jpg/png).']);
                return;
            }

            $file = $_FILES['recarga_imagen'];

            $maxBytes = 3 * 1024 * 1024; // 3MB
            if ($file['size'] <= 0 || $file['size'] > $maxBytes) {
                http_response_code(422);
                echo json_encode(['ok' => false, 'mensaje' => 'El comprobante debe pesar máximo 3MB.']);
                return;
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($file['tmp_name']) ?: '';
            $ext = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                default      => ''
            };

            if ($ext === '') {
                http_response_code(422);
                echo json_encode(['ok' => false, 'mensaje' => 'Formato no permitido. Sube una imagen JPG o PNG.']);
                return;
            }

            // 4) Evitar duplicidad (por usuario + método + id_operacion)
            $model = new RecargaSaldo();
            $codigoUsuario = (int)$usuarioAuth['codigo_usuario'];

            if ($model->existeOperacionParaUsuario($codigoUsuario, $metodo, $idOp)) {
                http_response_code(409);
                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Ya registraste una recarga con ese ID de operación. Verifica e intenta con otro.'
                ]);
                return;
            }

            // 5) Guardar archivo
            $dir = __DIR__ . '/../../resources/images/recargas';
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }

            $nombreSeguro = 'recarga_' . $codigoUsuario . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $rutaAbs = $dir . '/' . $nombreSeguro;

            if (!move_uploaded_file($file['tmp_name'], $rutaAbs)) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'mensaje' => 'No se pudo guardar el comprobante.']);
                return;
            }

            $rutaRel = 'resources/images/recargas/' . $nombreSeguro;

            // 6) Insert DB
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
            ]);
            return;

        } catch (Throwable $e) {
            error_log('[EV][apiRecargaSaldoController::registrar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'ok'      => false,
                'mensaje' => 'Error interno al registrar la recarga.',
            ]);
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
        if (!$payload) return null;

        // 1) Intentar traer email desde distintas variantes
        $email = '';
        if (!empty($payload['email'])) $email = (string)$payload['email'];
        elseif (!empty($payload['sub'])) $email = (string)$payload['sub'];
        elseif (!empty($payload['usuario']['email'])) $email = (string)$payload['usuario']['email'];

        $email = trim($email);

        // 2) Intentar traer codigo_usuario desde el token (si existiera)
        $codigoDesdeToken = null;
        if (!empty($payload['codigo_usuario'])) $codigoDesdeToken = (int)$payload['codigo_usuario'];
        elseif (!empty($payload['codigoUsuario'])) $codigoDesdeToken = (int)$payload['codigoUsuario'];
        elseif (!empty($payload['usuario']['codigo_usuario'])) $codigoDesdeToken = (int)$payload['usuario']['codigo_usuario'];

        // 3) Consultar usuario por email (tu estándar)
        $datos = null;
        if ($email !== '') {
            $u = new User();
            $datos = $u->DatosUsuario($email);
        }

        if (!$datos) {
            // Si no encontró por email pero sí vino el código en token, al menos devolvemos eso
            if ($codigoDesdeToken) {
                return ['codigo_usuario' => $codigoDesdeToken, 'email' => $email];
            }
            return null;
        }

        // 4) Normalizar codigo_usuario si tu SELECT lo retorna con otro nombre
        if (empty($datos['codigo_usuario'])) {
            // aliases frecuentes
            if (!empty($datos['id_usuario'])) $datos['codigo_usuario'] = (int)$datos['id_usuario'];
            elseif (!empty($datos['codigoUsuario'])) $datos['codigo_usuario'] = (int)$datos['codigoUsuario'];
            elseif (!empty($datos['codigo'])) $datos['codigo_usuario'] = (int)$datos['codigo'];
            elseif ($codigoDesdeToken) $datos['codigo_usuario'] = (int)$codigoDesdeToken;
        }

        // Asegurar email presente
        if (empty($datos['email']) && $email !== '') $datos['email'] = $email;

        return $datos;
    }
}
