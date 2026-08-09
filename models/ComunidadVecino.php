<?php
// models/ComunidadVecino.php
// Entre Vecinos - Consulta segura de publicaciones institucionales para vecinos.

declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class ComunidadVecino extends Conexion
{
    private const ROL_VECINO = 'vecino';
    private const ROL_ADMIN = 'admin';

    private function rol(array $auth): string
    {
        return strtolower(trim((string)($auth['rol'] ?? $auth['nombre_rol'] ?? '')));
    }

    private function codigoUsuario(array $auth): int
    {
        return (int)($auth['codigo_usuario'] ?? 0);
    }

    private function validarConsulta(array $auth): void
    {
        if ($this->codigoUsuario($auth) <= 0) {
            throw new RuntimeException('Tu sesión ha finalizado. Vuelve a iniciar sesión.');
        }

        if ($this->rol($auth) !== self::ROL_VECINO && !$this->esAdmin($auth)) {
            throw new RuntimeException('No tienes permisos para consultar esta vista de Comunidad.');
        }
    }

    private function esAdmin(array $auth): bool
    {
        $rol = $this->rol($auth);
        $codigoRol = (int)($auth['codigo_rol'] ?? 0);
        $adminRoleId = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        return in_array($rol, [self::ROL_ADMIN, 'administrador'], true) || $codigoRol === $adminRoleId;
    }

    public function listarComunidadesActivas(): array
    {
        $sql = "SELECT * FROM (
            SELECT 'condominio' tipo_conjunto, c.codigo_condominio codigo_comunidad,
                   c.nombre_condominio nombre_comunidad, c.direccion_condominio direccion,
                   d.codigo_distrito, d.nombre_distrito, pr.codigo_provincia, pr.nombre_provincia,
                   dep.codigo_departamento, dep.nombre_departamento
            FROM condominio c
            LEFT JOIN ubigeo_distrito d ON d.codigo_distrito=c.codigo_distrito
            LEFT JOIN ubigeo_provincia pr ON pr.codigo_provincia=d.codigo_provincia
            LEFT JOIN ubigeo_departamento dep ON dep.codigo_departamento=pr.codigo_departamento
            WHERE c.estado='A'
            UNION ALL
            SELECT 'urbanizacion', u.codigo_urbanizacion, u.nombre_urbanizacion, u.direccion_urbanizacion,
                   d.codigo_distrito, d.nombre_distrito, pr.codigo_provincia, pr.nombre_provincia,
                   dep.codigo_departamento, dep.nombre_departamento
            FROM urbanizacion u
            LEFT JOIN ubigeo_distrito d ON d.codigo_distrito=u.codigo_distrito
            LEFT JOIN ubigeo_provincia pr ON pr.codigo_provincia=d.codigo_provincia
            LEFT JOIN ubigeo_departamento dep ON dep.codigo_departamento=pr.codigo_departamento
            WHERE u.estado='A'
        ) x ORDER BY nombre_departamento,nombre_provincia,nombre_distrito,nombre_comunidad";
        return $this->dblink->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function obtenerComunidadAdmin(string $tipo, int $codigo): array
    {
        $tipo = strtolower(trim($tipo));
        if (!in_array($tipo, ['condominio','urbanizacion'], true) || $codigo <= 0) {
            throw new DomainException('Selecciona un condominio o una urbanización para consultar sus novedades.');
        }

        if ($tipo === 'condominio') {
            $sql = "SELECT c.codigo_condominio codigo_comunidad,c.nombre_condominio nombre_comunidad,c.direccion_condominio direccion,
                           d.nombre_distrito,pr.nombre_provincia,dep.nombre_departamento
                    FROM condominio c LEFT JOIN ubigeo_distrito d ON d.codigo_distrito=c.codigo_distrito
                    LEFT JOIN ubigeo_provincia pr ON pr.codigo_provincia=d.codigo_provincia
                    LEFT JOIN ubigeo_departamento dep ON dep.codigo_departamento=pr.codigo_departamento
                    WHERE c.codigo_condominio=:codigo AND c.estado='A' LIMIT 1";
        } else {
            $sql = "SELECT u.codigo_urbanizacion codigo_comunidad,u.nombre_urbanizacion nombre_comunidad,u.direccion_urbanizacion direccion,
                           d.nombre_distrito,pr.nombre_provincia,dep.nombre_departamento
                    FROM urbanizacion u LEFT JOIN ubigeo_distrito d ON d.codigo_distrito=u.codigo_distrito
                    LEFT JOIN ubigeo_provincia pr ON pr.codigo_provincia=d.codigo_provincia
                    LEFT JOIN ubigeo_departamento dep ON dep.codigo_departamento=pr.codigo_departamento
                    WHERE u.codigo_urbanizacion=:codigo AND u.estado='A' LIMIT 1";
        }
        $st=$this->dblink->prepare($sql);$st->execute([':codigo'=>$codigo]);$row=$st->fetch(PDO::FETCH_ASSOC);
        if(!is_array($row)) throw new DomainException('La comunidad seleccionada no está disponible.');
        return [
            'tipo_conjunto'=>$tipo,'codigo_comunidad'=>(int)$row['codigo_comunidad'],
            'codigo_condominio'=>$tipo==='condominio'?(int)$row['codigo_comunidad']:null,
            'codigo_urbanizacion'=>$tipo==='urbanizacion'?(int)$row['codigo_comunidad']:null,
            'nombre_comunidad'=>(string)$row['nombre_comunidad'],
            'etiqueta_tipo'=>$tipo==='urbanizacion'?'Urbanización':'Condominio',
            'direccion'=>(string)($row['direccion']??''),'nombre_distrito'=>(string)($row['nombre_distrito']??''),
            'nombre_provincia'=>(string)($row['nombre_provincia']??''),'nombre_departamento'=>(string)($row['nombre_departamento']??'')
        ];
    }

    public function resolverComunidad(array $auth, array $filtros=[]): array
    {
        $this->validarConsulta($auth);
        if ($this->esAdmin($auth)) {
            return $this->obtenerComunidadAdmin((string)($filtros['tipo_conjunto']??''),(int)($filtros['codigo_comunidad']??0));
        }
        return $this->obtenerComunidadActual($auth);
    }

    /**
     * Resuelve la residencia vigente directamente desde base de datos.
     * No confía únicamente en el JWT, para evitar que una sesión anterior
     * visualice publicaciones de una comunidad que ya no corresponde.
     */
    public function obtenerComunidadActual(array $auth): array
    {
        $this->validarConsulta($auth);

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
        $comunidad = $this->resolverComunidad($auth, $filtros);

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

    public function obtenerPublicacion(array $auth, int $codigoPublicacion, array $filtros = []): ?array
    {
        if ($codigoPublicacion <= 0) {
            throw new InvalidArgumentException('Identificador de publicación inválido.');
        }

        $comunidad = $this->resolverComunidad($auth, $filtros);

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
