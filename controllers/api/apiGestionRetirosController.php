<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/Retiro.php';

final class apiGestionRetirosController
{
    private function json(int $status, array $data): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function operador(bool $soloAdmin = false): ?array
    {
        $auth = $GLOBALS['EV_AUTH'] ?? null;
        if (!is_array($auth) || (int)($auth['codigo_usuario'] ?? 0) <= 0) {
            $this->json(401, ['ok' => false, 'error' => 'UNAUTHORIZED', 'mensaje' => 'Tu sesión no es válida.']);
            return null;
        }
        $rol = strtolower(trim((string)($auth['rol'] ?? $auth['nombre_rol'] ?? '')));
        $codigoRol = (int)($auth['codigo_rol'] ?? 0);
        $esAdmin = $rol === 'admin' || $codigoRol === (defined('EV_ADMIN_ROLE_ID') ? EV_ADMIN_ROLE_ID : 1);
        $esSoporte = $rol === 'soporte' || $codigoRol === (defined('EV_SOPORTE_ROLE_ID') ? EV_SOPORTE_ROLE_ID : 3);
        if (!$esAdmin && (!$esSoporte || $soloAdmin)) {
            $this->json(403, ['ok' => false, 'error' => 'FORBIDDEN', 'mensaje' => $soloAdmin ? 'Esta acción corresponde al Administrador EV.' : 'No tienes permisos para consultar retiros.']);
            return null;
        }
        $auth['_es_admin'] = $esAdmin;
        $auth['_es_soporte'] = $esSoporte;
        return $auth;
    }

    private function csrf(): bool
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $esperado = (string)($_SESSION['ev_retiros_csrf'] ?? '');
        $recibido = trim((string)($_SERVER['HTTP_X_EV_CSRF'] ?? ''));
        if ($esperado === '' || $recibido === '' || !hash_equals($esperado, $recibido)) {
            $this->json(419, ['ok' => false, 'error' => 'CSRF', 'mensaje' => 'La sesión de Gestión de retiros venció. Vuelve a abrir el módulo.']);
            return false;
        }
        return true;
    }

    private function input(): array
    {
        if (!empty($_POST)) return is_array($_POST) ? $_POST : [];
        $raw = file_get_contents('php://input');
        $json = json_decode((string)$raw, true);
        return is_array($json) ? $json : [];
    }

    public function listar(): void
    {
        $auth = $this->operador(false);
        if (!$auth) return;
        try {
            $data = (new Retiro())->listarGestion($_GET, (bool)$auth['_es_soporte'] && !(bool)$auth['_es_admin']);
            $this->json(200, ['ok' => true, 'data' => $data, 'solo_lectura' => !(bool)$auth['_es_admin']]);
        } catch (Throwable $e) {
            error_log('[EV][apiGestionRetiros][listar] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'mensaje' => 'No se pudo cargar la gestión de retiros.']);
        }
    }

    public function cuentas(): void
    {
        if (!$this->operador(true)) return;
        try {
            $this->json(200, ['ok' => true, 'data' => (new Retiro())->listarCuentasPendientes()]);
        } catch (Throwable $e) {
            error_log('[EV][apiGestionRetiros][cuentas] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'mensaje' => 'No se pudieron cargar las cuentas bancarias.']);
        }
    }

    public function actualizarCuenta(int $codigoCuenta): void
    {
        $auth = $this->operador(true);
        if (!$auth || !$this->csrf()) return;
        $in = $this->input();
        $r = (new Retiro())->validarCuentaAdmin(
            $codigoCuenta,
            (int)$auth['codigo_usuario'],
            (string)($in['accion'] ?? ''),
            (string)($in['observacion'] ?? '')
        );
        $this->json(($r['ok'] ?? false) ? 200 : 422, $r);
    }

    public function configuracion(): void
    {
        if (!$this->operador(true)) return;
        try {
            $this->json(200, ['ok' => true, 'data' => (new Retiro())->configuraciones()]);
        } catch (Throwable $e) {
            error_log('[EV][apiGestionRetiros][configuracion] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'mensaje' => 'No se pudo cargar la configuración de cortes.']);
        }
    }

    public function guardarConfiguracion(int $codigo): void
    {
        $auth = $this->operador(true);
        if (!$auth || !$this->csrf()) return;
        $r = (new Retiro())->guardarConfiguracion($codigo, $this->input(), (int)$auth['codigo_usuario']);
        $this->json(($r['ok'] ?? false) ? 200 : 422, $r);
    }

    public function observar(int $codigoRetiro): void
    {
        $auth = $this->operador(true);
        if (!$auth || !$this->csrf()) return;
        $in = $this->input();
        $r = (new Retiro())->marcarObservado($codigoRetiro, (int)$auth['codigo_usuario'], (string)($in['observacion'] ?? ''));
        $this->json(($r['ok'] ?? false) ? 200 : 422, $r);
    }

    public function cancelar(int $codigoRetiro): void
    {
        $auth = $this->operador(true);
        if (!$auth || !$this->csrf()) return;
        $in = $this->input();
        $r = (new Retiro())->cancelarYReintegrar($codigoRetiro, (int)$auth['codigo_usuario'], (string)($in['motivo'] ?? ''));
        $this->json(($r['ok'] ?? false) ? 200 : 422, $r);
    }

    private function guardarComprobante(int $codigoRetiro): array
    {
        $f = $_FILES['comprobante'] ?? null;
        if (!is_array($f) || (int)($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'mensaje' => 'Adjunta el comprobante del pago.'];
        }
        $tmp = (string)($f['tmp_name'] ?? '');
        $size = (int)($f['size'] ?? 0);
        if ($tmp === '' || !is_uploaded_file($tmp) || $size <= 0 || $size > 5 * 1024 * 1024) {
            return ['ok' => false, 'mensaje' => 'El comprobante no es válido o supera 5 MB.'];
        }
        $mime = '';
        if (function_exists('finfo_open')) {
            $fi = finfo_open(FILEINFO_MIME_TYPE);
            if ($fi) { $mime = (string)finfo_file($fi, $tmp); finfo_close($fi); }
        }
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
            default => '',
        };
        if ($ext === '') return ['ok' => false, 'mensaje' => 'El comprobante debe ser JPG, PNG, WEBP o PDF.'];

        $dir = rtrim((string)EV_UPLOADS_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'retiros';
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return ['ok' => false, 'mensaje' => 'No se pudo preparar la carpeta de comprobantes.'];
        }
        $nombre = 'retiro_' . $codigoRetiro . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
        $dest = $dir . DIRECTORY_SEPARATOR . $nombre;
        if (!move_uploaded_file($tmp, $dest)) return ['ok' => false, 'mensaje' => 'No se pudo guardar el comprobante.'];
        return ['ok' => true, 'path' => 'resources/uploads/retiros/' . $nombre];
    }

    public function pagar(int $codigoRetiro): void
    {
        $auth = $this->operador(true);
        if (!$auth || !$this->csrf()) return;
        $numeroOperacion = trim((string)($_POST['numero_operacion'] ?? ''));
        if ($numeroOperacion === '') {
            $this->json(422, ['ok' => false, 'mensaje' => 'Registra el número de operación.']);
            return;
        }
        $archivo = $this->guardarComprobante($codigoRetiro);
        if (!($archivo['ok'] ?? false)) {
            $this->json(422, $archivo);
            return;
        }
        $r = (new Retiro())->marcarPagado($codigoRetiro, (int)$auth['codigo_usuario'], $numeroOperacion, (string)$archivo['path']);
        if (!($r['ok'] ?? false)) {
            $abs = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string)$archivo['path']);
            if (is_file($abs)) @unlink($abs);
        }
        $this->json(($r['ok'] ?? false) ? 200 : 422, $r);
    }
}
