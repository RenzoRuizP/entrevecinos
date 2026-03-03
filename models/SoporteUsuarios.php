<?php
// models/SoporteUsuarios.php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class SoporteUsuarios extends Conexion
{
    public function listar(array $f): array
    {
        if (!method_exists($this, 'getDblink')) {
            return ['items' => [], 'total' => 0];
        }

        $db = $this->getDblink();
        if (!$db) return ['items' => [], 'total' => 0];

        $estadoRaw = strtolower(trim((string)($f['estado'] ?? 'revision')));
        $estado = match ($estadoRaw) {
            '1', 'revision', 'en_revision', 'en revisión' => 'revision',
            '2', 'habilitado', 'habilitados'              => 'habilitado',
            '3', 'observado', 'observados'                => 'observado',
            '0', 'inactivo', 'inactivos'                  => 'inactivo',
            'todos', 'all'                                => 'todos',
            default                                       => 'revision',
        };

        $q      = trim((string)($f['q'] ?? ''));
        $page   = max(1, (int)($f['page'] ?? 1));
        $limit  = max(1, min((int)($f['limit'] ?? 10), 100));
        $offset = ($page - 1) * $limit;

        $conjunto   = strtolower(trim((string)($f['conjunto'] ?? '')));
        $conjuntoId = (int)($f['conjunto_id'] ?? 0);

        $where  = [];
        $params = [];

        $joinRevision = "
            LEFT JOIN (
                SELECT
                    codigo_usuario,
                    MAX(estado_revision)     AS estado_revision,
                    MAX(mensaje_observacion) AS mensaje_observacion,
                    MAX(comprobante_path)    AS comprobante_path
                FROM usuario_revision
                GROUP BY codigo_usuario
            ) ur ON ur.codigo_usuario = u.codigo_usuario
        ";

        $joinResidencia = "
            LEFT JOIN (
                SELECT r1.*
                FROM usuario_residencia r1
                INNER JOIN (
                    SELECT codigo_usuario, MAX(codigo_usuario_residencia) AS max_id
                    FROM usuario_residencia
                    GROUP BY codigo_usuario
                ) r2
                  ON r2.codigo_usuario = r1.codigo_usuario
                 AND r2.max_id = r1.codigo_usuario_residencia
            ) r ON r.codigo_usuario = u.codigo_usuario
        ";

        // =========================
        // FILTRO DE ESTADO
        // =========================
        switch ($estado) {
            case 'observado':
                $where[] = 'ur.estado_revision = 3';
                break;

            case 'revision':
                // DDL: 1=En revision, 2=Habilitado, 3=Observado
                // (Toleramos 0 por compatibilidad de data antigua si existiera)
                $where[] = '(
                    u.estado = 1
                    AND (ur.estado_revision IS NULL OR ur.estado_revision IN (0,1))
                )';
                break;

            case 'habilitado':
                $where[] = 'u.estado = 2';
                break;

            case 'inactivo':
                // ✅ CLAVE (evita doble aparición):
                // Si está OBSERVADO (estado_revision=3), no entra en "Inactivos".
                $where[] = '(u.estado = 0 AND (ur.estado_revision IS NULL OR ur.estado_revision <> 3))';
                break;

            case 'todos':
            default:
                break;
        }

        // =========================
        // FILTRO BUSQUEDA
        // =========================
        if ($q !== '') {
            $where[] = "(u.nombre LIKE :q OR u.email LIKE :q OR u.documento LIKE :q)";
            $params[':q'] = "%{$q}%";
        }

        // =========================
        // FILTRO CONJUNTO
        // =========================
        if ($conjunto === 'condominio') {
            $where[] = "(LOWER(COALESCE(r.tipo_conjunto,'')) LIKE '%cond%')";
            if ($conjuntoId > 0) {
                $where[] = "r.codigo_condominio = :conjunto_id";
                $params[':conjunto_id'] = $conjuntoId;
            }
        } elseif ($conjunto === 'urbanizacion') {
            $where[] = "(LOWER(COALESCE(r.tipo_conjunto,'')) LIKE '%urban%')";
            if ($conjuntoId > 0) {
                $where[] = "r.codigo_urbanizacion = :conjunto_id";
                $params[':conjunto_id'] = $conjuntoId;
            }
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        // =========================
        // TOTAL
        // =========================
        $stTotal = $db->prepare("
            SELECT COUNT(*)
            FROM usuario u
            {$joinRevision}
            {$joinResidencia}
            {$whereSql}
        ");
        $stTotal->execute($params);
        $total = (int)$stTotal->fetchColumn();

        // =========================
        // ITEMS
        // =========================
        $st = $db->prepare("
            SELECT
                u.codigo_usuario,
                u.nombre,
                u.email,
                u.documento,
                u.telefono,
                u.estado AS usuario_estado,

                ur.estado_revision,
                ur.mensaje_observacion,

                r.tipo_conjunto,
                r.direccion,

                COALESCE(
                    ur.comprobante_path,
                    r.comprobante_domicilio
                ) AS comprobante_domicilio

            FROM usuario u
            {$joinRevision}
            {$joinResidencia}
            {$whereSql}
            ORDER BY
                CASE
                    WHEN ur.estado_revision = 3 THEN 3
                    WHEN ur.estado_revision = 1 THEN 2
                    ELSE 1
                END DESC,
                u.codigo_usuario DESC
            LIMIT :limit OFFSET :offset
        ");

        foreach ($params as $k => $v) {
            $st->bindValue($k, $v);
        }
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st->execute();

        return [
            'items' => $st->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'total' => $total
        ];
    }

    public function actualizarEstadoUsuario(array $p): bool
    {
        $id     = (int)($p['codigo_usuario'] ?? 0);
        $estado = (int)($p['estado'] ?? -1);

        if ($id <= 0 || !in_array($estado, [0, 1, 2], true)) {
            return false;
        }

        $db = $this->getDblink();
        $st = $db->prepare("
            UPDATE usuario
            SET estado = :e
            WHERE codigo_usuario = :id
        ");
        $st->execute([
            ':e'  => $estado,
            ':id' => $id
        ]);

        return $st->rowCount() > 0;
    }

    /**
     * ✅ OPCIÓN A (RAÍZ):
     * Si el usuario estaba OBSERVADO (estado_revision=3) y lo INACTIVAS,
     * aquí lo desmarcamos para que no quede en doble estado.
     *
     * No borramos el registro (evita impactos), solo cambiamos 3 -> 1 (En revisión).
     */
    public function quitarObservado(int $codigoUsuario): bool
    {
        $codigoUsuario = (int)$codigoUsuario;
        if ($codigoUsuario <= 0) return false;

        $db = $this->getDblink();
        if (!$db) return false;

        try {
            $st = $db->prepare("
                UPDATE usuario_revision
                SET estado_revision = 1
                WHERE codigo_usuario = :id AND estado_revision = 3
            ");
            $st->execute([':id' => $codigoUsuario]);
            return true;
        } catch (Throwable $e) {
            error_log('[EV][SoporteUsuarios::quitarObservado] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ Guarda observación en usuario_revision SIN marcar observado.
     * Para el caso: "Desactivar" con mensaje.
     *
     * - Si existe registro: actualiza mensaje_observacion (no toca estado_revision).
     * - Si no existe: inserta con estado_revision=1 (En revisión) respetando tu DDL.
     */
    public function guardarObservacionRevision(int $codigoUsuario, string $observacion): bool
    {
        $codigoUsuario = (int)$codigoUsuario;
        $observacion = trim($observacion);

        if ($codigoUsuario <= 0 || $observacion === '') {
            return false;
        }

        $db = $this->getDblink();
        if (!$db) return false;

        try {
            $db->beginTransaction();

            $stExists = $db->prepare("SELECT 1 FROM usuario_revision WHERE codigo_usuario = :id LIMIT 1");
            $stExists->execute([':id' => $codigoUsuario]);
            $exists = (bool)$stExists->fetchColumn();

            if ($exists) {
                $stUp = $db->prepare("
                    UPDATE usuario_revision
                    SET mensaje_observacion = :obs
                    WHERE codigo_usuario = :id
                ");
                $stUp->execute([
                    ':obs' => $observacion,
                    ':id'  => $codigoUsuario
                ]);
            } else {
                $stIns = $db->prepare("
                    INSERT INTO usuario_revision (codigo_usuario, estado_revision, mensaje_observacion)
                    VALUES (:id, 1, :obs)
                ");
                $stIns->execute([
                    ':id'  => $codigoUsuario,
                    ':obs' => $observacion
                ]);
            }

            $db->commit();
            return true;
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('[EV][SoporteUsuarios::guardarObservacionRevision] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ Limpia revisión/observación al aprobar:
     * deja estado_revision=2 (Habilitado) y mensaje_observacion NULL.
     */
    public function limpiarRevision(int $codigoUsuario): bool
    {
        $codigoUsuario = (int)$codigoUsuario;
        if ($codigoUsuario <= 0) return false;

        $db = $this->getDblink();
        if (!$db) return false;

        try {
            $st = $db->prepare("
                UPDATE usuario_revision
                SET estado_revision = 2,
                    mensaje_observacion = NULL
                WHERE codigo_usuario = :id
            ");
            $st->execute([':id' => $codigoUsuario]);
            return true;
        } catch (Throwable $e) {
            error_log('[EV][SoporteUsuarios::limpiarRevision] ' . $e->getMessage());
            return false;
        }
    }
}