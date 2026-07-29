<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

/**
 * Punto 14 y 15 de EV:
 * - Control de funcionalidades por alcance.
 * - Configuración de monetización por alcance.
 *
 * La resolución siempre prioriza la configuración más específica:
 * usuario/residencia (condominio o urbanización) y luego global.
 */
class ConfiguracionPlataforma extends Conexion
{
    private const ZONA_HORARIA = 'America/Lima';

    public const ALCANCE_GLOBAL = 'global';
    public const ALCANCE_CONDOMINIO = 'condominio';
    public const ALCANCE_URBANIZACION = 'urbanizacion';

    public const FUNC_PUBLICAR_PRODUCTOS = 'PUBLICAR_PRODUCTOS';
    public const FUNC_PUBLICAR_SERVICIOS = 'PUBLICAR_SERVICIOS';
    public const FUNC_MARKETPLACE = 'MARKETPLACE';
    public const FUNC_COMPRAR_PRODUCTOS = 'COMPRAR_PRODUCTOS';
    public const FUNC_SOLICITAR_SERVICIOS = 'SOLICITAR_SERVICIOS';
    public const FUNC_BILLETERA = 'BILLETERA';
    public const FUNC_PROMOCIONES = 'PROMOCIONES';

    public const MON_COMISION_PRODUCTO = 'COMISION_VENTA_PRODUCTO';
    public const MON_PUBLICACION_PRODUCTO = 'COSTO_PUBLICACION_PRODUCTO';
    public const MON_PUBLICACION_SERVICIO_DIA = 'COSTO_PUBLICACION_SERVICIO_DIA';
    public const MON_COMISION_SERVICIO = 'COMISION_SERVICIO';
    public const MON_DESTACADAS = 'PUBLICACIONES_DESTACADAS';
    public const MON_DESCUENTO_BILLETERA_PEDIDO = 'DESCUENTO_BILLETERA_PEDIDO';
    public const MON_RECARGAS = 'RECARGAS_HABILITADAS';
    public const MON_BILLETERA_VISIBLE = 'BILLETERA_VISIBLE';
    public const MON_BONO_BIENVENIDA = 'BONO_BIENVENIDA_HABILITADO';
    public const MON_BONO_BIENVENIDA_MONTO = 'BONO_BIENVENIDA_MONTO';

    /**
     * Mapeo de rutas del menú a la funcionalidad que las habilita.
     */
    public static function mapaRutasFuncionalidad(): array
    {
        return [
            '/marketplace' => self::FUNC_MARKETPLACE,
            '/mis-pedidos-comprador' => self::FUNC_COMPRAR_PRODUCTOS,
            '/mis-pedidos-vendedor' => self::FUNC_COMPRAR_PRODUCTOS,
            '/mis-solicitudes-servicio-comprador' => self::FUNC_SOLICITAR_SERVICIOS,
            '/mis-solicitudes-servicio-vendedor' => self::FUNC_SOLICITAR_SERVICIOS,
            '/billetera' => self::FUNC_BILLETERA,
        ];
    }

    public static function normalizarAlcance(string $tipo, int $codigo): array
    {
        $tipo = strtolower(trim($tipo));
        if (!in_array($tipo, [self::ALCANCE_GLOBAL, self::ALCANCE_CONDOMINIO, self::ALCANCE_URBANIZACION], true)) {
            $tipo = self::ALCANCE_GLOBAL;
        }

        if ($tipo === self::ALCANCE_GLOBAL) {
            $codigo = 0;
        }

        return [
            'tipo_alcance' => $tipo,
            'codigo_alcance' => max(0, $codigo),
        ];
    }


    /**
     * Valida un alcance antes de una escritura administrativa.
     * Un valor inválido nunca debe degradarse silenciosamente a global.
     */
    private function validarAlcanceEscritura(string $tipo, int $codigo, bool $permitirGlobal = true): array
    {
        $tipo = strtolower(trim($tipo));
        if (!in_array($tipo, [self::ALCANCE_GLOBAL, self::ALCANCE_CONDOMINIO, self::ALCANCE_URBANIZACION], true)) {
            return ['ok' => false, 'error' => 'TIPO_ALCANCE_INVALIDO', 'mensaje' => 'El tipo de alcance no es válido.'];
        }

        if ($tipo === self::ALCANCE_GLOBAL) {
            if (!$permitirGlobal) {
                return ['ok' => false, 'error' => 'ALCANCE_GLOBAL_NO_PERMITIDO', 'mensaje' => 'Selecciona un condominio o una urbanización para aplicar el perfil del piloto.'];
            }
            return ['ok' => true, 'alcance' => self::normalizarAlcance(self::ALCANCE_GLOBAL, 0)];
        }

        if ($codigo <= 0) {
            return ['ok' => false, 'error' => 'ALCANCE_INVALIDO', 'mensaje' => 'Selecciona una comunidad válida.'];
        }

        $tabla = $tipo === self::ALCANCE_CONDOMINIO ? 'condominio' : 'urbanizacion';
        $columna = $tipo === self::ALCANCE_CONDOMINIO ? 'codigo_condominio' : 'codigo_urbanizacion';
        $sql = "SELECT COUNT(*) FROM {$tabla} WHERE {$columna} = :codigo AND estado IN ('A', '1')";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo', $codigo, PDO::PARAM_INT);
        $st->execute();

        if ((int)$st->fetchColumn() <= 0) {
            return ['ok' => false, 'error' => 'COMUNIDAD_NO_ENCONTRADA', 'mensaje' => 'La comunidad seleccionada no existe o no está activa.'];
        }

        return ['ok' => true, 'alcance' => self::normalizarAlcance($tipo, $codigo)];
    }

    /**
     * Obtiene el alcance de una sesión JWT o arreglo equivalente.
     */
    public static function alcanceDesdeUsuario(array $usuario): array
    {
        $tipo = strtolower(trim((string)($usuario['tipo_conjunto'] ?? $usuario['conjunto_tipo'] ?? '')));

        if ($tipo === self::ALCANCE_CONDOMINIO) {
            $codigo = (int)($usuario['codigo_condominio'] ?? 0);
            if ($codigo > 0) {
                return self::normalizarAlcance(self::ALCANCE_CONDOMINIO, $codigo);
            }
        }

        if ($tipo === self::ALCANCE_URBANIZACION) {
            $codigo = (int)($usuario['codigo_urbanizacion'] ?? 0);
            if ($codigo > 0) {
                return self::normalizarAlcance(self::ALCANCE_URBANIZACION, $codigo);
            }
        }

        return self::normalizarAlcance(self::ALCANCE_GLOBAL, 0);
    }

    public static function esAdmin(array $usuario): bool
    {
        $rol = strtolower(trim((string)($usuario['rol'] ?? $usuario['nombre_rol'] ?? '')));
        $codigoRol = (int)($usuario['codigo_rol'] ?? 0);
        return $rol === 'admin' || (defined('EV_ADMIN_ROLE_ID') && $codigoRol === (int)EV_ADMIN_ROLE_ID);
    }

    public function obtenerAlcanceUsuario(int $codigoUsuario): array
    {
        if ($codigoUsuario <= 0) {
            return self::normalizarAlcance(self::ALCANCE_GLOBAL, 0);
        }

        try {
            $sql = "
                SELECT tipo_conjunto, codigo_condominio, codigo_urbanizacion
                FROM usuario_residencia
                WHERE codigo_usuario = :codigo_usuario
                  AND estado = 1
                ORDER BY codigo_usuario_residencia DESC
                LIMIT 1
            ";
            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
            $st->execute();
            $row = $st->fetch(PDO::FETCH_ASSOC);

            if (!is_array($row)) {
                return self::normalizarAlcance(self::ALCANCE_GLOBAL, 0);
            }

            return self::alcanceDesdeUsuario($row);
        } catch (Throwable $e) {
            error_log('[EV][ConfiguracionPlataforma][obtenerAlcanceUsuario] ' . $e->getMessage());
            return self::normalizarAlcance(self::ALCANCE_GLOBAL, 0);
        }
    }

    public function obtenerAlcancePublicacion(array $publicacion): array
    {
        $tipo = strtolower(trim((string)($publicacion['tipo_conjunto_publicacion'] ?? $publicacion['tipo_conjunto'] ?? '')));
        if ($tipo === self::ALCANCE_CONDOMINIO) {
            return self::normalizarAlcance($tipo, (int)($publicacion['codigo_condominio_publicacion'] ?? $publicacion['codigo_condominio'] ?? 0));
        }
        if ($tipo === self::ALCANCE_URBANIZACION) {
            return self::normalizarAlcance($tipo, (int)($publicacion['codigo_urbanizacion_publicacion'] ?? $publicacion['codigo_urbanizacion'] ?? 0));
        }
        return self::normalizarAlcance(self::ALCANCE_GLOBAL, 0);
    }

    private function configuracionVigente(array $row, string $prefijo = ''): bool
    {
        $modo = strtolower(trim((string)($row[$prefijo . 'modo_activacion'] ?? 'manual')));
        if ($modo !== 'programado') {
            return true;
        }

        $zona = new DateTimeZone(self::ZONA_HORARIA);
        $ahora = new DateTimeImmutable('now', $zona);
        $inicio = trim((string)($row[$prefijo . 'fecha_inicio'] ?? ''));
        $fin = trim((string)($row[$prefijo . 'fecha_fin'] ?? ''));

        try {
            if ($inicio !== '' && $ahora < new DateTimeImmutable($inicio, $zona)) {
                return false;
            }
            if ($fin !== '' && $ahora > new DateTimeImmutable($fin, $zona)) {
                return false;
            }
        } catch (Throwable $e) {
            error_log('[EV][ConfiguracionPlataforma][configuracionVigente] ' . $e->getMessage());
            return false;
        }

        return true;
    }

    public function obtenerFuncionalidadPorAlcance(string $clave, string $tipoAlcance, int $codigoAlcance): array
    {
        $clave = strtoupper(trim($clave));
        $alcance = self::normalizarAlcance($tipoAlcance, $codigoAlcance);

        try {
            $sql = "
                SELECT
                    f.codigo_funcionalidad,
                    f.clave,
                    f.nombre,
                    f.descripcion,
                    f.valor_defecto,
                    fc.codigo_funcionalidad_configuracion,
                    fc.tipo_alcance,
                    fc.codigo_alcance,
                    fc.habilitada,
                    fc.modo_activacion,
                    fc.fecha_inicio,
                    fc.fecha_fin,
                    fc.mensaje_usuario,
                    fc.motivo,
                    fc.updated_at,
                    CASE
                        WHEN fc.tipo_alcance = :tipo_especifico AND fc.codigo_alcance = :codigo_especifico THEN 1
                        WHEN fc.tipo_alcance = 'global' AND fc.codigo_alcance = 0 THEN 2
                        ELSE 9
                    END AS prioridad
                FROM ev_funcionalidad f
                LEFT JOIN ev_funcionalidad_configuracion fc
                    ON fc.codigo_funcionalidad = f.codigo_funcionalidad
                   AND fc.estado_registro = 1
                   AND (
                        (fc.tipo_alcance = :tipo_especifico_2 AND fc.codigo_alcance = :codigo_especifico_2)
                        OR (fc.tipo_alcance = 'global' AND fc.codigo_alcance = 0)
                   )
                WHERE f.clave = :clave
                  AND f.estado = 1
                ORDER BY prioridad ASC
            ";
            $st = $this->dblink->prepare($sql);
            $st->execute([
                ':tipo_especifico' => $alcance['tipo_alcance'],
                ':codigo_especifico' => $alcance['codigo_alcance'],
                ':tipo_especifico_2' => $alcance['tipo_alcance'],
                ':codigo_especifico_2' => $alcance['codigo_alcance'],
                ':clave' => $clave,
            ]);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $catalogo = null;
            foreach ($rows as $row) {
                $catalogo ??= $row;
                if (!empty($row['codigo_funcionalidad_configuracion']) && $this->configuracionVigente($row)) {
                    return [
                        'clave' => $clave,
                        'habilitada' => (int)$row['habilitada'] === 1,
                        'mensaje' => trim((string)($row['mensaje_usuario'] ?? '')),
                        'origen' => (string)($row['tipo_alcance'] ?? 'global'),
                        'codigo_alcance' => (int)($row['codigo_alcance'] ?? 0),
                        'modo_activacion' => (string)($row['modo_activacion'] ?? 'manual'),
                        'fecha_inicio' => $row['fecha_inicio'] ?? null,
                        'fecha_fin' => $row['fecha_fin'] ?? null,
                        'codigo_configuracion' => (int)$row['codigo_funcionalidad_configuracion'],
                    ];
                }
            }

            if (is_array($catalogo)) {
                return [
                    'clave' => $clave,
                    'habilitada' => (int)($catalogo['valor_defecto'] ?? 1) === 1,
                    'mensaje' => '',
                    'origen' => 'valor_defecto',
                    'codigo_alcance' => 0,
                    'modo_activacion' => 'manual',
                    'fecha_inicio' => null,
                    'fecha_fin' => null,
                    'codigo_configuracion' => null,
                ];
            }
        } catch (Throwable $e) {
            error_log('[EV][ConfiguracionPlataforma][obtenerFuncionalidadPorAlcance] ' . $e->getMessage());
        }

        // Compatibilidad segura antes de ejecutar el SQL: no rompe la aplicación actual.
        return [
            'clave' => $clave,
            'habilitada' => true,
            'mensaje' => '',
            'origen' => 'fallback_compatibilidad',
            'codigo_alcance' => 0,
            'modo_activacion' => 'manual',
            'fecha_inicio' => null,
            'fecha_fin' => null,
            'codigo_configuracion' => null,
        ];
    }

    public function obtenerFuncionalidad(string $clave, array $usuario, bool $adminBypass = true): array
    {
        if ($adminBypass && self::esAdmin($usuario)) {
            return [
                'clave' => strtoupper(trim($clave)),
                'habilitada' => true,
                'mensaje' => '',
                'origen' => 'bypass_admin',
                'codigo_alcance' => 0,
                'modo_activacion' => 'manual',
                'fecha_inicio' => null,
                'fecha_fin' => null,
                'codigo_configuracion' => null,
            ];
        }

        $alcance = self::alcanceDesdeUsuario($usuario);
        return $this->obtenerFuncionalidadPorAlcance($clave, $alcance['tipo_alcance'], $alcance['codigo_alcance']);
    }

    public function funcionalidadHabilitada(string $clave, array $usuario, bool $adminBypass = true): bool
    {
        return (bool)($this->obtenerFuncionalidad($clave, $usuario, $adminBypass)['habilitada'] ?? false);
    }

    public function listarFuncionalidadesResueltas(array $usuario, bool $adminBypass = true): array
    {
        $claves = [
            self::FUNC_PUBLICAR_PRODUCTOS,
            self::FUNC_PUBLICAR_SERVICIOS,
            self::FUNC_MARKETPLACE,
            self::FUNC_COMPRAR_PRODUCTOS,
            self::FUNC_SOLICITAR_SERVICIOS,
            self::FUNC_BILLETERA,
            self::FUNC_PROMOCIONES,
        ];

        $resultado = [];
        foreach ($claves as $clave) {
            $resultado[$clave] = $this->obtenerFuncionalidad($clave, $usuario, $adminBypass);
        }
        return $resultado;
    }

    public function filtrarMenus(array $menus, array $usuario): array
    {
        if (self::esAdmin($usuario)) {
            return $menus;
        }

        $mapa = self::mapaRutasFuncionalidad();
        $resultado = [];

        foreach ($menus as $menu) {
            $submenus = is_array($menu['submenus'] ?? null) ? $menu['submenus'] : [];
            $filtrados = [];

            foreach ($submenus as $submenu) {
                $ruta = '/' . ltrim((string)($submenu['ruta'] ?? ''), '/');
                $clave = $mapa[$ruta] ?? null;

                if ($clave !== null && !$this->funcionalidadHabilitada($clave, $usuario, false)) {
                    continue;
                }

                if ($ruta === '/publicacion') {
                    $productos = $this->funcionalidadHabilitada(self::FUNC_PUBLICAR_PRODUCTOS, $usuario, false);
                    $servicios = $this->funcionalidadHabilitada(self::FUNC_PUBLICAR_SERVICIOS, $usuario, false);
                    if (!$productos && !$servicios) {
                        continue;
                    }
                }

                if ($ruta === '/billetera') {
                    $reglaBilletera = $this->obtenerMonetizacion(self::MON_BILLETERA_VISIBLE, $usuario);
                    if (!(bool)($reglaBilletera['valor_booleano'] ?? false)) {
                        continue;
                    }
                }

                // Atención de recargas se gobierna por monetización.
                if ($ruta === '/atender-recargas') {
                    $regla = $this->obtenerMonetizacion(self::MON_RECARGAS, $usuario);
                    if (!(bool)($regla['valor_booleano'] ?? false)) {
                        continue;
                    }
                }

                $filtrados[] = $submenu;
            }

            if (!empty($filtrados)) {
                $menu['submenus'] = $filtrados;
                $resultado[] = $menu;
            }
        }

        return $resultado;
    }

    public function obtenerMonetizacionPorAlcance(string $clave, string $tipoAlcance, int $codigoAlcance): array
    {
        $clave = strtoupper(trim($clave));
        $alcance = self::normalizarAlcance($tipoAlcance, $codigoAlcance);

        try {
            $sql = "
                SELECT
                    r.codigo_monetizacion_regla,
                    r.clave,
                    r.nombre,
                    r.descripcion,
                    r.tipo_valor,
                    r.valor_decimal_defecto,
                    r.valor_booleano_defecto,
                    r.valor_texto_defecto,
                    mc.codigo_monetizacion_configuracion,
                    mc.tipo_alcance,
                    mc.codigo_alcance,
                    mc.valor_decimal,
                    mc.valor_booleano,
                    mc.valor_texto,
                    mc.modo_activacion,
                    mc.fecha_inicio,
                    mc.fecha_fin,
                    mc.motivo,
                    mc.updated_at,
                    CASE
                        WHEN mc.tipo_alcance = :tipo_especifico AND mc.codigo_alcance = :codigo_especifico THEN 1
                        WHEN mc.tipo_alcance = 'global' AND mc.codigo_alcance = 0 THEN 2
                        ELSE 9
                    END AS prioridad
                FROM ev_monetizacion_regla r
                LEFT JOIN ev_monetizacion_configuracion mc
                    ON mc.codigo_monetizacion_regla = r.codigo_monetizacion_regla
                   AND mc.estado_registro = 1
                   AND (
                        (mc.tipo_alcance = :tipo_especifico_2 AND mc.codigo_alcance = :codigo_especifico_2)
                        OR (mc.tipo_alcance = 'global' AND mc.codigo_alcance = 0)
                   )
                WHERE r.clave = :clave
                  AND r.estado = 1
                ORDER BY prioridad ASC
            ";
            $st = $this->dblink->prepare($sql);
            $st->execute([
                ':tipo_especifico' => $alcance['tipo_alcance'],
                ':codigo_especifico' => $alcance['codigo_alcance'],
                ':tipo_especifico_2' => $alcance['tipo_alcance'],
                ':codigo_especifico_2' => $alcance['codigo_alcance'],
                ':clave' => $clave,
            ]);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $catalogo = null;
            foreach ($rows as $row) {
                $catalogo ??= $row;
                if (!empty($row['codigo_monetizacion_configuracion']) && $this->configuracionVigente($row)) {
                    return $this->formatearReglaMonetizacion($row, false);
                }
            }

            if (is_array($catalogo)) {
                return $this->formatearReglaMonetizacion($catalogo, true);
            }
        } catch (Throwable $e) {
            error_log('[EV][ConfiguracionPlataforma][obtenerMonetizacionPorAlcance] ' . $e->getMessage());
        }

        return $this->fallbackMonetizacion($clave);
    }

    private function formatearReglaMonetizacion(array $row, bool $usarDefecto): array
    {
        $tipo = (string)($row['tipo_valor'] ?? 'decimal');
        $valorDecimal = $usarDefecto ? $row['valor_decimal_defecto'] ?? null : $row['valor_decimal'] ?? null;
        $valorBooleano = $usarDefecto ? $row['valor_booleano_defecto'] ?? null : $row['valor_booleano'] ?? null;
        $valorTexto = $usarDefecto ? $row['valor_texto_defecto'] ?? null : $row['valor_texto'] ?? null;

        return [
            'clave' => (string)($row['clave'] ?? ''),
            'nombre' => (string)($row['nombre'] ?? ''),
            'tipo_valor' => $tipo,
            'valor_decimal' => $valorDecimal !== null ? (float)$valorDecimal : null,
            'valor_booleano' => $valorBooleano !== null ? (int)$valorBooleano === 1 : null,
            'valor_texto' => $valorTexto !== null ? (string)$valorTexto : null,
            'origen' => $usarDefecto ? 'valor_defecto' : (string)($row['tipo_alcance'] ?? 'global'),
            'codigo_alcance' => $usarDefecto ? 0 : (int)($row['codigo_alcance'] ?? 0),
            'codigo_configuracion' => $usarDefecto ? null : (int)($row['codigo_monetizacion_configuracion'] ?? 0),
            'modo_activacion' => $usarDefecto ? 'manual' : (string)($row['modo_activacion'] ?? 'manual'),
            'fecha_inicio' => $usarDefecto ? null : ($row['fecha_inicio'] ?? null),
            'fecha_fin' => $usarDefecto ? null : ($row['fecha_fin'] ?? null),
        ];
    }

    private function fallbackMonetizacion(string $clave): array
    {
        // Valores heredados para que el código no cambie de comportamiento antes del SQL.
        $decimales = [
            self::MON_COMISION_PRODUCTO => 10.0,
            self::MON_PUBLICACION_PRODUCTO => 0.0,
            self::MON_PUBLICACION_SERVICIO_DIA => 0.0,
            self::MON_COMISION_SERVICIO => 0.0,
            self::MON_BONO_BIENVENIDA_MONTO => 15.0,
        ];
        $booleanos = [
            self::MON_DESTACADAS => true,
            self::MON_DESCUENTO_BILLETERA_PEDIDO => true,
            self::MON_RECARGAS => true,
            self::MON_BILLETERA_VISIBLE => true,
            self::MON_BONO_BIENVENIDA => true,
        ];

        return [
            'clave' => $clave,
            'nombre' => $clave,
            'tipo_valor' => array_key_exists($clave, $booleanos) ? 'booleano' : 'decimal',
            'valor_decimal' => $decimales[$clave] ?? 0.0,
            'valor_booleano' => $booleanos[$clave] ?? null,
            'valor_texto' => null,
            'origen' => 'fallback_compatibilidad',
            'codigo_alcance' => 0,
            'codigo_configuracion' => null,
            'modo_activacion' => 'manual',
            'fecha_inicio' => null,
            'fecha_fin' => null,
        ];
    }

    public function obtenerMonetizacion(string $clave, array $usuario): array
    {
        $alcance = self::alcanceDesdeUsuario($usuario);
        return $this->obtenerMonetizacionPorAlcance($clave, $alcance['tipo_alcance'], $alcance['codigo_alcance']);
    }

    public function listarMonetizacionResuelta(array $usuario): array
    {
        $claves = [
            self::MON_COMISION_PRODUCTO,
            self::MON_PUBLICACION_PRODUCTO,
            self::MON_PUBLICACION_SERVICIO_DIA,
            self::MON_COMISION_SERVICIO,
            self::MON_DESTACADAS,
            self::MON_DESCUENTO_BILLETERA_PEDIDO,
            self::MON_RECARGAS,
            self::MON_BILLETERA_VISIBLE,
            self::MON_BONO_BIENVENIDA,
            self::MON_BONO_BIENVENIDA_MONTO,
        ];

        $resultado = [];
        foreach ($claves as $clave) {
            $resultado[$clave] = $this->obtenerMonetizacion($clave, $usuario);
        }
        return $resultado;
    }

    public function listarCatalogoAdmin(string $tipoAlcance, int $codigoAlcance): array
    {
        $alcance = self::normalizarAlcance($tipoAlcance, $codigoAlcance);

        $funcionalidades = [];
        $monetizacion = [];

        try {
            $st = $this->dblink->query("SELECT codigo_funcionalidad, clave, nombre, descripcion, valor_defecto FROM ev_funcionalidad WHERE estado = 1 ORDER BY orden, nombre");
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                $resuelta = $this->obtenerFuncionalidadPorAlcance((string)$row['clave'], $alcance['tipo_alcance'], $alcance['codigo_alcance']);
                $row['resuelta'] = $resuelta;
                $row['configuracion_directa'] = $this->obtenerConfiguracionFuncionalidadDirecta((int)$row['codigo_funcionalidad'], $alcance);
                $funcionalidades[] = $row;
            }

            $st = $this->dblink->query("SELECT codigo_monetizacion_regla, clave, nombre, descripcion, tipo_valor, unidad, valor_decimal_defecto, valor_booleano_defecto, valor_texto_defecto FROM ev_monetizacion_regla WHERE estado = 1 ORDER BY orden, nombre");
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                $row['resuelta'] = $this->obtenerMonetizacionPorAlcance((string)$row['clave'], $alcance['tipo_alcance'], $alcance['codigo_alcance']);
                $row['configuracion_directa'] = $this->obtenerConfiguracionMonetizacionDirecta((int)$row['codigo_monetizacion_regla'], $alcance);
                $monetizacion[] = $row;
            }
        } catch (Throwable $e) {
            error_log('[EV][ConfiguracionPlataforma][listarCatalogoAdmin] ' . $e->getMessage());
            throw $e;
        }

        return [
            'alcance' => $alcance,
            'funcionalidades' => $funcionalidades,
            'monetizacion' => $monetizacion,
            'historial' => $this->listarHistorial($alcance['tipo_alcance'], $alcance['codigo_alcance'], 30),
        ];
    }

    private function obtenerConfiguracionFuncionalidadDirecta(int $codigoFuncionalidad, array $alcance): ?array
    {
        $sql = "SELECT * FROM ev_funcionalidad_configuracion WHERE codigo_funcionalidad = :f AND tipo_alcance = :t AND codigo_alcance = :c AND estado_registro = 1 LIMIT 1";
        $st = $this->dblink->prepare($sql);
        $st->execute([':f' => $codigoFuncionalidad, ':t' => $alcance['tipo_alcance'], ':c' => $alcance['codigo_alcance']]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    private function obtenerConfiguracionMonetizacionDirecta(int $codigoRegla, array $alcance): ?array
    {
        $sql = "SELECT * FROM ev_monetizacion_configuracion WHERE codigo_monetizacion_regla = :r AND tipo_alcance = :t AND codigo_alcance = :c AND estado_registro = 1 LIMIT 1";
        $st = $this->dblink->prepare($sql);
        $st->execute([':r' => $codigoRegla, ':t' => $alcance['tipo_alcance'], ':c' => $alcance['codigo_alcance']]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function listarComunidades(): array
    {
        $resultado = [
            ['tipo_alcance' => self::ALCANCE_GLOBAL, 'codigo_alcance' => 0, 'nombre' => 'Todo Entre Vecinos'],
        ];

        $st = $this->dblink->query("SELECT codigo_condominio AS codigo, nombre_condominio AS nombre FROM condominio WHERE estado IN ('A', '1') ORDER BY nombre_condominio");
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $resultado[] = ['tipo_alcance' => self::ALCANCE_CONDOMINIO, 'codigo_alcance' => (int)$row['codigo'], 'nombre' => (string)$row['nombre']];
        }

        $st = $this->dblink->query("SELECT codigo_urbanizacion AS codigo, nombre_urbanizacion AS nombre FROM urbanizacion WHERE estado IN ('A', '1') ORDER BY nombre_urbanizacion");
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $resultado[] = ['tipo_alcance' => self::ALCANCE_URBANIZACION, 'codigo_alcance' => (int)$row['codigo'], 'nombre' => (string)$row['nombre']];
        }

        return $resultado;
    }


    /**
     * Devuelve un alcance concreto para conservar una selección válida sin
     * cargar el catálogo completo de comunidades en cada consulta.
     */
    public function obtenerComunidadPorAlcance(string $tipo, int $codigo): array
    {
        $alcance = self::normalizarAlcance($tipo, $codigo);
        if ($alcance['tipo_alcance'] === self::ALCANCE_GLOBAL) {
            return [
                'tipo_alcance' => self::ALCANCE_GLOBAL,
                'codigo_alcance' => 0,
                'nombre' => 'Todo Entre Vecinos',
            ];
        }

        $tabla = $alcance['tipo_alcance'] === self::ALCANCE_CONDOMINIO ? 'condominio' : 'urbanizacion';
        $columnaCodigo = $alcance['tipo_alcance'] === self::ALCANCE_CONDOMINIO ? 'codigo_condominio' : 'codigo_urbanizacion';
        $columnaNombre = $alcance['tipo_alcance'] === self::ALCANCE_CONDOMINIO ? 'nombre_condominio' : 'nombre_urbanizacion';

        $sql = "SELECT {$columnaCodigo} AS codigo, {$columnaNombre} AS nombre
                FROM {$tabla}
                WHERE {$columnaCodigo} = :codigo
                  AND estado IN ('A', '1')
                LIMIT 1";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo', $alcance['codigo_alcance'], PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if (!is_array($row)) {
            return [
                'tipo_alcance' => self::ALCANCE_GLOBAL,
                'codigo_alcance' => 0,
                'nombre' => 'Todo Entre Vecinos',
            ];
        }

        return [
            'tipo_alcance' => $alcance['tipo_alcance'],
            'codigo_alcance' => (int)$row['codigo'],
            'nombre' => (string)$row['nombre'],
        ];
    }

    /**
     * Búsqueda paginada ligera para el combobox administrativo.
     * Evita enviar todos los condominios y urbanizaciones en cada carga.
     */
    public function buscarComunidades(string $termino = '', int $limite = 20): array
    {
        $termino = trim(mb_substr($termino, 0, 100, 'UTF-8'));
        $limite = max(5, min(50, $limite));
        $resultado = [];

        $terminoNormalizado = mb_strtolower($termino, 'UTF-8');
        $nombreGlobal = 'Todo Entre Vecinos';
        if ($termino === '' || mb_stripos(mb_strtolower($nombreGlobal, 'UTF-8'), $terminoNormalizado, 0, 'UTF-8') !== false) {
            $resultado[] = [
                'tipo_alcance' => self::ALCANCE_GLOBAL,
                'codigo_alcance' => 0,
                'nombre' => $nombreGlobal,
            ];
        }

        $terminoSql = str_replace(['%', '_'], [' ', ' '], $termino);
        $busqueda = '%' . $terminoSql . '%';
        $consultas = [
            [
                'tipo' => self::ALCANCE_CONDOMINIO,
                'sql' => "SELECT codigo_condominio AS codigo, nombre_condominio AS nombre
                          FROM condominio
                          WHERE estado IN ('A', '1')
                            AND (:termino_vacio = 1 OR nombre_condominio LIKE :busqueda)
                          ORDER BY nombre_condominio
                          LIMIT :limite",
            ],
            [
                'tipo' => self::ALCANCE_URBANIZACION,
                'sql' => "SELECT codigo_urbanizacion AS codigo, nombre_urbanizacion AS nombre
                          FROM urbanizacion
                          WHERE estado IN ('A', '1')
                            AND (:termino_vacio = 1 OR nombre_urbanizacion LIKE :busqueda)
                          ORDER BY nombre_urbanizacion
                          LIMIT :limite",
            ],
        ];

        foreach ($consultas as $consulta) {
            $st = $this->dblink->prepare($consulta['sql']);
            $st->bindValue(':termino_vacio', $termino === '' ? 1 : 0, PDO::PARAM_INT);
            $st->bindValue(':busqueda', $busqueda, PDO::PARAM_STR);
            $st->bindValue(':limite', $limite, PDO::PARAM_INT);
            $st->execute();

            foreach ($st->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                $resultado[] = [
                    'tipo_alcance' => $consulta['tipo'],
                    'codigo_alcance' => (int)$row['codigo'],
                    'nombre' => (string)$row['nombre'],
                ];
            }
        }

        $global = array_values(array_filter($resultado, static fn(array $item): bool => $item['tipo_alcance'] === self::ALCANCE_GLOBAL));
        $comunidades = array_values(array_filter($resultado, static fn(array $item): bool => $item['tipo_alcance'] !== self::ALCANCE_GLOBAL));
        usort($comunidades, static fn(array $a, array $b): int => strnatcasecmp($a['nombre'], $b['nombre']));

        return array_slice(array_merge($global, $comunidades), 0, $limite);
    }

    public function guardarFuncionalidad(array $data, int $codigoAdmin, bool $gestionarTransaccion = true): array
    {
        $clave = strtoupper(trim((string)($data['clave'] ?? '')));
        $validacionAlcance = $this->validarAlcanceEscritura(
            (string)($data['tipo_alcance'] ?? ''),
            (int)($data['codigo_alcance'] ?? 0)
        );
        if (!($validacionAlcance['ok'] ?? false)) {
            return $validacionAlcance;
        }
        $alcance = $validacionAlcance['alcance'];
        $habilitada = !empty($data['habilitada']) ? 1 : 0;
        $modo = strtolower(trim((string)($data['modo_activacion'] ?? 'manual')));
        $modo = in_array($modo, ['manual', 'programado'], true) ? $modo : 'manual';
        $fechaInicioRaw = trim((string)($data['fecha_inicio'] ?? ''));
        $fechaFinRaw = trim((string)($data['fecha_fin'] ?? ''));

        // La modalidad es elegida por el Administrador EV para cualquier alcance.
        // En modo manual no debe quedar una programación residual almacenada.
        if ($modo === 'manual') {
            $fechaInicioRaw = '';
            $fechaFinRaw = '';
        }

        $fechaInicio = $this->normalizarFechaNullable($fechaInicioRaw);
        $fechaFin = $this->normalizarFechaNullable($fechaFinRaw);
        if (($fechaInicioRaw !== '' && $fechaInicio === null) || ($fechaFinRaw !== '' && $fechaFin === null)) {
            return ['ok' => false, 'error' => 'FECHA_INVALIDA', 'mensaje' => 'La fecha de inicio o fin no tiene un formato válido.'];
        }
        if ($modo === 'programado' && $fechaInicio === null && $fechaFin === null) {
            return ['ok' => false, 'error' => 'PROGRAMACION_INCOMPLETA', 'mensaje' => 'Para utilizar la modalidad programada debes indicar al menos una fecha de inicio o de fin.'];
        }
        $mensaje = mb_substr(trim((string)($data['mensaje_usuario'] ?? '')), 0, 500, 'UTF-8');
        $motivo = mb_substr(trim((string)($data['motivo'] ?? 'Actualización administrativa')), 0, 500, 'UTF-8');

        if ($modo === 'programado' && $fechaInicio !== null && $fechaFin !== null && strtotime($fechaFin) < strtotime($fechaInicio)) {
            return ['ok' => false, 'error' => 'RANGO_FECHAS_INVALIDO', 'mensaje' => 'La fecha de fin no puede ser anterior a la fecha de inicio.'];
        }

        $st = $this->dblink->prepare("SELECT codigo_funcionalidad FROM ev_funcionalidad WHERE clave = :clave AND estado = 1 LIMIT 1");
        $st->execute([':clave' => $clave]);
        $codigoFuncionalidad = (int)$st->fetchColumn();
        if ($codigoFuncionalidad <= 0) {
            return ['ok' => false, 'error' => 'FUNCIONALIDAD_NO_ENCONTRADA', 'mensaje' => 'La funcionalidad indicada no existe.'];
        }

        try {
            if ($gestionarTransaccion) {
                $this->dblink->beginTransaction();
            }
            $anterior = $this->obtenerConfiguracionFuncionalidadDirecta($codigoFuncionalidad, $alcance);

            $sql = "
                INSERT INTO ev_funcionalidad_configuracion
                (codigo_funcionalidad, tipo_alcance, codigo_alcance, habilitada, modo_activacion, fecha_inicio, fecha_fin, mensaje_usuario, motivo, actualizado_por, estado_registro)
                VALUES (:f, :t, :c, :h, :m, :fi, :ff, :msg, :motivo, :admin, 1)
                ON DUPLICATE KEY UPDATE
                    habilitada = VALUES(habilitada),
                    modo_activacion = VALUES(modo_activacion),
                    fecha_inicio = VALUES(fecha_inicio),
                    fecha_fin = VALUES(fecha_fin),
                    mensaje_usuario = VALUES(mensaje_usuario),
                    motivo = VALUES(motivo),
                    actualizado_por = VALUES(actualizado_por),
                    estado_registro = 1,
                    updated_at = NOW()
            ";
            $st = $this->dblink->prepare($sql);
            $st->execute([
                ':f' => $codigoFuncionalidad,
                ':t' => $alcance['tipo_alcance'],
                ':c' => $alcance['codigo_alcance'],
                ':h' => $habilitada,
                ':m' => $modo,
                ':fi' => $fechaInicio,
                ':ff' => $fechaFin,
                ':msg' => $mensaje !== '' ? $mensaje : null,
                ':motivo' => $motivo,
                ':admin' => $codigoAdmin,
            ]);

            $nueva = $this->obtenerConfiguracionFuncionalidadDirecta($codigoFuncionalidad, $alcance);
            $this->registrarHistorialFuncionalidad($codigoFuncionalidad, $alcance, $anterior, $nueva, $motivo, $codigoAdmin);
            if ($gestionarTransaccion) {
                $this->dblink->commit();
            }

            return ['ok' => true, 'mensaje' => 'Funcionalidad actualizada correctamente.'];
        } catch (Throwable $e) {
            if ($gestionarTransaccion && $this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            throw $e;
        }
    }

    public function guardarMonetizacion(array $data, int $codigoAdmin, bool $gestionarTransaccion = true): array
    {
        $clave = strtoupper(trim((string)($data['clave'] ?? '')));
        $validacionAlcance = $this->validarAlcanceEscritura(
            (string)($data['tipo_alcance'] ?? ''),
            (int)($data['codigo_alcance'] ?? 0)
        );
        if (!($validacionAlcance['ok'] ?? false)) {
            return $validacionAlcance;
        }
        $alcance = $validacionAlcance['alcance'];
        $modo = strtolower(trim((string)($data['modo_activacion'] ?? 'manual')));
        $modo = in_array($modo, ['manual', 'programado'], true) ? $modo : 'manual';
        $fechaInicioRaw = trim((string)($data['fecha_inicio'] ?? ''));
        $fechaFinRaw = trim((string)($data['fecha_fin'] ?? ''));

        // La modalidad es elegida por el Administrador EV para cualquier alcance.
        // En modo manual no debe quedar una programación residual almacenada.
        if ($modo === 'manual') {
            $fechaInicioRaw = '';
            $fechaFinRaw = '';
        }

        $fechaInicio = $this->normalizarFechaNullable($fechaInicioRaw);
        $fechaFin = $this->normalizarFechaNullable($fechaFinRaw);
        if (($fechaInicioRaw !== '' && $fechaInicio === null) || ($fechaFinRaw !== '' && $fechaFin === null)) {
            return ['ok' => false, 'error' => 'FECHA_INVALIDA', 'mensaje' => 'La fecha de inicio o fin no tiene un formato válido.'];
        }
        if ($modo === 'programado' && $fechaInicio === null && $fechaFin === null) {
            return ['ok' => false, 'error' => 'PROGRAMACION_INCOMPLETA', 'mensaje' => 'Para utilizar la modalidad programada debes indicar al menos una fecha de inicio o de fin.'];
        }
        if ($modo === 'programado' && $fechaInicio !== null && $fechaFin !== null && strtotime($fechaFin) < strtotime($fechaInicio)) {
            return ['ok' => false, 'error' => 'RANGO_FECHAS_INVALIDO', 'mensaje' => 'La fecha de fin no puede ser anterior a la fecha de inicio.'];
        }
        $motivo = mb_substr(trim((string)($data['motivo'] ?? 'Actualización administrativa')), 0, 500, 'UTF-8');

        $st = $this->dblink->prepare("SELECT * FROM ev_monetizacion_regla WHERE clave = :clave AND estado = 1 LIMIT 1");
        $st->execute([':clave' => $clave]);
        $regla = $st->fetch(PDO::FETCH_ASSOC);
        if (!is_array($regla)) {
            return ['ok' => false, 'error' => 'REGLA_NO_ENCONTRADA', 'mensaje' => 'La regla de monetización indicada no existe.'];
        }

        $tipoValor = (string)$regla['tipo_valor'];
        $valorDecimal = null;
        $valorBooleano = null;
        $valorTexto = null;

        if ($tipoValor === 'booleano') {
            $valorBooleano = !empty($data['valor_booleano']) ? 1 : 0;
        } elseif ($tipoValor === 'texto') {
            $valorTexto = mb_substr(trim((string)($data['valor_texto'] ?? '')), 0, 255, 'UTF-8');
        } else {
            $valorDecimal = round((float)($data['valor_decimal'] ?? 0), 4);
            if ($valorDecimal < 0) {
                return ['ok' => false, 'error' => 'VALOR_INVALIDO', 'mensaje' => 'El valor no puede ser negativo.'];
            }
            if ($tipoValor === 'porcentaje' && $valorDecimal > 100) {
                return ['ok' => false, 'error' => 'PORCENTAJE_INVALIDO', 'mensaje' => 'El porcentaje no puede superar el 100 %.'];
            }

        }

        try {
            if ($gestionarTransaccion) {
                $this->dblink->beginTransaction();
            }
            $codigoRegla = (int)$regla['codigo_monetizacion_regla'];
            $anterior = $this->obtenerConfiguracionMonetizacionDirecta($codigoRegla, $alcance);

            $sql = "
                INSERT INTO ev_monetizacion_configuracion
                (codigo_monetizacion_regla, tipo_alcance, codigo_alcance, valor_decimal, valor_booleano, valor_texto, modo_activacion, fecha_inicio, fecha_fin, motivo, actualizado_por, estado_registro)
                VALUES (:r, :t, :c, :vd, :vb, :vt, :m, :fi, :ff, :motivo, :admin, 1)
                ON DUPLICATE KEY UPDATE
                    valor_decimal = VALUES(valor_decimal),
                    valor_booleano = VALUES(valor_booleano),
                    valor_texto = VALUES(valor_texto),
                    modo_activacion = VALUES(modo_activacion),
                    fecha_inicio = VALUES(fecha_inicio),
                    fecha_fin = VALUES(fecha_fin),
                    motivo = VALUES(motivo),
                    actualizado_por = VALUES(actualizado_por),
                    estado_registro = 1,
                    updated_at = NOW()
            ";
            $st = $this->dblink->prepare($sql);
            $st->bindValue(':r', $codigoRegla, PDO::PARAM_INT);
            $st->bindValue(':t', $alcance['tipo_alcance'], PDO::PARAM_STR);
            $st->bindValue(':c', $alcance['codigo_alcance'], PDO::PARAM_INT);
            $valorDecimal === null ? $st->bindValue(':vd', null, PDO::PARAM_NULL) : $st->bindValue(':vd', $valorDecimal);
            $valorBooleano === null ? $st->bindValue(':vb', null, PDO::PARAM_NULL) : $st->bindValue(':vb', $valorBooleano, PDO::PARAM_INT);
            $valorTexto === null ? $st->bindValue(':vt', null, PDO::PARAM_NULL) : $st->bindValue(':vt', $valorTexto, PDO::PARAM_STR);
            $st->bindValue(':m', $modo, PDO::PARAM_STR);
            $fechaInicio === null ? $st->bindValue(':fi', null, PDO::PARAM_NULL) : $st->bindValue(':fi', $fechaInicio, PDO::PARAM_STR);
            $fechaFin === null ? $st->bindValue(':ff', null, PDO::PARAM_NULL) : $st->bindValue(':ff', $fechaFin, PDO::PARAM_STR);
            $st->bindValue(':motivo', $motivo, PDO::PARAM_STR);
            $st->bindValue(':admin', $codigoAdmin, PDO::PARAM_INT);
            $st->execute();

            $nueva = $this->obtenerConfiguracionMonetizacionDirecta($codigoRegla, $alcance);
            $this->registrarHistorialMonetizacion($codigoRegla, $alcance, $anterior, $nueva, $motivo, $codigoAdmin);
            if ($gestionarTransaccion) {
                $this->dblink->commit();
            }

            return ['ok' => true, 'mensaje' => 'Regla de monetización actualizada correctamente.'];
        } catch (Throwable $e) {
            if ($gestionarTransaccion && $this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            throw $e;
        }
    }

    public function aplicarPerfilPiloto(string $tipoAlcance, int $codigoAlcance, int $codigoAdmin, array $opciones = []): array
    {
        $validacionAlcance = $this->validarAlcanceEscritura($tipoAlcance, $codigoAlcance, false);
        if (!($validacionAlcance['ok'] ?? false)) {
            return $validacionAlcance;
        }
        $alcance = $validacionAlcance['alcance'];

        $modo = strtolower(trim((string)($opciones['modo_activacion'] ?? 'manual')));
        $modo = in_array($modo, ['manual', 'programado'], true) ? $modo : 'manual';
        $fechaInicioRaw = trim((string)($opciones['fecha_inicio'] ?? ''));
        $fechaFinRaw = trim((string)($opciones['fecha_fin'] ?? ''));
        if ($modo === 'manual') {
            $fechaInicioRaw = '';
            $fechaFinRaw = '';
        }

        $fechaInicio = $this->normalizarFechaNullable($fechaInicioRaw);
        $fechaFin = $this->normalizarFechaNullable($fechaFinRaw);
        if (($fechaInicioRaw !== '' && $fechaInicio === null) || ($fechaFinRaw !== '' && $fechaFin === null)) {
            return ['ok' => false, 'error' => 'FECHA_INVALIDA', 'mensaje' => 'La fecha de inicio o fin del perfil no tiene un formato válido.'];
        }
        if ($modo === 'programado' && $fechaInicio === null && $fechaFin === null) {
            return ['ok' => false, 'error' => 'PROGRAMACION_INCOMPLETA', 'mensaje' => 'Para programar el perfil debes indicar al menos una fecha de inicio o de fin.'];
        }
        if ($modo === 'programado' && $fechaInicio !== null && $fechaFin !== null && strtotime($fechaFin) < strtotime($fechaInicio)) {
            return ['ok' => false, 'error' => 'RANGO_FECHAS_INVALIDO', 'mensaje' => 'La fecha de fin del perfil no puede ser anterior a la fecha de inicio.'];
        }

        $funciones = [
            self::FUNC_PUBLICAR_PRODUCTOS => true,
            self::FUNC_PUBLICAR_SERVICIOS => true,
            self::FUNC_MARKETPLACE => false,
            self::FUNC_COMPRAR_PRODUCTOS => false,
            self::FUNC_SOLICITAR_SERVICIOS => false,
            self::FUNC_BILLETERA => false,
            self::FUNC_PROMOCIONES => true,
        ];

        $monetizacionDecimal = [
            self::MON_COMISION_PRODUCTO => 0,
            self::MON_PUBLICACION_PRODUCTO => 0,
            self::MON_PUBLICACION_SERVICIO_DIA => 0,
            self::MON_COMISION_SERVICIO => 0,
            self::MON_BONO_BIENVENIDA_MONTO => 0,
        ];

        $monetizacionBoolean = [
            self::MON_DESTACADAS => false,
            self::MON_DESCUENTO_BILLETERA_PEDIDO => false,
            self::MON_RECARGAS => false,
            self::MON_BILLETERA_VISIBLE => false,
            self::MON_BONO_BIENVENIDA => false,
        ];

        try {
            $this->dblink->beginTransaction();

            foreach ($funciones as $clave => $valor) {
                $res = $this->guardarFuncionalidad([
                    'clave' => $clave,
                    'tipo_alcance' => $alcance['tipo_alcance'],
                    'codigo_alcance' => $alcance['codigo_alcance'],
                    'habilitada' => $valor ? 1 : 0,
                    'modo_activacion' => $modo,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'mensaje_usuario' => $valor ? '' : 'Esta funcionalidad aún no está disponible durante la fase actual del piloto.',
                    'motivo' => 'Aplicación del perfil gratuito de piloto EV',
                ], $codigoAdmin, false);
                if (!($res['ok'] ?? false)) {
                    throw new RuntimeException((string)($res['mensaje'] ?? 'No se pudo guardar una funcionalidad del perfil.'));
                }
            }

            foreach ($monetizacionDecimal as $clave => $valor) {
                $res = $this->guardarMonetizacion([
                    'clave' => $clave,
                    'tipo_alcance' => $alcance['tipo_alcance'],
                    'codigo_alcance' => $alcance['codigo_alcance'],
                    'valor_decimal' => $valor,
                    'modo_activacion' => $modo,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'motivo' => 'Aplicación del perfil gratuito de piloto EV',
                ], $codigoAdmin, false);
                if (!($res['ok'] ?? false)) {
                    throw new RuntimeException((string)($res['mensaje'] ?? 'No se pudo guardar una regla decimal del perfil.'));
                }
            }

            foreach ($monetizacionBoolean as $clave => $valor) {
                $res = $this->guardarMonetizacion([
                    'clave' => $clave,
                    'tipo_alcance' => $alcance['tipo_alcance'],
                    'codigo_alcance' => $alcance['codigo_alcance'],
                    'valor_booleano' => $valor ? 1 : 0,
                    'modo_activacion' => $modo,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'motivo' => 'Aplicación del perfil gratuito de piloto EV',
                ], $codigoAdmin, false);
                if (!($res['ok'] ?? false)) {
                    throw new RuntimeException((string)($res['mensaje'] ?? 'No se pudo guardar una regla booleana del perfil.'));
                }
            }

            $this->dblink->commit();
            $modalidad = $modo === 'programado' ? 'programada' : 'manual';
            return ['ok' => true, 'mensaje' => 'Perfil gratuito del piloto aplicado correctamente en modalidad ' . $modalidad . '.'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            error_log('[EV][ConfiguracionPlataforma][aplicarPerfilPiloto] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'PERFIL_PILOTO_NO_APLICADO', 'mensaje' => 'No se pudo aplicar el perfil completo. No se guardó ningún cambio parcial.'];
        }
    }

    private function normalizarFechaNullable(mixed $value): ?string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }

        try {
            $fecha = new DateTimeImmutable($value, new DateTimeZone(self::ZONA_HORARIA));
        } catch (Throwable $e) {
            return null;
        }

        return $fecha->format('Y-m-d H:i:s');
    }

    private function registrarHistorialFuncionalidad(int $codigoFuncionalidad, array $alcance, ?array $anterior, ?array $nuevo, string $motivo, int $codigoAdmin): void
    {
        $sql = "
            INSERT INTO ev_funcionalidad_configuracion_historial
            (codigo_funcionalidad, tipo_alcance, codigo_alcance, valor_anterior_json, valor_nuevo_json, motivo, codigo_usuario_admin)
            VALUES (:f, :t, :c, :a, :n, :m, :u)
        ";
        $st = $this->dblink->prepare($sql);
        $st->execute([
            ':f' => $codigoFuncionalidad,
            ':t' => $alcance['tipo_alcance'],
            ':c' => $alcance['codigo_alcance'],
            ':a' => $anterior !== null ? json_encode($anterior, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            ':n' => $nuevo !== null ? json_encode($nuevo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            ':m' => $motivo,
            ':u' => $codigoAdmin,
        ]);
    }

    private function registrarHistorialMonetizacion(int $codigoRegla, array $alcance, ?array $anterior, ?array $nuevo, string $motivo, int $codigoAdmin): void
    {
        $sql = "
            INSERT INTO ev_monetizacion_configuracion_historial
            (codigo_monetizacion_regla, tipo_alcance, codigo_alcance, valor_anterior_json, valor_nuevo_json, motivo, codigo_usuario_admin)
            VALUES (:r, :t, :c, :a, :n, :m, :u)
        ";
        $st = $this->dblink->prepare($sql);
        $st->execute([
            ':r' => $codigoRegla,
            ':t' => $alcance['tipo_alcance'],
            ':c' => $alcance['codigo_alcance'],
            ':a' => $anterior !== null ? json_encode($anterior, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            ':n' => $nuevo !== null ? json_encode($nuevo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            ':m' => $motivo,
            ':u' => $codigoAdmin,
        ]);
    }

    public function listarHistorial(string $tipoAlcance, int $codigoAlcance, int $limite = 30): array
    {
        $alcance = self::normalizarAlcance($tipoAlcance, $codigoAlcance);
        $limite = max(1, min(100, $limite));

        $sql = "
            SELECT * FROM (
                SELECT
                    'funcionalidad' AS tipo,
                    f.nombre AS concepto,
                    h.motivo,
                    h.created_at,
                    u.nombre AS administrador
                FROM ev_funcionalidad_configuracion_historial h
                INNER JOIN ev_funcionalidad f ON f.codigo_funcionalidad = h.codigo_funcionalidad
                LEFT JOIN usuario u ON u.codigo_usuario = h.codigo_usuario_admin
                WHERE h.tipo_alcance = :tf AND h.codigo_alcance = :cf

                UNION ALL

                SELECT
                    'monetizacion' AS tipo,
                    r.nombre AS concepto,
                    h.motivo,
                    h.created_at,
                    u.nombre AS administrador
                FROM ev_monetizacion_configuracion_historial h
                INNER JOIN ev_monetizacion_regla r ON r.codigo_monetizacion_regla = h.codigo_monetizacion_regla
                LEFT JOIN usuario u ON u.codigo_usuario = h.codigo_usuario_admin
                WHERE h.tipo_alcance = :tm AND h.codigo_alcance = :cm
            ) x
            ORDER BY created_at DESC
            LIMIT {$limite}
        ";
        $st = $this->dblink->prepare($sql);
        $st->execute([
            ':tf' => $alcance['tipo_alcance'],
            ':cf' => $alcance['codigo_alcance'],
            ':tm' => $alcance['tipo_alcance'],
            ':cm' => $alcance['codigo_alcance'],
        ]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
