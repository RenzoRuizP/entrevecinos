<?php
// controllers/api/apiComunidadController.php

declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/ComunidadPublicacion.php';

final class apiComunidadController
{
    private const MAX_PORTADA_BYTES = 2 * 1024 * 1024;

    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function auth(): array
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        return is_array($auth) ? $auth : [];
    }

    private function rolActual(): string
    {
        $auth = $this->auth();
        return strtolower(trim((string)($auth['rol'] ?? $auth['nombre_rol'] ?? '')));
    }

    private function puedeGestionar(): bool
    {
        return in_array($this->rolActual(), ['admin', 'administrador_comunidad'], true);
    }

    private function exigirGestion(): bool
    {
        if ((int)($this->auth()['codigo_usuario'] ?? 0) <= 0) {
            $this->json(401, ['ok' => false, 'mensaje' => 'Tu sesión ha finalizado. Vuelve a iniciar sesión.']);
            return false;
        }

        if (!$this->puedeGestionar()) {
            $this->json(403, ['ok' => false, 'mensaje' => 'No tienes permisos para gestionar publicaciones de comunidad.']);
            return false;
        }

        return true;
    }

    public function destinos(): void
    {
        if (!$this->exigirGestion()) {
            return;
        }

        try {
            $m = new ComunidadPublicacion();
            $this->json(200, [
                'ok' => true,
                'es_admin_sistema' => $this->rolActual() === 'admin',
                'items' => $m->listarDestinosGestion($this->auth()),
            ]);
        } catch (Throwable $e) {
            $this->error($e, 'destinos');
        }
    }

    public function listar(): void
    {
        if (!$this->exigirGestion()) {
            return;
        }

        try {
            $filtros = [
                'estado' => $_GET['estado'] ?? 'all',
                'tipo' => $_GET['tipo'] ?? 'all',
                'q' => $_GET['q'] ?? '',
                'page' => $_GET['page'] ?? 1,
                'size' => $_GET['size'] ?? 10,
            ];

            $m = new ComunidadPublicacion();
            $data = $m->listarGestion($this->auth(), $filtros);
            $this->json(200, ['ok' => true] + $data);
        } catch (Throwable $e) {
            $this->error($e, 'listar');
        }
    }

    public function detalle(string|int $codigoPublicacion): void
    {
        if (!$this->exigirGestion()) {
            return;
        }

        $id = (int)$codigoPublicacion;
        if ($id <= 0) {
            $this->json(422, ['ok' => false, 'mensaje' => 'Identificador de publicación inválido.']);
            return;
        }

        try {
            $m = new ComunidadPublicacion();
            $item = $m->obtenerGestion($this->auth(), $id);
            if (!$item) {
                $this->json(404, ['ok' => false, 'mensaje' => 'La publicación no fue encontrada.']);
                return;
            }

            $this->json(200, ['ok' => true, 'item' => $item]);
        } catch (Throwable $e) {
            $this->error($e, 'detalle');
        }
    }

    public function historial(string|int $codigoPublicacion): void
    {
        if (!$this->exigirGestion()) {
            return;
        }

        $id = (int)$codigoPublicacion;
        if ($id <= 0) {
            $this->json(422, ['ok' => false, 'mensaje' => 'Identificador de publicación inválido.']);
            return;
        }

        try {
            $m = new ComunidadPublicacion();
            $items = $m->listarHistorial($this->auth(), $id);
            $this->json(200, ['ok' => true, 'items' => $items]);
        } catch (Throwable $e) {
            $this->error($e, 'historial');
        }
    }

    public function crear(): void
    {
        if (!$this->exigirGestion()) {
            return;
        }

        $archivoNuevo = null;

        try {
            $data = $this->datosValidados();
            $archivoNuevo = $this->procesarPortada();

            $m = new ComunidadPublicacion();
            $id = $m->crear($this->auth(), $data, $archivoNuevo['ruta'] ?? null);

            $mensaje = $data['accion'] === 'publicar'
                ? 'La publicación fue creada y publicada correctamente.'
                : 'El borrador fue guardado correctamente.';

            $this->json(201, ['ok' => true, 'codigo_publicacion' => $id, 'mensaje' => $mensaje]);
        } catch (Throwable $e) {
            $this->eliminarArchivoNuevo($archivoNuevo);
            $this->error($e, 'crear');
        }
    }

    public function actualizar(string|int $codigoPublicacion): void
    {
        if (!$this->exigirGestion()) {
            return;
        }

        $id = (int)$codigoPublicacion;
        if ($id <= 0) {
            $this->json(422, ['ok' => false, 'mensaje' => 'Identificador de publicación inválido.']);
            return;
        }

        $archivoNuevo = null;

        try {
            $data = $this->datosValidados();
            $archivoNuevo = $this->procesarPortada();

            $m = new ComunidadPublicacion();
            $resultado = $m->actualizar($this->auth(), $id, $data, $archivoNuevo['ruta'] ?? null);

            if ($archivoNuevo && !empty($resultado['imagen_anterior'])) {
                $this->eliminarPortadaAnterior((string)$resultado['imagen_anterior']);
            }

            $mensaje = $resultado['estado'] === 'publicado'
                ? 'La publicación fue actualizada correctamente.'
                : 'El borrador fue actualizado correctamente.';

            $this->json(200, ['ok' => true, 'mensaje' => $mensaje]);
        } catch (Throwable $e) {
            $this->eliminarArchivoNuevo($archivoNuevo);
            $this->error($e, 'actualizar');
        }
    }

    public function publicar(string|int $codigoPublicacion): void
    {
        if (!$this->exigirGestion()) {
            return;
        }

        $id = (int)$codigoPublicacion;
        if ($id <= 0) {
            $this->json(422, ['ok' => false, 'mensaje' => 'Identificador de publicación inválido.']);
            return;
        }

        try {
            $m = new ComunidadPublicacion();
            $m->publicar($this->auth(), $id);
            $this->json(200, ['ok' => true, 'mensaje' => 'La publicación ya está visible para la comunidad.']);
        } catch (Throwable $e) {
            $this->error($e, 'publicar');
        }
    }

    public function desactivar(string|int $codigoPublicacion): void
    {
        if (!$this->exigirGestion()) {
            return;
        }

        $id = (int)$codigoPublicacion;
        if ($id <= 0) {
            $this->json(422, ['ok' => false, 'mensaje' => 'Identificador de publicación inválido.']);
            return;
        }

        try {
            $m = new ComunidadPublicacion();
            $m->desactivar($this->auth(), $id);
            $this->json(200, ['ok' => true, 'mensaje' => 'La publicación fue desactivada correctamente.']);
        } catch (Throwable $e) {
            $this->error($e, 'desactivar');
        }
    }

    private function datosValidados(): array
    {
        $tipo = strtolower(trim((string)($_POST['tipo_publicacion'] ?? '')));
        $titulo = trim((string)($_POST['titulo'] ?? ''));
        $resumen = trim((string)($_POST['resumen'] ?? ''));
        $contenido = trim((string)($_POST['contenido'] ?? ''));
        $prioridad = strtolower(trim((string)($_POST['prioridad'] ?? 'normal')));
        $accion = strtolower(trim((string)($_POST['accion'] ?? 'guardar_borrador')));
        $destacado = ((string)($_POST['destacado_dashboard'] ?? '0') === '1') ? 1 : 0;

        if (!in_array($tipo, ['comunicado', 'noticia', 'evento'], true)) {
            throw new InvalidArgumentException('Selecciona un tipo de publicación válido.');
        }
        if (mb_strlen($titulo, 'UTF-8') < 5 || mb_strlen($titulo, 'UTF-8') > 140) {
            throw new InvalidArgumentException('El título debe tener entre 5 y 140 caracteres.');
        }
        if (mb_strlen($resumen, 'UTF-8') < 10 || mb_strlen($resumen, 'UTF-8') > 240) {
            throw new InvalidArgumentException('El resumen debe tener entre 10 y 240 caracteres.');
        }
        if (mb_strlen($contenido, 'UTF-8') < 20) {
            throw new InvalidArgumentException('El contenido debe tener al menos 20 caracteres.');
        }
        if (!in_array($prioridad, ['normal', 'importante', 'urgente'], true)) {
            throw new InvalidArgumentException('Selecciona una prioridad válida.');
        }
        if (!in_array($accion, ['guardar_borrador', 'publicar'], true)) {
            throw new InvalidArgumentException('La acción solicitada no es válida.');
        }

        $fechaExpiracion = $this->fechaOpcional($_POST['fecha_expiracion'] ?? null);
        $fechaInicio = null;
        $fechaFin = null;
        $ubicacion = null;

        if ($fechaExpiracion !== null && strtotime($fechaExpiracion) <= time()) {
            throw new InvalidArgumentException('La fecha de expiración debe ser futura.');
        }

        if ($tipo === 'evento') {
            $fechaInicio = $this->fechaOpcional($_POST['fecha_evento_inicio'] ?? null);
            $fechaFin = $this->fechaOpcional($_POST['fecha_evento_fin'] ?? null);
            $ubicacion = trim((string)($_POST['ubicacion_evento'] ?? ''));

            if ($fechaInicio === null) {
                throw new InvalidArgumentException('Indica la fecha y hora de inicio del evento.');
            }
            if ($accion === 'publicar' && strtotime($fechaInicio) < time()) {
                throw new InvalidArgumentException('No puedes publicar un evento cuya fecha de inicio ya pasó.');
            }
            if (mb_strlen($ubicacion, 'UTF-8') < 3 || mb_strlen($ubicacion, 'UTF-8') > 180) {
                throw new InvalidArgumentException('Indica una ubicación válida para el evento.');
            }
            if ($fechaFin !== null && strtotime($fechaFin) <= strtotime($fechaInicio)) {
                throw new InvalidArgumentException('La fecha de fin debe ser posterior al inicio del evento.');
            }
        }

        return [
            'tipo_publicacion' => $tipo,
            'titulo' => $titulo,
            'resumen' => $resumen,
            'contenido' => $contenido,
            'prioridad' => $prioridad,
            'destacado_dashboard' => $destacado,
            'accion' => $accion,
            'tipo_conjunto' => strtolower(trim((string)($_POST['tipo_conjunto'] ?? ''))),
            'codigo_comunidad' => (int)($_POST['codigo_comunidad'] ?? 0),
            'fecha_expiracion' => $fechaExpiracion,
            'fecha_evento_inicio' => $fechaInicio,
            'fecha_evento_fin' => $fechaFin,
            'ubicacion_evento' => $ubicacion,
        ];
    }

    private function fechaOpcional(mixed $valor): ?string
    {
        $valor = trim((string)$valor);
        if ($valor === '') {
            return null;
        }

        $dt = DateTime::createFromFormat('Y-m-d\TH:i', $valor);
        if (!$dt || $dt->format('Y-m-d\TH:i') !== $valor) {
            throw new InvalidArgumentException('Se recibió una fecha u hora inválida.');
        }

        return $dt->format('Y-m-d H:i:s');
    }

    private function procesarPortada(): ?array
    {
        if (!isset($_FILES['imagen_portada']) || !is_array($_FILES['imagen_portada'])) {
            return null;
        }

        $file = $_FILES['imagen_portada'];
        $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('No se pudo cargar la imagen de portada.');
        }

        $tmp = (string)($file['tmp_name'] ?? '');
        $size = (int)($file['size'] ?? 0);

        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new InvalidArgumentException('La imagen recibida no es válida.');
        }
        if ($size <= 0 || $size > self::MAX_PORTADA_BYTES) {
            throw new InvalidArgumentException('La imagen debe pesar como máximo 2 MB.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($tmp);
        $extensiones = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!array_key_exists($mime, $extensiones) || @getimagesize($tmp) === false) {
            throw new InvalidArgumentException('Solo se permiten imágenes JPG, PNG o WEBP.');
        }

        $directorio = rtrim(EV_UPLOADS_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'comunidad';
        if (!is_dir($directorio) && !mkdir($directorio, 0775, true) && !is_dir($directorio)) {
            throw new RuntimeException('No se pudo preparar la carpeta de imágenes de Comunidad.');
        }

        $nombre = 'comunidad_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $extensiones[$mime];
        $absoluta = $directorio . DIRECTORY_SEPARATOR . $nombre;

        if (!move_uploaded_file($tmp, $absoluta)) {
            throw new RuntimeException('No se pudo guardar la imagen de portada.');
        }

        return [
            'ruta' => 'resources/uploads/comunidad/' . $nombre,
            'absoluta' => $absoluta,
        ];
    }

    private function eliminarArchivoNuevo(?array $archivo): void
    {
        if ($archivo && !empty($archivo['absoluta']) && is_file((string)$archivo['absoluta'])) {
            @unlink((string)$archivo['absoluta']);
        }
    }

    private function eliminarPortadaAnterior(string $ruta): void
    {
        $prefijo = 'resources/uploads/comunidad/';
        if (!str_starts_with($ruta, $prefijo)) {
            return;
        }

        $nombre = basename($ruta);
        $absoluta = rtrim(EV_UPLOADS_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'comunidad' . DIRECTORY_SEPARATOR . $nombre;
        if (is_file($absoluta)) {
            @unlink($absoluta);
        }
    }

    private function error(Throwable $e, string $metodo): void
    {
        if ($e instanceof InvalidArgumentException) {
            $this->json(422, ['ok' => false, 'mensaje' => $e->getMessage()]);
            return;
        }
        if ($e instanceof DomainException) {
            $this->json(409, ['ok' => false, 'mensaje' => $e->getMessage()]);
            return;
        }
        if ($e instanceof RuntimeException && str_contains($e->getMessage(), 'permis')) {
            $this->json(403, ['ok' => false, 'mensaje' => $e->getMessage()]);
            return;
        }

        error_log('[EV][apiComunidadController::' . $metodo . '] ' . $e->getMessage());
        $this->json(500, ['ok' => false, 'mensaje' => 'Ocurrió un error interno al procesar la publicación.']);
    }
}
