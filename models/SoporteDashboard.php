<?php
// models/SoporteDashboard.php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class SoporteDashboard extends Conexion
{
    private array $tableCache = [];
    private array $columnCache = [];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Devuelve resumen dashboard soporte:
     * - KPIs: cuentas, publicaciones, recargas e incidencias de servicios
     * - Atender ahora: mezcla cuentas + publicaciones + recargas + servicios
     */
    public function resumen(string $tiempo = 'hoy', int $limit = 10): array
    {
        $limit = ($limit <= 0) ? 10 : min($limit, 50);

        [$desde, $metaTiempo] = $this->calcularDesde($tiempo);

        $kpis = [
            'cuentas'       => $this->kpiCuentas($desde),
            'publicaciones' => $this->kpiPublicaciones($desde),
            'recargas'      => $this->kpiRecargas($desde),
            'servicios'     => $this->kpiServicios($desde),
        ];

        $atender = $this->atenderAhora($desde, $limit);

        return [
            'kpis'    => $kpis,
            'atender' => $atender,
            'meta'    => [
                'tiempo' => $metaTiempo,
                'desde'  => $desde->format('Y-m-d H:i:s'),
                'limit'  => $limit,
            ],
        ];
    }

    // ------------------------------------------------------------
    // KPIs
    // ------------------------------------------------------------

    private function kpiCuentas(\DateTimeImmutable $desde): array
    {
        $table = 'usuario';

        if (!$this->tableExists($table)) {
            return [
                'pendientes'    => 0,
                'aprobadas_hoy' => 0,
                'rechazadas'    => 0,
            ];
        }

        $estadoCol = $this->firstColumn($table, ['estado']);

        if (!$estadoCol) {
            return [
                'pendientes'    => 0,
                'aprobadas_hoy' => 0,
                'rechazadas'    => 0,
            ];
        }

        $pendientes = $this->countSql("
            SELECT COUNT(*)
            FROM {$this->qt($table)}
            WHERE {$this->qc($estadoCol)} = 1
        ");

        $rechazadas = $this->countSql("
            SELECT COUNT(*)
            FROM {$this->qt($table)}
            WHERE {$this->qc($estadoCol)} = 0
        ");

        $fechaActualizacionCol = $this->firstColumn($table, [
            'fecha_actualizacion',
            'updated_at',
            'fecha_revision',
            'fecha_modificacion',
        ]);

        $aprobadasHoy = 0;

        if ($fechaActualizacionCol) {
            $aprobadasHoy = $this->countSql("
                SELECT COUNT(*)
                FROM {$this->qt($table)}
                WHERE {$this->qc($estadoCol)} = 2
                  AND DATE({$this->qc($fechaActualizacionCol)}) = CURDATE()
            ");
        }

        return [
            'pendientes'    => $pendientes,
            'aprobadas_hoy' => $aprobadasHoy,
            'rechazadas'    => $rechazadas,
        ];
    }

    private function kpiRecargas(\DateTimeImmutable $desde): array
    {
        $table = 'recarga_saldo';

        if (!$this->tableExists($table)) {
            return [
                'pendientes'    => 0,
                'validadas_hoy' => 0,
                'observadas'    => 0,
            ];
        }

        $estadoCol = $this->firstColumn($table, ['estado', 'estado_recarga']);

        if (!$estadoCol) {
            return [
                'pendientes'    => 0,
                'validadas_hoy' => 0,
                'observadas'    => 0,
            ];
        }

        $estadoExpr = $this->estadoExpr($estadoCol);

        $pendientes = $this->countSql("
            SELECT COUNT(*)
            FROM {$this->qt($table)}
            WHERE {$estadoExpr} IN ('pendiente', 'pendiente_validacion', 'en_revision')
        ");

        $observadas = $this->countSql("
            SELECT COUNT(*)
            FROM {$this->qt($table)}
            WHERE {$estadoExpr} IN ('observada', 'observado')
        ");

        $fechaRevisionCol = $this->firstColumn($table, [
            'fecha_revision',
            'fecha_actualizacion',
            'updated_at',
            'fecha_validacion',
        ]);

        $validadasHoy = 0;

        if ($fechaRevisionCol) {
            $validadasHoy = $this->countSql("
                SELECT COUNT(*)
                FROM {$this->qt($table)}
                WHERE {$estadoExpr} IN ('aprobada', 'aprobado', 'validada', 'validado')
                  AND DATE({$this->qc($fechaRevisionCol)}) = CURDATE()
            ");
        }

        return [
            'pendientes'    => $pendientes,
            'validadas_hoy' => $validadasHoy,
            'observadas'    => $observadas,
        ];
    }

    private function kpiPublicaciones(\DateTimeImmutable $desde): array
    {
        $table = 'producto';

        if (!$this->tableExists($table)) {
            return [
                'en_revision' => 0,
                'reportadas'  => 0,
                'suspendidas' => 0,
            ];
        }

        $estadoCol = $this->firstColumn($table, [
            'estado_revision',
            'estado_publicacion',
            'estado_moderacion',
            'estado',
        ]);

        $visibleCol = $this->firstColumn($table, ['visible']);

        $reportadoCol = $this->firstColumn($table, [
            'reportado',
            'es_reportado',
            'tiene_reporte',
        ]);

        $suspendidoCol = $this->firstColumn($table, [
            'suspendido',
            'es_suspendido',
        ]);

        $enRevision = 0;
        $reportadas = 0;
        $suspendidas = 0;

        /*
         * REGLA EV:
         * visible = 0 => Borrador. NO debe aparecer en soporte.
         * visible = 1 => Pendiente / En revisión. SÍ debe aparecer en soporte.
         * visible = 2 => Aprobada.
         * visible = 3 => Rechazada.
         */
        if ($visibleCol) {
            $enRevision = $this->countSql("
                SELECT COUNT(*)
                FROM {$this->qt($table)}
                WHERE {$this->qc($visibleCol)} = 1
            ");
        } elseif ($estadoCol) {
            $enRevision = $this->countEstadoValues($table, $estadoCol, [
                'en_revision',
                'revision',
                'pendiente',
                'pendiente_revision',
                'pendiente_aprobacion',
                'observada',
                'observado',
            ]);
        }

        $whereReportadas = [];

        if ($reportadoCol) {
            $whereReportadas[] = "COALESCE({$this->qc($reportadoCol)}, 0) = 1";
        }

        if ($estadoCol) {
            $whereReportadas[] = "{$this->estadoExpr($estadoCol)} IN ('reportada', 'reportado')";
        }

        if ($whereReportadas) {
            $reportadas = $this->countSql("
                SELECT COUNT(*)
                FROM {$this->qt($table)}
                WHERE (" . implode(' OR ', $whereReportadas) . ")
            ");
        }

        $whereSuspendidas = [];

        if ($suspendidoCol) {
            $whereSuspendidas[] = "COALESCE({$this->qc($suspendidoCol)}, 0) = 1";
        }

        if ($estadoCol) {
            $whereSuspendidas[] = "{$this->estadoExpr($estadoCol)} IN (
                'suspendida',
                'suspendido',
                'bloqueada',
                'bloqueado'
            )";
        }

        if ($whereSuspendidas) {
            $suspendidas = $this->countSql("
                SELECT COUNT(*)
                FROM {$this->qt($table)}
                WHERE (" . implode(' OR ', $whereSuspendidas) . ")
            ");
        }

        return [
            'en_revision' => $enRevision,
            'reportadas'  => $reportadas,
            'suspendidas' => $suspendidas,
        ];
    }

    private function kpiServicios(\DateTimeImmutable $desde): array
    {
        if (!$this->tableExists('solicitud_servicio_incidencia')) {
            return [
                'abiertas' => 0,
                'esperando_informacion' => 0,
                'resueltas_hoy' => 0,
            ];
        }

        $abiertas = $this->countSql("
            SELECT COUNT(*)
            FROM `solicitud_servicio_incidencia`
            WHERE requiere_soporte = 1
              AND estado IN ('revision_soporte', 'esperando_informacion', 'abierta', 'en_atencion', 'persiste', 'solucion_pendiente_confirmacion')
        ");

        $esperando = $this->countSql("
            SELECT COUNT(*)
            FROM `solicitud_servicio_incidencia`
            WHERE requiere_soporte = 1
              AND estado = 'esperando_informacion'
        ");

        $resueltasHoy = $this->countSql("
            SELECT COUNT(*)
            FROM `solicitud_servicio_incidencia`
            WHERE requiere_soporte = 1
              AND estado IN ('resuelta', 'cerrada', 'cancelada')
              AND fecha_resolucion_soporte IS NOT NULL
              AND DATE(fecha_resolucion_soporte) = CURDATE()
        ");

        return [
            'abiertas' => $abiertas,
            'esperando_informacion' => $esperando,
            'resueltas_hoy' => $resueltasHoy,
        ];
    }

    // ------------------------------------------------------------
    // ATENDER AHORA
    // ------------------------------------------------------------

    private function atenderAhora(\DateTimeImmutable $desde, int $limit): array
    {
        $items = [];

        $items = array_merge($items, $this->atenderCuentasEnRevision($desde, $limit));
        $items = array_merge($items, $this->atenderRecargasPendientes($desde, $limit));
        $items = array_merge($items, $this->atenderPublicacionesPendientes($desde, $limit));
        $items = array_merge($items, $this->atenderServiciosPendientes($desde, $limit));

        usort($items, function (array $a, array $b): int {
            $pa = $this->pesoPrioridad((string)($a['prioridad'] ?? ''));
            $pb = $this->pesoPrioridad((string)($b['prioridad'] ?? ''));

            if ($pa !== $pb) {
                return $pb <=> $pa;
            }

            return ((int)($b['_ts'] ?? 0)) <=> ((int)($a['_ts'] ?? 0));
        });

        $items = array_slice($items, 0, $limit);

        foreach ($items as &$item) {
            unset($item['_ts']);
        }
        unset($item);

        return $items;
    }

    private function atenderCuentasEnRevision(\DateTimeImmutable $desde, int $limit): array
    {
        $table = 'usuario';

        if (!$this->tableExists($table)) {
            return [];
        }

        $idCol = $this->firstColumn($table, ['codigo_usuario', 'id_usuario', 'id']);
        $estadoCol = $this->firstColumn($table, ['estado']);
        $nombreCol = $this->firstColumn($table, ['nombre', 'nombres', 'nombre_completo']);
        $emailCol = $this->firstColumn($table, ['email', 'correo', 'correo_electronico']);
        $documentoCol = $this->firstColumn($table, ['documento', 'dni', 'numero_documento']);
        $telefonoCol = $this->firstColumn($table, ['telefono', 'celular']);
        $fechaCol = $this->firstColumn($table, ['fecha_creacion', 'created_at', 'fecha_registro']);

        if (!$idCol || !$estadoCol) {
            return [];
        }

        $select = [
            "u.{$this->qc($idCol)} AS codigo_usuario",
            $nombreCol ? "u.{$this->qc($nombreCol)} AS nombre" : "'' AS nombre",
            $emailCol ? "u.{$this->qc($emailCol)} AS email" : "'' AS email",
            $documentoCol ? "u.{$this->qc($documentoCol)} AS documento" : "'' AS documento",
            $telefonoCol ? "u.{$this->qc($telefonoCol)} AS telefono" : "'' AS telefono",
            "u.{$this->qc($estadoCol)} AS estado",
            $fechaCol ? "u.{$this->qc($fechaCol)} AS fecha_raw" : "NULL AS fecha_raw",
        ];

        $join = '';

        if ($this->tableExists('usuario_residencia')) {
            $urUserCol = $this->firstColumn('usuario_residencia', ['codigo_usuario', 'id_usuario']);

            $urIdCol = $this->firstColumn('usuario_residencia', [
                'codigo_usuario_residencia',
                'id_usuario_residencia',
                'codigo_residencia',
                'id_residencia',
                'id',
            ]);

            $urFechaCol = $this->firstColumn('usuario_residencia', [
                'fecha_actualizacion',
                'updated_at',
                'fecha_creacion',
                'created_at',
                'fecha_registro',
            ]);

            $urTipoCol = $this->firstColumn('usuario_residencia', ['tipo_conjunto']);
            $urCondoCol = $this->firstColumn('usuario_residencia', ['codigo_condominio']);
            $urUrbCol = $this->firstColumn('usuario_residencia', ['codigo_urbanizacion']);
            $urDirCol = $this->firstColumn('usuario_residencia', ['direccion']);
            $urCompCol = $this->firstColumn('usuario_residencia', ['comprobante_domicilio']);

            if ($urUserCol && $urIdCol) {
                $join = "
                    LEFT JOIN {$this->qt('usuario_residencia')} ur
                           ON ur.{$this->qc($urUserCol)} = u.{$this->qc($idCol)}
                          AND ur.{$this->qc($urIdCol)} = (
                                SELECT MAX(urx.{$this->qc($urIdCol)})
                                FROM {$this->qt('usuario_residencia')} urx
                                WHERE urx.{$this->qc($urUserCol)} = u.{$this->qc($idCol)}
                          )
                ";

                $select[] = $urTipoCol ? "ur.{$this->qc($urTipoCol)} AS tipo_conjunto" : "'' AS tipo_conjunto";
                $select[] = $urCondoCol ? "ur.{$this->qc($urCondoCol)} AS codigo_condominio" : "NULL AS codigo_condominio";
                $select[] = $urUrbCol ? "ur.{$this->qc($urUrbCol)} AS codigo_urbanizacion" : "NULL AS codigo_urbanizacion";
                $select[] = $urDirCol ? "ur.{$this->qc($urDirCol)} AS direccion" : "'' AS direccion";
                $select[] = $urCompCol ? "ur.{$this->qc($urCompCol)} AS comprobante_domicilio" : "'' AS comprobante_domicilio";
            } elseif ($urUserCol && $urFechaCol) {
                $join = "
                    LEFT JOIN {$this->qt('usuario_residencia')} ur
                           ON ur.{$this->qc($urUserCol)} = u.{$this->qc($idCol)}
                          AND UNIX_TIMESTAMP(ur.{$this->qc($urFechaCol)}) = (
                                SELECT MAX(UNIX_TIMESTAMP(urx.{$this->qc($urFechaCol)}))
                                FROM {$this->qt('usuario_residencia')} urx
                                WHERE urx.{$this->qc($urUserCol)} = u.{$this->qc($idCol)}
                          )
                ";

                $select[] = $urTipoCol ? "ur.{$this->qc($urTipoCol)} AS tipo_conjunto" : "'' AS tipo_conjunto";
                $select[] = $urCondoCol ? "ur.{$this->qc($urCondoCol)} AS codigo_condominio" : "NULL AS codigo_condominio";
                $select[] = $urUrbCol ? "ur.{$this->qc($urUrbCol)} AS codigo_urbanizacion" : "NULL AS codigo_urbanizacion";
                $select[] = $urDirCol ? "ur.{$this->qc($urDirCol)} AS direccion" : "'' AS direccion";
                $select[] = $urCompCol ? "ur.{$this->qc($urCompCol)} AS comprobante_domicilio" : "'' AS comprobante_domicilio";
            }
        }

        if (!$join) {
            $select[] = "'' AS tipo_conjunto";
            $select[] = "NULL AS codigo_condominio";
            $select[] = "NULL AS codigo_urbanizacion";
            $select[] = "'' AS direccion";
            $select[] = "'' AS comprobante_domicilio";
        }

        $orderBy = $fechaCol
            ? "u.{$this->qc($fechaCol)} DESC"
            : "u.{$this->qc($idCol)} DESC";

        $sql = "
            SELECT
                " . implode(",\n                ", $select) . "
            FROM {$this->qt($table)} u
            {$join}
            WHERE u.{$this->qc($estadoCol)} = 1
            ORDER BY {$orderBy}
            LIMIT :limit
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $st->execute();

        $rows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $usuariosProcesados = [];
        $out = [];

        foreach ($rows as $r) {
            $codigoUsuario = (int)($r['codigo_usuario'] ?? 0);

            if ($codigoUsuario <= 0) {
                continue;
            }

            if (isset($usuariosProcesados[$codigoUsuario])) {
                continue;
            }

            $usuariosProcesados[$codigoUsuario] = true;

            $fechaRaw = $r['fecha_raw'] ?? null;

            $out[] = [
                'fecha'                 => $this->fmtFecha($fechaRaw),
                'tipo'                  => 'Cuenta en revisión',
                'prioridad'             => 'Alta',
                'codigo_usuario'        => $codigoUsuario,
                'nombre'                => (string)($r['nombre'] ?? ''),
                'email'                 => (string)($r['email'] ?? ''),
                'documento'             => (string)($r['documento'] ?? ''),
                'telefono'              => (string)($r['telefono'] ?? ''),
                'estado'                => (int)($r['estado'] ?? 1),
                'tipo_conjunto'         => (string)($r['tipo_conjunto'] ?? ''),
                'codigo_condominio'     => $r['codigo_condominio'] !== null ? (int)$r['codigo_condominio'] : null,
                'codigo_urbanizacion'   => $r['codigo_urbanizacion'] !== null ? (int)$r['codigo_urbanizacion'] : null,
                'direccion'             => (string)($r['direccion'] ?? ''),
                'comprobante_domicilio' => (string)($r['comprobante_domicilio'] ?? ''),
                'url'                   => '/atender-cuentas',
                '_ts'                   => $this->tsFecha($fechaRaw),
            ];

            if (count($out) >= $limit) {
                break;
            }
        }

        return $out;
    }

    private function atenderRecargasPendientes(\DateTimeImmutable $desde, int $limit): array
    {
        $table = 'recarga_saldo';

        if (!$this->tableExists($table)) {
            return [];
        }

        $idCol = $this->firstColumn($table, ['codigo_recarga', 'codigo_recarga_saldo', 'id_recarga', 'id']);
        $userCol = $this->firstColumn($table, ['codigo_usuario', 'id_usuario']);
        $estadoCol = $this->firstColumn($table, ['estado', 'estado_recarga']);
        $montoCol = $this->firstColumn($table, ['monto', 'monto_recarga', 'importe', 'saldo_recarga']);
        $fechaCol = $this->firstColumn($table, ['fecha_creacion', 'fecha_registro', 'created_at', 'fecha_solicitud']);

        if (!$idCol || !$estadoCol) {
            return [];
        }

        $usuarioIdCol = $this->tableExists('usuario')
            ? $this->firstColumn('usuario', ['codigo_usuario', 'id_usuario', 'id'])
            : null;

        $usuarioNombreCol = $this->tableExists('usuario')
            ? $this->firstColumn('usuario', ['nombre', 'nombres', 'nombre_completo'])
            : null;

        $usuarioEmailCol = $this->tableExists('usuario')
            ? $this->firstColumn('usuario', ['email', 'correo', 'correo_electronico'])
            : null;

        $select = [
            "r.{$this->qc($idCol)} AS codigo_recarga",
            $userCol ? "r.{$this->qc($userCol)} AS codigo_usuario" : "NULL AS codigo_usuario",
            "r.{$this->qc($estadoCol)} AS estado",
            $montoCol ? "r.{$this->qc($montoCol)} AS monto" : "NULL AS monto",
            $fechaCol ? "r.{$this->qc($fechaCol)} AS fecha_raw" : "NULL AS fecha_raw",
        ];

        $join = '';

        if ($userCol && $usuarioIdCol) {
            $join = "
                LEFT JOIN {$this->qt('usuario')} u
                       ON u.{$this->qc($usuarioIdCol)} = r.{$this->qc($userCol)}
            ";

            $select[] = $usuarioNombreCol ? "u.{$this->qc($usuarioNombreCol)} AS usuario_nombre" : "'' AS usuario_nombre";
            $select[] = $usuarioEmailCol ? "u.{$this->qc($usuarioEmailCol)} AS usuario_email" : "'' AS usuario_email";
        } else {
            $select[] = "'' AS usuario_nombre";
            $select[] = "'' AS usuario_email";
        }

        $estadoExpr = "LOWER(TRIM(CAST(r.{$this->qc($estadoCol)} AS CHAR)))";

        $orderBy = $fechaCol
            ? "r.{$this->qc($fechaCol)} DESC"
            : "r.{$this->qc($idCol)} DESC";

        $sql = "
            SELECT
                " . implode(",\n                ", $select) . "
            FROM {$this->qt($table)} r
            {$join}
            WHERE {$estadoExpr} IN ('pendiente', 'pendiente_validacion', 'en_revision', 'observada', 'observado')
            ORDER BY {$orderBy}
            LIMIT :limit
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $st->execute();

        $rows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $out = [];

        foreach ($rows as $r) {
            $estado = strtolower(trim((string)($r['estado'] ?? '')));
            $fechaRaw = $r['fecha_raw'] ?? null;
            $monto = $r['monto'] !== null ? (float)$r['monto'] : null;

            $esObservada = in_array($estado, ['observada', 'observado'], true);

            $out[] = [
                'fecha'          => $this->fmtFecha($fechaRaw),
                'tipo'           => $esObservada ? 'Recarga observada' : 'Recarga pendiente',
                'prioridad'      => $esObservada ? 'Media' : 'Alta',
                'codigo_recarga' => (int)($r['codigo_recarga'] ?? 0),
                'codigo_usuario' => $r['codigo_usuario'] !== null ? (int)$r['codigo_usuario'] : null,
                'nombre'         => (string)($r['usuario_nombre'] ?? ''),
                'email'          => (string)($r['usuario_email'] ?? ''),
                'monto'          => $monto !== null ? number_format($monto, 2, '.', '') : '',
                'detalle'        => $esObservada ? 'Requiere revisión de observación' : 'Voucher pendiente de validación',
                'url'            => '/atender-recargas',
                '_ts'            => $this->tsFecha($fechaRaw),
            ];
        }

        return $out;
    }

    private function atenderPublicacionesPendientes(\DateTimeImmutable $desde, int $limit): array
    {
        $table = 'producto';

        if (!$this->tableExists($table)) {
            return [];
        }

        $idCol = $this->firstColumn($table, ['codigo_producto', 'id_producto', 'id']);
        $userCol = $this->firstColumn($table, ['codigo_usuario', 'id_usuario', 'codigo_vendedor', 'id_vendedor']);
        $tituloCol = $this->firstColumn($table, ['titulo', 'nombre_producto', 'nombre', 'producto']);
        $descripcionCol = $this->firstColumn($table, ['descripcion', 'detalle']);
        $visibleCol = $this->firstColumn($table, ['visible']);

        $estadoCol = $this->firstColumn($table, [
            'estado_revision',
            'estado_publicacion',
            'estado_moderacion',
            'estado',
        ]);

        $reportadoCol = $this->firstColumn($table, ['reportado', 'es_reportado', 'tiene_reporte']);
        $suspendidoCol = $this->firstColumn($table, ['suspendido', 'es_suspendido']);
        $fechaCol = $this->firstColumn($table, ['fecha_creacion', 'fecha_registro', 'created_at', 'fecha_publicacion']);

        if (!$idCol) {
            return [];
        }

        $usuarioIdCol = $this->tableExists('usuario')
            ? $this->firstColumn('usuario', ['codigo_usuario', 'id_usuario', 'id'])
            : null;

        $usuarioNombreCol = $this->tableExists('usuario')
            ? $this->firstColumn('usuario', ['nombre', 'nombres', 'nombre_completo'])
            : null;

        $usuarioEmailCol = $this->tableExists('usuario')
            ? $this->firstColumn('usuario', ['email', 'correo', 'correo_electronico'])
            : null;

        $select = [
            "p.{$this->qc($idCol)} AS codigo_producto",
            $userCol ? "p.{$this->qc($userCol)} AS codigo_usuario" : "NULL AS codigo_usuario",
            $tituloCol ? "p.{$this->qc($tituloCol)} AS titulo" : "'' AS titulo",
            $descripcionCol ? "p.{$this->qc($descripcionCol)} AS descripcion" : "'' AS descripcion",
            $fechaCol ? "p.{$this->qc($fechaCol)} AS fecha_raw" : "NULL AS fecha_raw",
            $estadoCol ? "p.{$this->qc($estadoCol)} AS estado_publicacion" : "'' AS estado_publicacion",
            $visibleCol ? "p.{$this->qc($visibleCol)} AS visible" : "NULL AS visible",
            $reportadoCol ? "p.{$this->qc($reportadoCol)} AS reportado" : "0 AS reportado",
            $suspendidoCol ? "p.{$this->qc($suspendidoCol)} AS suspendido" : "0 AS suspendido",
        ];

        $join = '';

        if ($userCol && $usuarioIdCol) {
            $join = "
                LEFT JOIN {$this->qt('usuario')} u
                       ON u.{$this->qc($usuarioIdCol)} = p.{$this->qc($userCol)}
            ";

            $select[] = $usuarioNombreCol ? "u.{$this->qc($usuarioNombreCol)} AS usuario_nombre" : "'' AS usuario_nombre";
            $select[] = $usuarioEmailCol ? "u.{$this->qc($usuarioEmailCol)} AS usuario_email" : "'' AS usuario_email";
        } else {
            $select[] = "'' AS usuario_nombre";
            $select[] = "'' AS usuario_email";
        }

        $where = [];

        /*
         * REGLA EV:
         * Para "Publicación en revisión" se usa visible = 1.
         * Nunca se usa visible = 0, porque visible = 0 es Borrador.
         */
        if ($visibleCol) {
            $where[] = "p.{$this->qc($visibleCol)} = 1";
        } elseif ($estadoCol) {
            $estadoExpr = "LOWER(TRIM(CAST(p.{$this->qc($estadoCol)} AS CHAR)))";

            $where[] = "{$estadoExpr} IN (
                'en_revision',
                'revision',
                'pendiente',
                'pendiente_revision',
                'pendiente_aprobacion',
                'observada',
                'observado'
            )";
        }

        if ($reportadoCol) {
            $where[] = "COALESCE(p.{$this->qc($reportadoCol)}, 0) = 1";
        }

        if ($suspendidoCol) {
            $where[] = "COALESCE(p.{$this->qc($suspendidoCol)}, 0) = 1";
        }

        if ($estadoCol) {
            $estadoExpr = "LOWER(TRIM(CAST(p.{$this->qc($estadoCol)} AS CHAR)))";

            $where[] = "{$estadoExpr} IN (
                'reportada',
                'reportado',
                'suspendida',
                'suspendido',
                'bloqueada',
                'bloqueado'
            )";
        }

        if (!$where) {
            return [];
        }

        $orderBy = $fechaCol
            ? "p.{$this->qc($fechaCol)} DESC"
            : "p.{$this->qc($idCol)} DESC";

        $sql = "
            SELECT
                " . implode(",\n                ", $select) . "
            FROM {$this->qt($table)} p
            {$join}
            WHERE (" . implode(" OR ", $where) . ")
            ORDER BY {$orderBy}
            LIMIT :limit
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $st->execute();

        $rows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $out = [];

        foreach ($rows as $r) {
            $estado = strtolower(trim((string)($r['estado_publicacion'] ?? '')));
            $visible = $r['visible'] ?? null;
            $reportado = (int)($r['reportado'] ?? 0);
            $suspendido = (int)($r['suspendido'] ?? 0);
            $fechaRaw = $r['fecha_raw'] ?? null;

            $esRevision = false;

            if ($visible !== null) {
                $esRevision = ((int)$visible === 1);
            } else {
                $esRevision = in_array($estado, [
                    'en_revision',
                    'revision',
                    'pendiente',
                    'pendiente_revision',
                    'pendiente_aprobacion',
                    'observada',
                    'observado',
                ], true);
            }

            $esReportada = $reportado === 1 || in_array($estado, [
                'reportada',
                'reportado',
            ], true);

            $esSuspendida = $suspendido === 1 || in_array($estado, [
                'suspendida',
                'suspendido',
                'bloqueada',
                'bloqueado',
            ], true);

            /*
             * Blindaje final:
             * Si el producto está en borrador, aprobado o rechazado normal,
             * no debe aparecer en "Atender ahora".
             */
            if (!$esRevision && !$esReportada && !$esSuspendida) {
                continue;
            }

            $tipo = 'Publicación en revisión';
            $prioridad = 'Media';

            if ($esReportada) {
                $tipo = 'Publicación reportada';
                $prioridad = 'Alta';
            }

            if ($esSuspendida) {
                $tipo = 'Publicación suspendida';
                $prioridad = 'Media';
            }

            $titulo = trim((string)($r['titulo'] ?? ''));

            $out[] = [
                'fecha'           => $this->fmtFecha($fechaRaw),
                'tipo'            => $tipo,
                'prioridad'       => $prioridad,
                'codigo_producto' => (int)($r['codigo_producto'] ?? 0),
                'codigo_usuario'  => $r['codigo_usuario'] !== null ? (int)$r['codigo_usuario'] : null,
                'nombre'          => $titulo !== '' ? $titulo : 'Publicación sin título',
                'email'           => (string)($r['usuario_email'] ?? ''),
                'usuario_nombre'  => (string)($r['usuario_nombre'] ?? ''),
                'detalle'         => (string)($r['descripcion'] ?? ''),
                'url'             => '/atender-publicacion',
                '_ts'             => $this->tsFecha($fechaRaw),
            ];
        }

        return $out;
    }

    private function atenderServiciosPendientes(\DateTimeImmutable $desde, int $limit): array
    {
        if (
            !$this->tableExists('solicitud_servicio_incidencia')
            || !$this->tableExists('solicitud_servicio')
            || !$this->tableExists('producto')
        ) {
            return [];
        }

        $sql = "
            SELECT
                i.codigo_incidencia,
                i.codigo_solicitud_servicio,
                i.categoria,
                i.descripcion,
                i.estado,
                i.created_at,
                i.updated_at,
                p.titulo AS titulo_servicio,
                ur.nombre AS nombre_reporta
            FROM solicitud_servicio_incidencia i
            INNER JOIN solicitud_servicio ss
                    ON ss.codigo_solicitud_servicio = i.codigo_solicitud_servicio
            INNER JOIN producto p
                    ON p.codigo_producto = ss.codigo_producto
            INNER JOIN usuario ur
                    ON ur.codigo_usuario = i.codigo_usuario_reporta
            WHERE i.requiere_soporte = 1
              AND i.estado IN ('revision_soporte', 'esperando_informacion', 'abierta', 'en_atencion', 'persiste', 'solucion_pendiente_confirmacion')
            ORDER BY
                CASE i.estado
                    WHEN 'revision_soporte' THEN 1
                    WHEN 'persiste' THEN 2
                    WHEN 'esperando_informacion' THEN 3
                    ELSE 4
                END,
                i.updated_at ASC
            LIMIT :limit
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $out = [];

        $categorias = [
            'servicio_incompleto' => 'Servicio incompleto',
            'resultado_diferente' => 'Resultado diferente a lo acordado',
            'problema_calidad' => 'Problema de calidad',
            'incumplimiento_fecha_hora' => 'Incumplimiento de fecha u hora',
            'servicio_no_realizado' => 'Servicio no realizado / inasistencia',
            'problema_comunicacion_trato' => 'Problema de comunicación o trato',
            'monto_condicion_diferente' => 'Monto o condición diferente a la cotización',
            'dano_durante_servicio' => 'Daño durante el servicio',
            'otro' => 'Otro',
        ];

        foreach ($rows as $row) {
            $estado = (string)($row['estado'] ?? '');
            $prioridad = in_array($estado, ['revision_soporte', 'persiste'], true) ? 'Alta' : 'Media';
            $fechaRaw = $row['updated_at'] ?? $row['created_at'] ?? null;
            $categoria = (string)($row['categoria'] ?? 'otro');

            $out[] = [
                'fecha' => $this->fmtFecha($fechaRaw),
                'tipo' => 'Incidencia de servicio',
                'prioridad' => $prioridad,
                'codigo_incidencia' => (int)($row['codigo_incidencia'] ?? 0),
                'codigo_solicitud_servicio' => (int)($row['codigo_solicitud_servicio'] ?? 0),
                'nombre' => (string)($row['titulo_servicio'] ?? 'Servicio'),
                'email' => '',
                'detalle' => ($categorias[$categoria] ?? 'Problema en servicio') . ' · Reportado por ' . (string)($row['nombre_reporta'] ?? 'Vecino'),
                'url' => '/atender-servicios',
                '_ts' => $this->tsFecha($fechaRaw),
            ];
        }

        return $out;
    }

    // ------------------------------------------------------------
    // Helpers DB
    // ------------------------------------------------------------

    private function tableExists(string $table): bool
    {
        if (isset($this->tableCache[$table])) {
            return $this->tableCache[$table];
        }

        try {
            $sql = "
                SELECT COUNT(*)
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
                  AND table_name = :table
            ";

            $st = $this->dblink->prepare($sql);
            $st->execute([':table' => $table]);

            $exists = ((int)$st->fetchColumn()) > 0;
            $this->tableCache[$table] = $exists;

            return $exists;
        } catch (\Throwable $e) {
            error_log('[EV][SoporteDashboard::tableExists] ' . $e->getMessage());
            $this->tableCache[$table] = false;
            return false;
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        $key = $table . '.' . $column;

        if (isset($this->columnCache[$key])) {
            return $this->columnCache[$key];
        }

        try {
            $sql = "
                SELECT COUNT(*)
                FROM information_schema.columns
                WHERE table_schema = DATABASE()
                  AND table_name = :table
                  AND column_name = :column
            ";

            $st = $this->dblink->prepare($sql);
            $st->execute([
                ':table'  => $table,
                ':column' => $column,
            ]);

            $exists = ((int)$st->fetchColumn()) > 0;
            $this->columnCache[$key] = $exists;

            return $exists;
        } catch (\Throwable $e) {
            error_log('[EV][SoporteDashboard::columnExists] ' . $e->getMessage());
            $this->columnCache[$key] = false;
            return false;
        }
    }

    private function firstColumn(string $table, array $candidates): ?string
    {
        if (!$this->tableExists($table)) {
            return null;
        }

        foreach ($candidates as $column) {
            $column = trim((string)$column);

            if ($column !== '' && $this->columnExists($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function countSql(string $sql): int
    {
        try {
            return (int)$this->dblink->query($sql)->fetchColumn();
        } catch (\Throwable $e) {
            error_log('[EV][SoporteDashboard::countSql] ' . $e->getMessage());
            return 0;
        }
    }

    private function countEstadoValues(string $table, string $estadoCol, array $values): int
    {
        if (!$values) {
            return 0;
        }

        $quotedValues = array_map(
            static fn($v) => "'" . str_replace("'", "''", strtolower(trim((string)$v))) . "'",
            $values
        );

        $sql = "
            SELECT COUNT(*)
            FROM {$this->qt($table)}
            WHERE {$this->estadoExpr($estadoCol)} IN (" . implode(',', $quotedValues) . ")
        ";

        return $this->countSql($sql);
    }

    private function qt(string $table): string
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new \InvalidArgumentException('Nombre de tabla inválido.');
        }

        return "`{$table}`";
    }

    private function qc(string $column): string
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            throw new \InvalidArgumentException('Nombre de columna inválido.');
        }

        return "`{$column}`";
    }

    private function estadoExpr(string $estadoCol): string
    {
        return "LOWER(TRIM(CAST({$this->qc($estadoCol)} AS CHAR)))";
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

        return [$now->setTime(0, 0, 0), 'hoy'];
    }

    private function fmtFecha($dt): string
    {
        if (!$dt) {
            return '';
        }

        try {
            $d = new \DateTimeImmutable((string)$dt);
            return $d->format('d/m H:i');
        } catch (\Throwable $e) {
            return (string)$dt;
        }
    }

    private function tsFecha($dt): int
    {
        if (!$dt) {
            return 0;
        }

        try {
            return (new \DateTimeImmutable((string)$dt))->getTimestamp();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function pesoPrioridad(string $prioridad): int
    {
        $p = strtolower(trim($prioridad));

        if ($p === 'alta') {
            return 3;
        }

        if ($p === 'media') {
            return 2;
        }

        if ($p === 'baja') {
            return 1;
        }

        return 0;
    }
}