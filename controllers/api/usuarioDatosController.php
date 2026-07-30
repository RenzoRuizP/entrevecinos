<?php
// controllers/api/usuarioDatosController.php
// EV — API: Datos de usuario + Solicitud cambio de residencia + Guardado por secciones + Foto de perfil

declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../models/UsuarioResidenciaSolicitud.php';
require_once __DIR__ . '/../../models/CondominioModel.php';
require_once __DIR__ . '/../../models/Urbanizacion.php';
require_once __DIR__ . '/../../models/UsuarioSoporte.php';
require_once __DIR__ . '/../../models/Notificacion.php';
require_once __DIR__ . '/../../models/SesionJWT.php';

class usuarioDatosController
{
    private const MAX_FOTO_PERFIL_BYTES = 2 * 1024 * 1024;

    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function authUserId(): int
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        return (int)($auth['codigo_usuario'] ?? 0);
    }

    private function normalizeTipo(string $tipo): string
    {
        return strtolower(trim($tipo));
    }

    private function obtenerDireccionDesdeBD(string $tipo, int $codCon, int $codUrb): string
    {
        if ($tipo === 'condominio') {
            $m = new CondominioModel();
            return trim((string)$m->obtenerDireccionPorId($codCon));
        }

        $m = new Urbanizacion();
        return trim((string)$m->obtenerDireccionPorId($codUrb));
    }

    public function obtenerDatos(): void
    {
        try {
            $codigoUsuario = $this->authUserId();
            if ($codigoUsuario <= 0) {
                $this->json(401, ['success' => false, 'error' => 'UNAUTHORIZED']);
                return;
            }

            $usuarioModel = new Usuario();
            $usuario = $usuarioModel->obtenerPorCodigo($codigoUsuario);

            if (!$usuario) {
                $this->json(404, ['success' => false, 'error' => 'USUARIO_NO_ENCONTRADO']);
                return;
            }

            $this->json(200, ['success' => true, 'usuario' => $usuario]);
        } catch (Throwable $e) {
            $this->json(500, [
                'success' => false,
                'error'   => 'ERROR_SERVIDOR',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public function comunidadActual(): void
    {
        try {
            $codigoUsuario = $this->authUserId();
            if ($codigoUsuario <= 0) {
                $this->json(401, ['ok' => false, 'error' => 'UNAUTHORIZED']);
                return;
            }

            $auth = $GLOBALS['EV_AUTH'] ?? [];
            $rol = (string)($auth['rol'] ?? $auth['nombre_rol'] ?? '');

            $sesion = new SesionJWT();
            $comunidad = $sesion->obtenerComunidadActual($codigoUsuario, $rol);

            $this->json(200, [
                'ok' => true,
                'data' => $comunidad,
            ]);
        } catch (Throwable $e) {
            $this->json(500, [
                'ok' => false,
                'error' => 'ERROR_COMUNIDAD_ACTUAL',
                'mensaje' => 'No se pudo actualizar la comunidad visible.',
            ]);
        }
    }

    public function actualizarDatos(): void
    {
        try {
            $codigoUsuario = $this->authUserId();
            if ($codigoUsuario <= 0) {
                $this->json(401, ['success' => false, 'error' => 'UNAUTHORIZED']);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput ?: '[]', true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                $this->json(400, [
                    'success' => false,
                    'error'   => 'JSON_INVALIDO',
                    'detalle' => json_last_error_msg(),
                ]);
                return;
            }

            $email  = trim((string)($data['email'] ?? ''));
            $nombre = trim((string)($data['nombre_completo'] ?? ''));

            if ($email === '' || $nombre === '') {
                $this->json(400, ['success' => false, 'error' => 'DATOS_INCOMPLETOS']);
                return;
            }

            $usuarioModel = new Usuario();
            $usuarioModel->actualizarDatos($codigoUsuario, $data);

            $this->json(200, [
                'success' => true,
                'message' => 'Datos actualizados correctamente',
            ]);
        } catch (Exception $e) {
            $this->json(400, [
                'success' => false,
                'error'   => 'VALIDACION',
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable $e) {
            $this->json(500, [
                'success' => false,
                'error'   => 'ERROR_SERVIDOR',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public function actualizarTelefono(): void
    {
        try {
            $codigoUsuario = $this->authUserId();
            if ($codigoUsuario <= 0) {
                $this->json(401, ['ok' => false, 'error' => 'UNAUTHORIZED']);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput ?: '[]', true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                $this->json(400, [
                    'ok'      => false,
                    'error'   => 'JSON_INVALIDO',
                    'detalle' => json_last_error_msg(),
                ]);
                return;
            }

            $telefono = trim((string)($data['telefono'] ?? ''));
            if ($telefono === '') {
                $this->json(422, ['ok' => false, 'error' => 'TELEFONO_REQUERIDO', 'mensaje' => 'Ingresa tu teléfono.']);
                return;
            }

            $usuarioModel = new Usuario();
            $ok = $usuarioModel->actualizarTelefono($codigoUsuario, $telefono);

            if (!$ok) {
                $this->json(422, ['ok' => false, 'error' => 'TELEFONO_INVALIDO', 'mensaje' => 'Formato esperado: 9XXXXXXXX.']);
                return;
            }

            $this->json(200, ['ok' => true, 'mensaje' => 'Teléfono actualizado correctamente.']);
        } catch (Throwable $e) {
            $this->json(500, [
                'ok'      => false,
                'error'   => 'ERROR_SERVIDOR',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public function actualizarResidencia(): void
    {
        $this->solicitarCambioResidencia();
    }

    public function solicitarCambioResidencia(): void
    {
        try {
            $codigoUsuario = $this->authUserId();
            if ($codigoUsuario <= 0) {
                $this->json(401, ['ok' => false, 'mensaje' => 'No autenticado.']);
                return;
            }

            $tipo = $this->normalizeTipo((string)($_POST['tipo_conjunto'] ?? ''));

            $codCon = (int)($_POST['codigo_condominio'] ?? 0);
            $codUrb = (int)($_POST['codigo_urbanizacion'] ?? 0);

            $ubDep  = trim((string)($_POST['ubigeo_departamento'] ?? ''));
            $ubProv = trim((string)($_POST['ubigeo_provincia'] ?? ''));
            $ubDist = trim((string)($_POST['ubigeo_distrito'] ?? ''));

            if (!in_array($tipo, ['condominio', 'urbanizacion'], true)) {
                $this->json(422, ['ok' => false, 'mensaje' => 'Tipo de conjunto inválido.']);
                return;
            }

            if ($ubDep === '' || $ubProv === '' || $ubDist === '') {
                $this->json(422, ['ok' => false, 'mensaje' => 'Ubigeo requerido.']);
                return;
            }

            if ($tipo === 'condominio' && $codCon <= 0) {
                $this->json(422, ['ok' => false, 'mensaje' => 'Condominio obligatorio.']);
                return;
            }
            if ($tipo === 'urbanizacion' && $codUrb <= 0) {
                $this->json(422, ['ok' => false, 'mensaje' => 'Urbanización obligatoria.']);
                return;
            }

            $codigoDistrito = (int)$ubDist;
            $perteneceAlDistrito = $tipo === 'condominio'
                ? (new CondominioModel())->perteneceADistrito($codCon, $codigoDistrito)
                : (new Urbanizacion())->perteneceADistrito($codUrb, $codigoDistrito);

            if (!$perteneceAlDistrito) {
                $this->json(422, [
                    'ok' => false,
                    'mensaje' => 'El conjunto residencial seleccionado no pertenece al distrito indicado.',
                ]);
                return;
            }

            $direccion = $this->obtenerDireccionDesdeBD($tipo, $codCon, $codUrb);
            if ($direccion === '') {
                $this->json(422, ['ok' => false, 'mensaje' => 'No se pudo obtener la dirección desde BD.']);
                return;
            }

            if (!isset($_FILES['documento_domicilio']) || !is_array($_FILES['documento_domicilio'])) {
                $this->json(422, ['ok' => false, 'mensaje' => 'Adjunta un comprobante de domicilio.']);
                return;
            }

            $f = $_FILES['documento_domicilio'];

            if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $this->json(422, ['ok' => false, 'mensaje' => 'No se pudo cargar el archivo.']);
                return;
            }

            $max = 5 * 1024 * 1024;
            if ((int)($f['size'] ?? 0) > $max) {
                $this->json(422, ['ok' => false, 'mensaje' => 'El archivo supera 5MB.']);
                return;
            }

            $name = (string)($f['name'] ?? '');
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
                $this->json(422, ['ok' => false, 'mensaje' => 'Tipo de archivo no permitido.']);
                return;
            }

            $uploadsDir = __DIR__ . '/../../resources/uploads/comprobantes';
            if (!is_dir($uploadsDir)) {
                @mkdir($uploadsDir, 0775, true);
            }

            $safeFile = 'comp_res_' . $codigoUsuario . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destPath = $uploadsDir . '/' . $safeFile;

            if (!move_uploaded_file((string)($f['tmp_name'] ?? ''), $destPath)) {
                $this->json(500, ['ok' => false, 'mensaje' => 'No se pudo guardar el archivo.']);
                return;
            }

            $rutaRelativa = 'resources/uploads/comprobantes/' . $safeFile;

            $model = new UsuarioResidenciaSolicitud();
            $idSolicitud = $model->upsertPendiente($codigoUsuario, [
                'tipo_conjunto'        => $tipo,
                'codigo_condominio'    => ($tipo === 'condominio') ? $codCon : null,
                'codigo_urbanizacion'  => ($tipo === 'urbanizacion') ? $codUrb : null,
                'direccion'            => $direccion,
            ], $rutaRelativa);

            if ($idSolicitud <= 0) {
                $this->json(500, ['ok' => false, 'mensaje' => 'No se pudo registrar la solicitud.']);
                return;
            }

            try {
                $uSoporte = new UsuarioSoporte();
                $uSoporte->actualizarEstado($codigoUsuario, 1);
            } catch (Throwable $e) {
                error_log('[EV][usuarioDatosController][solicitarCambioResidencia][estado] ' . $e->getMessage());
            }

            $notificacionesSoporte = 0;
            try {
                $usuario = (new Usuario())->obtenerPorCodigo($codigoUsuario);
                $nombreVecino = trim((string)($usuario['nombre_completo'] ?? 'Un vecino'));
                $adminId = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
                $soporteId = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;

                $notif = new Notificacion($model->getDblink());
                $notificacionesSoporte = $notif->crearParaRoles(
                    [$adminId, $soporteId],
                    [
                        'categoria' => Notificacion::CAT_SOPORTE,
                        'subcategoria' => 'residencia_pendiente_soporte',
                        'referencia_id' => $idSolicitud,
                        'titulo' => 'Nueva solicitud de cambio de residencia',
                        'mensaje' => $nombreVecino . ' envió una solicitud que requiere revisión.',
                        'payload' => [
                            'codigo_solicitud' => $idSolicitud,
                            'codigo_usuario' => $codigoUsuario,
                            'estado' => 'pendiente',
                            'modo' => 'residencias',
                            'rol_destino' => 'soporte',
                            'ruta' => '/atender-cuentas',
                        ],
                    ]
                );
            } catch (Throwable $e) {
                error_log('[EV][usuarioDatosController][solicitarCambioResidencia][notificacion_soporte] ' . $e->getMessage());
            }

            $this->json(200, [
                'ok'      => true,
                'id'      => $idSolicitud,
                'mensaje' => 'Solicitud registrada. Queda en revisión.',
                'notificaciones_soporte' => $notificacionesSoporte,
            ]);

        } catch (Throwable $e) {
            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'ERROR_SERVIDOR',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public function cambiarClave(): void
    {
        try {
            $codigoUsuario = $this->authUserId();
            if ($codigoUsuario <= 0) {
                $this->json(401, ['success' => false, 'error' => 'UNAUTHORIZED']);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput ?: '[]', true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                $this->json(400, [
                    'success' => false,
                    'error'   => 'JSON_INVALIDO',
                    'detalle' => json_last_error_msg(),
                ]);
                return;
            }

            $actual = trim((string)($data['password_actual'] ?? ''));
            $nueva  = trim((string)($data['password_nueva'] ?? ''));
            $conf   = trim((string)($data['password_confirmar'] ?? ''));

            if ($actual === '' || $nueva === '' || $conf === '') {
                $this->json(422, [
                    'success' => false,
                    'error'   => 'DATOS_INCOMPLETOS',
                    'message' => 'Completa los 3 campos.'
                ]);
                return;
            }

            if ($nueva !== $conf) {
                $this->json(422, [
                    'success' => false,
                    'error'   => 'NO_COINCIDE',
                    'message' => 'La nueva contraseña y la confirmación no coinciden.'
                ]);
                return;
            }

            if (strlen($nueva) < 8) {
                $this->json(422, [
                    'success' => false,
                    'error'   => 'CLAVE_DEBIL',
                    'message' => 'La contraseña debe tener mínimo 8 caracteres.'
                ]);
                return;
            }

            if ($nueva === $actual) {
                $this->json(422, [
                    'success' => false,
                    'error'   => 'CLAVE_IGUAL',
                    'message' => 'La nueva contraseña debe ser distinta a la actual.'
                ]);
                return;
            }

            $usuarioModel = new Usuario();

            $hash = $usuarioModel->obtenerHashClave($codigoUsuario);
            if (!$hash || !password_verify($actual, (string)$hash)) {
                $this->json(403, [
                    'success' => false,
                    'error'   => 'CLAVE_ACTUAL_INVALIDA',
                    'message' => 'La contraseña actual no es correcta.'
                ]);
                return;
            }

            $hashNueva = password_hash($nueva, PASSWORD_BCRYPT);

            $ok = $usuarioModel->actualizarClave($codigoUsuario, $hashNueva);
            if (!$ok) {
                $this->json(500, [
                    'success' => false,
                    'error'   => 'NO_SE_PUDO_ACTUALIZAR',
                    'message' => 'No se pudo actualizar la contraseña.'
                ]);
                return;
            }

            $this->json(200, [
                'success' => true,
                'message' => 'Contraseña actualizada correctamente.'
            ]);

        } catch (Throwable $e) {
            $this->json(500, [
                'success' => false,
                'error'   => 'ERROR_SERVIDOR',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public function actualizarFotoPerfil(): void
    {
        $archivoNuevo = null;

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido.']);
                return;
            }

            $codigoUsuario = $this->authUserId();
            if ($codigoUsuario <= 0) {
                $this->json(401, ['ok' => false, 'error' => 'UNAUTHORIZED', 'mensaje' => 'Tu sesión no es válida.']);
                return;
            }

            $archivoNuevo = $this->procesarFotoPerfil($codigoUsuario);
            $rutaNueva = (string)($archivoNuevo['ruta'] ?? '');

            if ($rutaNueva === '') {
                throw new RuntimeException('No se pudo preparar la foto de perfil.');
            }

            $usuarioModel = new Usuario();
            $fotoAnterior = $usuarioModel->obtenerFotoPerfil($codigoUsuario);
            $ok = $usuarioModel->actualizarFotoPerfil($codigoUsuario, $rutaNueva);

            if (!$ok) {
                throw new RuntimeException('No se pudo actualizar la foto de perfil.');
            }

            $this->eliminarFotoPerfilAnterior((string)$fotoAnterior, $rutaNueva);

            $this->json(200, [
                'ok' => true,
                'mensaje' => 'Tu foto de perfil fue actualizada correctamente.',
                'data' => [
                    'foto_perfil' => $rutaNueva,
                    'foto_perfil_url' => $this->urlPublica($rutaNueva),
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            $this->eliminarArchivoNuevo($archivoNuevo);
            $this->json(422, ['ok' => false, 'mensaje' => $e->getMessage()]);
        } catch (Throwable $e) {
            $this->eliminarArchivoNuevo($archivoNuevo);
            error_log('[EV][usuarioDatosController][actualizarFotoPerfil] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'mensaje' => 'No se pudo actualizar la foto de perfil.']);
        }
    }

    private function procesarFotoPerfil(int $codigoUsuario): array
    {
        if (!isset($_FILES['foto_perfil']) || !is_array($_FILES['foto_perfil'])) {
            throw new InvalidArgumentException('Selecciona una foto de perfil.');
        }

        $file = $_FILES['foto_perfil'];
        $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE) {
            throw new InvalidArgumentException('Selecciona una foto de perfil.');
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('No se pudo cargar la foto seleccionada.');
        }

        $tmp = (string)($file['tmp_name'] ?? '');
        $size = (int)($file['size'] ?? 0);

        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new InvalidArgumentException('La foto recibida no es válida.');
        }

        if ($size <= 0 || $size > self::MAX_FOTO_PERFIL_BYTES) {
            throw new InvalidArgumentException('La foto debe pesar como máximo 2 MB.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($tmp);
        $extensiones = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];

        if (!array_key_exists($mime, $extensiones) || @getimagesize($tmp) === false) {
            throw new InvalidArgumentException('Solo se permiten imágenes JPG, PNG o WEBP.');
        }

        $directorio = rtrim((string)EV_UPLOADS_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'perfiles';

        if (!is_dir($directorio) && !mkdir($directorio, 0775, true) && !is_dir($directorio)) {
            throw new RuntimeException('No se pudo preparar la carpeta de fotos de perfil.');
        }

        $nombre = 'perfil_' . $codigoUsuario . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $extensiones[$mime];
        $absoluta = $directorio . DIRECTORY_SEPARATOR . $nombre;

        if (!move_uploaded_file($tmp, $absoluta)) {
            throw new RuntimeException('No se pudo guardar la foto de perfil.');
        }

        return [
            'ruta' => 'resources/uploads/perfiles/' . $nombre,
            'absoluta' => $absoluta,
        ];
    }

    private function urlPublica(string $ruta): string
    {
        $ruta = trim($ruta);

        if ($ruta === '') {
            return rtrim(BASE_URL, '/') . '/views/fotos/00000000.png';
        }

        if (preg_match('#^https?://#i', $ruta)) {
            return $ruta;
        }

        if (str_starts_with($ruta, '/')) {
            return $ruta;
        }

        return rtrim(BASE_URL, '/') . '/' . ltrim($ruta, '/');
    }

    private function eliminarArchivoNuevo(?array $archivo): void
    {
        if ($archivo && !empty($archivo['absoluta']) && is_file((string)$archivo['absoluta'])) {
            @unlink((string)$archivo['absoluta']);
        }
    }

    private function eliminarFotoPerfilAnterior(string $rutaAnterior, string $rutaNueva): void
    {
        $rutaAnterior = trim($rutaAnterior);
        $rutaNueva = trim($rutaNueva);

        if ($rutaAnterior === '' || $rutaAnterior === $rutaNueva) {
            return;
        }

        $prefijo = 'resources/uploads/perfiles/';
        if (!str_starts_with($rutaAnterior, $prefijo)) {
            return;
        }

        $nombre = basename($rutaAnterior);
        $absoluta = rtrim((string)EV_UPLOADS_DIR, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . 'perfiles'
            . DIRECTORY_SEPARATOR
            . $nombre;

        if (is_file($absoluta)) {
            @unlink($absoluta);
        }
    }
}
