<?php
// controllers/api/apiRecargaSaldoController.php

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/RecargaSaldo.php';
require_once __DIR__ . '/../../models/Notificacion.php';
require_once __DIR__ . '/../../models/ConfiguracionPlataforma.php';

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

            if (!$this->recargasDisponiblesParaUsuario((int)$usuarioAuth['codigo_usuario'])) {
                $this->responderRecargasNoDisponibles();
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

            if (
                !isset($_FILES['recarga_imagen']) ||
                !is_array($_FILES['recarga_imagen']) ||
                (int)$_FILES['recarga_imagen']['error'] !== UPLOAD_ERR_OK
            ) {
                http_response_code(422);
                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Sube una imagen del comprobante (jpg/png).'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $file = $_FILES['recarga_imagen'];

            $validacionArchivo = $this->validarArchivoImagen($file);
            if (!$validacionArchivo['ok']) {
                http_response_code(422);
                echo json_encode([
                    'ok' => false,
                    'mensaje' => $validacionArchivo['mensaje']
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $ext = $validacionArchivo['ext'];

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

            if ($model->existeOperacionGlobal($metodo, $idOp)) {
                http_response_code(409);
                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Ese ID de operación ya fue registrado. Verifica los datos e intenta nuevamente.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $guardado = $this->guardarImagenRecarga($codigoUsuario, $file, $ext);
            if (!$guardado['ok']) {
                http_response_code(500);
                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'No se pudo guardar el comprobante.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $rutaRel = $guardado['ruta_rel'];

            $codigoRecarga = $model->registrarRecarga(
                $codigoUsuario,
                $monto,
                $metodo,
                $idOp,
                $rutaRel
            );

            $notificacionesSoporte = $this->notificarEquipoSoporteRecarga(
                $usuarioAuth,
                $codigoRecarga,
                $monto,
                $metodo,
                false
            );

            echo json_encode([
                'ok'      => true,
                'id'      => $codigoRecarga,
                'estado'  => 'pendiente',
                'notificaciones_soporte' => $notificacionesSoporte,
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

    public function subsanar($codigo_recarga)
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

            $codigoRecarga = (int)$codigo_recarga;
            if ($codigoRecarga <= 0) {
                http_response_code(422);
                echo json_encode([
                    'ok'      => false,
                    'error'   => 'CODIGO_INVALIDO',
                    'mensaje' => 'La recarga a subsanar no es válida.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if (!$this->recargasDisponiblesParaUsuario((int)$usuarioAuth['codigo_usuario'])) {
                $this->responderRecargasNoDisponibles();
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

            $model = new RecargaSaldo();
            $codigoUsuario = (int)$usuarioAuth['codigo_usuario'];

            $recarga = $model->obtenerRecargaUsuarioPorId($codigoRecarga, $codigoUsuario);
            if (!$recarga) {
                http_response_code(404);
                echo json_encode([
                    'ok' => false,
                    'error' => 'RECARGA_NO_ENCONTRADA',
                    'mensaje' => 'La recarga no fue encontrada.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if (strtolower((string)($recarga['estado'] ?? '')) !== 'observada') {
                http_response_code(409);
                echo json_encode([
                    'ok' => false,
                    'error' => 'RECARGA_NO_SUBSANABLE',
                    'mensaje' => 'Solo puedes subsanar recargas observadas.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if ($model->existeOperacionEnOtroRegistro($codigoRecarga, $metodo, $idOp)) {
                http_response_code(409);
                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Ese ID de operación ya pertenece a otra recarga. Verifica los datos.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $rutaRel = (string)($recarga['comprobante_path'] ?? '');
            $nuevoArchivoSubido = false;

            if (
                isset($_FILES['recarga_imagen']) &&
                is_array($_FILES['recarga_imagen']) &&
                (int)$_FILES['recarga_imagen']['error'] !== UPLOAD_ERR_NO_FILE
            ) {
                if ((int)$_FILES['recarga_imagen']['error'] !== UPLOAD_ERR_OK) {
                    http_response_code(422);
                    echo json_encode([
                        'ok' => false,
                        'mensaje' => 'No se pudo procesar la nueva imagen del comprobante.'
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }

                $file = $_FILES['recarga_imagen'];

                $validacionArchivo = $this->validarArchivoImagen($file);
                if (!$validacionArchivo['ok']) {
                    http_response_code(422);
                    echo json_encode([
                        'ok' => false,
                        'mensaje' => $validacionArchivo['mensaje']
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }

                $guardado = $this->guardarImagenRecarga($codigoUsuario, $file, $validacionArchivo['ext']);
                if (!$guardado['ok']) {
                    http_response_code(500);
                    echo json_encode([
                        'ok' => false,
                        'mensaje' => 'No se pudo guardar el nuevo comprobante.'
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }

                $rutaRel = $guardado['ruta_rel'];
                $nuevoArchivoSubido = true;
            }

            $ok = $model->subsanarRecargaObservada(
                $codigoRecarga,
                $codigoUsuario,
                $monto,
                $metodo,
                $idOp,
                $rutaRel
            );

            if (!$ok) {
                http_response_code(500);
                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'No se pudo reenviar la recarga observada.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $notificacionesSoporte = $this->notificarEquipoSoporteRecarga(
                $usuarioAuth,
                $codigoRecarga,
                $monto,
                $metodo,
                true
            );

            echo json_encode([
                'ok' => true,
                'id' => $codigoRecarga,
                'estado' => 'pendiente',
                'reenviada_usuario' => 1,
                'notificaciones_soporte' => $notificacionesSoporte,
                'mensaje' => $nuevoArchivoSubido
                    ? 'Recarga corregida y reenviada a validación.'
                    : 'Recarga corregida y reenviada a validación.'
            ], JSON_UNESCAPED_UNICODE);
            return;

        } catch (Throwable $e) {
            error_log('[EV][apiRecargaSaldoController::subsanar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'ok'      => false,
                'mensaje' => 'Error interno al subsanar la recarga.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
    }

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

    private function recargasDisponiblesParaUsuario(int $codigoUsuario): bool
    {
        if ($codigoUsuario <= 0) {
            return false;
        }

        try {
            $estado = (new ConfiguracionPlataforma())->obtenerEstadoBilleteraUsuario($codigoUsuario);
            return (bool)($estado['recargas_disponibles'] ?? false);
        } catch (Throwable $e) {
            error_log('[EV][apiRecargaSaldoController::recargasDisponiblesParaUsuario] ' . $e->getMessage());
            return false;
        }
    }

    private function responderRecargasNoDisponibles(): void
    {
        http_response_code(403);
        echo json_encode([
            'ok' => false,
            'error' => 'RECARGAS_NO_DISPONIBLES',
            'mensaje' => 'Las recargas no están disponibles para tu comunidad en este momento.',
            'redirect' => rtrim(BASE_URL, '/') . '/MenuPrincipal',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function obtenerUsuarioAuth(): ?array
    {
        $token = $_COOKIE['auth_token'] ?? null;
        if (!$token) return null;

        $payload = SesionJWT::verificarToken($token);
        if (!$payload || !is_array($payload)) return null;

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

        if ($codigoUsuario > 0) {
            return [
                'codigo_usuario' => $codigoUsuario,
                'email' => $email
            ];
        }

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

    private function notificarEquipoSoporteRecarga(
        array $usuarioAuth,
        int $codigoRecarga,
        float $monto,
        string $metodo,
        bool $esReenvio
    ): int {
        try {
            $codigoUsuario = (int)($usuarioAuth['codigo_usuario'] ?? 0);
            $nombre = trim((string)($usuarioAuth['nombre'] ?? ''));
            $email = trim((string)($usuarioAuth['email'] ?? ''));

            if ($nombre === '' && $email !== '') {
                try {
                    $usuarioModel = new User();
                    $datos = $usuarioModel->DatosUsuario($email);
                    if (is_array($datos)) {
                        $nombre = trim((string)($datos['nombre'] ?? ''));
                    }
                } catch (Throwable $eUsuario) {
                    error_log('[EV][apiRecargaSaldoController::notificarEquipoSoporteRecarga][usuario] ' . $eUsuario->getMessage());
                }
            }

            if ($nombre === '') {
                $nombre = $codigoUsuario > 0 ? 'Vecino #' . $codigoUsuario : 'Un vecino';
            }

            $metodoVisible = strtoupper($metodo);
            $montoVisible = 'S/ ' . number_format($monto, 2, '.', '');
            $titulo = $esReenvio
                ? 'Recarga corregida por revisar'
                : 'Nueva solicitud de recarga';
            $mensaje = $esReenvio
                ? $nombre . ' corrigió y reenvió una recarga de ' . $montoVisible . ' mediante ' . $metodoVisible . '.'
                : $nombre . ' envió una solicitud de recarga de ' . $montoVisible . ' mediante ' . $metodoVisible . '.';

            $notificacion = new Notificacion();
            return $notificacion->crearParaRoles([1, 3], [
                'categoria' => Notificacion::CAT_BILLETERA,
                'subcategoria' => 'recarga_pendiente_soporte',
                'referencia_id' => $codigoRecarga,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'payload' => [
                    'codigo_recarga' => $codigoRecarga,
                    'codigo_usuario' => $codigoUsuario,
                    'monto' => $monto,
                    'metodo' => $metodo,
                    'estado' => 'pendiente',
                    'reenviada_usuario' => $esReenvio ? 1 : 0,
                    'rol_destino' => 'soporte',
                    'ruta' => '/atender-recargas?estado=pendiente&recarga=' . $codigoRecarga,
                ],
            ]);
        } catch (Throwable $eNotif) {
            error_log('[EV][apiRecargaSaldoController::notificarEquipoSoporteRecarga] ' . $eNotif->getMessage());
            return 0;
        }
    }

    private function validarArchivoImagen(array $file): array
    {
        $maxBytes = 3 * 1024 * 1024;
        if ((int)($file['size'] ?? 0) <= 0 || (int)($file['size'] ?? 0) > $maxBytes) {
            return [
                'ok' => false,
                'mensaje' => 'El comprobante debe pesar máximo 3MB.'
            ];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file((string)$file['tmp_name']) ?: '';

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            default      => ''
        };

        if ($ext === '') {
            return [
                'ok' => false,
                'mensaje' => 'Formato no permitido. Sube una imagen JPG o PNG.'
            ];
        }

        return [
            'ok' => true,
            'ext' => $ext
        ];
    }

    private function guardarImagenRecarga(int $codigoUsuario, array $file, string $ext): array
    {
        $dir = __DIR__ . '/../../resources/images/recargas';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $nombreSeguro = 'recarga_' . $codigoUsuario . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $rutaAbs = $dir . '/' . $nombreSeguro;

        if (!move_uploaded_file((string)$file['tmp_name'], $rutaAbs)) {
            return ['ok' => false];
        }

        return [
            'ok' => true,
            'ruta_rel' => 'resources/images/recargas/' . $nombreSeguro
        ];
    }
}