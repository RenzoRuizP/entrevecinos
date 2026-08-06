<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class DashboardGerencial extends Conexion
{
    private const ALCANCES = ['global', 'departamento', 'provincia', 'distrito', 'condominio', 'urbanizacion'];
    private const PERIODOS = ['dia', 'mes', 'semestre', 'anio', 'personalizado'];

    public function catalogos(): array
    {
        return [
            'departamentos' => $this->rows("SELECT codigo_departamento AS codigo, nombre_departamento AS nombre FROM ubigeo_departamento WHERE estado = 1 ORDER BY nombre_departamento"),
            'provincias' => $this->rows("SELECT p.codigo_provincia AS codigo,p.codigo_departamento,CONCAT(p.nombre_provincia,' · ',d.nombre_departamento) AS nombre FROM ubigeo_provincia p INNER JOIN ubigeo_departamento d ON d.codigo_departamento=p.codigo_departamento WHERE p.estado=1 ORDER BY d.nombre_departamento,p.nombre_provincia"),
            'distritos' => $this->rows("SELECT di.codigo_distrito AS codigo,di.codigo_provincia,CONCAT(di.nombre_distrito,' · ',p.nombre_provincia,' · ',d.nombre_departamento) AS nombre FROM ubigeo_distrito di INNER JOIN ubigeo_provincia p ON p.codigo_provincia=di.codigo_provincia INNER JOIN ubigeo_departamento d ON d.codigo_departamento=p.codigo_departamento WHERE di.estado=1 ORDER BY d.nombre_departamento,p.nombre_provincia,di.nombre_distrito"),
            'comunidades' => $this->listarComunidades(),
            'periodos' => [
                ['codigo' => 'dia', 'nombre' => 'Día'],
                ['codigo' => 'mes', 'nombre' => 'Mes'],
                ['codigo' => 'semestre', 'nombre' => 'Semestre'],
                ['codigo' => 'anio', 'nombre' => 'Año'],
                ['codigo' => 'personalizado', 'nombre' => 'Rango personalizado'],
            ],
        ];
    }

    public function listarComunidades(): array
    {
        $sql = "
            SELECT * FROM (
                SELECT
                    'condominio' AS tipo_conjunto,
                    c.codigo_condominio AS codigo_comunidad,
                    c.nombre_condominio AS nombre_comunidad,
                    c.direccion_condominio AS direccion,
                    d.codigo_distrito,
                    d.nombre_distrito,
                    p.codigo_provincia,
                    p.nombre_provincia,
                    dep.codigo_departamento,
                    dep.nombre_departamento
                FROM condominio c
                LEFT JOIN ubigeo_distrito d ON d.codigo_distrito = c.codigo_distrito
                LEFT JOIN ubigeo_provincia p ON p.codigo_provincia = d.codigo_provincia
                LEFT JOIN ubigeo_departamento dep ON dep.codigo_departamento = p.codigo_departamento
                WHERE c.estado = 'A'
                UNION ALL
                SELECT
                    'urbanizacion' AS tipo_conjunto,
                    u.codigo_urbanizacion AS codigo_comunidad,
                    u.nombre_urbanizacion AS nombre_comunidad,
                    u.direccion_urbanizacion AS direccion,
                    d.codigo_distrito,
                    d.nombre_distrito,
                    p.codigo_provincia,
                    p.nombre_provincia,
                    dep.codigo_departamento,
                    dep.nombre_departamento
                FROM urbanizacion u
                LEFT JOIN ubigeo_distrito d ON d.codigo_distrito = u.codigo_distrito
                LEFT JOIN ubigeo_provincia p ON p.codigo_provincia = d.codigo_provincia
                LEFT JOIN ubigeo_departamento dep ON dep.codigo_departamento = p.codigo_departamento
                WHERE u.estado = 'A'
            ) comunidades
            ORDER BY nombre_departamento, nombre_provincia, nombre_distrito, nombre_comunidad
        ";
        return $this->rows($sql);
    }

    public function resumen(array $filtros): array
    {
        $ctx = $this->normalizarFiltros($filtros);
        $kpis = $this->kpis($ctx);
        $meta = $this->obtenerMeta($ctx);
        $ingresoActual = (float)$kpis['ingreso_total'];
        $montoMeta = (float)($meta['monto_objetivo'] ?? 0);

        return [
            'filtros' => [
                'periodo' => $ctx['periodo'],
                'fecha_referencia' => $ctx['fecha_referencia'],
                'fecha_desde' => $ctx['desde']->format('Y-m-d'),
                'fecha_hasta' => $ctx['hasta']->modify('-1 second')->format('Y-m-d'),
                'tipo_alcance' => $ctx['tipo_alcance'],
                'codigo_alcance' => $ctx['codigo_alcance'],
                'etiqueta_periodo' => $ctx['etiqueta_periodo'],
            ],
            'kpis' => $kpis,
            'meta' => [
                'monto_objetivo' => $montoMeta,
                'ingreso_actual' => $ingresoActual,
                'monto_faltante' => max(0, $montoMeta - $ingresoActual),
                'porcentaje' => $montoMeta > 0 ? min(100, round(($ingresoActual / $montoMeta) * 100, 2)) : 0,
                'configurada' => $montoMeta > 0,
                'setup_requerido' => !$this->tablaExiste('ev_meta_gerencial'),
            ],
            'series' => $this->series($ctx),
            'comunidades' => $this->reporteComunidades($ctx),
            'resumen_estados' => $this->resumenEstados($ctx),
        ];
    }

    public function guardarMeta(array $input, int $codigoAdmin): array
    {
        if (!$this->tablaExiste('ev_meta_gerencial')) {
            throw new RuntimeException('Falta ejecutar el script SQL del Dashboard gerencial antes de registrar metas.');
        }

        $ctx = $this->normalizarFiltros($input);
        $monto = round((float)($input['monto_objetivo'] ?? 0), 2);
        if ($monto <= 0) {
            throw new InvalidArgumentException('La meta debe ser mayor a S/ 0.00.');
        }

        $sql = "
            INSERT INTO ev_meta_gerencial
                (periodo_tipo, fecha_inicio, fecha_fin, tipo_alcance, codigo_alcance, monto_objetivo, creado_por, actualizado_por, estado)
            VALUES
                (:periodo, :inicio, :fin, :alcance, :codigo, :monto, :admin, :admin, 1)
            ON DUPLICATE KEY UPDATE
                monto_objetivo = VALUES(monto_objetivo),
                actualizado_por = VALUES(actualizado_por),
                estado = 1,
                updated_at = CURRENT_TIMESTAMP
        ";
        $st = $this->dblink->prepare($sql);
        $st->execute([
            ':periodo' => $ctx['periodo'],
            ':inicio' => $ctx['desde']->format('Y-m-d'),
            ':fin' => $ctx['hasta']->modify('-1 day')->format('Y-m-d'),
            ':alcance' => $ctx['tipo_alcance'],
            ':codigo' => $ctx['codigo_alcance'],
            ':monto' => $monto,
            ':admin' => $codigoAdmin,
        ]);

        return ['ok' => true, 'mensaje' => 'La meta gerencial fue guardada correctamente.', 'monto_objetivo' => $monto];
    }

    private function kpis(array $ctx): array
    {
        [, $usersParams] = $this->filtroUbicacion($ctx, 'c', 'urb', 'd', 'pr', 'dep');
        $users = $this->scalar(
            $this->sqlUsuarios("COUNT(DISTINCT u.codigo_usuario)", $ctx),
            $this->paramsPeriodo($ctx) + $usersParams
        );

        $salesWhere = "pe.created_at >= :desde AND pe.created_at < :hasta AND pe.estado_actual = 'entregado_vendedor'";
        [$salesLoc, $salesParams] = $this->filtroUbicacion($ctx, 'c', 'urb', 'd', 'pr', 'dep');
        $salesSql = "SELECT COUNT(DISTINCT pe.codigo_pedido) FROM pedido pe
                     INNER JOIN producto prod ON prod.codigo_producto = pe.codigo_producto
                     {$this->joinsUbicacionProducto('prod')}
                     WHERE {$salesWhere}{$salesLoc}";
        $sales = $this->scalar($salesSql, $this->paramsPeriodo($ctx) + $salesParams);

        $salesAmountSql = str_replace('COUNT(DISTINCT pe.codigo_pedido)', 'COALESCE(SUM(pe.monto_total),0)', $salesSql);
        $salesAmount = (float)$this->scalar($salesAmountSql, $this->paramsPeriodo($ctx) + $salesParams);

        [$serviceLoc, $serviceParams] = $this->filtroUbicacion($ctx, 'c', 'urb', 'd', 'pr', 'dep');
        $serviceSql = "SELECT COUNT(DISTINCT ss.codigo_solicitud_servicio)
                       FROM solicitud_servicio ss
                       INNER JOIN producto prod ON prod.codigo_producto = ss.codigo_producto
                       {$this->joinsUbicacionProducto('prod')}
                       WHERE ss.created_at >= :desde AND ss.created_at < :hasta
                         AND ss.estado = 'servicio_confirmado_solicitante'{$serviceLoc}";
        $services = $this->scalar($serviceSql, $this->paramsPeriodo($ctx) + $serviceParams);

        $serviceAmountSql = "SELECT COALESCE(SUM(prop.monto_propuesto),0)
                       FROM solicitud_servicio ss
                       INNER JOIN producto prod ON prod.codigo_producto = ss.codigo_producto
                       INNER JOIN solicitud_servicio_propuesta prop
                          ON prop.codigo_solicitud_servicio = ss.codigo_solicitud_servicio AND prop.estado = 'aceptada'
                       {$this->joinsUbicacionProducto('prod')}
                       WHERE ss.created_at >= :desde AND ss.created_at < :hasta
                         AND ss.estado = 'servicio_confirmado_solicitante'{$serviceLoc}";
        $serviceAmount = (float)$this->scalar($serviceAmountSql, $this->paramsPeriodo($ctx) + $serviceParams);

        $commissionSql = "SELECT COALESCE(SUM(vc.monto),0)
                          FROM vendedor_comision_ev vc
                          INNER JOIN pedido pe ON pe.codigo_pedido = vc.codigo_pedido
                          INNER JOIN producto prod ON prod.codigo_producto = pe.codigo_producto
                          {$this->joinsUbicacionProducto('prod')}
                          WHERE vc.created_at >= :desde AND vc.created_at < :hasta
                            AND vc.estado = 'cobrada'{$salesLoc}";
        $productCommissions = (float)$this->scalar($commissionSql, $this->paramsPeriodo($ctx) + $salesParams);

        [$walletLoc, $walletParams] = $this->filtroUbicacion($ctx, 'c', 'urb', 'd', 'pr', 'dep');
        $serviceFeesSql = "SELECT COALESCE(SUM(CASE WHEN bm.tipo_movimiento='D' THEN bm.monto ELSE 0 END),0)
                           FROM billetera_movimiento bm
                           INNER JOIN billetera b ON b.codigo_billetera = bm.codigo_billetera
                           {$this->joinsUbicacionUsuario('b.codigo_usuario')}
                           WHERE bm.fecha_movimiento >= :desde AND bm.fecha_movimiento < :hasta
                             AND (LOWER(bm.origen) LIKE '%servicio%' OR LOWER(COALESCE(bm.referencia_tipo,'')) LIKE '%servicio%'){$walletLoc}";
        $serviceFees = (float)$this->scalar($serviceFeesSql, $this->paramsPeriodo($ctx) + $walletParams);

        $communities = count(array_filter(
            $this->listarComunidades(),
            fn(array $row): bool => $this->rowCoincideAlcance($row, $ctx)
        ));

        return [
            'usuarios_registrados' => (int)$users,
            'ventas_productos' => (int)$sales,
            'monto_ventas_productos' => $salesAmount,
            'servicios_contratados' => (int)$services,
            'monto_servicios' => $serviceAmount,
            'comisiones_productos' => $productCommissions,
            'ingresos_publicacion_servicios' => $serviceFees,
            'ingreso_total' => $productCommissions + $serviceFees,
            'comunidades_activas' => (int)$communities,
            'ticket_promedio_producto' => $sales > 0 ? round($salesAmount / $sales, 2) : 0,
            'ticket_promedio_servicio' => $services > 0 ? round($serviceAmount / $services, 2) : 0,
        ];
    }

    private function series(array $ctx): array
    {
        $labels = $this->generarBuckets($ctx);
        $format = $ctx['group_format'];

        [, $usersParams] = $this->filtroUbicacion($ctx, 'c', 'urb', 'd', 'pr', 'dep');
        $users = $this->serieQuery(
            $this->sqlUsuarios("DATE_FORMAT(u.fecha_creacion, '{$format}') AS bucket, COUNT(DISTINCT u.codigo_usuario) AS valor", $ctx, true),
            $this->paramsPeriodo($ctx) + $usersParams,
            $labels
        );

        [$loc, $params] = $this->filtroUbicacion($ctx, 'c', 'urb', 'd', 'pr', 'dep');
        $sales = $this->serieQuery("SELECT DATE_FORMAT(pe.created_at, '{$format}') bucket, COUNT(DISTINCT pe.codigo_pedido) valor
            FROM pedido pe INNER JOIN producto prod ON prod.codigo_producto=pe.codigo_producto {$this->joinsUbicacionProducto('prod')}
            WHERE pe.created_at>=:desde AND pe.created_at<:hasta AND pe.estado_actual='entregado_vendedor'{$loc} GROUP BY bucket ORDER BY bucket", $this->paramsPeriodo($ctx)+$params, $labels);

        $services = $this->serieQuery("SELECT DATE_FORMAT(ss.created_at, '{$format}') bucket, COUNT(DISTINCT ss.codigo_solicitud_servicio) valor
            FROM solicitud_servicio ss INNER JOIN producto prod ON prod.codigo_producto=ss.codigo_producto {$this->joinsUbicacionProducto('prod')}
            WHERE ss.created_at>=:desde AND ss.created_at<:hasta AND ss.estado='servicio_confirmado_solicitante'{$loc} GROUP BY bucket ORDER BY bucket", $this->paramsPeriodo($ctx)+$params, $labels);

        $commissions = $this->serieQuery("SELECT DATE_FORMAT(vc.created_at, '{$format}') bucket, COALESCE(SUM(vc.monto),0) valor
            FROM vendedor_comision_ev vc INNER JOIN pedido pe ON pe.codigo_pedido=vc.codigo_pedido INNER JOIN producto prod ON prod.codigo_producto=pe.codigo_producto {$this->joinsUbicacionProducto('prod')}
            WHERE vc.created_at>=:desde AND vc.created_at<:hasta AND vc.estado='cobrada'{$loc} GROUP BY bucket ORDER BY bucket", $this->paramsPeriodo($ctx)+$params, $labels);

        [$walletLoc, $walletParams] = $this->filtroUbicacion($ctx, 'c', 'urb', 'd', 'pr', 'dep');
        $serviceFees = $this->serieQuery("SELECT DATE_FORMAT(bm.fecha_movimiento, '{$format}') bucket, COALESCE(SUM(CASE WHEN bm.tipo_movimiento='D' THEN bm.monto ELSE 0 END),0) valor
            FROM billetera_movimiento bm INNER JOIN billetera b ON b.codigo_billetera=bm.codigo_billetera {$this->joinsUbicacionUsuario('b.codigo_usuario')}
            WHERE bm.fecha_movimiento>=:desde AND bm.fecha_movimiento<:hasta AND (LOWER(bm.origen) LIKE '%servicio%' OR LOWER(COALESCE(bm.referencia_tipo,'')) LIKE '%servicio%'){$walletLoc} GROUP BY bucket ORDER BY bucket", $this->paramsPeriodo($ctx)+$walletParams, $labels);

        return [
            'labels' => array_values(array_map(fn($x) => $x['label'], $labels)),
            'usuarios' => $users,
            'ventas' => $sales,
            'servicios' => $services,
            'comisiones_productos' => $commissions,
            'publicacion_servicios' => $serviceFees,
            'ingresos_totales' => array_map(fn($i) => round((float)$commissions[$i] + (float)$serviceFees[$i], 2), array_keys($commissions)),
        ];
    }

    private function reporteComunidades(array $ctx): array
    {
        // El periodo se aplica a actividad; la comunidad se conserva aunque tenga cero operaciones.
        $sql = "
          SELECT base.*,
            COALESCE(us.total_usuarios,0) total_usuarios,
            COALESCE(pub.total_publicaciones,0) total_publicaciones,
            COALESCE(ven.total_ventas,0) total_ventas,
            COALESCE(serv.total_servicios,0) total_servicios,
            COALESCE(ing.total_ingresos,0) total_ingresos,
            GREATEST(COALESCE(us.ultima_actividad,'1000-01-01'),COALESCE(pub.ultima_actividad,'1000-01-01'),COALESCE(ven.ultima_actividad,'1000-01-01'),COALESCE(serv.ultima_actividad,'1000-01-01')) ultima_actividad
          FROM (
            SELECT 'condominio' tipo_conjunto,c.codigo_condominio codigo_comunidad,c.nombre_condominio nombre_comunidad,d.codigo_distrito,d.nombre_distrito,pr.codigo_provincia,pr.nombre_provincia,dep.codigo_departamento,dep.nombre_departamento
            FROM condominio c LEFT JOIN ubigeo_distrito d ON d.codigo_distrito=c.codigo_distrito LEFT JOIN ubigeo_provincia pr ON pr.codigo_provincia=d.codigo_provincia LEFT JOIN ubigeo_departamento dep ON dep.codigo_departamento=pr.codigo_departamento WHERE c.estado='A'
            UNION ALL
            SELECT 'urbanizacion',u.codigo_urbanizacion,u.nombre_urbanizacion,d.codigo_distrito,d.nombre_distrito,pr.codigo_provincia,pr.nombre_provincia,dep.codigo_departamento,dep.nombre_departamento
            FROM urbanizacion u LEFT JOIN ubigeo_distrito d ON d.codigo_distrito=u.codigo_distrito LEFT JOIN ubigeo_provincia pr ON pr.codigo_provincia=d.codigo_provincia LEFT JOIN ubigeo_departamento dep ON dep.codigo_departamento=pr.codigo_departamento WHERE u.estado='A'
          ) base
          LEFT JOIN (
            SELECT ur.tipo_conjunto,COALESCE(ur.codigo_condominio,ur.codigo_urbanizacion) codigo_comunidad,COUNT(DISTINCT u.codigo_usuario) total_usuarios,MAX(u.fecha_creacion) ultima_actividad
            FROM usuario u INNER JOIN usuario_residencia ur ON ur.codigo_usuario_residencia=(SELECT MAX(ur2.codigo_usuario_residencia) FROM usuario_residencia ur2 WHERE ur2.codigo_usuario=u.codigo_usuario AND ur2.estado=1)
            WHERE u.fecha_creacion>=:desde_us AND u.fecha_creacion<:hasta_us GROUP BY ur.tipo_conjunto,codigo_comunidad
          ) us ON us.tipo_conjunto=base.tipo_conjunto AND us.codigo_comunidad=base.codigo_comunidad
          LEFT JOIN (
            SELECT p.tipo_conjunto_publicacion tipo_conjunto,COALESCE(p.codigo_condominio_publicacion,p.codigo_urbanizacion_publicacion) codigo_comunidad,COUNT(*) total_publicaciones,MAX(p.created_at) ultima_actividad
            FROM producto p WHERE p.created_at>=:desde_pub AND p.created_at<:hasta_pub GROUP BY p.tipo_conjunto_publicacion,codigo_comunidad
          ) pub ON pub.tipo_conjunto=base.tipo_conjunto AND pub.codigo_comunidad=base.codigo_comunidad
          LEFT JOIN (
            SELECT prod.tipo_conjunto_publicacion tipo_conjunto,COALESCE(prod.codigo_condominio_publicacion,prod.codigo_urbanizacion_publicacion) codigo_comunidad,COUNT(DISTINCT pe.codigo_pedido) total_ventas,MAX(pe.created_at) ultima_actividad
            FROM pedido pe INNER JOIN producto prod ON prod.codigo_producto=pe.codigo_producto WHERE pe.created_at>=:desde_ven AND pe.created_at<:hasta_ven AND pe.estado_actual='entregado_vendedor' GROUP BY prod.tipo_conjunto_publicacion,codigo_comunidad
          ) ven ON ven.tipo_conjunto=base.tipo_conjunto AND ven.codigo_comunidad=base.codigo_comunidad
          LEFT JOIN (
            SELECT prod.tipo_conjunto_publicacion tipo_conjunto,COALESCE(prod.codigo_condominio_publicacion,prod.codigo_urbanizacion_publicacion) codigo_comunidad,COUNT(DISTINCT ss.codigo_solicitud_servicio) total_servicios,MAX(ss.created_at) ultima_actividad
            FROM solicitud_servicio ss INNER JOIN producto prod ON prod.codigo_producto=ss.codigo_producto WHERE ss.created_at>=:desde_ser AND ss.created_at<:hasta_ser AND ss.estado='servicio_confirmado_solicitante' GROUP BY prod.tipo_conjunto_publicacion,codigo_comunidad
          ) serv ON serv.tipo_conjunto=base.tipo_conjunto AND serv.codigo_comunidad=base.codigo_comunidad
          LEFT JOIN (
            SELECT prod.tipo_conjunto_publicacion tipo_conjunto,COALESCE(prod.codigo_condominio_publicacion,prod.codigo_urbanizacion_publicacion) codigo_comunidad,COALESCE(SUM(vc.monto),0) total_ingresos
            FROM vendedor_comision_ev vc INNER JOIN pedido pe ON pe.codigo_pedido=vc.codigo_pedido INNER JOIN producto prod ON prod.codigo_producto=pe.codigo_producto WHERE vc.created_at>=:desde_ing AND vc.created_at<:hasta_ing AND vc.estado='cobrada' GROUP BY prod.tipo_conjunto_publicacion,codigo_comunidad
          ) ing ON ing.tipo_conjunto=base.tipo_conjunto AND ing.codigo_comunidad=base.codigo_comunidad
        ";
        $params = [];
        foreach (['us','pub','ven','ser','ing'] as $k) {
            $params[":desde_{$k}"] = $ctx['desde']->format('Y-m-d H:i:s');
            $params[":hasta_{$k}"] = $ctx['hasta']->format('Y-m-d H:i:s');
        }
        $rows = $this->rows($sql, $params);
        $rows = array_values(array_filter($rows, fn($r) => $this->rowCoincideAlcance($r, $ctx)));
        usort($rows, fn($a,$b) => ((float)$b['total_ingresos'] <=> (float)$a['total_ingresos']) ?: ((int)$b['total_usuarios'] <=> (int)$a['total_usuarios']));
        return array_slice($rows, 0, 50);
    }

    private function resumenEstados(array $ctx): array
    {
        [$loc, $params] = $this->filtroUbicacion($ctx, 'c', 'urb', 'd', 'pr', 'dep');
        $serv = $this->rows("SELECT ss.estado,COUNT(*) total FROM solicitud_servicio ss INNER JOIN producto prod ON prod.codigo_producto=ss.codigo_producto {$this->joinsUbicacionProducto('prod')} WHERE ss.created_at>=:desde AND ss.created_at<:hasta{$loc} GROUP BY ss.estado ORDER BY total DESC", $this->paramsPeriodo($ctx)+$params);
        $ped = $this->rows("SELECT pe.estado_actual estado,COUNT(*) total FROM pedido pe INNER JOIN producto prod ON prod.codigo_producto=pe.codigo_producto {$this->joinsUbicacionProducto('prod')} WHERE pe.created_at>=:desde AND pe.created_at<:hasta{$loc} GROUP BY pe.estado_actual ORDER BY total DESC", $this->paramsPeriodo($ctx)+$params);
        return ['servicios' => $serv, 'pedidos' => $ped];
    }

    private function obtenerMeta(array $ctx): ?array
    {
        if (!$this->tablaExiste('ev_meta_gerencial')) return null;
        $sql = "SELECT monto_objetivo FROM ev_meta_gerencial WHERE periodo_tipo=:periodo AND fecha_inicio=:inicio AND fecha_fin=:fin AND tipo_alcance=:alcance AND codigo_alcance=:codigo AND estado=1 LIMIT 1";
        $st=$this->dblink->prepare($sql);
        $st->execute([
            ':periodo'=>$ctx['periodo'], ':inicio'=>$ctx['desde']->format('Y-m-d'), ':fin'=>$ctx['hasta']->modify('-1 day')->format('Y-m-d'), ':alcance'=>$ctx['tipo_alcance'], ':codigo'=>$ctx['codigo_alcance']
        ]);
        $r=$st->fetch(PDO::FETCH_ASSOC);
        return is_array($r)?$r:null;
    }

    private function normalizarFiltros(array $f): array
    {
        $periodo = strtolower(trim((string)($f['periodo'] ?? 'mes')));
        if (!in_array($periodo, self::PERIODOS, true)) $periodo='mes';
        $fechaRefRaw = trim((string)($f['fecha_referencia'] ?? date('Y-m-d')));
        try { $ref = new DateTimeImmutable($fechaRefRaw ?: 'today'); } catch (Throwable) { $ref = new DateTimeImmutable('today'); }

        if ($periodo === 'dia') {
            $desde=$ref->setTime(0,0); $hasta=$desde->modify('+1 day'); $format='%Y-%m-%d %H'; $label='Día '.$desde->format('d/m/Y');
        } elseif ($periodo === 'semestre') {
            $month=(int)$ref->format('n'); $startMonth=$month<=6?1:7; $desde=$ref->setDate((int)$ref->format('Y'),$startMonth,1)->setTime(0,0); $hasta=$desde->modify('+6 months'); $format='%Y-%m'; $label=($startMonth===1?'Primer':'Segundo').' semestre '.$desde->format('Y');
        } elseif ($periodo === 'anio') {
            $desde=$ref->setDate((int)$ref->format('Y'),1,1)->setTime(0,0); $hasta=$desde->modify('+1 year'); $format='%Y-%m'; $label='Año '.$desde->format('Y');
        } elseif ($periodo === 'personalizado') {
            try { $desde=new DateTimeImmutable((string)($f['fecha_desde']??$fechaRefRaw)); } catch(Throwable){$desde=$ref;}
            try { $hastaInc=new DateTimeImmutable((string)($f['fecha_hasta']??$fechaRefRaw)); } catch(Throwable){$hastaInc=$ref;}
            $desde=$desde->setTime(0,0); $hasta=$hastaInc->setTime(0,0)->modify('+1 day'); if($hasta<=$desde)$hasta=$desde->modify('+1 day');
            $days=(int)$desde->diff($hasta)->days; $format=$days<=62?'%Y-%m-%d':'%Y-%m'; $label=$desde->format('d/m/Y').' - '.$hasta->modify('-1 day')->format('d/m/Y');
        } else {
            $desde=$ref->setDate((int)$ref->format('Y'),(int)$ref->format('m'),1)->setTime(0,0); $hasta=$desde->modify('+1 month'); $format='%Y-%m-%d'; $label=ucfirst($desde->format('F Y'));
        }

        $alcance=strtolower(trim((string)($f['tipo_alcance']??'global')));
        if(!in_array($alcance,self::ALCANCES,true))$alcance='global';
        $codigo=$alcance==='global'?0:max(0,(int)($f['codigo_alcance']??0));
        if($alcance!=='global'&&$codigo<=0){$alcance='global';$codigo=0;}

        return ['periodo'=>$periodo,'fecha_referencia'=>$ref->format('Y-m-d'),'desde'=>$desde,'hasta'=>$hasta,'group_format'=>$format,'etiqueta_periodo'=>$label,'tipo_alcance'=>$alcance,'codigo_alcance'=>$codigo];
    }

    private function sqlUsuarios(string $select, array $ctx, bool $group=false): string
    {
        [$loc,] = $this->filtroUbicacion($ctx,'c','urb','d','pr','dep');
        return "SELECT {$select} FROM usuario u {$this->joinsUbicacionUsuario('u.codigo_usuario')} WHERE u.fecha_creacion>=:desde AND u.fecha_creacion<:hasta{$loc}".($group?' GROUP BY bucket ORDER BY bucket':'');
    }

    private function joinsUbicacionUsuario(string $usuarioExpr): string
    {
        return " INNER JOIN usuario_residencia ur ON ur.codigo_usuario_residencia=(SELECT MAX(ur2.codigo_usuario_residencia) FROM usuario_residencia ur2 WHERE ur2.codigo_usuario={$usuarioExpr} AND ur2.estado=1)
                 LEFT JOIN condominio c ON c.codigo_condominio=ur.codigo_condominio
                 LEFT JOIN urbanizacion urb ON urb.codigo_urbanizacion=ur.codigo_urbanizacion
                 LEFT JOIN ubigeo_distrito d ON d.codigo_distrito=COALESCE(c.codigo_distrito,urb.codigo_distrito)
                 LEFT JOIN ubigeo_provincia pr ON pr.codigo_provincia=d.codigo_provincia
                 LEFT JOIN ubigeo_departamento dep ON dep.codigo_departamento=pr.codigo_departamento ";
    }

    private function joinsUbicacionProducto(string $alias): string
    {
        return " LEFT JOIN condominio c ON c.codigo_condominio={$alias}.codigo_condominio_publicacion
                 LEFT JOIN urbanizacion urb ON urb.codigo_urbanizacion={$alias}.codigo_urbanizacion_publicacion
                 LEFT JOIN ubigeo_distrito d ON d.codigo_distrito=COALESCE(c.codigo_distrito,urb.codigo_distrito)
                 LEFT JOIN ubigeo_provincia pr ON pr.codigo_provincia=d.codigo_provincia
                 LEFT JOIN ubigeo_departamento dep ON dep.codigo_departamento=pr.codigo_departamento ";
    }

    private function filtroUbicacion(array $ctx,string $c,string $u,string $d,string $p,string $dep): array
    {
        $type=$ctx['tipo_alcance'];$code=$ctx['codigo_alcance'];
        return match($type){
            'departamento'=>[" AND {$dep}.codigo_departamento=:alcance",[':alcance'=>$code]],
            'provincia'=>[" AND {$p}.codigo_provincia=:alcance",[':alcance'=>$code]],
            'distrito'=>[" AND {$d}.codigo_distrito=:alcance",[':alcance'=>$code]],
            'condominio'=>[" AND {$c}.codigo_condominio=:alcance",[':alcance'=>$code]],
            'urbanizacion'=>[" AND {$u}.codigo_urbanizacion=:alcance",[':alcance'=>$code]],
            default=>['',[]],
        };
    }

    private function paramsPeriodo(array $ctx): array { return [':desde'=>$ctx['desde']->format('Y-m-d H:i:s'),':hasta'=>$ctx['hasta']->format('Y-m-d H:i:s')]; }

    private function generarBuckets(array $ctx): array
    {
        $out=[];$cursor=$ctx['desde'];$format=$ctx['group_format'];
        $step=str_contains($format,'%H')?'+1 hour':(str_ends_with($format,'%d')?'+1 day':'+1 month');
        while($cursor<$ctx['hasta']){
            $key=str_contains($format,'%H')?$cursor->format('Y-m-d H'):(str_ends_with($format,'%d')?$cursor->format('Y-m-d'):$cursor->format('Y-m'));
            $label=str_contains($format,'%H')?$cursor->format('H:00'):(str_ends_with($format,'%d')?$cursor->format('d/m'):$this->mesCorto((int)$cursor->format('n')));
            $out[]=['key'=>$key,'label'=>$label];$cursor=$cursor->modify($step);
            if(count($out)>400)break;
        }
        return $out;
    }

    private function serieQuery(string $sql,array $params,array $buckets): array
    {
        $map=[];foreach($this->rows($sql,$params) as $r)$map[(string)$r['bucket']]=(float)$r['valor'];
        return array_map(fn($b)=>$map[$b['key']]??0,$buckets);
    }

    private function rowCoincideAlcance(array $r,array $ctx): bool
    {
        $code=$ctx['codigo_alcance'];return match($ctx['tipo_alcance']){
            'departamento'=>(int)$r['codigo_departamento']===$code,
            'provincia'=>(int)$r['codigo_provincia']===$code,
            'distrito'=>(int)$r['codigo_distrito']===$code,
            'condominio'=>$r['tipo_conjunto']==='condominio'&&(int)$r['codigo_comunidad']===$code,
            'urbanizacion'=>$r['tipo_conjunto']==='urbanizacion'&&(int)$r['codigo_comunidad']===$code,
            default=>true,
        };
    }

    private function mesCorto(int $m): string { return ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'][$m]??''; }
    private function tablaExiste(string $table): bool { $st=$this->dblink->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=:t");$st->execute([':t'=>$table]);return (int)$st->fetchColumn()>0; }
    private function scalar(string $sql,array $params=[]): int|float|string { $st=$this->dblink->prepare($sql);$st->execute($params);$v=$st->fetchColumn();return is_numeric($v)?(float)$v:(string)$v; }
    private function rows(string $sql,array $params=[]): array { $st=$this->dblink->prepare($sql);$st->execute($params);return $st->fetchAll(PDO::FETCH_ASSOC)?:[]; }
}
