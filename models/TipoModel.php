<?php
require_once __DIR__ . '/../database/Conexion.php';

class TipoModel extends Conexion {

    public function listarTipo() {
        $sql = "SELECT codigo_tipo, nombre
                FROM tipo
                WHERE estado = '1'";
        $stmt = $this->dblink->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarCategoria_grupo($tipoId) {
        $sql = "SELECT 
                    g.codigo_grupo,
                    g.nombre AS grupo,
                    g.orden  AS orden_grupo,
                    c.codigo_categoria,
                    c.nombre AS categoria,
                    c.orden  AS orden_categoria
                FROM 
                    categoria_grupo g JOIN categoria c
                ON 
                    c.codigo_grupo = g.codigo_grupo AND c.estado = 1
                WHERE 
                    g.codigo_tipo = :tipo AND g.estado = 1
                ORDER BY 
                    g.orden, 
                    g.nombre, 
                    c.orden, 
                    c.nombre";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':tipo', $tipoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
