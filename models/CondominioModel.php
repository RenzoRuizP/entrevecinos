<?php
// models/CondominioModel.php
require_once __DIR__ . '/../database/Conexion.php';

class CondominioModel extends Conexion
{
    public function listarCondominios(): array
    {
        // ✅ Incluye direccion_condominio para autocompletar en Datos Personales
        $sql = "SELECT codigo_condominio, nombre_condominio, direccion_condominio
                FROM condominio
                WHERE estado = 'A'
                ORDER BY nombre_condominio ASC";
        $stmt = $this->dblink->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listarTorres($condominioId): array
    {
        // legacy (si aún se usa en otro módulo)
        $sql = "SELECT codigo_torre, nombre_torre
                FROM torre
                WHERE codigo_condominio = :id
                ORDER BY nombre_torre ASC";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':id', $condominioId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listarDepartamentos($torreId): array
    {
        // legacy (si aún se usa en otro módulo)
        $sql = "SELECT codigo_departamento, numero_departamento
                FROM departamento
                WHERE codigo_torre = :id
                ORDER BY numero_departamento ASC";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':id', $torreId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listarPorDistrito(int $codigoDistrito): array
    {
        $sql = "SELECT codigo_condominio, nombre_condominio, direccion_condominio
                FROM condominio
                WHERE estado = 'A' AND codigo_distrito = :dist
                ORDER BY nombre_condominio ASC";
        $st = $this->dblink->prepare($sql);
        $st->bindParam(':dist', $codigoDistrito, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * ✅ NUEVO: Obtiene la dirección del condominio por ID.
     * Usado para Opción A (dirección desde BD; no confiar en POST si el input está disabled).
     */
    public function obtenerDireccionPorId(int $codigoCondominio): string
    {
        if ($codigoCondominio <= 0) return '';

        $sql = "SELECT direccion_condominio
                FROM condominio
                WHERE codigo_condominio = :id
                LIMIT 1";
        $st = $this->dblink->prepare($sql);
        $st->bindParam(':id', $codigoCondominio, PDO::PARAM_INT);
        $st->execute();

        $dir = $st->fetchColumn();
        return is_string($dir) ? trim($dir) : '';
    }
}
