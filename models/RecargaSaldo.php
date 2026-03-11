<?php
// models/RecargaSaldo.php

require_once __DIR__ . '/../Config/EnvConfig.php';
require_once __DIR__ . '/../database/Conexion.php';

class RecargaSaldo extends Conexion
{
    public function existeOperacion(string $metodo, string $idOperacion): bool
    {
        $sql = "SELECT 1 FROM recarga_saldo WHERE metodo = :metodo AND id_operacion = :id_operacion LIMIT 1";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':metodo', $metodo, PDO::PARAM_STR);
        $stmt->bindParam(':id_operacion', $idOperacion, PDO::PARAM_STR);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    public function existeOperacionGlobal(string $metodo, string $idOperacion): bool
    {
        $sql = "SELECT 1
                FROM recarga_saldo
                WHERE metodo = :metodo
                  AND id_operacion = :id_operacion
                LIMIT 1";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':metodo', $metodo, PDO::PARAM_STR);
        $stmt->bindParam(':id_operacion', $idOperacion, PDO::PARAM_STR);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    public function existeOperacionParaUsuario(int $codigoUsuario, string $metodo, string $idOperacion): bool
    {
        $sql = "SELECT 1
                FROM recarga_saldo
                WHERE codigo_usuario = :codigo_usuario
                  AND metodo = :metodo
                  AND id_operacion = :id_operacion
                LIMIT 1";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmt->bindParam(':metodo', $metodo, PDO::PARAM_STR);
        $stmt->bindParam(':id_operacion', $idOperacion, PDO::PARAM_STR);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    public function existeOperacionEnOtroRegistro(int $codigoRecarga, string $metodo, string $idOperacion): bool
    {
        $sql = "SELECT 1
                FROM recarga_saldo
                WHERE metodo = :metodo
                  AND id_operacion = :id_operacion
                  AND codigo_recarga <> :codigo_recarga
                LIMIT 1";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':metodo', $metodo, PDO::PARAM_STR);
        $stmt->bindParam(':id_operacion', $idOperacion, PDO::PARAM_STR);
        $stmt->bindParam(':codigo_recarga', $codigoRecarga, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    public function registrarRecarga(
        int $codigoUsuario,
        float $monto,
        string $metodo,
        string $idOperacion,
        ?string $comprobantePath
    ): int {
        $sql = "
            INSERT INTO recarga_saldo
            (
                codigo_usuario,
                monto,
                metodo,
                id_operacion,
                comprobante_path,
                estado,
                comentario_soporte,
                codigo_soporte,
                fecha_revision,
                reenviada_usuario,
                fecha_creacion
            )
            VALUES
            (
                :codigo_usuario,
                :monto,
                :metodo,
                :id_operacion,
                :comprobante_path,
                'pendiente',
                NULL,
                NULL,
                NULL,
                0,
                NOW()
            )
        ";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmt->bindParam(':monto', $monto);
        $stmt->bindParam(':metodo', $metodo, PDO::PARAM_STR);
        $stmt->bindParam(':id_operacion', $idOperacion, PDO::PARAM_STR);
        $stmt->bindParam(':comprobante_path', $comprobantePath, PDO::PARAM_STR);
        $stmt->execute();

        return (int)$this->dblink->lastInsertId();
    }

    public function registrarSolicitud(
        int $codigoUsuario,
        float $monto,
        string $tipo,
        string $idOperacion,
        ?string $rutaComprobante
    ): int {
        return $this->registrarRecarga($codigoUsuario, $monto, $tipo, $idOperacion, $rutaComprobante);
    }

    public function listarSoporte(array $filtros): array
    {
        $estado = $filtros['estado'] ?? 'pendiente';
        $rango  = $filtros['rango'] ?? '7';
        $q      = trim($filtros['q'] ?? '');
        $page   = max(1, (int)($filtros['page'] ?? 1));
        $size   = min(50, max(5, (int)($filtros['size'] ?? 10)));
        $offset = ($page - 1) * $size;

        $where = " WHERE r.estado = :estado ";
        $params = [':estado' => $estado];

        if ($rango === 'hoy') {
            $where .= " AND DATE(r.fecha_creacion) = CURDATE() ";
        } else {
            $dias = (int)$rango;
            if ($dias > 0) {
                $where .= " AND r.fecha_creacion >= (NOW() - INTERVAL {$dias} DAY) ";
            }
        }

        if ($q !== '') {
            $where .= " AND (
                u.nombre LIKE :q OR
                u.documento LIKE :q OR
                r.id_operacion LIKE :q
            ) ";
            $params[':q'] = "%{$q}%";
        }

        $sqlCount = "
            SELECT COUNT(*) total
            FROM recarga_saldo r
            INNER JOIN usuario u ON u.codigo_usuario = r.codigo_usuario
            {$where}
        ";
        $stmt = $this->dblink->prepare($sqlCount);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        $total = (int)$stmt->fetchColumn();

        $sql = "
            SELECT
                r.codigo_recarga AS id,
                DATE_FORMAT(r.fecha_creacion, '%d/%m/%Y') AS fecha,
                DATE_FORMAT(r.fecha_creacion, '%h:%i %p') AS hora,
                u.nombre AS usuario_nombre,
                u.documento AS dni,
                r.monto,
                r.metodo,
                r.id_operacion,
                r.estado,
                r.comprobante_path,
                r.reenviada_usuario,

                COALESCE(t.nombre_torre, '—') AS torre,
                COALESCE(d.numero_departamento, '—') AS departamento,
                COALESCE(c.nombre_condominio, '—') AS condominio

            FROM recarga_saldo r
            INNER JOIN usuario u ON u.codigo_usuario = r.codigo_usuario

            LEFT JOIN usuario_departamento ud
                ON ud.codigo_usuario = u.codigo_usuario
               AND ud.fecha_fin IS NULL

            LEFT JOIN departamento d ON d.codigo_departamento = ud.codigo_departamento
            LEFT JOIN torre t ON t.codigo_torre = d.codigo_torre
            LEFT JOIN condominio c ON c.codigo_condominio = t.codigo_condominio

            {$where}
            ORDER BY r.fecha_creacion DESC, r.codigo_recarga DESC
            LIMIT :lim OFFSET :off
        ";
        $stmt2 = $this->dblink->prepare($sql);
        foreach ($params as $k => $v) $stmt2->bindValue($k, $v);
        $stmt2->bindValue(':lim', $size, PDO::PARAM_INT);
        $stmt2->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt2->execute();
        $items = $stmt2->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $sqlPend = "SELECT COUNT(*) FROM recarga_saldo WHERE estado='pendiente'";
        $pendientes = (int)$this->dblink->query($sqlPend)->fetchColumn();

        return [
            'total' => $total,
            'page' => $page,
            'size' => $size,
            'pendientes' => $pendientes,
            'items' => $items
        ];
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT * FROM recarga_saldo WHERE codigo_recarga = :id LIMIT 1";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function obtenerRecargaUsuarioPorId(int $codigoRecarga, int $codigoUsuario): ?array
    {
        $sql = "
            SELECT *
            FROM recarga_saldo
            WHERE codigo_recarga = :codigo_recarga
              AND codigo_usuario = :codigo_usuario
            LIMIT 1
        ";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':codigo_recarga', $codigoRecarga, PDO::PARAM_INT);
        $stmt->bindParam(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function actualizarEstado(
        int $codigoRecarga,
        string $estado,
        ?string $comentario,
        int $codigoSoporte
    ): bool {
        $sql = "
            UPDATE recarga_saldo
            SET estado = :estado,
                comentario_soporte = :comentario,
                codigo_soporte = :codigo_soporte,
                fecha_revision = NOW()
            WHERE codigo_recarga = :codigo_recarga
            LIMIT 1
        ";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
        $stmt->bindParam(':comentario', $comentario, PDO::PARAM_STR);
        $stmt->bindParam(':codigo_soporte', $codigoSoporte, PDO::PARAM_INT);
        $stmt->bindParam(':codigo_recarga', $codigoRecarga, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function subsanarRecargaObservada(
        int $codigoRecarga,
        int $codigoUsuario,
        float $monto,
        string $metodo,
        string $idOperacion,
        ?string $comprobantePath
    ): bool {
        $sql = "
            UPDATE recarga_saldo
            SET
                monto = :monto,
                metodo = :metodo,
                id_operacion = :id_operacion,
                comprobante_path = :comprobante_path,
                estado = 'pendiente',
                comentario_soporte = NULL,
                codigo_soporte = NULL,
                fecha_revision = NULL,
                reenviada_usuario = 1
            WHERE codigo_recarga = :codigo_recarga
              AND codigo_usuario = :codigo_usuario
              AND estado = 'observada'
            LIMIT 1
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':monto', $monto);
        $stmt->bindParam(':metodo', $metodo, PDO::PARAM_STR);
        $stmt->bindParam(':id_operacion', $idOperacion, PDO::PARAM_STR);
        $stmt->bindParam(':comprobante_path', $comprobantePath, PDO::PARAM_STR);
        $stmt->bindParam(':codigo_recarga', $codigoRecarga, PDO::PARAM_INT);
        $stmt->bindParam(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function listarMisRecargas(int $codigoUsuario, int $limit = 20): array
    {
        $limit = max(1, min(50, $limit));

        $sql = "
            SELECT
                r.codigo_recarga AS id,
                DATE_FORMAT(r.fecha_creacion, '%d/%m/%Y') AS fecha,
                DATE_FORMAT(r.fecha_creacion, '%h:%i %p') AS hora,
                r.monto,
                r.metodo,
                r.id_operacion,
                r.estado,
                r.comentario_soporte,
                r.comprobante_path,
                r.reenviada_usuario
            FROM recarga_saldo r
            WHERE r.codigo_usuario = :u
            ORDER BY r.fecha_creacion DESC, r.codigo_recarga DESC
            LIMIT :lim
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();

        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}