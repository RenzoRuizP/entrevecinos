<?php
// models/DisponibilidadPedido.php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

class DisponibilidadPedido extends Conexion
{
    public function usuarioTieneProductosPublicados(int $codigoUsuario): bool
    {
        return $this->contarProductosPublicados($codigoUsuario) > 0;
    }

    public function contarProductosPublicados(int $codigoUsuario): int
    {
        if ($codigoUsuario <= 0) {
            return 0;
        }

        $sql = "
            SELECT COUNT(*) AS total
            FROM producto
            WHERE codigo_usuario = :u
              AND visible = 2
              AND estado_residencial_publicacion = 'activa'
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();

        return (int)$st->fetchColumn();
    }

    public function obtenerDisponibilidadActual(int $codigoUsuario): int
    {
        if ($codigoUsuario <= 0) {
            return 0;
        }

        $sql = "
            SELECT COALESCE(disponibilidad_pedidos, 0)
            FROM usuario
            WHERE codigo_usuario = :u
            LIMIT 1
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();

        $valor = $st->fetchColumn();

        return ($valor === false || $valor === null) ? 0 : (int)$valor;
    }

    public function actualizarDisponibilidad(int $codigoUsuario, int $disponible): bool
    {
        if ($codigoUsuario <= 0) {
            return false;
        }

        $disponible = ((int)$disponible === 1) ? 1 : 0;

        $sql = "
            UPDATE usuario
            SET disponibilidad_pedidos = :d,
                fecha_actualizacion = CURRENT_TIMESTAMP
            WHERE codigo_usuario = :u
            LIMIT 1
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':d', $disponible, PDO::PARAM_INT);
        $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);

        return $st->execute();
    }

    public function desconectarPorLogout(int $codigoUsuario): bool
    {
        return $this->actualizarDisponibilidad($codigoUsuario, 0);
    }

    public function desconectarPorLogin(int $codigoUsuario): bool
    {
        return $this->actualizarDisponibilidad($codigoUsuario, 0);
    }

    public function obtenerEstadoWidget(int $codigoUsuario): array
    {
        $productos = $this->contarProductosPublicados($codigoUsuario);
        $mostrar = $productos > 0;

        return [
            'mostrar_control'      => $mostrar,
            'productos_publicados' => $productos,
            'disponibilidad'       => $mostrar ? $this->obtenerDisponibilidadActual($codigoUsuario) : 0
        ];
    }
}