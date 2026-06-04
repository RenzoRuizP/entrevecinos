<?php
// models/ComunidadVecino.php
// Entre Vecinos - Consulta segura de publicaciones institucionales para vecinos.

declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class ComunidadVecino extends Conexion
{
    private const ROL_VECINO = 'vecino';

    private function rol(array $auth): string
    {
        return strtolower(trim((string)($auth['rol'] ?? $auth['nombre_rol'] ?? '')));
    }

    private function codigoUsuario(array $auth): int
    {
        return (int)($auth['codigo_usuario'] ?? 0);
    }

    private function validarVecino(array $auth): void
    {
        if ($this->codigoUsuario($auth) <= 0) {
            throw new RuntimeException('Tu sesión ha finalizado. Vuelve a iniciar sesión.');
        }

        if ($this->rol($auth) !== self::ROL_VECINO) {
            throw new RuntimeException('No tienes permisos para consultar esta vista de Comunidad.');
        }
    }

    /**
     * Resuelve la residencia vigente directamente desde base de datos.
     * No confía únicamente en el JWT, para evitar que una sesión anterior
     * visualice publicaciones de una comunidad que ya no corresponde.
     */
    public function obtenerComunidadActual(array $auth): array
    {
        $this->validarVecino($auth);

        $sql = "
            SELECT
                ur.tipo_conjunto,
                ur.codigo_condominio,
                ur.codigo_urbanizacion,
                CASE
                    WHEN ur.tipo_conjunto = 'urbanizacion' THEN urb.nombre_urbanizacion
                    WHEN ur.tipo_conjunto = 'condominio' THEN c.nombre_condominio
                    ELSE NULL
                END AS nombre_comunidad
            FROM usuario u
            INNER JOIN usuario_residencia ur
                ON ur.codigo_usuario_residencia = (
                    SELECT ur2.codigo_usuario_residencia
                    FROM usuario_residencia ur2
                    WHERE ur2.codigo_usuario = u.codigo_usuario
                    ORDER BY ur2.codigo_usuario_residencia DESC
                    LIMIT 1
                )
            LEFT JOIN urbanizacion urb
                ON urb.codigo_urbanizacion = ur.codigo_urbanizacion
            LEFT JOIN condominio c
                ON c.codigo_condominio = ur.codigo_condominio
            WHERE u.codigo_usuario = :codigo_usuario
              AND u.estado = 2
            LIMIT 1
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario', $this->codigoUsuario($auth), PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row)) {
            throw new DomainException('No tienes una comunidad habilitada para visualizar novedades.');
        }

        $tipo = strtolower(trim((string)($row['tipo_conjunto'] ?? '')));

        if ($tipo === 'urbanizacion' && (int)($row['codigo_urbanizacion'] ?? 0) > 0) {
            return [
                'tipo_conjunto' => 'urbanizacion',
                'codigo_comunidad' => (int)$row['codigo_urbanizacion'],
                'codigo_condominio' => null,
                'codigo_urbanizacion' => (int)$row['codigo_urbanizacion'],
                'nombre_comunidad' => (string)($row['nombre_comunidad'] ?? 'Urbanización'),
                'etiqueta_tipo' => 'Urbanización',
            ];
        }

        if ($tipo === 'condominio' && (int)($row['codigo_condominio'] ?? 0) > 0) {
            return [
                'tipo_conjunto' => 'condominio',
                'codigo_comunidad' => (int)$row['codigo_condominio'],
                'codigo_condominio' => (int)$row['codigo_condominio'],
                'codigo_urbanizacion' => null,
                'nombre_comunidad' => (string)($row['nombre_comunidad'] ?? 'Condominio'),
                'etiqueta_tipo' => 'Condominio',
            ];
        }

        throw new DomainException('La residencia registrada no tiene una comunidad válida.');
    }

    private function filtroVisibleSql(array $comunidad, array &$params, string $alias = 'p'): string
    {
        $params[':tipo_comunidad'] = (string)$comunidad['tipo_conjunto'];

        $condiciones = [
            "{$alias}.alcance = 'comunidad'",
            "{$alias}.estado = 'publicado'",
            "({$alias}.fecha_expiracion IS NULL OR {$alias}.fecha_expiracion > NOW())",
            "{$alias}.tipo_conjunto = :tipo_comunidad",
        ];

        if ($comunidad['tipo_conjunto'] === 'urbanizacion') {
            $params[':codigo_urbanizacion'] = (int)$comunidad['codigo_urbanizacion'];
            $condiciones[] = "{$alias}.codigo_urbanizacion = :codigo_urbanizacion";
            $condiciones[] = "{$alias}.codigo_condominio IS NULL";
        } else {
            $params[':codigo_condominio'] = (int)$comunidad['codigo_condominio'];
            $condiciones[] = "{$alias}.codigo_condominio = :codigo_condominio";
            $condiciones[] = "{$alias}.codigo_urbanizacion IS NULL";
        }

        return implode(' AND ', $condiciones);
    }

    private function bindParams(PDOStatement $st, array $params): void
    {
        foreach ($params as $key => $value) {
            $st->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
    }

    public function listarPublicaciones(array $auth, array $filtros = []): array
    {
        $comunidad = $this->obtenerComunidadActual($auth);

        $tipo = strtolower(trim((string)($filtros['tipo'] ?? 'all')));
        $q = trim((string)($filtros['q'] ?? ''));
        $page = max(1, (int)($filtros['page'] ?? 1));
        $size = max(1, min(24, (int)($filtros['size'] ?? 9)));
        $offset = ($page - 1) * $size;

        $params = [];
        $where = [$this->filtroVisibleSql($comunidad, $params)];

        if (in_array($tipo, ['comunicado', 'noticia', 'evento'], true)) {
            $where[] = 'p.tipo_publicacion = :tipo_publicacion';
            $params[':tipo_publicacion'] = $tipo;
        }

        if ($q !== '') {
            // Marcadores separados: compatible con ATTR_EMULATE_PREPARES=false.
            $like = '%' . $q . '%';
            $where[] = '(p.titulo LIKE :q_titulo OR p.resumen LIKE :q_resumen OR p.contenido LIKE :q_contenido)';
            $params[':q_titulo'] = $like;
            $params[':q_resumen'] = $like;
            $params[':q_contenido'] = $like;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $stTotal = $this->dblink->prepare("
            SELECT COUNT(*)
            FROM comunidad_publicacion p
            {$whereSql}
        ");
        $this->bindParams($stTotal, $params);
        $stTotal->execute();
        $total = (int)$stTotal->fetchColumn();

        $sql = "
            SELECT
                p.codigo_publicacion,
                p.tipo_publicacion,
                p.titulo,
                p.resumen,
                p.imagen_portada,
                p.prioridad,
                p.destacado_dashboard,
                p.fecha_publicacion,
                p.fecha_expiracion,
                p.fecha_evento_inicio,
                p.fecha_evento_fin,
                p.ubicacion_evento
            FROM comunidad_publicacion p
            {$whereSql}
            ORDER BY
                p.destacado_dashboard DESC,
                CASE p.prioridad
                    WHEN 'urgente' THEN 1
                    WHEN 'importante' THEN 2
                    ELSE 3
                END,
                p.fecha_publicacion DESC,
                p.codigo_publicacion DESC
            LIMIT :lim OFFSET :off
        ";

        $st = $this->dblink->prepare($sql);
        $this->bindParams($st, $params);
        $st->bindValue(':lim', $size, PDO::PARAM_INT);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();

        return [
            'comunidad' => $comunidad,
            'items' => $st->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'meta' => [
                'page' => $page,
                'size' => $size,
                'total' => $total,
                'pages' => max(1, (int)ceil($total / $size)),
            ],
            'counts' => $this->contarPublicacionesVisibles($comunidad),
        ];
    }

    private function contarPublicacionesVisibles(array $comunidad): array
    {
        $params = [];
        $where = $this->filtroVisibleSql($comunidad, $params);

        $sql = "
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN p.tipo_publicacion = 'comunicado' THEN 1 ELSE 0 END) AS comunicados,
                SUM(CASE WHEN p.tipo_publicacion = 'noticia' THEN 1 ELSE 0 END) AS noticias,
                SUM(CASE WHEN p.tipo_publicacion = 'evento' THEN 1 ELSE 0 END) AS eventos,
                SUM(CASE WHEN p.destacado_dashboard = 1 THEN 1 ELSE 0 END) AS destacados
            FROM comunidad_publicacion p
            WHERE {$where}
        ";

        $st = $this->dblink->prepare($sql);
        $this->bindParams($st, $params);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => (int)($row['total'] ?? 0),
            'comunicados' => (int)($row['comunicados'] ?? 0),
            'noticias' => (int)($row['noticias'] ?? 0),
            'eventos' => (int)($row['eventos'] ?? 0),
            'destacados' => (int)($row['destacados'] ?? 0),
        ];
    }

    public function obtenerPublicacion(array $auth, int $codigoPublicacion): ?array
    {
        if ($codigoPublicacion <= 0) {
            throw new InvalidArgumentException('Identificador de publicación inválido.');
        }

        $comunidad = $this->obtenerComunidadActual($auth);

        $params = [':codigo_publicacion' => $codigoPublicacion];
        $where = $this->filtroVisibleSql($comunidad, $params);

        $sql = "
            SELECT
                p.codigo_publicacion,
                p.tipo_publicacion,
                p.titulo,
                p.resumen,
                p.contenido,
                p.imagen_portada,
                p.prioridad,
                p.destacado_dashboard,
                p.fecha_publicacion,
                p.fecha_expiracion,
                p.fecha_evento_inicio,
                p.fecha_evento_fin,
                p.ubicacion_evento
            FROM comunidad_publicacion p
            WHERE p.codigo_publicacion = :codigo_publicacion
              AND {$where}
            LIMIT 1
        ";

        $st = $this->dblink->prepare($sql);
        $this->bindParams($st, $params);
        $st->execute();

        $item = $st->fetch(PDO::FETCH_ASSOC);
        if (!is_array($item)) {
            return null;
        }

        $item['nombre_comunidad'] = $comunidad['nombre_comunidad'];
        $item['tipo_conjunto'] = $comunidad['tipo_conjunto'];

        return $item;
    }
}
