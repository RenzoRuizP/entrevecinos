<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

class Menu extends Conexion
{
    private ?int $codigoMenu = null;
    private string $nombre = '';

    public function getCodigoMenu(): ?int
    {
        return $this->codigoMenu;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function setCodigoMenu(int $codigoMenu): void
    {
        $this->codigoMenu = $codigoMenu;
    }

    public function setNombre(string $nombre): void
    {
        $this->nombre = trim($nombre);
    }

    /**
     * Lista todos los menús activos
     */
    public function listar(): array
    {
        try {
            $sql = "
                SELECT
                    codigo_menu,
                    nombre,
                    icono,
                    orden
                FROM menu
                WHERE estado = 1
                ORDER BY orden ASC
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Lista los items/submenús activos de un menú
     */
    public function listarItems(int $codigoMenu): array
    {
        try {
            $sql = "
                SELECT
                    codigo_menu_item,
                    codigo_menu,
                    nombre,
                    ruta,
                    icono,
                    orden
                FROM menu_item
                WHERE codigo_menu = :codigo_menu
                  AND estado = 1
                ORDER BY orden ASC
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindValue(':codigo_menu', $codigoMenu, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Lista menús con sus submenús
     */
    public function listarMenuConSubmenu(): array
    {
        try {
            $menus = $this->listar();

            foreach ($menus as &$menu) {
                $menu['submenus'] = $this->listarItems((int)$menu['codigo_menu']);
            }

            return $menus;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Lista menús permitidos para un rol
     */
    public function listarPorRol(int $codigoRol): array
    {
        try {
            $sql = "
                SELECT DISTINCT
                    m.codigo_menu,
                    m.nombre,
                    m.icono,
                    m.orden
                FROM rol_menu_item rmi
                INNER JOIN menu_item mi
                    ON mi.codigo_menu_item = rmi.codigo_menu_item
                INNER JOIN menu m
                    ON m.codigo_menu = mi.codigo_menu
                WHERE rmi.codigo_rol = :codigo_rol
                  AND m.estado = 1
                  AND mi.estado = 1
                ORDER BY m.orden ASC
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindValue(':codigo_rol', $codigoRol, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Lista items/submenús permitidos para un rol dentro de un menú
     */
    public function listarItemsPorRol(int $codigoRol, int $codigoMenu): array
    {
        try {
            $sql = "
                SELECT
                    mi.codigo_menu_item,
                    mi.codigo_menu,
                    mi.nombre,
                    mi.ruta,
                    mi.icono,
                    mi.orden
                FROM rol_menu_item rmi
                INNER JOIN menu_item mi
                    ON mi.codigo_menu_item = rmi.codigo_menu_item
                WHERE rmi.codigo_rol = :codigo_rol
                  AND mi.codigo_menu = :codigo_menu
                  AND mi.estado = 1
                ORDER BY mi.orden ASC
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindValue(':codigo_rol', $codigoRol, PDO::PARAM_INT);
            $stmt->bindValue(':codigo_menu', $codigoMenu, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Lista menú completo por rol
     */
    public function listarMenuConSubmenuPorRol(int $codigoRol): array
    {
        try {
            $menus = $this->listarPorRol($codigoRol);

            foreach ($menus as &$menu) {
                $menu['submenus'] = $this->listarItemsPorRol(
                    $codigoRol,
                    (int)$menu['codigo_menu']
                );
            }

            return $menus;
        } catch (Throwable $e) {
            throw $e;
        }
    }
}