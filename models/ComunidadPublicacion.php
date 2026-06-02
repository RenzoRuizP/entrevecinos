<?php
// models/ComunidadPublicacion.php

declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class ComunidadPublicacion extends Conexion
{
    private const ROL_ADMIN = 'admin';
    private const ROL_ADMIN_COMUNIDAD = 'administrador_comunidad';

    private function rol(array $auth): string
    {
        return strtolower(trim((string)($auth['rol'] ?? $auth['nombre_rol'] ?? '')));
    }

    private function codigoUsuario(array $auth): int
    {
        return (int)($auth['codigo_usuario'] ?? 0);
    }

    private function esAdminSistema(array $auth): bool
    {
        return $this->rol($auth) === self::ROL_ADMIN;
    }

    private function esAdministradorComunidad(array $auth): bool
    {
        return $this->rol($auth) === self::ROL_ADMIN_COMUNIDAD;
    }

    private function validarRolGestion(array $auth): void
    {
        if (!$this->esAdminSistema($auth) && !$this->esAdministradorComunidad($auth)) {
            throw new RuntimeException('No tienes permisos para gestionar publicaciones de comunidad.');
        }
    }

    /**
     * Obtiene la comunidad institucional autorizada para la cuenta.
     */
    public function obtenerComunidadAsignada(int $codigoUsuario): ?array
    {
        $sql = "
            SELECT
                ac.tipo_conjunto,
                ac.codigo_condominio,
                ac.codigo_urbanizacion,
                CASE
                    WHEN ac.tipo_conjunto = 'urbanizacion' THEN u.nombre_urbanizacion
                    WHEN ac.tipo_conjunto = 'condominio' THEN c.nombre_condominio
                    ELSE 'Comunidad'
                END AS nombre_comunidad
            FROM administrador_comunidad ac
            LEFT JOIN urbanizacion u
                ON u.codigo_urbanizacion = ac.codigo_urbanizacion
            LEFT JOIN condominio c
                ON c.codigo_condominio = ac.codigo_condominio
            WHERE ac.codigo_usuario = :usuario
              AND ac.estado = 1
            ORDER BY ac.codigo_administrador_comunidad ASC
            LIMIT 1
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':usuario', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Destinos visibles en el formulario.
     * - admin: todas las comunidades activas.
     * - administrador_comunidad: solo su comunidad asignada.
     */
    public function listarDestinosGestion(array $auth): array
    {
        $this->validarRolGestion($auth);

        if ($this->esAdministradorComunidad($auth)) {
            $asignada = $this->obtenerComunidadAsignada($this->codigoUsuario($auth));
            return $asignada ? [$asignada] : [];
        }

        $sql = "
            SELECT
                'urbanizacion' AS tipo_conjunto,
                NULL AS codigo_condominio,
                u.codigo_urbanizacion,
                u.nombre_urbanizacion AS nombre_comunidad
            FROM urbanizacion u
            WHERE u.estado = 'A'

            UNION ALL

            SELECT
                'condominio' AS tipo_conjunto,
                c.codigo_condominio,
                NULL AS codigo_urbanizacion,
                c.nombre_condominio AS nombre_comunidad
            FROM condominio c
            WHERE c.estado = 'A'

            ORDER BY nombre_comunidad ASC
        ";

        $st = $this->dblink->prepare($sql);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function destinoPermitido(array $auth, array $data): array
    {
        $this->validarRolGestion($auth);

        if ($this->esAdministradorComunidad($auth)) {
            $asignada = $this->obtenerComunidadAsignada($this->codigoUsuario($auth));
            if (!$asignada) {
                throw new DomainException('Tu cuenta no tiene una comunidad activa asignada.');
            }
            return $asignada;
        }

        $tipo = strtolower(trim((string)($data['tipo_conjunto'] ?? '')));
        $codigo = (int)($data['codigo_comunidad'] ?? 0);

        if (!in_array($tipo, ['urbanizacion', 'condominio'], true) || $codigo <= 0) {
            throw new InvalidArgumentException('Selecciona la comunidad a la que pertenece la publicación.');
        }

        if ($tipo === 'urbanizacion') {
            $sql = "SELECT codigo_urbanizacion, nombre_urbanizacion AS nombre_comunidad
                    FROM urbanizacion
                    WHERE codigo_urbanizacion = :codigo AND estado = 'A'
                    LIMIT 1";
            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo', $codigo, PDO::PARAM_INT);
            $st->execute();
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                throw new InvalidArgumentException('La urbanización seleccionada no se encuentra activa.');
            }
            return [
                'tipo_conjunto' => 'urbanizacion',
                'codigo_condominio' => null,
                'codigo_urbanizacion' => (int)$row['codigo_urbanizacion'],
                'nombre_comunidad' => (string)$row['nombre_comunidad'],
            ];
        }

        $sql = "SELECT codigo_condominio, nombre_condominio AS nombre_comunidad
                FROM condominio
                WHERE codigo_condominio = :codigo AND estado = 'A'
                LIMIT 1";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo', $codigo, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new InvalidArgumentException('El condominio seleccionado no se encuentra activo.');
        }
        return [
            'tipo_conjunto' => 'condominio',
            'codigo_condominio' => (int)$row['codigo_condominio'],
            'codigo_urbanizacion' => null,
            'nombre_comunidad' => (string)$row['nombre_comunidad'],
        ];
    }

    private function alcancePermitidoSql(array $auth, array &$params, string $alias = 'p'): string
    {
        $this->validarRolGestion($auth);

        if ($this->esAdminSistema($auth)) {
            return "{$alias}.origen_publicacion = 'administracion_comunidad'";
        }

        $asignada = $this->obtenerComunidadAsignada($this->codigoUsuario($auth));
        if (!$asignada) {
            return '1 = 0';
        }

        $params[':tipo_permiso'] = $asignada['tipo_conjunto'];

        if ($asignada['tipo_conjunto'] === 'urbanizacion') {
            $params[':codigo_urbanizacion_permiso'] = (int)$asignada['codigo_urbanizacion'];
            return "{$alias}.origen_publicacion = 'administracion_comunidad'
                    AND {$alias}.tipo_conjunto = :tipo_permiso
                    AND {$alias}.codigo_urbanizacion = :codigo_urbanizacion_permiso";
        }

        $params[':codigo_condominio_permiso'] = (int)$asignada['codigo_condominio'];
        return "{$alias}.origen_publicacion = 'administracion_comunidad'
                AND {$alias}.tipo_conjunto = :tipo_permiso
                AND {$alias}.codigo_condominio = :codigo_condominio_permiso";
    }

    public function listarGestion(array $auth, array $filtros): array
    {
        $estado = strtolower(trim((string)($filtros['estado'] ?? 'all')));
        $tipo = strtolower(trim((string)($filtros['tipo'] ?? 'all')));
        $q = trim((string)($filtros['q'] ?? ''));
        $page = max(1, (int)($filtros['page'] ?? 1));
        $size = max(1, min(30, (int)($filtros['size'] ?? 10)));
        $offset = ($page - 1) * $size;

        $params = [];
        $where = [$this->alcancePermitidoSql($auth, $params)];

        if (in_array($estado, ['borrador', 'publicado', 'inactivo', 'ocultado_moderacion'], true)) {
            $where[] = 'p.estado = :estado';
            $params[':estado'] = $estado;
        }

        if (in_array($tipo, ['comunicado', 'noticia', 'evento'], true)) {
            $where[] = 'p.tipo_publicacion = :tipo';
            $params[':tipo'] = $tipo;
        }

        if ($q !== '') {
            $where[] = '(p.titulo LIKE :q OR p.resumen LIKE :q OR p.contenido LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $stTotal = $this->dblink->prepare("SELECT COUNT(*) FROM comunidad_publicacion p {$whereSql}");
        foreach ($params as $key => $value) {
            $stTotal->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stTotal->execute();
        $total = (int)$stTotal->fetchColumn();

        $sql = "
            SELECT
                p.codigo_publicacion,
                p.tipo_publicacion,
                p.titulo,
                p.resumen,
                p.prioridad,
                p.destacado_dashboard,
                p.estado,
                p.fecha_publicacion,
                p.fecha_evento_inicio,
                p.updated_at,
                p.created_at,
                p.tipo_conjunto,
                CASE
                    WHEN p.tipo_conjunto = 'urbanizacion' THEN u.nombre_urbanizacion
                    WHEN p.tipo_conjunto = 'condominio' THEN c.nombre_condominio
                    ELSE 'Comunidad'
                END AS nombre_comunidad
            FROM comunidad_publicacion p
            LEFT JOIN urbanizacion u ON u.codigo_urbanizacion = p.codigo_urbanizacion
            LEFT JOIN condominio c ON c.codigo_condominio = p.codigo_condominio
            {$whereSql}
            ORDER BY
                CASE p.estado
                    WHEN 'publicado' THEN 1
                    WHEN 'borrador' THEN 2
                    WHEN 'ocultado_moderacion' THEN 3
                    ELSE 4
                END,
                COALESCE(p.fecha_publicacion, p.created_at) DESC,
                p.codigo_publicacion DESC
            LIMIT :lim OFFSET :off
        ";

        $st = $this->dblink->prepare($sql);
        foreach ($params as $key => $value) {
            $st->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->bindValue(':lim', $size, PDO::PARAM_INT);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();

        return [
            'items' => $st->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'total' => $total,
            'page' => $page,
            'size' => $size,
            'counts' => $this->resumenGestion($auth),
        ];
    }

    public function resumenGestion(array $auth): array
    {
        $params = [];
        $where = $this->alcancePermitidoSql($auth, $params);

        $sql = "
            SELECT
                SUM(CASE WHEN p.estado = 'publicado' THEN 1 ELSE 0 END) AS publicadas,
                SUM(CASE WHEN p.estado = 'borrador' THEN 1 ELSE 0 END) AS borradores,
                SUM(CASE WHEN p.estado = 'publicado' AND p.tipo_publicacion = 'evento'
                          AND p.fecha_evento_inicio >= NOW() THEN 1 ELSE 0 END) AS eventos_proximos,
                SUM(CASE WHEN p.estado = 'publicado' AND p.destacado_dashboard = 1 THEN 1 ELSE 0 END) AS destacadas
            FROM comunidad_publicacion p
            WHERE {$where}
        ";

        $st = $this->dblink->prepare($sql);
        foreach ($params as $key => $value) {
            $st->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'publicadas' => (int)($row['publicadas'] ?? 0),
            'borradores' => (int)($row['borradores'] ?? 0),
            'eventos_proximos' => (int)($row['eventos_proximos'] ?? 0),
            'destacadas' => (int)($row['destacadas'] ?? 0),
        ];
    }

    public function obtenerGestion(array $auth, int $codigoPublicacion): ?array
    {
        $params = [':codigo_publicacion' => $codigoPublicacion];
        $permiso = $this->alcancePermitidoSql($auth, $params);

        $sql = "
            SELECT
                p.*,
                CASE
                    WHEN p.tipo_conjunto = 'urbanizacion' THEN u.nombre_urbanizacion
                    WHEN p.tipo_conjunto = 'condominio' THEN c.nombre_condominio
                    ELSE 'Comunidad'
                END AS nombre_comunidad
            FROM comunidad_publicacion p
            LEFT JOIN urbanizacion u ON u.codigo_urbanizacion = p.codigo_urbanizacion
            LEFT JOIN condominio c ON c.codigo_condominio = p.codigo_condominio
            WHERE p.codigo_publicacion = :codigo_publicacion
              AND {$permiso}
            LIMIT 1
        ";

        $st = $this->dblink->prepare($sql);
        foreach ($params as $key => $value) {
            $st->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function crear(array $auth, array $data, ?string $imagenPortada): int
    {
        $this->validarRolGestion($auth);
        $destino = $this->destinoPermitido($auth, $data);
        $usuario = $this->codigoUsuario($auth);
        $publicar = ($data['accion'] ?? '') === 'publicar';

        if ($usuario <= 0) {
            throw new RuntimeException('No se pudo identificar al usuario autenticado.');
        }

        try {
            $this->dblink->beginTransaction();

            $sql = "
                INSERT INTO comunidad_publicacion (
                    tipo_publicacion, origen_publicacion, alcance,
                    tipo_conjunto, codigo_condominio, codigo_urbanizacion,
                    titulo, resumen, contenido, imagen_portada,
                    prioridad, destacado_dashboard, estado,
                    fecha_publicacion, fecha_expiracion,
                    fecha_evento_inicio, fecha_evento_fin, ubicacion_evento,
                    codigo_usuario_creacion, codigo_usuario_publicacion, codigo_usuario_modificacion
                ) VALUES (
                    :tipo_publicacion, 'administracion_comunidad', 'comunidad',
                    :tipo_conjunto, :codigo_condominio, :codigo_urbanizacion,
                    :titulo, :resumen, :contenido, :imagen_portada,
                    :prioridad, :destacado, 'borrador',
                    NULL, :fecha_expiracion,
                    :fecha_evento_inicio, :fecha_evento_fin, :ubicacion_evento,
                    :usuario_creacion, NULL, :usuario_modificacion
                )
            ";

            $st = $this->dblink->prepare($sql);
            $this->bindDatosPublicacion($st, $data, $destino, $imagenPortada, $usuario, true);
            $st->execute();
            $id = (int)$this->dblink->lastInsertId();

            $this->registrarHistorial($id, 'creacion', null, 'borrador', $usuario, null);

            if ($publicar) {
                $this->publicarDentroTransaccion($id, $usuario, 'borrador');
            }

            $this->dblink->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            throw $e;
        }
    }

    public function actualizar(array $auth, int $codigoPublicacion, array $data, ?string $imagenPortada): array
    {
        $actual = $this->obtenerGestion($auth, $codigoPublicacion);
        if (!$actual) {
            throw new DomainException('La publicación no existe o no está dentro de tu comunidad.');
        }

        if ($actual['estado'] === 'ocultado_moderacion') {
            throw new DomainException('Esta publicación fue ocultada por moderación y no puede editarse desde gestión.');
        }
        if ($actual['estado'] === 'inactivo') {
            throw new DomainException('Una publicación inactiva no puede editarse. Crea una nueva publicación.');
        }

        $destino = $this->destinoPermitido($auth, $data);
        $usuario = $this->codigoUsuario($auth);
        $rutaImagen = $imagenPortada ?? ($actual['imagen_portada'] ?: null);
        $publicar = ($data['accion'] ?? '') === 'publicar' && $actual['estado'] === 'borrador';

        try {
            $this->dblink->beginTransaction();

            $sql = "
                UPDATE comunidad_publicacion
                SET tipo_publicacion = :tipo_publicacion,
                    tipo_conjunto = :tipo_conjunto,
                    codigo_condominio = :codigo_condominio,
                    codigo_urbanizacion = :codigo_urbanizacion,
                    titulo = :titulo,
                    resumen = :resumen,
                    contenido = :contenido,
                    imagen_portada = :imagen_portada,
                    prioridad = :prioridad,
                    destacado_dashboard = :destacado,
                    fecha_expiracion = :fecha_expiracion,
                    fecha_evento_inicio = :fecha_evento_inicio,
                    fecha_evento_fin = :fecha_evento_fin,
                    ubicacion_evento = :ubicacion_evento,
                    codigo_usuario_modificacion = :usuario_modificacion
                WHERE codigo_publicacion = :codigo_publicacion
                LIMIT 1
            ";

            $st = $this->dblink->prepare($sql);
            $this->bindDatosPublicacion($st, $data, $destino, $rutaImagen, $usuario, false);
            $st->bindValue(':codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);
            $st->execute();

            $this->registrarHistorial(
                $codigoPublicacion,
                'edicion',
                (string)$actual['estado'],
                (string)$actual['estado'],
                $usuario,
                null
            );

            if ($publicar) {
                $this->publicarDentroTransaccion($codigoPublicacion, $usuario, 'borrador');
            }

            $this->dblink->commit();

            return [
                'imagen_anterior' => $imagenPortada !== null ? ($actual['imagen_portada'] ?: null) : null,
                'estado' => $publicar ? 'publicado' : $actual['estado'],
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            throw $e;
        }
    }

    public function publicar(array $auth, int $codigoPublicacion): void
    {
        $actual = $this->obtenerGestion($auth, $codigoPublicacion);
        if (!$actual) {
            throw new DomainException('La publicación no existe o no está dentro de tu comunidad.');
        }
        if ($actual['estado'] !== 'borrador') {
            throw new DomainException('Solo los borradores pueden publicarse.');
        }
        if (
            $actual['tipo_publicacion'] === 'evento'
            && !empty($actual['fecha_evento_inicio'])
            && strtotime((string)$actual['fecha_evento_inicio']) < time()
        ) {
            throw new DomainException('No puedes publicar un evento cuya fecha de inicio ya pasó.');
        }
        if (
            !empty($actual['fecha_expiracion'])
            && strtotime((string)$actual['fecha_expiracion']) <= time()
        ) {
            throw new DomainException('Actualiza la fecha de expiración antes de publicar este borrador.');
        }

        $usuario = $this->codigoUsuario($auth);
        try {
            $this->dblink->beginTransaction();
            $this->publicarDentroTransaccion($codigoPublicacion, $usuario, 'borrador');
            $this->dblink->commit();
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            throw $e;
        }
    }

    public function desactivar(array $auth, int $codigoPublicacion): void
    {
        $actual = $this->obtenerGestion($auth, $codigoPublicacion);
        if (!$actual) {
            throw new DomainException('La publicación no existe o no está dentro de tu comunidad.');
        }
        if ($actual['estado'] !== 'publicado') {
            throw new DomainException('Solo una publicación publicada puede desactivarse.');
        }

        $usuario = $this->codigoUsuario($auth);
        try {
            $this->dblink->beginTransaction();
            $st = $this->dblink->prepare("UPDATE comunidad_publicacion
                                          SET estado = 'inactivo',
                                              codigo_usuario_modificacion = :usuario
                                          WHERE codigo_publicacion = :id LIMIT 1");
            $st->bindValue(':usuario', $usuario, PDO::PARAM_INT);
            $st->bindValue(':id', $codigoPublicacion, PDO::PARAM_INT);
            $st->execute();
            $this->registrarHistorial($codigoPublicacion, 'desactivacion', 'publicado', 'inactivo', $usuario, null);
            $this->dblink->commit();
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            throw $e;
        }
    }

    public function listarHistorial(array $auth, int $codigoPublicacion): array
    {
        if (!$this->obtenerGestion($auth, $codigoPublicacion)) {
            throw new DomainException('La publicación no existe o no está dentro de tu comunidad.');
        }

        $sql = "
            SELECT
                h.codigo_historial,
                h.accion,
                h.estado_anterior,
                h.estado_nuevo,
                h.motivo,
                h.created_at,
                u.nombre AS usuario_accion
            FROM comunidad_publicacion_historial h
            INNER JOIN usuario u ON u.codigo_usuario = h.codigo_usuario_accion
            WHERE h.codigo_publicacion = :id
            ORDER BY h.codigo_historial DESC
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':id', $codigoPublicacion, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function bindDatosPublicacion(
        PDOStatement $st,
        array $data,
        array $destino,
        ?string $imagenPortada,
        int $usuario,
        bool $incluirCreador
    ): void {
        $st->bindValue(':tipo_publicacion', $data['tipo_publicacion'], PDO::PARAM_STR);
        $st->bindValue(':tipo_conjunto', $destino['tipo_conjunto'], PDO::PARAM_STR);
        $st->bindValue(
            ':codigo_condominio',
            $destino['codigo_condominio'],
            $destino['codigo_condominio'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT
        );
        $st->bindValue(
            ':codigo_urbanizacion',
            $destino['codigo_urbanizacion'],
            $destino['codigo_urbanizacion'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT
        );
        $st->bindValue(':titulo', $data['titulo'], PDO::PARAM_STR);
        $st->bindValue(':resumen', $data['resumen'], PDO::PARAM_STR);
        $st->bindValue(':contenido', $data['contenido'], PDO::PARAM_STR);
        $st->bindValue(':imagen_portada', $imagenPortada, $imagenPortada === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $st->bindValue(':prioridad', $data['prioridad'], PDO::PARAM_STR);
        $st->bindValue(':destacado', (int)$data['destacado_dashboard'], PDO::PARAM_INT);
        $st->bindValue(
            ':fecha_expiracion',
            $data['fecha_expiracion'],
            $data['fecha_expiracion'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR
        );
        $st->bindValue(
            ':fecha_evento_inicio',
            $data['fecha_evento_inicio'],
            $data['fecha_evento_inicio'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR
        );
        $st->bindValue(
            ':fecha_evento_fin',
            $data['fecha_evento_fin'],
            $data['fecha_evento_fin'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR
        );
        $st->bindValue(
            ':ubicacion_evento',
            $data['ubicacion_evento'],
            $data['ubicacion_evento'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR
        );
        if ($incluirCreador) {
            $st->bindValue(':usuario_creacion', $usuario, PDO::PARAM_INT);
        }
        $st->bindValue(':usuario_modificacion', $usuario, PDO::PARAM_INT);
    }

    private function publicarDentroTransaccion(int $codigoPublicacion, int $usuario, string $estadoAnterior): void
    {
        $st = $this->dblink->prepare("UPDATE comunidad_publicacion
                                      SET estado = 'publicado',
                                          fecha_publicacion = NOW(),
                                          codigo_usuario_publicacion = :usuario,
                                          codigo_usuario_modificacion = :usuario
                                      WHERE codigo_publicacion = :id LIMIT 1");
        $st->bindValue(':usuario', $usuario, PDO::PARAM_INT);
        $st->bindValue(':id', $codigoPublicacion, PDO::PARAM_INT);
        $st->execute();
        $this->registrarHistorial($codigoPublicacion, 'publicacion', $estadoAnterior, 'publicado', $usuario, null);
    }

    private function registrarHistorial(
        int $codigoPublicacion,
        string $accion,
        ?string $estadoAnterior,
        string $estadoNuevo,
        int $usuario,
        ?string $motivo
    ): void {
        $sql = "INSERT INTO comunidad_publicacion_historial
                (codigo_publicacion, accion, estado_anterior, estado_nuevo, codigo_usuario_accion, motivo)
                VALUES (:publicacion, :accion, :anterior, :nuevo, :usuario, :motivo)";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':publicacion', $codigoPublicacion, PDO::PARAM_INT);
        $st->bindValue(':accion', $accion, PDO::PARAM_STR);
        $st->bindValue(':anterior', $estadoAnterior, $estadoAnterior === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $st->bindValue(':nuevo', $estadoNuevo, PDO::PARAM_STR);
        $st->bindValue(':usuario', $usuario, PDO::PARAM_INT);
        $st->bindValue(':motivo', $motivo, $motivo === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $st->execute();
    }
}
