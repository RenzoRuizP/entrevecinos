<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

class TipoModel extends Conexion
{
    private function codigoGrupoDesdeTipoPublicacion(?string $tipoPublicacion): ?int
    {
        $tipoPublicacion = strtolower(trim((string)$tipoPublicacion));

        return match ($tipoPublicacion) {
            'producto' => 1,
            'servicio' => 2,
            default => null,
        };
    }

    /**
     * Lista tipos activos.
     *
     * Si $tipoPublicacion es producto|servicio, solo devuelve tipos que tengan
     * al menos una categoría activa en el grupo correspondiente:
     *   producto => categoria.codigo_grupo = 1
     *   servicio => categoria.codigo_grupo = 2
     */
    public function listarTipo(?string $tipoPublicacion = null): array
    {
        $codigoGrupo = $this->codigoGrupoDesdeTipoPublicacion($tipoPublicacion);

        if ($codigoGrupo !== null) {
            $sql = "
                SELECT DISTINCT
                    t.codigo_tipo,
                    t.codigo_categoria_grupo,
                    t.nombre,
                    t.descripcion,
                    t.estado
                FROM tipo t
                INNER JOIN categoria c ON c.codigo_tipo = t.codigo_tipo
                WHERE t.estado = 1
                  AND c.estado = 1
                  AND c.codigo_grupo = :codigo_grupo
                ORDER BY t.nombre
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindValue(':codigo_grupo', $codigoGrupo, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        $sql = "
            SELECT
                codigo_tipo,
                codigo_categoria_grupo,
                nombre,
                descripcion,
                estado
            FROM tipo
            WHERE estado = 1
            ORDER BY nombre
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Lista categorías activas por tipo.
     *
     * Si $tipoPublicacion es producto|servicio, filtra por:
     *   producto => categoria.codigo_grupo = 1
     *   servicio => categoria.codigo_grupo = 2
     */
    public function listarCategoria_grupo(int $tipoId, ?string $tipoPublicacion = null): array
    {
        $codigoGrupo = $this->codigoGrupoDesdeTipoPublicacion($tipoPublicacion);

        $whereGrupo = '';
        if ($codigoGrupo !== null) {
            $whereGrupo = ' AND c.codigo_grupo = :codigo_grupo ';
        }

        $sql = "
            SELECT
                c.codigo_categoria,
                c.codigo_grupo,
                c.codigo_tipo,
                c.nombre,
                c.nombre AS categoria,
                c.descripcion,
                c.orden,
                c.estado,
                COALESCE(c.requiere_preparacion_default, 0) AS requiere_preparacion_default,
                CASE
                    WHEN c.codigo_grupo = 1 THEN 'Productos'
                    WHEN c.codigo_grupo = 2 THEN 'Servicios'
                    ELSE 'Categorías'
                END AS grupo
            FROM categoria c
            WHERE c.codigo_tipo = :tipo_id
              AND c.estado = 1
              {$whereGrupo}
            ORDER BY c.orden ASC, c.nombre ASC
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':tipo_id', $tipoId, PDO::PARAM_INT);
        if ($codigoGrupo !== null) {
            $stmt->bindValue(':codigo_grupo', $codigoGrupo, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
