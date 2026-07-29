<?php
// models/Notificacion.php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

/**
 * Núcleo centralizado del Centro de Notificaciones EV.
 *
 * Todas las notificaciones internas deben crearse desde este modelo para
 * mantener categorías, payloads, seguridad y reglas de agrupación coherentes.
 */
final class Notificacion extends Conexion
{
    public const CAT_CUENTA = 'cuenta';
    public const CAT_RESIDENCIA = 'residencia';
    public const CAT_PUBLICACION = 'publicacion';
    public const CAT_BILLETERA = 'billetera';
    public const CAT_PEDIDO = 'pedido';
    public const CAT_SERVICIO = 'servicio';
    public const CAT_COMUNIDAD = 'comunidad';
    public const CAT_SOPORTE = 'soporte';

    public const MARCADA = 'actualizada';
    public const YA_LEIDA = 'ya_leida';
    public const NO_ENCONTRADA = 'no_encontrada';

    /** @var string[] */
    private const CATEGORIAS_VALIDAS = [
        self::CAT_CUENTA,
        self::CAT_RESIDENCIA,
        self::CAT_PUBLICACION,
        self::CAT_BILLETERA,
        self::CAT_PEDIDO,
        self::CAT_SERVICIO,
        self::CAT_COMUNIDAD,
        self::CAT_SOPORTE,
    ];

    /** @var string[] */
    private const RUTAS_INTERNAS_PERMITIDAS = [
        '/MenuPrincipal',
        '/notificaciones',
        '/notificaciones-residencia',
        '/cuenta-observada',
        '/publicacion',
        '/billetera',
        '/comunidad',
        '/mis-pedidos-comprador',
        '/mis-pedidos-vendedor',
        '/mis-solicitudes-servicio-comprador',
        '/mis-solicitudes-servicio-vendedor',
        '/atender-servicios',
    ];

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo instanceof PDO) {
            $this->dblink = $pdo;
            return;
        }

        parent::__construct();
    }

    public static function normalizarCategoria(string $categoria): string
    {
        $categoria = strtolower(trim($categoria));

        return match ($categoria) {
            'pedidos' => self::CAT_PEDIDO,
            'recarga', 'recargas', 'wallet' => self::CAT_BILLETERA,
            default => $categoria,
        };
    }

    public static function esCategoriaFiltroValida(string $categoria): bool
    {
        $categoria = strtolower(trim($categoria));
        return in_array($categoria, [
            'all',
            'cuenta_residencia',
            'billetera_recargas',
            'pedido',
            'pedidos',
            ...self::CATEGORIAS_VALIDAS,
        ], true);
    }

    public static function sanitizarRutaInterna(?string $ruta): ?string
    {
        $ruta = trim((string)$ruta);
        if ($ruta === '') {
            return null;
        }

        if (
            preg_match('/^[a-z][a-z0-9+.-]*:/i', $ruta)
            || str_starts_with($ruta, '//')
            || preg_match('/[\x00-\x1F\x7F]/', $ruta)
        ) {
            return null;
        }

        $partes = parse_url($ruta);
        if ($partes === false || isset($partes['host']) || isset($partes['scheme'])) {
            return null;
        }

        $path = '/' . ltrim((string)($partes['path'] ?? ''), '/');
        $path = preg_replace('#/+#', '/', $path) ?: '/';

        if (!in_array($path, self::RUTAS_INTERNAS_PERMITIDAS, true)) {
            return null;
        }

        $query = isset($partes['query']) && $partes['query'] !== ''
            ? '?' . $partes['query']
            : '';

        return $path . $query;
    }

    public function crear(array $data): int
    {
        $normalizada = $this->normalizarDatosCreacion($data);

        $sql = "INSERT INTO notificacion
                (codigo_usuario, canal, categoria, subcategoria, referencia_id, titulo, mensaje, payload_json, estado)
                VALUES
                (:u, :canal, :cat, :sub, :ref, :tit, :msg, :payload, 'no_leida')";

        $st = $this->dblink->prepare($sql);
        $this->bindCreacion($st, $normalizada);
        $st->execute();

        return (int)$this->dblink->lastInsertId();
    }

    /**
     * Agrupa eventos repetitivos. Si ya existe una notificación no leída con
     * igual usuario/categoría/subcategoría/referencia, actualiza esa fila y la
     * vuelve a colocar arriba del listado.
     */
    public function crearOActualizarNoLeida(array $data): int
    {
        $normalizada = $this->normalizarDatosCreacion($data);

        if ($normalizada['referencia_id'] === null || $normalizada['subcategoria'] === '') {
            return $this->crear($normalizada);
        }

        $sql = "SELECT codigo_notificacion
                FROM notificacion
                WHERE codigo_usuario = :u
                  AND categoria = :cat
                  AND subcategoria = :sub
                  AND referencia_id = :ref
                  AND estado = 'no_leida'
                ORDER BY codigo_notificacion DESC
                LIMIT 1
                FOR UPDATE";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':u', $normalizada['codigo_usuario'], PDO::PARAM_INT);
        $st->bindValue(':cat', $normalizada['categoria'], PDO::PARAM_STR);
        $st->bindValue(':sub', $normalizada['subcategoria'], PDO::PARAM_STR);
        $st->bindValue(':ref', $normalizada['referencia_id'], PDO::PARAM_INT);
        $st->execute();
        $id = (int)($st->fetchColumn() ?: 0);

        if ($id <= 0) {
            return $this->crear($normalizada);
        }

        $up = $this->dblink->prepare(
            "UPDATE notificacion
             SET canal = :canal,
                 titulo = :tit,
                 mensaje = :msg,
                 payload_json = :payload,
                 created_at = CURRENT_TIMESTAMP,
                 read_at = NULL
             WHERE codigo_notificacion = :id
               AND codigo_usuario = :u
               AND estado = 'no_leida'
             LIMIT 1"
        );
        $up->bindValue(':canal', $normalizada['canal'], PDO::PARAM_STR);
        $up->bindValue(':tit', $normalizada['titulo'], PDO::PARAM_STR);
        $up->bindValue(':msg', $normalizada['mensaje'], PDO::PARAM_STR);
        $up->bindValue(
            ':payload',
            $normalizada['payload_json'],
            $normalizada['payload_json'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR
        );
        $up->bindValue(':id', $id, PDO::PARAM_INT);
        $up->bindValue(':u', $normalizada['codigo_usuario'], PDO::PARAM_INT);
        $up->execute();

        return $id;
    }

    public function listarPorUsuario(int $codigoUsuario, array $filtros): array
    {
        $categoria = strtolower(trim((string)($filtros['categoria'] ?? 'all')));
        $estado = strtolower(trim((string)($filtros['estado'] ?? 'no_leida')));
        $page = max(1, (int)($filtros['page'] ?? 1));
        $size = max(1, min(50, (int)($filtros['size'] ?? 10)));
        $off = ($page - 1) * $size;

        if (!self::esCategoriaFiltroValida($categoria)) {
            $categoria = 'all';
        }
        if (!in_array($estado, ['no_leida', 'leida', 'all'], true)) {
            $estado = 'no_leida';
        }

        [$whereSql, $params] = $this->construirFiltroUsuarioCategoriaEstado(
            $codigoUsuario,
            $categoria,
            $estado
        );

        $stT = $this->dblink->prepare("SELECT COUNT(*) FROM notificacion {$whereSql}");
        $this->bindParams($stT, $params);
        $stT->execute();
        $total = (int)$stT->fetchColumn();

        $sql = "SELECT
                    codigo_notificacion,
                    codigo_usuario,
                    canal,
                    categoria,
                    subcategoria,
                    referencia_id,
                    titulo,
                    mensaje,
                    payload_json,
                    estado,
                    created_at,
                    read_at
                FROM notificacion
                {$whereSql}
                ORDER BY created_at DESC, codigo_notificacion DESC
                LIMIT :lim OFFSET :off";

        $st = $this->dblink->prepare($sql);
        $this->bindParams($st, $params);
        $st->bindValue(':lim', $size, PDO::PARAM_INT);
        $st->bindValue(':off', $off, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $rows = array_map(fn(array $row): array => $this->hidratarPayload($row), $rows);

        return [
            'ok' => true,
            'data' => $rows,
            'meta' => [
                'page' => $page,
                'size' => $size,
                'total' => $total,
            ],
        ];
    }

    /**
     * Resumen de pendientes en una sola consulta.
     */
    public function resumenNoLeidas(int $codigoUsuario): array
    {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN categoria = 'cuenta' THEN 1 ELSE 0 END) AS cuenta,
                    SUM(CASE WHEN categoria = 'residencia' THEN 1 ELSE 0 END) AS residencia,
                    SUM(CASE WHEN categoria = 'publicacion' THEN 1 ELSE 0 END) AS publicacion,
                    SUM(CASE WHEN categoria IN ('billetera', 'recarga', 'recargas') THEN 1 ELSE 0 END) AS billetera,
                    SUM(CASE WHEN categoria IN ('pedido', 'pedidos') THEN 1 ELSE 0 END) AS pedido,
                    SUM(CASE WHEN categoria = 'servicio' THEN 1 ELSE 0 END) AS servicio,
                    SUM(CASE WHEN categoria = 'comunidad' THEN 1 ELSE 0 END) AS comunidad,
                    SUM(CASE WHEN categoria = 'soporte' THEN 1 ELSE 0 END) AS soporte
                FROM notificacion
                WHERE codigo_usuario = :u
                  AND estado = 'no_leida'";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC) ?: [];

        $categorias = [];
        foreach (self::CATEGORIAS_VALIDAS as $categoria) {
            $categorias[$categoria] = (int)($row[$categoria] ?? 0);
        }

        return [
            'total' => (int)($row['total'] ?? 0),
            'categorias' => $categorias,
            // Alias temporal para clientes anteriores.
            'pedidos' => $categorias[self::CAT_PEDIDO],
        ];
    }

    public function resumen(int $codigoUsuario, bool $incluirItems = false, int $limite = 8): array
    {
        $data = $this->resumenNoLeidas($codigoUsuario);
        $data['items'] = [];

        if ($incluirItems) {
            $limite = max(1, min(20, $limite));
            $st = $this->dblink->prepare(
                "SELECT codigo_notificacion, codigo_usuario, canal, categoria, subcategoria,
                        referencia_id, titulo, mensaje, payload_json, estado, created_at, read_at
                 FROM notificacion
                 WHERE codigo_usuario = :u
                 ORDER BY created_at DESC, codigo_notificacion DESC
                 LIMIT :lim"
            );
            $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
            $st->bindValue(':lim', $limite, PDO::PARAM_INT);
            $st->execute();
            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $data['items'] = array_map(fn(array $row): array => $this->hidratarPayload($row), $rows);
        }

        return $data;
    }

    /**
     * Resultado: actualizada | ya_leida | no_encontrada.
     */
    public function marcarLeidaConResultado(int $codigoNotificacion, int $codigoUsuario): string
    {
        $st = $this->dblink->prepare(
            "SELECT estado
             FROM notificacion
             WHERE codigo_notificacion = :id
               AND codigo_usuario = :u
             LIMIT 1"
        );
        $st->bindValue(':id', $codigoNotificacion, PDO::PARAM_INT);
        $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();
        $estado = $st->fetchColumn();

        if ($estado === false) {
            return self::NO_ENCONTRADA;
        }
        if ((string)$estado === 'leida') {
            return self::YA_LEIDA;
        }

        $up = $this->dblink->prepare(
            "UPDATE notificacion
             SET estado = 'leida', read_at = CURRENT_TIMESTAMP
             WHERE codigo_notificacion = :id
               AND codigo_usuario = :u
               AND estado = 'no_leida'
             LIMIT 1"
        );
        $up->bindValue(':id', $codigoNotificacion, PDO::PARAM_INT);
        $up->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $up->execute();

        return $up->rowCount() === 1 ? self::MARCADA : self::YA_LEIDA;
    }

    public function marcarLeida(int $codigoNotificacion, int $codigoUsuario): bool
    {
        return $this->marcarLeidaConResultado($codigoNotificacion, $codigoUsuario) !== self::NO_ENCONTRADA;
    }

    public function contarNoLeidas(int $codigoUsuario, string $categoria = 'all'): int
    {
        $resumen = $this->resumenNoLeidas($codigoUsuario);
        $categoria = strtolower(trim($categoria));

        if ($categoria === '' || $categoria === 'all') {
            return (int)$resumen['total'];
        }
        if ($categoria === 'pedidos') {
            $categoria = self::CAT_PEDIDO;
        }
        if ($categoria === 'cuenta_residencia') {
            return (int)$resumen['categorias'][self::CAT_CUENTA]
                + (int)$resumen['categorias'][self::CAT_RESIDENCIA];
        }
        if ($categoria === 'billetera_recargas') {
            return (int)$resumen['categorias'][self::CAT_BILLETERA];
        }

        return (int)($resumen['categorias'][self::normalizarCategoria($categoria)] ?? 0);
    }

    public function marcarTodasLeidas(int $codigoUsuario, string $categoria = 'all'): int
    {
        $categoria = strtolower(trim($categoria));
        if (!self::esCategoriaFiltroValida($categoria)) {
            $categoria = 'all';
        }

        [$whereSql, $params] = $this->construirFiltroUsuarioCategoriaEstado(
            $codigoUsuario,
            $categoria,
            'no_leida'
        );

        $st = $this->dblink->prepare(
            "UPDATE notificacion
             SET estado = 'leida', read_at = CURRENT_TIMESTAMP
             {$whereSql}"
        );
        $this->bindParams($st, $params);
        $st->execute();

        return (int)$st->rowCount();
    }

    public function marcarLeidasPorReferencia(int $codigoUsuario, string $categoria, int $referenciaId): int
    {
        $categoria = self::normalizarCategoria($categoria);

        $sql = "UPDATE notificacion
                SET estado = 'leida', read_at = CURRENT_TIMESTAMP
                WHERE codigo_usuario = :u
                  AND categoria = :cat
                  AND referencia_id = :ref
                  AND estado = 'no_leida'";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $st->bindValue(':cat', $categoria, PDO::PARAM_STR);
        $st->bindValue(':ref', $referenciaId, PDO::PARAM_INT);
        $st->execute();

        return (int)$st->rowCount();
    }

    /**
     * Inserción masiva para una publicación de Comunidad. Usa la residencia
     * vigente más reciente de cada vecino habilitado y evita un bucle PHP.
     */
    public function crearMasivaComunidad(array $data): int
    {
        $tipoConjunto = strtolower(trim((string)($data['tipo_conjunto'] ?? '')));
        $codigoComunidad = (int)($data['codigo_comunidad'] ?? 0);

        if (!in_array($tipoConjunto, ['condominio', 'urbanizacion'], true) || $codigoComunidad <= 0) {
            throw new InvalidArgumentException('Destino de comunidad inválido para notificaciones.');
        }

        $normalizada = $this->normalizarDatosCreacion([
            'codigo_usuario' => 1, // marcador temporal; no se usa en el INSERT SELECT.
            'canal' => 'app',
            'categoria' => self::CAT_COMUNIDAD,
            'subcategoria' => $data['subcategoria'] ?? 'comunidad_publicacion',
            'referencia_id' => $data['referencia_id'] ?? null,
            'titulo' => $data['titulo'] ?? 'Nueva publicación de tu comunidad',
            'mensaje' => $data['mensaje'] ?? 'Revisa la nueva publicación disponible en Entre Vecinos.',
            'payload' => $data['payload'] ?? [],
        ]);

        $columna = $tipoConjunto === 'condominio' ? 'codigo_condominio' : 'codigo_urbanizacion';

        $sql = "INSERT INTO notificacion
                    (codigo_usuario, canal, categoria, subcategoria, referencia_id,
                     titulo, mensaje, payload_json, estado)
                SELECT
                    u.codigo_usuario,
                    :canal,
                    :cat,
                    :sub,
                    :ref,
                    :tit,
                    :msg,
                    :payload,
                    'no_leida'
                FROM usuario u
                INNER JOIN rol r
                    ON r.codigo_rol = u.codigo_rol
                INNER JOIN usuario_residencia ur
                    ON ur.codigo_usuario_residencia = (
                        SELECT ur2.codigo_usuario_residencia
                        FROM usuario_residencia ur2
                        WHERE ur2.codigo_usuario = u.codigo_usuario
                          AND ur2.estado = 1
                        ORDER BY ur2.codigo_usuario_residencia DESC
                        LIMIT 1
                    )
                WHERE u.estado = 2
                  AND r.estado = 1
                  AND LOWER(TRIM(r.nombre)) = 'vecino'
                  AND ur.tipo_conjunto = :tipo_conjunto
                  AND ur.{$columna} = :codigo_comunidad
                  AND NOT EXISTS (
                      SELECT 1
                      FROM notificacion n
                      WHERE n.codigo_usuario = u.codigo_usuario
                        AND n.categoria = :cat_dup
                        AND n.subcategoria = :sub_dup
                        AND n.referencia_id = :ref_dup
                  )";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':canal', $normalizada['canal'], PDO::PARAM_STR);
        $st->bindValue(':cat', $normalizada['categoria'], PDO::PARAM_STR);
        $st->bindValue(':sub', $normalizada['subcategoria'], PDO::PARAM_STR);
        $st->bindValue(':ref', $normalizada['referencia_id'], PDO::PARAM_INT);
        $st->bindValue(':tit', $normalizada['titulo'], PDO::PARAM_STR);
        $st->bindValue(':msg', $normalizada['mensaje'], PDO::PARAM_STR);
        $st->bindValue(
            ':payload',
            $normalizada['payload_json'],
            $normalizada['payload_json'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR
        );
        $st->bindValue(':tipo_conjunto', $tipoConjunto, PDO::PARAM_STR);
        $st->bindValue(':codigo_comunidad', $codigoComunidad, PDO::PARAM_INT);
        $st->bindValue(':cat_dup', $normalizada['categoria'], PDO::PARAM_STR);
        $st->bindValue(':sub_dup', $normalizada['subcategoria'], PDO::PARAM_STR);
        $st->bindValue(':ref_dup', $normalizada['referencia_id'], PDO::PARAM_INT);
        $st->execute();

        return (int)$st->rowCount();
    }

    private function normalizarDatosCreacion(array $data): array
    {
        $codigoUsuario = (int)($data['codigo_usuario'] ?? 0);
        $categoria = self::normalizarCategoria((string)($data['categoria'] ?? ''));
        $titulo = $this->limitarTexto((string)($data['titulo'] ?? ''), 160);
        $mensaje = $this->limitarTexto((string)($data['mensaje'] ?? ''), 1000);

        if ($codigoUsuario <= 0) {
            throw new InvalidArgumentException('Usuario inválido para la notificación.');
        }
        if (!in_array($categoria, self::CATEGORIAS_VALIDAS, true)) {
            throw new InvalidArgumentException('Categoría de notificación inválida.');
        }
        if ($titulo === '') {
            throw new InvalidArgumentException('El título de la notificación es obligatorio.');
        }

        $payload = $data['payload'] ?? null;
        if (!is_array($payload)) {
            $raw = $data['payload_json'] ?? null;
            if (is_string($raw) && trim($raw) !== '') {
                $decoded = json_decode($raw, true);
                $payload = is_array($decoded) ? $decoded : [];
            } else {
                $payload = [];
            }
        }

        if (array_key_exists('ruta', $payload)) {
            $ruta = self::sanitizarRutaInterna((string)$payload['ruta']);
            if ($ruta === null) {
                unset($payload['ruta']);
            } else {
                $payload['ruta'] = $ruta;
            }
        }

        $payloadJson = $payload !== []
            ? json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : null;

        if ($payloadJson === false) {
            $payloadJson = null;
        }

        $referencia = $data['referencia_id'] ?? null;
        $referencia = ($referencia === null || $referencia === '') ? null : (int)$referencia;
        if ($referencia !== null && $referencia <= 0) {
            $referencia = null;
        }

        return [
            'codigo_usuario' => $codigoUsuario,
            'canal' => $this->limitarTexto((string)($data['canal'] ?? 'app'), 30) ?: 'app',
            'categoria' => $categoria,
            'subcategoria' => $this->limitarTexto((string)($data['subcategoria'] ?? ''), 80),
            'referencia_id' => $referencia,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'payload_json' => $payloadJson,
        ];
    }

    private function bindCreacion(PDOStatement $st, array $data): void
    {
        $st->bindValue(':u', $data['codigo_usuario'], PDO::PARAM_INT);
        $st->bindValue(':canal', $data['canal'], PDO::PARAM_STR);
        $st->bindValue(':cat', $data['categoria'], PDO::PARAM_STR);
        $st->bindValue(':sub', $data['subcategoria'], PDO::PARAM_STR);
        $st->bindValue(
            ':ref',
            $data['referencia_id'],
            $data['referencia_id'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT
        );
        $st->bindValue(':tit', $data['titulo'], PDO::PARAM_STR);
        $st->bindValue(':msg', $data['mensaje'], PDO::PARAM_STR);
        $st->bindValue(
            ':payload',
            $data['payload_json'],
            $data['payload_json'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR
        );
    }

    private function limitarTexto(string $texto, int $maximo): string
    {
        $texto = trim($texto);
        if (function_exists('mb_substr')) {
            return mb_substr($texto, 0, $maximo, 'UTF-8');
        }
        return substr($texto, 0, $maximo);
    }

    /** @return array{0:string,1:array<string,int|string>} */
    private function construirFiltroUsuarioCategoriaEstado(
        int $codigoUsuario,
        string $categoria,
        string $estado
    ): array {
        $where = ['codigo_usuario = :u'];
        $params = [':u' => $codigoUsuario];

        switch ($categoria) {
            case 'all':
            case '':
                break;
            case 'cuenta_residencia':
                $where[] = "categoria IN ('cuenta', 'residencia')";
                break;
            case 'billetera_recargas':
                $where[] = "categoria IN ('billetera', 'recarga', 'recargas')";
                break;
            case 'pedido':
            case 'pedidos':
                $where[] = "categoria IN ('pedido', 'pedidos')";
                break;
            default:
                $where[] = 'categoria = :cat';
                $params[':cat'] = self::normalizarCategoria($categoria);
                break;
        }

        if (in_array($estado, ['no_leida', 'leida'], true)) {
            $where[] = 'estado = :est';
            $params[':est'] = $estado;
        }

        return ['WHERE ' . implode(' AND ', $where), $params];
    }

    /** @param array<string,int|string> $params */
    private function bindParams(PDOStatement $st, array $params): void
    {
        foreach ($params as $key => $value) {
            $st->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
    }

    private function hidratarPayload(array $row): array
    {
        $payloadRaw = trim((string)($row['payload_json'] ?? ''));
        $payload = $payloadRaw !== '' ? json_decode($payloadRaw, true) : [];
        $row['payload'] = is_array($payload) ? $payload : [];
        return $row;
    }
}
