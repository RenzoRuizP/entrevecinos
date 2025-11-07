<?php
/*
    imprime un json: echo json_encode($usuario);            
    compara e imprime, si sale false no son iguales: var_dump(password_verify($this->clave, $usuario['clave'])); // ¿true o false?
    encripta: $nuevoHash = password_hash('123456', PASSWORD_BCRYPT);
    echo $nuevoHash; 

*/
require_once __DIR__ . '/../Config/EnvConfig.php';
require_once __DIR__ . '/../Config/JwtConfig.php';
require_once __DIR__ . '/../database/Conexion.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class Publicacion extends Conexion {

    private $titulo;
    private $imagen_portada;
    private $descripcion;
    private $precio;

    public function setTitulo($titulo) {
        $this->titulo = $titulo;
    }

    public function setImagen_portada($imagen_portada) {
        $this->imagen_portada = $imagen_portada;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function setPrecio($precio) {
        $this->precio = $precio;
    }

    public function registrarPublicacion() {
        try {
            $sql = "
                SELECT 
                    distinct m.codigo_menu, 
                    m.nombre,
                    m.icono
                FROM 
                    rol r INNER JOIN menu_item_accesos m_i_a
                ON 
                    r.codigo_rol = m_i_a.codigo_rol INNER JOIN menu_item m_i
                ON
                    m_i.codigo_menu_item = m_i_a.codigo_menu_item INNER JOIN menu m
                ON
                    m.codigo_menu = m_i.codigo_menu
                WHERE 
                    r.nombre like :p_nombre_rol;
                ";
            $sentencia = $this->dblink->prepare($sql);
            $sentencia->bindParam(":p_nombre_rol", $nombre_rol);
            $sentencia->execute();
            $resultado = $sentencia->fetchAll(PDO::FETCH_ASSOC);
            return $resultado;
        } catch (Exception $exc) {
            throw $exc;
        }
    }




}
