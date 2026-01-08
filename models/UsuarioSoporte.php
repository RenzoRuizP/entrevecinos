<?php
// models/UsuarioSoporte.php
require_once __DIR__ . '/../database/Conexion.php';

class UsuarioSoporte extends Conexion
{
    public function listar(array $filtros): array
    {
        $estado = isset($filtros['estado']) && $filtros['estado'] !== '' ? (int)$filtros['estado'] : null;
        $tipo   = strtolower(trim((string)($filtros['tipo'] ?? '')));
        $codigo = (int)($filtros['codigo'] ?? 0);
        $q      = trim((string)($filtros['q'] ?? ''));
        $page   = max(1, (int)($filtros['page'] ?? 1));
        $size   = max(1, min(50, (int)($filtros['size'] ?? 10)));
        $off    = ($page - 1) * $size;

        $where = [];
        $params = [];

        // Estado: 1=en revisión, 2=habilitado, 0=inactivo
        if ($estado !== null && in_array($estado, [0,1,2], true)) {
            $where[] = "u.estado = :estado";
            $params[':estado'] = $estado;
        }

        // Tipo de conjunto
        if ($tipo === 'condominio' || $tipo === 'urbanizacion') {
            $where[] = "ur.tipo_conjunto = :tipo";
            $params[':tipo'] = $tipo;

            if ($codigo > 0) {
                if ($tipo === 'condominio') {
                    $where[] = "ur.codigo_condominio = :codigo";
                } else {
                    $where[] = "ur.codigo_urbanizacion = :codigo";
                }
                $params[':codigo'] = $codigo;
            }
        }

        // Búsqueda
        if ($q !== '') {
            $where[] = "(u.nombre LIKE :q OR u.email LIKE :q OR u.documento LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }

        $whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

        // Total
        $sqlTotal = "
            SELECT COUNT(*) AS total
            FROM usuario u
            INNER JOIN usuario_residencia ur ON ur.codigo_usuario = u.codigo_usuario
            LEFT JOIN condominio c ON c.codigo_condominio = ur.codigo_condominio
            LEFT JOIN urbanizacion ub ON ub.codigo_urbanizacion = ur.codigo_urbanizacion
            {$whereSql}
        ";

        $stT = $this->dblink->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stT->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stT->execute();
        $total = (int)($stT->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        // Data
        $sql = "
            SELECT
                u.codigo_usuario,
                u.nombre,
                u.email,
                u.documento,
                u.telefono,
                u.estado,

                ur.tipo_conjunto,
                ur.direccion AS direccion_residencia,
                ur.comprobante_domicilio,

                c.codigo_condominio,
                c.nombre_condominio,

                ub.codigo_urbanizacion,
                ub.nombre_urbanizacion

            FROM usuario u
            INNER JOIN usuario_residencia ur ON ur.codigo_usuario = u.codigo_usuario
            LEFT JOIN condominio c ON c.codigo_condominio = ur.codigo_condominio
            LEFT JOIN urbanizacion ub ON ub.codigo_urbanizacion = ur.codigo_urbanizacion
            {$whereSql}
            ORDER BY u.fecha_creacion DESC
            LIMIT :lim OFFSET :off
        ";

        $st = $this->dblink->prepare($sql);

        foreach ($params as $k => $v) {
            $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->bindValue(':lim', $size, PDO::PARAM_INT);
        $st->bindValue(':off', $off, PDO::PARAM_INT);

        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'ok' => true,
            'data' => $rows,
            'meta' => [
                'page' => $page,
                'size' => $size,
                'total' => $total
            ]
        ];
    }

    public function actualizarEstado(int $codigoUsuario, int $estado): bool
    {
        $sql = "UPDATE usuario SET estado = :estado WHERE codigo_usuario = :id LIMIT 1";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':estado', $estado, PDO::PARAM_INT);
        $st->bindValue(':id', $codigoUsuario, PDO::PARAM_INT);
        return $st->execute();
    }
}
