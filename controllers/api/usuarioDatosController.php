<?php
// controllers/api/usuarioDatosController.php
// EV — API: Datos de usuario + Solicitud cambio de residencia

declare(strict_types=1);

require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../models/UsuarioResidenciaSolicitud.php';

class usuarioDatosController
{
    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
    }

    private function authUserId(): int
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        return (int)($auth['codigo_usuario'] ?? 0);
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

            $this->json(200, [
                'success' => true,
                'usuario' => $usuario,
            ]);
        } catch (Throwable $e) {
            $this->json(500, [
                'success' => false,
                'error'   => 'ERROR_SERVIDOR',
                'detalle' => $e->getMessage(),
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

            // Importante: tu modelo debe validar internamente qué campos actualiza.
            // Aquí no rompemos compatibilidad: enviamos $data tal cual.
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

    /**
     * POST /api/usuario/residencia/solicitar  (sugerido)
     * FormData:
     * - tipo_conjunto: condominio|urbanizacion
     * - codigo_condominio (si condominio)
     * - codigo_departamento (si condominio)
     * - codigo_urbanizacion (si urbanizacion)
     * - direccion
     * - documento_domicilio (file: pdf|jpg|jpeg|png <= 5MB)
     *
     * Nota: ubigeo_* se valida si lo envías desde UI, pero NO se inserta
     * en BD porque tu tabla usuario_residencia_solicitud (según tu modelo)
     * no lo tiene aún. Cuando lo agregues a tabla, se amplía.
     */
    public function solicitarCambioResidencia(): void
    {
        try {
            $codigoUsuario = $this->authUserId();
            if ($codigoUsuario <= 0) {
                $this->json(401, ['ok' => false, 'mensaje' => 'No autenticado.']);
                return;
            }

            $tipo   = strtolower(trim((string)($_POST['tipo_conjunto'] ?? '')));
            $dir    = trim((string)($_POST['direccion'] ?? ''));

            $codCon = (int)($_POST['codigo_condominio'] ?? 0);
            $codDep = (int)($_POST['codigo_departamento'] ?? 0);
            $codUrb = (int)($_POST['codigo_urbanizacion'] ?? 0);

            // Si en UI envías ubigeo, lo validamos (sin persistir por ahora)
            $ubDep  = trim((string)($_POST['ubigeo_departamento'] ?? ''));
            $ubProv = trim((string)($_POST['ubigeo_provincia'] ?? ''));
            $ubDist = trim((string)($_POST['ubigeo_distrito'] ?? ''));

            if (!in_array($tipo, ['condominio', 'urbanizacion'], true)) {
                $this->json(422, ['ok' => false, 'mensaje' => 'Tipo de conjunto inválido.']);
                return;
            }
            if ($dir === '') {
                $this->json(422, ['ok' => false, 'mensaje' => 'Dirección requerida.']);
                return;
            }

            // Ubigeo: si lo mandas, debe estar completo (no obligamos si tu UI aún no lo usa)
            $algunoUbigeo = ($ubDep !== '' || $ubProv !== '' || $ubDist !== '');
            if ($algunoUbigeo && ($ubDep === '' || $ubProv === '' || $ubDist === '')) {
                $this->json(422, ['ok' => false, 'mensaje' => 'Ubigeo incompleto.']);
                return;
            }

            if ($tipo === 'condominio') {
                if ($codCon <= 0 || $codDep <= 0) {
                    $this->json(422, ['ok' => false, 'mensaje' => 'Condominio y departamento son obligatorios.']);
                    return;
                }
            } else {
                if ($codUrb <= 0) {
                    $this->json(422, ['ok' => false, 'mensaje' => 'Urbanización obligatoria.']);
                    return;
                }
            }

            // Archivo
            if (!isset($_FILES['documento_domicilio']) || !is_array($_FILES['documento_domicilio'])) {
                $this->json(422, ['ok' => false, 'mensaje' => 'Adjunta un documento de domicilio.']);
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

            // Guardado físico
            $uploadsDir = __DIR__ . '/../../uploads/residencias';
            if (!is_dir($uploadsDir)) {
                @mkdir($uploadsDir, 0775, true);
            }

            $safeFile = 'residencia_' . $codigoUsuario . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destPath = $uploadsDir . '/' . $safeFile;

            if (!move_uploaded_file((string)$f['tmp_name'], $destPath)) {
                $this->json(500, ['ok' => false, 'mensaje' => 'No se pudo guardar el archivo.']);
                return;
            }

            // Ruta relativa (para BD) — consistente con tu modelo UsuarioResidenciaSolicitud::crear(...)
            $rutaRelativa = 'uploads/residencias/' . $safeFile;

            // Registrar solicitud (BD)
            $model = new UsuarioResidenciaSolicitud();

            $idSolicitud = $model->crear($codigoUsuario, [
                'tipo_conjunto'        => $tipo,
                'codigo_condominio'    => ($tipo === 'condominio')   ? $codCon : null,
                'codigo_departamento'  => ($tipo === 'condominio')   ? $codDep : null,
                'codigo_urbanizacion'  => ($tipo === 'urbanizacion') ? $codUrb : null,
                'direccion'            => $dir,
                // ubigeo_* no se inserta porque tu tabla/modelo no lo contemplan aún
            ], $rutaRelativa);

            $this->json(200, [
                'ok' => $idSolicitud > 0,
                'id' => $idSolicitud,
                'mensaje' => ($idSolicitud > 0)
                    ? 'Solicitud registrada. Queda en revisión.'
                    : 'No se pudo registrar la solicitud.',
            ]);
        } catch (Throwable $e) {
            $this->json(500, [
                'ok' => false,
                'mensaje' => 'ERROR_SERVIDOR',
                'detalle' => $e->getMessage(),
            ]);
        }
    }
}
