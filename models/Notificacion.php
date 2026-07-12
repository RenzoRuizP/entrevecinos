<?php
// models/Notificacion.php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class Notificacion extends Conexion
{
    public function crear(array $data): int
    {
        $sql = "INSERT INTO notificacion
                (codigo_usuario, canal, categoria, subcategoria, referencia_id, titulo, mensaje, payload_json, estado)
                VALUES
                (:u, :canal, :cat, :sub, :ref, :tit, :msg, :payload, 'no_leida')";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':u', (int)($data['codigo_usuario'] ?? 0), PDO::PARAM_INT);
        $st->bindValue(':canal', (string)($data['canal'] ?? 'app'), PDO::PARAM_STR);
        $st->bindValue(':cat', (string)($data['categoria'] ?? ''), PDO::PARAM_STR);
        $st->bindValue(':sub', (string)($data['subcategoria'] ?? ''), PDO::PARAM_STR);

        $ref = $data['referencia_id'] ?? null;
        $st->bindValue(':ref', $ref === null ? null : (int)$ref, $ref === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

        $st->bindValue(':tit', (string)($data['titulo'] ?? ''), PDO::PARAM_STR);
        $st->bindValue(':msg', (string)($data['mensaje'] ?? ''), PDO::PARAM_STR);

        $payload = $data['payload_json'] ?? null;
        $st->bindValue(':payload', $payload !== null ? (string)$payload : null, $payload !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

        $ok = $st->execute();
        if (!$ok) return 0;

        return (int)$this->dblink->lastInsertId();
    }

    public function listarPorUsuario(int $codigoUsuario, array $filtros): array
    {
        $categoria = strtolower(trim((string)($filtros['categoria'] ?? 'residencia')));
        $estado    = strtolower(trim((string)($filtros['estado'] ?? 'no_leida'))); // no_leida|leida|all
        $page      = max(1, (int)($filtros['page'] ?? 1));
        $size      = max(1, min(50, (int)($filtros['size'] ?? 10)));
        $off       = ($page - 1) * $size;

        $where = ["codigo_usuario = :u"];
        $params = [':u' => $codigoUsuario];

        if ($categoria !== 'all' && $categoria !== '') {
            $where[] = "categoria = :cat";
            $params[':cat'] = $categoria;
        }

        if ($estado !== 'all' && in_array($estado, ['no_leida','leida'], true)) {
            $where[] = "estado = :est";
            $params[':est'] = $estado;
        }

        $whereSql = "WHERE " . implode(" AND ", $where);

        // Total
        $stT = $this->dblink->prepare("SELECT COUNT(*) AS total FROM notificacion {$whereSql}");
        foreach ($params as $k => $v) {
            $stT->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stT->execute();
        $total = (int)($stT->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        // Data
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
                ORDER BY created_at DESC
                LIMIT :lim OFFSET :off";

        $st = $this->dblink->prepare($sql);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->bindValue(':lim', $size, PDO::PARAM_INT);
        $st->bindValue(':off', $off, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as &$row) {
            $payloadRaw = trim((string)($row['payload_json'] ?? ''));
            $payload = $payloadRaw !== '' ? json_decode($payloadRaw, true) : [];
            $row['payload'] = is_array($payload) ? $payload : [];
        }
        unset($row);

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

    public function marcarLeida(int $codigoNotificacion, int $codigoUsuario): bool
    {
        $sql = "UPDATE notificacion
                SET estado = 'leida', read_at = CURRENT_TIMESTAMP
                WHERE codigo_notificacion = :id
                  AND codigo_usuario = :u
                LIMIT 1";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':id', $codigoNotificacion, PDO::PARAM_INT);
        $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        return (bool)$st->execute();
    }

    public function contarNoLeidas(int $codigoUsuario, string $categoria = 'residencia'): int
    {
        $categoria = strtolower(trim($categoria));
        $categoria = $categoria !== '' ? $categoria : 'all';

        $sql = "
            SELECT COUNT(*) AS c
            FROM notificacion
            WHERE codigo_usuario = :u
              AND estado = 'no_leida'
        ";

        if ($categoria !== 'all') {
            $sql .= " AND categoria = :cat";
        }

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);

        if ($categoria !== 'all') {
            $st->bindValue(':cat', $categoria, PDO::PARAM_STR);
        }

        $st->execute();
        return (int)($st->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
    }

    /**
     * ✅ FIX RAÍZ:
     * Cierra (marca leídas) todas las notificaciones del usuario para una referencia (solicitud).
     * Ej: categoria='residencia' y referencia_id = codigo_solicitud observado/rechazado.
     */
    public function marcarLeidasPorReferencia(int $codigoUsuario, string $categoria, int $referenciaId): int
    {
        $categoria = strtolower(trim($categoria));

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
}
