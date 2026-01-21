<?php
// models/SoporteDashboard.php
declare(strict_types=1);

//require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../database/Conexion.php';



final class SoporteDashboard extends Conexion
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Devuelve resumen dashboard:
     * - KPIs (cuentas/publicaciones/recargas)
     * - Atender ahora (por ahora: cuentas en revisión; se puede ampliar luego)
     */
    public function resumen(string $tiempo = 'hoy', int $limit = 10): array
    {
        $limit = ($limit <= 0) ? 10 : min($limit, 50);

        [$desde, $metaTiempo] = $this->calcularDesde($tiempo);

        $kpis = [
            'cuentas' => $this->kpiCuentas($desde),
            'publicaciones' => $this->kpiPublicaciones($desde),
            'recargas' => $this->kpiRecargas($desde),
        ];

        // “Atender ahora”: priorizamos cuentas en revisión (usuario.estado = 1)
        $atender = $this->atenderCuentasEnRevision($desde, $limit);

        return [
            'kpis' => $kpis,
            'atender' => $atender,
            'meta' => [
                'tiempo' => $metaTiempo,
                'desde'  => $desde->format('Y-m-d H:i:s'),
                'limit'  => $limit
            ]
        ];
    }

    // ------------------------------------------------------------
    // KPIs
    // ------------------------------------------------------------

    private function kpiCuentas(\DateTimeImmutable $desde): array
    {
        // En tu tabla usuario:
        // 0=Inactivo, 1=En revisión, 2=Habilitado
        $pdo = $this->dblink;

        // Pendientes = estado 1 (en revisión)
        $pend = (int)$pdo->query("SELECT COUNT(*) FROM usuario WHERE estado = 1")->fetchColumn();

        // Aprobadas hoy = estado 2 y actualizadas hoy
        $stmtA = $pdo->prepare("
            SELECT COUNT(*)
            FROM usuario
            WHERE estado = 2
              AND DATE(fecha_actualizacion) = CURDATE()
        ");
        $stmtA->execute();
        $aprobHoy = (int)$stmtA->fetchColumn();

        // Rechazadas/Inactivas = estado 0
        $rech = (int)$pdo->query("SELECT COUNT(*) FROM usuario WHERE estado = 0")->fetchColumn();

        return [
            'pendientes'     => $pend,
            'aprobadas_hoy'  => $aprobHoy,
            'rechazadas'     => $rech,
        ];
    }

    private function kpiRecargas(\DateTimeImmutable $desde): array
    {
        $pdo = $this->dblink;

        $pend = (int)$pdo->query("SELECT COUNT(*) FROM recarga_saldo WHERE estado = 'pendiente'")->fetchColumn();
        $obs  = (int)$pdo->query("SELECT COUNT(*) FROM recarga_saldo WHERE estado = 'observada'")->fetchColumn();

        $stmtV = $pdo->prepare("
            SELECT COUNT(*)
            FROM recarga_saldo
            WHERE estado = 'aprobada'
              AND DATE(fecha_revision) = CURDATE()
        ");
        $stmtV->execute();
        $valHoy = (int)$stmtV->fetchColumn();

        return [
            'pendientes'     => $pend,
            'validadas_hoy'  => $valHoy,
            'observadas'     => $obs,
        ];
    }

    private function kpiPublicaciones(\DateTimeImmutable $desde): array
    {
        // Como no me pasaste tu lógica final de moderación de productos,
        // dejo algo conservador y NO rompe:
        // - en_revision: producto.visible = 0 (borrador/no publicado)
        // - reportadas/suspendidas: 0 por ahora (ajustable cuando tengas campos)
        $pdo = $this->dblink;

        $enRev = (int)$pdo->query("SELECT COUNT(*) FROM producto WHERE visible = 0")->fetchColumn();

        return [
            'en_revision' => $enRev,
            'reportadas'  => 0,
            'suspendidas' => 0,
        ];
    }

    // ------------------------------------------------------------
    // ATENDER AHORA
    // ------------------------------------------------------------

    private function atenderCuentasEnRevision(\DateTimeImmutable $desde, int $limit): array
    {
        $pdo = $this->dblink;

        // Traemos usuarios estado=1 (en revisión) + residencia si existe
        // Nota: Si quieres basarlo en fecha_creacion >= desde, activas el filtro.
        $sql = "
            SELECT
              u.codigo_usuario,
              u.nombre,
              u.email,
              u.documento,
              u.telefono,
              u.estado,
              u.fecha_creacion,
              ur.tipo_conjunto,
              ur.codigo_condominio,
              ur.codigo_urbanizacion,
              ur.direccion,
              ur.comprobante_domicilio
            FROM usuario u
            LEFT JOIN usuario_residencia ur
                   ON ur.codigo_usuario = u.codigo_usuario
            WHERE u.estado = 1
            ORDER BY u.fecha_creacion DESC
            LIMIT :limit
        ";

        $st = $pdo->prepare($sql);
        $st->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $st->execute();

        $rows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Normalizamos al formato que el JS del dashboard puede renderizar
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'fecha'        => $this->fmtFecha($r['fecha_creacion'] ?? null),
                'tipo'         => 'Cuenta en revisión',
                'prioridad'    => 'Alta',

                'codigo_usuario' => (int)$r['codigo_usuario'],
                'nombre'       => (string)($r['nombre'] ?? ''),
                'email'        => (string)($r['email'] ?? ''),
                'documento'    => $r['documento'] !== null ? (string)$r['documento'] : '',
                'telefono'     => $r['telefono'] !== null ? (string)$r['telefono'] : '',
                'estado'       => (int)($r['estado'] ?? 1),

                'tipo_conjunto'      => $r['tipo_conjunto'] !== null ? (string)$r['tipo_conjunto'] : '',
                'codigo_condominio'  => $r['codigo_condominio'] !== null ? (int)$r['codigo_condominio'] : null,
                'codigo_urbanizacion'=> $r['codigo_urbanizacion'] !== null ? (int)$r['codigo_urbanizacion'] : null,
                'direccion'          => $r['direccion'] !== null ? (string)$r['direccion'] : '',
                'comprobante_domicilio' => $r['comprobante_domicilio'] !== null ? (string)$r['comprobante_domicilio'] : '',
            ];
        }

        return $out;
    }

    // ------------------------------------------------------------
    // Utils
    // ------------------------------------------------------------

    private function calcularDesde(string $tiempo): array
    {
        $t = strtolower(trim($tiempo));

        $now = new \DateTimeImmutable('now');
        if ($t === '7d') {
            return [$now->modify('-7 days')->setTime(0, 0, 0), '7d'];
        }
        if ($t === '30d') {
            return [$now->modify('-30 days')->setTime(0, 0, 0), '30d'];
        }

        // default hoy
        return [$now->setTime(0, 0, 0), 'hoy'];
    }

    private function fmtFecha($dt): string
    {
        if (!$dt) return '';
        try {
            $d = new \DateTimeImmutable((string)$dt);
            return $d->format('d/m H:i');
        } catch (\Throwable $e) {
            return (string)$dt;
        }
    }
}
