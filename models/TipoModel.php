<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

class TipoModel extends Conexion
{
    private function normalizarTipoPublicacion(?string $tipoPublicacion): ?string
    {
        $valor = strtolower(trim((string)$tipoPublicacion));

        return in_array($valor, ['producto', 'servicio'], true)
            ? $valor
            : null;
    }

    /**
     * Devuelve el tipo principal de la publicación.
     *
     * Regla funcional EV:
     * - Producto => tipo "Producto"
     * - Servicio => tipo "Servicio"
     *
     * El formulario no permite que el usuario cambie este campo; solo se
     * completa automáticamente según lo elegido en el Paso 1.
     */
    public function listarTipo(?string $tipoPublicacion = null): array
    {
        $tipoPublicacion = $this->normalizarTipoPublicacion($tipoPublicacion);

        $sql = "
            SELECT
                t.codigo_tipo,
                t.nombre,
                t.estado
            FROM tipo t
            WHERE t.estado = 1
        ";

        $params = [];

        if ($tipoPublicacion !== null) {
            $sql .= " AND LOWER(TRIM(t.nombre)) = :tipo_publicacion ";
            $params[':tipo_publicacion'] = $tipoPublicacion;
        }

        $sql .= " ORDER BY t.codigo_tipo ASC ";

        $stmt = $this->dblink->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Devuelve las categorías del tipo seleccionado.
     *
     * La categoría sí es editable. El agrupador visual corresponde a
     * categoria_grupo (por ejemplo: "Eventos y catering"), no al modo
     * principal Producto/Servicio.
     */
    public function listarCategoria_grupo(int $tipoId, ?string $tipoPublicacion = null): array
    {
        $tipoPublicacion = $this->normalizarTipoPublicacion($tipoPublicacion);

        $sql = "
            SELECT
                c.codigo_categoria,
                c.codigo_grupo,
                c.codigo_tipo,
                c.nombre,
                c.nombre AS categoria,
                c.orden,
                c.estado,
                COALESCE(cg.nombre, 'Categorías') AS grupo
            FROM categoria c
            INNER JOIN tipo t
                ON t.codigo_tipo = c.codigo_tipo
               AND t.estado = 1
            LEFT JOIN categoria_grupo cg
                ON cg.codigo_grupo = c.codigo_grupo
               AND cg.codigo_tipo = c.codigo_tipo
               AND cg.estado = 1
            WHERE c.codigo_tipo = :tipo_id
              AND c.estado = 1
        ";

        $params = [
            ':tipo_id' => $tipoId,
        ];

        /*
         * Seguridad adicional: si la ruta solicita modo producto o servicio,
         * el tipo recibido en la URL debe corresponder al nombre del modo.
         */
        if ($tipoPublicacion !== null) {
            $sql .= " AND LOWER(TRIM(t.nombre)) = :tipo_publicacion ";
            $params[':tipo_publicacion'] = $tipoPublicacion;
        }

        $sql .= " ORDER BY COALESCE(cg.orden, 999), cg.nombre, c.orden ASC, c.nombre ASC ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':tipo_id', $params[':tipo_id'], PDO::PARAM_INT);

        if (isset($params[':tipo_publicacion'])) {
            $stmt->bindValue(':tipo_publicacion', $params[':tipo_publicacion'], PDO::PARAM_STR);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
