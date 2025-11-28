<?php
// models/Billetera.php
require_once __DIR__ . '/../database/Conexion.php';

class Billetera extends Conexion
{
    /**
     * Obtener billetera por código de usuario.
     * Si no existe, puede crearse con saldo 0 desde otro método.
     */
    public function obtenerPorUsuario(int $codigo_usuario): ?array
    {
        $sql = "SELECT 
                    b.codigo_billetera,
                    b.codigo_usuario,
                    b.saldo_actual,
                    b.estado,
                    b.fecha_creacion,
                    b.fecha_actualizacion
                FROM billetera b
                WHERE b.codigo_usuario = :codigo_usuario
                LIMIT 1";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':codigo_usuario', $codigo_usuario, PDO::PARAM_INT);
        $stmt->execute();

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ?: null;
    }

    /**
     * Crear una billetera nueva para el usuario (si no existiera).
     */
    private function crearNueva(int $codigo_usuario, float $saldoInicial = 0.00): array
    {
        $sql = "INSERT INTO billetera (codigo_usuario, saldo_actual, estado)
                VALUES (:codigo_usuario, :saldo_actual, 1)";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':codigo_usuario', $codigo_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':saldo_actual', $saldoInicial);
        $stmt->execute();

        $codigo_billetera = (int)$this->dblink->lastInsertId();

        return [
            'codigo_billetera' => $codigo_billetera,
            'codigo_usuario'   => $codigo_usuario,
            'saldo_actual'     => $saldoInicial,
            'estado'           => 1
        ];
    }

    /**
     * Obtener billetera o crearla si no existe.
     */
    public function obtenerOCrear(int $codigo_usuario): array
    {
        $billetera = $this->obtenerPorUsuario($codigo_usuario);
        if ($billetera) {
            return $billetera;
        }
        return $this->crearNueva($codigo_usuario, 0.00);
    }

    /**
     * Obtener saldo actual de un usuario (0.00 si no tiene billetera).
     */
    public function obtenerSaldo(int $codigo_usuario): float
    {
        $billetera = $this->obtenerPorUsuario($codigo_usuario);
        if (!$billetera) {
            return 0.00;
        }
        return (float)$billetera['saldo_actual'];
    }

    /**
     * Verificar si el usuario tiene saldo suficiente.
     */
    public function tieneSaldoSuficiente(int $codigo_usuario, float $monto): bool
    {
        $saldo = $this->obtenerSaldo($codigo_usuario);
        return $saldo >= $monto;
    }

    /**
     * Listar movimientos de billetera del usuario.
     *
     * Retorna un array de movimientos de la tabla billetera_movimiento
     * asociados al usuario (a través de billetera.codigo_billetera).
     */
    public function listarMovimientosPorUsuario(int $codigo_usuario, int $limite = 50): array
    {
        $sql = "SELECT 
                    m.codigo_billetera,
                    m.tipo_movimiento,
                    m.monto,
                    m.saldo_despues,
                    m.origen,
                    m.codigo_referencia,
                    m.descripcion
                FROM billetera b
                INNER JOIN billetera_movimiento m
                    ON m.codigo_billetera = b.codigo_billetera
                WHERE b.codigo_usuario = :codigo_usuario
                LIMIT :limite";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':codigo_usuario', $codigo_usuario, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows ?: [];
    }

    /**
     * Debitar S/ 1.00 (u otro monto) por destacar una publicación.
     * Registra movimiento en billetera_movimiento.
     *
     * Retorna:
     *  - ['ok' => true, 'saldo_actual' => x.yz]
     *  - ['ok' => false, 'codigo' => 'SALDO_INSUFICIENTE', 'mensaje' => '...']
     *  - ['ok' => false, 'codigo' => 'ERROR', 'mensaje' => '...']
     */
    public function debitarPorPublicacionDestacada(
        int $codigo_usuario,
        int $codigo_publicacion,
        float $monto = 1.00
    ): array {
        try {
            // Transacción para mantener consistencia
            $this->dblink->beginTransaction();

            // 1) Obtener o crear billetera
            $this->obtenerOCrear($codigo_usuario);

            // 2) Volvemos a leer la billetera pero con FOR UPDATE para bloquear la fila
            $sqlLock = "SELECT 
                            codigo_billetera,
                            saldo_actual
                        FROM billetera
                        WHERE codigo_usuario = :codigo_usuario
                        FOR UPDATE";
            $stmtLock = $this->dblink->prepare($sqlLock);
            $stmtLock->bindParam(':codigo_usuario', $codigo_usuario, PDO::PARAM_INT);
            $stmtLock->execute();
            $billetera = $stmtLock->fetch(PDO::FETCH_ASSOC);

            if (!$billetera) {
                // Algo falló al crear/leer la billetera
                $this->dblink->rollBack();
                return [
                    'ok'     => false,
                    'codigo' => 'ERROR',
                    'mensaje'=> 'No se pudo obtener la billetera del usuario.'
                ];
            }

            $codigo_billetera = (int)$billetera['codigo_billetera'];
            $saldo_actual     = (float)$billetera['saldo_actual'];

            // 3) Validar saldo suficiente
            if ($saldo_actual < $monto) {
                $this->dblink->rollBack();
                return [
                    'ok'     => false,
                    'codigo' => 'SALDO_INSUFICIENTE',
                    'mensaje'=> 'Tu billetera no tiene saldo suficiente para destacar la publicación.'
                ];
            }

            // 4) Calcular saldo resultante
            $saldo_nuevo = $saldo_actual - $monto;

            // 5) Actualizar saldo en billetera
            $sqlUpdate = "UPDATE billetera
                          SET saldo_actual = :saldo_nuevo,
                              fecha_actualizacion = NOW()
                          WHERE codigo_billetera = :codigo_billetera";
            $stmtUpdate = $this->dblink->prepare($sqlUpdate);
            $stmtUpdate->bindParam(':saldo_nuevo', $saldo_nuevo);
            $stmtUpdate->bindParam(':codigo_billetera', $codigo_billetera, PDO::PARAM_INT);
            $stmtUpdate->execute();

            // 6) Registrar movimiento
            $sqlMov = "INSERT INTO billetera_movimiento (
                            codigo_billetera,
                            tipo_movimiento,
                            monto,
                            saldo_despues,
                            origen,
                            codigo_referencia,
                            descripcion
                        )
                        VALUES (
                            :codigo_billetera,
                            'D',
                            :monto,
                            :saldo_despues,
                            :origen,
                            :codigo_referencia,
                            :descripcion
                        )";

            $origen      = 'PUBLICACION_DESTACADA';
            $descripcion = 'Cargo por destacar publicación en Recomendados.';

            $stmtMov = $this->dblink->prepare($sqlMov);
            $stmtMov->bindParam(':codigo_billetera', $codigo_billetera, PDO::PARAM_INT);
            $stmtMov->bindParam(':monto', $monto);
            $stmtMov->bindParam(':saldo_despues', $saldo_nuevo);
            $stmtMov->bindParam(':origen', $origen);
            $stmtMov->bindParam(':codigo_referencia', $codigo_publicacion, PDO::PARAM_INT);
            $stmtMov->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $stmtMov->execute();

            $this->dblink->commit();

            return [
                'ok'           => true,
                'saldo_actual' => $saldo_nuevo
            ];

        } catch (Exception $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            error_log('Error debitarPorPublicacionDestacada: ' . $e->getMessage());

            return [
                'ok'     => false,
                'codigo' => 'ERROR',
                'mensaje'=> 'Ocurrió un problema al procesar el cargo en tu billetera.'
            ];
        }
    }
}
