<?php
// models/UsuarioRevision.php
declare(strict_types=1);

require_once __DIR__ . '/Conexion.php';

final class UsuarioRevision extends Conexion
{
  /**
   * Retorna el registro de usuario_revision para un usuario.
   */
  public function obtenerPorUsuario(int $codigoUsuario): ?array
  {
    $sql = "SELECT *
            FROM usuario_revision
            WHERE codigo_usuario = :id
            LIMIT 1";
    $st = $this->dblink->prepare($sql);
    $st->bindValue(':id', $codigoUsuario, PDO::PARAM_INT);
    $st->execute();
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
  }

  /**
   * Guarda el reenvío del comprobante cuando estaba OBSERVADO:
   * - estado_revision pasa a 1 (en revisión)
   * - comprobante_path se actualiza
   * - fecha_reenvio se setea
   */
  public function registrarReenvio(int $codigoUsuario, string $comprobantePath): bool
  {
    $sql = "UPDATE usuario_revision
            SET estado_revision = 1,
                comprobante_path = :path,
                fecha_reenvio = NOW(),
                fecha_actualizacion = NOW()
            WHERE codigo_usuario = :id";
    $st = $this->dblink->prepare($sql);
    $st->bindValue(':path', $comprobantePath, PDO::PARAM_STR);
    $st->bindValue(':id', $codigoUsuario, PDO::PARAM_INT);
    $st->execute();
    return $st->rowCount() > 0;
  }

  public function estaObservado(int $codigoUsuario): bool
  {
    $row = $this->obtenerPorUsuario($codigoUsuario);
    return $row && (int)$row['estado_revision'] === 3;
  }
}
