<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';
require_once __DIR__ . '/Notificacion.php';

final class Retiro extends Conexion
{
    public const SALDO_MINIMO_FALLBACK = 20.00;
    private const TZ = 'America/Lima';

    public function __construct()
    {
        parent::__construct();
        // Las ventanas de retiro son reglas de negocio en hora Perú.
        // Fijamos la sesión MySQL para que TIMESTAMP/NOW() se comparen en la misma zona.
        $this->dblink?->exec("SET time_zone = '-05:00'");
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone(self::TZ));
    }

    private function textoLongitudesCuenta(array $longitudes): string
    {
        $longitudes = array_values(array_unique(array_map('intval', $longitudes)));
        sort($longitudes);
        if (count($longitudes) === 1) {
            return (string)$longitudes[0] . ' dígitos';
        }
        $ultimo = array_pop($longitudes);
        return implode(', ', $longitudes) . ' o ' . $ultimo . ' dígitos';
    }

    /**
     * Valida únicamente estructura bancaria verificable por EV sin consultar al banco.
     * No confirma existencia, estado ni titularidad real de la cuenta.
     */
    private function validarDatosCuentaBancaria(string $banco, string $tipo, string $numero, string $cci): array
    {
        $reglas = ev_retiro_bank_rules();
        if (!isset($reglas[$banco])) {
            return ['ok' => false, 'error' => 'BANCO_INVALIDO', 'mensaje' => 'Selecciona un banco de la lista.'];
        }
        if (!in_array($tipo, ['ahorros', 'corriente'], true)) {
            return ['ok' => false, 'error' => 'TIPO_CUENTA_INVALIDO', 'mensaje' => 'Selecciona el tipo de cuenta.'];
        }
        if ($numero === '' || !preg_match('/^\d+$/', $numero)) {
            return ['ok' => false, 'error' => 'CUENTA_NO_NUMERICA', 'mensaje' => 'El número de cuenta debe contener solo dígitos, sin espacios ni guiones.'];
        }
        if (preg_match('/^(\d)\1+$/', $numero)) {
            return ['ok' => false, 'error' => 'CUENTA_INVALIDA', 'mensaje' => 'Revisa el número de cuenta ingresado.'];
        }

        $longitudes = $reglas[$banco]['cuenta_longitudes'][$tipo] ?? [];
        $longitudes = array_values(array_unique(array_map('intval', is_array($longitudes) ? $longitudes : [])));
        if (!$longitudes || !in_array(strlen($numero), $longitudes, true)) {
            return [
                'ok' => false,
                'error' => 'LONGITUD_CUENTA_INVALIDA',
                'mensaje' => 'La cuenta ' . ($tipo === 'ahorros' ? 'de ahorros' : 'corriente') . ' de ' . $banco . ' debe tener ' . $this->textoLongitudesCuenta($longitudes) . '.',
            ];
        }

        if (!preg_match('/^\d{20}$/', $cci)) {
            return ['ok' => false, 'error' => 'CCI_INVALIDO', 'mensaje' => 'El CCI debe contener exactamente 20 dígitos, sin espacios ni guiones.'];
        }
        if (preg_match('/^(\d)\1{19}$/', $cci)) {
            return ['ok' => false, 'error' => 'CCI_INVALIDO', 'mensaje' => 'Revisa el CCI ingresado.'];
        }

        $codigoCci = (string)($reglas[$banco]['codigo_cci'] ?? '');
        if ($codigoCci === '' || substr($cci, 0, 3) !== $codigoCci) {
            return [
                'ok' => false,
                'error' => 'CCI_BANCO_NO_COINCIDE',
                'mensaje' => 'El CCI no corresponde a ' . $banco . '. Para este banco debe comenzar con ' . $codigoCci . '.',
            ];
        }

        return ['ok' => true, 'banco' => $banco, 'tipo_cuenta' => $tipo, 'numero_cuenta' => $numero, 'cci' => $cci];
    }

    private function dt(string $valor): DateTimeImmutable
    {
        return new DateTimeImmutable($valor, new DateTimeZone(self::TZ));
    }

    private function formatDb(DateTimeInterface $dt): string
    {
        return $dt->format('Y-m-d H:i:s');
    }

    private function obtenerBilleteraBloqueada(int $codigoUsuario): array
    {
        $st = $this->dblink->prepare(
            "SELECT codigo_billetera, codigo_usuario, saldo_actual
             FROM billetera
             WHERE codigo_usuario = :u
             FOR UPDATE"
        );
        $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return [
                'codigo_billetera' => (int)$row['codigo_billetera'],
                'codigo_usuario' => (int)$row['codigo_usuario'],
                'saldo_actual' => round((float)$row['saldo_actual'], 2),
            ];
        }

        $ins = $this->dblink->prepare(
            "INSERT INTO billetera (codigo_usuario, saldo, saldo_actual, estado)
             VALUES (:u, 0.00, 0.00, 1)"
        );
        $ins->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $ins->execute();

        return [
            'codigo_billetera' => (int)$this->dblink->lastInsertId(),
            'codigo_usuario' => $codigoUsuario,
            'saldo_actual' => 0.00,
        ];
    }

    private function actualizarSaldo(int $codigoBilletera, float $saldo): void
    {
        $saldo = round(max(0.0, $saldo), 2);
        $st = $this->dblink->prepare(
            "UPDATE billetera
             SET saldo = :saldo, saldo_actual = :saldo_actual
             WHERE codigo_billetera = :b"
        );
        $st->bindValue(':saldo', $saldo);
        $st->bindValue(':saldo_actual', $saldo);
        $st->bindValue(':b', $codigoBilletera, PDO::PARAM_INT);
        $st->execute();
    }

    private function registrarMovimiento(
        int $codigoBilletera,
        string $tipo,
        float $monto,
        float $saldoAntes,
        float $saldoDespues,
        string $origen,
        string $descripcion,
        int $codigoReferencia
    ): void {
        $st = $this->dblink->prepare(
            "INSERT INTO billetera_movimiento
            (codigo_billetera, tipo_movimiento, concepto, monto,
             saldo_antes, saldo_despues, saldo_anterior, saldo_posterior,
             descripcion, origen, codigo_referencia, referencia_tipo, referencia_id,
             es_promocional, fecha_expira)
            VALUES
            (:b, :tipo, :concepto, :monto,
             :antes, :despues, :anterior, :posterior,
             :descripcion, :origen, :ref, :ref_tipo, :ref_id,
             0, NULL)"
        );
        $st->bindValue(':b', $codigoBilletera, PDO::PARAM_INT);
        $st->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $st->bindValue(':concepto', mb_substr($origen, 0, 150, 'UTF-8'), PDO::PARAM_STR);
        $st->bindValue(':monto', round($monto, 2));
        $st->bindValue(':antes', round($saldoAntes, 2));
        $st->bindValue(':despues', round($saldoDespues, 2));
        $st->bindValue(':anterior', round($saldoAntes, 2));
        $st->bindValue(':posterior', round($saldoDespues, 2));
        $st->bindValue(':descripcion', mb_substr($descripcion, 0, 255, 'UTF-8'), PDO::PARAM_STR);
        $st->bindValue(':origen', $origen, PDO::PARAM_STR);
        $st->bindValue(':ref', $codigoReferencia, PDO::PARAM_INT);
        $st->bindValue(':ref_tipo', $origen, PDO::PARAM_STR);
        $st->bindValue(':ref_id', $codigoReferencia, PDO::PARAM_INT);
        $st->execute();
    }

    private function existeMovimientoRetiro(int $codigoRetiro, string $origen): bool
    {
        $st = $this->dblink->prepare(
            "SELECT 1 FROM billetera_movimiento
             WHERE origen = :origen
               AND codigo_referencia = :ref
             LIMIT 1"
        );
        $st->bindValue(':origen', $origen, PDO::PARAM_STR);
        $st->bindValue(':ref', $codigoRetiro, PDO::PARAM_INT);
        $st->execute();
        return (bool)$st->fetchColumn();
    }

    private function obtenerSaldoUsuario(int $codigoUsuario): float
    {
        $st = $this->dblink->prepare("SELECT saldo_actual FROM billetera WHERE codigo_usuario = :u LIMIT 1");
        $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();
        return round((float)($st->fetchColumn() ?: 0), 2);
    }

    private function obtenerSaldoAlCierre(int $codigoBilletera, string $corteFin): float
    {
        $st = $this->dblink->prepare(
            "SELECT saldo_despues
             FROM billetera_movimiento
             WHERE codigo_billetera = :b
               AND fecha_movimiento <= :fin
             ORDER BY fecha_movimiento DESC, codigo_movimiento DESC
             LIMIT 1"
        );
        $st->bindValue(':b', $codigoBilletera, PDO::PARAM_INT);
        $st->bindValue(':fin', $corteFin, PDO::PARAM_STR);
        $st->execute();
        $valor = $st->fetchColumn();
        if ($valor !== false) {
            return round((float)$valor, 2);
        }

        // Si no hubo movimientos antes del corte, el saldo_antes del primer
        // movimiento posterior reconstruye el saldo que existía al cierre.
        $sig = $this->dblink->prepare(
            "SELECT saldo_antes
             FROM billetera_movimiento
             WHERE codigo_billetera = :b
               AND fecha_movimiento > :fin
             ORDER BY fecha_movimiento ASC, codigo_movimiento ASC
             LIMIT 1"
        );
        $sig->bindValue(':b', $codigoBilletera, PDO::PARAM_INT);
        $sig->bindValue(':fin', $corteFin, PDO::PARAM_STR);
        $sig->execute();
        $antes = $sig->fetchColumn();
        if ($antes !== false) {
            return round((float)$antes, 2);
        }

        $cur = $this->dblink->prepare("SELECT saldo_actual FROM billetera WHERE codigo_billetera = :b LIMIT 1");
        $cur->bindValue(':b', $codigoBilletera, PDO::PARAM_INT);
        $cur->execute();
        return round((float)($cur->fetchColumn() ?: 0), 2);
    }

    private function configuracionesActivas(): array
    {
        $st = $this->dblink->query(
            "SELECT codigo_retiro_configuracion, nombre_jornada, dia_pago,
                    dia_inicio_corte, hora_inicio_corte, dia_fin_corte, hora_fin_corte,
                    saldo_minimo, activo
             FROM retiro_configuracion
             WHERE activo = 1
             ORDER BY dia_pago, codigo_retiro_configuracion"
        );
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function fechaPagoCandidata(DateTimeImmutable $desde, int $diaPago, int $diasAdelante): DateTimeImmutable
    {
        $n = (int)$desde->format('N');
        $delta = ($diaPago - $n + 7) % 7;
        $fecha = $desde->setTime(0, 0)->modify('+' . $delta . ' days');
        if ($diasAdelante > 0) {
            $fecha = $fecha->modify('+' . $diasAdelante . ' days');
        }
        return $fecha;
    }

    private function fechaSemanaAnteriorA(DateTimeImmutable $pago, int $diaObjetivo, string $hora): DateTimeImmutable
    {
        $nPago = (int)$pago->format('N');
        $delta = ($nPago - $diaObjetivo + 7) % 7;
        if ($delta === 0) {
            $delta = 7;
        }
        $fecha = $pago->modify('-' . $delta . ' days');
        [$h, $m, $s] = array_pad(array_map('intval', explode(':', $hora)), 3, 0);
        return $fecha->setTime($h, $m, $s);
    }

    private function construirVentana(array $cfg, DateTimeImmutable $fechaPago): array
    {
        $fin = $this->fechaSemanaAnteriorA(
            $fechaPago,
            (int)$cfg['dia_fin_corte'],
            (string)$cfg['hora_fin_corte']
        );
        $inicio = $this->fechaSemanaAnteriorA(
            $fechaPago,
            (int)$cfg['dia_inicio_corte'],
            (string)$cfg['hora_inicio_corte']
        );
        if ($inicio > $fin) {
            $inicio = $inicio->modify('-7 days');
        }

        return [
            'codigo_retiro_configuracion' => (int)$cfg['codigo_retiro_configuracion'],
            'nombre_jornada' => (string)$cfg['nombre_jornada'],
            'dia_pago' => (int)$cfg['dia_pago'],
            'inicio' => $inicio,
            'fin' => $fin,
            'pago' => $fechaPago->setTime(0, 0),
            'saldo_minimo' => round((float)($cfg['saldo_minimo'] ?? self::SALDO_MINIMO_FALLBACK), 2),
        ];
    }

    public function obtenerCorteActual(?DateTimeImmutable $ahora = null): ?array
    {
        $ahora = $ahora ?: $this->now();
        foreach ($this->configuracionesActivas() as $cfg) {
            $diaPago = (int)$cfg['dia_pago'];
            for ($semana = 0; $semana <= 1; $semana++) {
                $pago = $this->fechaPagoCandidata($ahora, $diaPago, $semana * 7);
                $v = $this->construirVentana($cfg, $pago);
                if ($ahora >= $v['inicio'] && $ahora <= $v['fin']) {
                    return $this->serializarVentana($v);
                }
            }
        }
        return null;
    }

    private function serializarVentana(array $v): array
    {
        return [
            'codigo_retiro_configuracion' => (int)$v['codigo_retiro_configuracion'],
            'nombre_jornada' => (string)$v['nombre_jornada'],
            'dia_pago' => (int)$v['dia_pago'],
            'corte_inicio' => $this->formatDb($v['inicio']),
            'corte_fin' => $this->formatDb($v['fin']),
            'fecha_pago_programada' => $v['pago']->format('Y-m-d'),
            'saldo_minimo' => round((float)$v['saldo_minimo'], 2),
        ];
    }

    private function cuentaActiva(int $codigoUsuario, bool $forUpdate = false): ?array
    {
        $sql = "SELECT c.*, u.nombre AS titular_usuario, u.documento AS documento_usuario
                FROM usuario_cuenta_bancaria c
                INNER JOIN usuario u ON u.codigo_usuario = c.codigo_usuario
                WHERE c.codigo_usuario = :u AND c.estado_registro = 1
                ORDER BY c.codigo_cuenta_bancaria DESC
                LIMIT 1" . ($forUpdate ? ' FOR UPDATE' : '');
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function enmascarar(string $valor, int $visibles = 4): string
    {
        $v = preg_replace('/\s+/', '', trim($valor)) ?: '';
        if ($v === '') return '';
        if (mb_strlen($v) <= $visibles) return $v;
        return str_repeat('•', max(4, mb_strlen($v) - $visibles)) . mb_substr($v, -$visibles);
    }

    private function cuentaPublica(?array $cuenta, bool $mostrarCompleta = false): ?array
    {
        if (!$cuenta) return null;
        return [
            'codigo_cuenta_bancaria' => (int)$cuenta['codigo_cuenta_bancaria'],
            'banco' => (string)$cuenta['banco'],
            'tipo_cuenta' => (string)$cuenta['tipo_cuenta'],
            'numero_cuenta' => $mostrarCompleta ? (string)$cuenta['numero_cuenta'] : $this->enmascarar((string)$cuenta['numero_cuenta']),
            'cci' => $mostrarCompleta ? (string)$cuenta['cci'] : $this->enmascarar((string)$cuenta['cci']),
            'titular_nombre' => (string)$cuenta['titular_nombre'],
            'titular_documento' => $mostrarCompleta ? (string)$cuenta['titular_documento'] : $this->enmascarar((string)$cuenta['titular_documento'], 2),
            'estado' => (string)$cuenta['estado_validacion'],
            'observacion' => (string)($cuenta['observacion_admin'] ?? ''),
            'declaracion_titularidad' => (int)$cuenta['declara_titularidad'] === 1,
            'fecha_validacion' => $cuenta['fecha_validacion'] ?? null,
        ];
    }

    private function solicitudDelCorte(int $codigoUsuario, array $corte): ?array
    {
        $st = $this->dblink->prepare(
            "SELECT * FROM retiro_solicitud
             WHERE codigo_usuario = :u
               AND corte_inicio = :inicio
               AND corte_fin = :fin
             LIMIT 1"
        );
        $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $st->bindValue(':inicio', $corte['corte_inicio'], PDO::PARAM_STR);
        $st->bindValue(':fin', $corte['corte_fin'], PDO::PARAM_STR);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function guardarCuentaUsuario(int $codigoUsuario, array $data): array
    {
        $banco = trim((string)($data['banco'] ?? ''));
        $tipo = strtolower(trim((string)($data['tipo_cuenta'] ?? '')));
        $numero = trim((string)($data['numero_cuenta'] ?? ''));
        $cci = trim((string)($data['cci'] ?? ''));
        $declara = filter_var($data['declara_titularidad'] ?? false, FILTER_VALIDATE_BOOL);

        $validacionCuenta = $this->validarDatosCuentaBancaria($banco, $tipo, $numero, $cci);
        if (!($validacionCuenta['ok'] ?? false)) {
            return $validacionCuenta;
        }
        if (!$declara) {
            return ['ok' => false, 'error' => 'DECLARACION_REQUERIDA', 'mensaje' => 'Debes declarar que la cuenta bancaria está a tu nombre.'];
        }

        try {
            $this->dblink->beginTransaction();

            // Serializamos operaciones sensibles del mismo vecino para evitar que una
            // edición de cuenta se cruce con la creación de una solicitud de retiro.
            $stU = $this->dblink->prepare("SELECT nombre, documento FROM usuario WHERE codigo_usuario = :u LIMIT 1 FOR UPDATE");
            $stU->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
            $stU->execute();
            $usuario = $stU->fetch(PDO::FETCH_ASSOC);
            if (!$usuario) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'USUARIO_NO_ENCONTRADO', 'mensaje' => 'No se encontró el usuario.'];
            }

            $nombre = trim((string)$usuario['nombre']);
            $documento = trim((string)$usuario['documento']);
            if ($nombre === '' || $documento === '') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'PERFIL_INCOMPLETO', 'mensaje' => 'Completa tus datos de identidad en Entre Vecinos antes de registrar una cuenta bancaria.'];
            }

            $pendiente = $this->dblink->prepare(
                "SELECT 1 FROM retiro_solicitud
                 WHERE codigo_usuario = :u
                   AND estado IN ('solicitado','programado','observado')
                 LIMIT 1 FOR UPDATE"
            );
            $pendiente->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
            $pendiente->execute();
            if ($pendiente->fetchColumn()) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'RETIRO_PENDIENTE', 'mensaje' => 'No puedes modificar tu cuenta bancaria mientras tengas un retiro pendiente.'];
            }

            $actual = $this->cuentaActiva($codigoUsuario, true);

            $duplicada = $this->dblink->prepare(
                "SELECT codigo_cuenta_bancaria
                 FROM usuario_cuenta_bancaria
                 WHERE estado_registro = 1
                   AND codigo_usuario <> :u
                   AND (cci = :cci OR (banco = :banco AND numero_cuenta = :numero))
                 LIMIT 1 FOR UPDATE"
            );
            $duplicada->execute([':u' => $codigoUsuario, ':cci' => $cci, ':banco' => $banco, ':numero' => $numero]);
            if ($duplicada->fetchColumn()) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'CUENTA_EN_USO', 'mensaje' => 'La cuenta bancaria o CCI ya se encuentra registrada por otro usuario.'];
            }

            if ($actual) {
                $sinCambios =
                    strcasecmp((string)$actual['banco'], $banco) === 0 &&
                    (string)$actual['tipo_cuenta'] === $tipo &&
                    (string)$actual['numero_cuenta'] === $numero &&
                    (string)$actual['cci'] === $cci &&
                    (string)$actual['titular_nombre'] === $nombre &&
                    (string)$actual['titular_documento'] === $documento;

                if ($sinCambios) {
                    $this->dblink->commit();
                    return ['ok' => true, 'mensaje' => 'La cuenta bancaria ya se encuentra registrada.', 'data' => $this->cuentaPublica($actual, true)];
                }

                // No modificamos la fila histórica que pudo haber sido usada por retiros ya pagados.
                // La cuenta anterior queda inactiva y la edición crea una nueva versión pendiente de validación.
                $desactivar = $this->dblink->prepare(
                    "UPDATE usuario_cuenta_bancaria
                     SET estado_registro = 0, updated_at = NOW()
                     WHERE codigo_cuenta_bancaria = :id"
                );
                $desactivar->bindValue(':id', (int)$actual['codigo_cuenta_bancaria'], PDO::PARAM_INT);
                $desactivar->execute();

                $ins = $this->dblink->prepare(
                    "INSERT INTO usuario_cuenta_bancaria
                    (codigo_usuario, banco, tipo_cuenta, numero_cuenta, cci,
                     titular_nombre, titular_documento, declara_titularidad,
                     estado_validacion, estado_registro)
                    VALUES
                    (:u, :banco, :tipo, :numero, :cci, :nombre, :documento, 1, 'pendiente', 1)"
                );
                $ins->execute([
                    ':u' => $codigoUsuario, ':banco' => $banco, ':tipo' => $tipo,
                    ':numero' => $numero, ':cci' => $cci, ':nombre' => $nombre, ':documento' => $documento
                ]);
                $id = (int)$this->dblink->lastInsertId();
            } else {
                $ins = $this->dblink->prepare(
                    "INSERT INTO usuario_cuenta_bancaria
                    (codigo_usuario, banco, tipo_cuenta, numero_cuenta, cci,
                     titular_nombre, titular_documento, declara_titularidad,
                     estado_validacion, estado_registro)
                    VALUES
                    (:u, :banco, :tipo, :numero, :cci, :nombre, :documento, 1, 'pendiente', 1)"
                );
                $ins->execute([
                    ':u' => $codigoUsuario, ':banco' => $banco, ':tipo' => $tipo,
                    ':numero' => $numero, ':cci' => $cci, ':nombre' => $nombre, ':documento' => $documento
                ]);
                $id = (int)$this->dblink->lastInsertId();
            }

            $this->dblink->commit();
            $cuenta = $this->cuentaActiva($codigoUsuario, false);
            return [
                'ok' => true,
                'mensaje' => 'Cuenta bancaria registrada. El Administrador EV debe validarla antes de tu primer retiro.',
                'data' => $this->cuentaPublica($cuenta, true),
                'codigo_cuenta_bancaria' => $id,
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][Retiro][guardarCuentaUsuario] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_GUARDAR_CUENTA', 'mensaje' => 'No se pudo guardar la cuenta bancaria.'];
        }
    }

    public function solicitarRetiro(int $codigoUsuario): array
    {
        $this->procesarSolicitudesVencidas();
        $corte = $this->obtenerCorteActual();
        if (!$corte) {
            return ['ok' => false, 'error' => 'FUERA_DE_CORTE', 'mensaje' => 'En este momento no hay una ventana de retiro abierta.'];
        }

        try {
            $this->dblink->beginTransaction();
            $lockUsuario = $this->dblink->prepare("SELECT codigo_usuario FROM usuario WHERE codigo_usuario = :u LIMIT 1 FOR UPDATE");
            $lockUsuario->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
            $lockUsuario->execute();
            if (!$lockUsuario->fetchColumn()) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'USUARIO_NO_ENCONTRADO', 'mensaje' => 'No se encontró el usuario.'];
            }

            $cuenta = $this->cuentaActiva($codigoUsuario, true);
            if (!$cuenta || (string)$cuenta['estado_validacion'] !== 'validada') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'CUENTA_NO_VALIDADA', 'mensaje' => 'Registra y valida tu cuenta bancaria antes de solicitar un retiro.'];
            }

            $exist = $this->solicitudDelCorte($codigoUsuario, $corte);
            if ($exist) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'RETIRO_YA_SOLICITADO', 'mensaje' => 'Ya registraste tu retiro para el corte actual.'];
            }

            $billetera = $this->obtenerBilleteraBloqueada($codigoUsuario);
            $saldo = round((float)$billetera['saldo_actual'], 2);
            $minimo = round((float)$corte['saldo_minimo'], 2);
            if ($saldo <= $minimo) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SALDO_NO_RETIRABLE', 'mensaje' => 'Necesitas tener más de S/ ' . number_format($minimo, 2) . ' para solicitar el retiro.'];
            }

            $estimado = round(max(0.0, $saldo - $minimo), 2);
            $ins = $this->dblink->prepare(
                "INSERT INTO retiro_solicitud
                (codigo_usuario, codigo_cuenta_bancaria, codigo_retiro_configuracion,
                 jornada_nombre, corte_inicio, corte_fin, fecha_pago_programada,
                 fecha_solicitud, saldo_solicitud, saldo_minimo_snapshot,
                 monto_estimado, estado)
                VALUES
                (:u, :cuenta, :cfg, :jornada, :inicio, :fin, :pago,
                 NOW(), :saldo, :minimo, :estimado, 'solicitado')"
            );
            $ins->execute([
                ':u' => $codigoUsuario,
                ':cuenta' => (int)$cuenta['codigo_cuenta_bancaria'],
                ':cfg' => (int)$corte['codigo_retiro_configuracion'],
                ':jornada' => (string)$corte['nombre_jornada'],
                ':inicio' => (string)$corte['corte_inicio'],
                ':fin' => (string)$corte['corte_fin'],
                ':pago' => (string)$corte['fecha_pago_programada'],
                ':saldo' => $saldo,
                ':minimo' => $minimo,
                ':estimado' => $estimado,
            ]);
            $id = (int)$this->dblink->lastInsertId();
            $this->dblink->commit();

            return [
                'ok' => true,
                'mensaje' => 'Tu retiro quedó registrado para el corte actual. El monto final se calculará al cierre.',
                'data' => [
                    'codigo_retiro' => $id,
                    'monto_estimado' => $estimado,
                    'saldo_minimo' => $minimo,
                    'corte_fin' => $corte['corte_fin'],
                    'fecha_pago_programada' => $corte['fecha_pago_programada'],
                ],
            ];
        } catch (PDOException $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            if ((string)$e->getCode() === '23000') {
                return ['ok' => false, 'error' => 'RETIRO_YA_SOLICITADO', 'mensaje' => 'Ya registraste tu retiro para el corte actual.'];
            }
            error_log('[EV][Retiro][solicitarRetiro] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_SOLICITAR_RETIRO', 'mensaje' => 'No se pudo registrar el retiro.'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][Retiro][solicitarRetiro] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_SOLICITAR_RETIRO', 'mensaje' => 'No se pudo registrar el retiro.'];
        }
    }

    public function procesarSolicitudesVencidas(): void
    {
        $ahora = $this->formatDb($this->now());
        $st = $this->dblink->prepare(
            "SELECT codigo_retiro
             FROM retiro_solicitud
             WHERE estado = 'solicitado'
               AND corte_fin < :ahora
             ORDER BY codigo_retiro
             LIMIT 200"
        );
        $st->bindValue(':ahora', $ahora, PDO::PARAM_STR);
        $st->execute();
        $ids = array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN) ?: []);

        foreach ($ids as $id) {
            try {
                $this->dblink->beginTransaction();
                $lock = $this->dblink->prepare("SELECT * FROM retiro_solicitud WHERE codigo_retiro = :id FOR UPDATE");
                $lock->bindValue(':id', $id, PDO::PARAM_INT);
                $lock->execute();
                $retiro = $lock->fetch(PDO::FETCH_ASSOC);
                if (!$retiro || (string)$retiro['estado'] !== 'solicitado') {
                    $this->dblink->rollBack();
                    continue;
                }

                $b = $this->obtenerBilleteraBloqueada((int)$retiro['codigo_usuario']);
                $saldoActual = round((float)$b['saldo_actual'], 2);
                // El monto se calcula con el saldo histórico exacto al cierre del corte.
                // Los abonos posteriores al corte permanecen disponibles para el siguiente retiro.
                $saldoCierre = $this->obtenerSaldoAlCierre((int)$b['codigo_billetera'], (string)$retiro['corte_fin']);
                $minimo = round((float)$retiro['saldo_minimo_snapshot'], 2);
                $monto = round(max(0.0, $saldoCierre - $minimo), 2);
                $saldoDespues = $saldoActual;
                $estado = 'sin_saldo';
                $observacion = null;

                if ($monto > 0) {
                    if ($saldoActual + 0.0001 < $monto) {
                        // Este caso solo debería aparecer si el cierre no se procesó oportunamente
                        // y el usuario consumió fondos después del corte. No fabricamos saldo ni
                        // reducimos silenciosamente el pago: lo dejamos observado para trazabilidad.
                        $estado = 'observado';
                        $observacion = 'No fue posible reservar automáticamente el monto calculado al cierre. Revisión administrativa requerida.';
                    } else {
                        $saldoDespues = round($saldoActual - $monto, 2);
                        $this->registrarMovimiento(
                            (int)$b['codigo_billetera'], 'D', $monto, $saldoActual, $saldoDespues,
                            'RETIRO_RESERVA', 'Reserva de saldo para retiro #' . $id, $id
                        );
                        $this->actualizarSaldo((int)$b['codigo_billetera'], $saldoDespues);
                        $estado = 'programado';
                    }
                }

                $up = $this->dblink->prepare(
                    "UPDATE retiro_solicitud
                     SET saldo_cierre = :saldo_cierre,
                         monto_final = :monto,
                         estado = :estado,
                         observacion_admin = :observacion,
                         fecha_cierre_procesado = NOW(),
                         updated_at = NOW()
                     WHERE codigo_retiro = :id"
                );
                $up->bindValue(':saldo_cierre', $saldoCierre);
                $up->bindValue(':monto', $monto);
                $up->bindValue(':estado', $estado, PDO::PARAM_STR);
                $up->bindValue(':observacion', $observacion, $observacion === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $up->bindValue(':id', $id, PDO::PARAM_INT);
                $up->execute();
                $this->dblink->commit();
            } catch (Throwable $e) {
                if ($this->dblink->inTransaction()) $this->dblink->rollBack();
                error_log('[EV][Retiro][procesarSolicitudesVencidas][' . $id . '] ' . $e->getMessage());
            }
        }
    }

    public function resumenUsuario(int $codigoUsuario): array
    {
        $this->procesarSolicitudesVencidas();
        $saldo = $this->obtenerSaldoUsuario($codigoUsuario);
        $cuenta = $this->cuentaActiva($codigoUsuario, false);
        $corte = $this->obtenerCorteActual();
        $solicitud = $corte ? $this->solicitudDelCorte($codigoUsuario, $corte) : null;

        $st = $this->dblink->prepare(
            "SELECT COALESCE(SUM(monto_final),0)
             FROM retiro_solicitud
             WHERE codigo_usuario = :u AND estado IN ('programado','observado')"
        );
        $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();
        $enRetiro = round((float)$st->fetchColumn(), 2);

        $stBloqueoCuenta = $this->dblink->prepare(
            "SELECT COUNT(*)
             FROM retiro_solicitud
             WHERE codigo_usuario = :u
               AND estado IN ('solicitado','programado','observado')"
        );
        $stBloqueoCuenta->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $stBloqueoCuenta->execute();
        $cuentaBloqueadaPorRetiro = (int)$stBloqueoCuenta->fetchColumn() > 0;

        $hist = $this->dblink->prepare(
            "SELECT codigo_retiro, jornada_nombre, corte_inicio, corte_fin,
                    fecha_pago_programada, fecha_solicitud, saldo_solicitud,
                    saldo_cierre, saldo_minimo_snapshot, monto_estimado, monto_final,
                    estado, fecha_pago, numero_operacion, comprobante_path, observacion_admin
             FROM retiro_solicitud
             WHERE codigo_usuario = :u
             ORDER BY codigo_retiro DESC
             LIMIT 20"
        );
        $hist->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $hist->execute();
        $retiros = $hist->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $stUsuario = $this->dblink->prepare("SELECT nombre, documento FROM usuario WHERE codigo_usuario = :u LIMIT 1");
        $stUsuario->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $stUsuario->execute();
        $titular = $stUsuario->fetch(PDO::FETCH_ASSOC) ?: ['nombre' => '', 'documento' => ''];

        return [
            'saldo_actual' => $saldo,
            'saldo_en_retiro' => $enRetiro,
            'titular_usuario' => [
                'nombre' => (string)($titular['nombre'] ?? ''),
                'documento' => (string)($titular['documento'] ?? ''),
            ],
            'cuenta' => $this->cuentaPublica($cuenta, true),
            'cuenta_bloqueada_por_retiro' => $cuentaBloqueadaPorRetiro,
            'corte_actual' => $corte,
            'solicitud_actual' => $solicitud ? $this->formatearRetiro($solicitud) : null,
            'puede_solicitar' => $corte !== null
                && $solicitud === null
                && $cuenta !== null
                && (string)$cuenta['estado_validacion'] === 'validada'
                && $saldo > round((float)($corte['saldo_minimo'] ?? self::SALDO_MINIMO_FALLBACK), 2),
            'retiros' => array_map(fn(array $r) => $this->formatearRetiro($r), $retiros),
        ];
    }

    private function formatearRetiro(array $r): array
    {
        return [
            'codigo_retiro' => (int)$r['codigo_retiro'],
            'codigo' => 'RET-' . str_pad((string)$r['codigo_retiro'], 6, '0', STR_PAD_LEFT),
            'jornada_nombre' => (string)($r['jornada_nombre'] ?? ''),
            'corte_inicio' => $r['corte_inicio'] ?? null,
            'corte_fin' => $r['corte_fin'] ?? null,
            'fecha_pago_programada' => $r['fecha_pago_programada'] ?? null,
            'fecha_solicitud' => $r['fecha_solicitud'] ?? null,
            'saldo_solicitud' => round((float)($r['saldo_solicitud'] ?? 0), 2),
            'saldo_cierre' => isset($r['saldo_cierre']) ? round((float)$r['saldo_cierre'], 2) : null,
            'saldo_minimo' => round((float)($r['saldo_minimo_snapshot'] ?? self::SALDO_MINIMO_FALLBACK), 2),
            'monto_estimado' => round((float)($r['monto_estimado'] ?? 0), 2),
            'monto_final' => isset($r['monto_final']) ? round((float)$r['monto_final'], 2) : null,
            'estado' => (string)($r['estado'] ?? ''),
            'fecha_pago' => $r['fecha_pago'] ?? null,
            'numero_operacion' => (string)($r['numero_operacion'] ?? ''),
            'comprobante_path' => (string)($r['comprobante_path'] ?? ''),
            'observacion' => (string)($r['observacion_admin'] ?? ''),
        ];
    }

    public function listarGestion(array $filtros, bool $esSoporte = false): array
    {
        $this->procesarSolicitudesVencidas();
        $estado = strtolower(trim((string)($filtros['estado'] ?? '')));
        $permitidos = ['solicitado','programado','pagado','observado','cancelado','sin_saldo'];
        $where = ['1=1'];
        $params = [];
        if (in_array($estado, $permitidos, true)) {
            $where[] = 'r.estado = :estado';
            $params[':estado'] = $estado;
        }
        $q = trim((string)($filtros['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(u.nombre LIKE :q OR u.documento LIKE :q OR CAST(r.codigo_retiro AS CHAR) LIKE :q)';
            $params[':q'] = '%' . mb_substr($q, 0, 80, 'UTF-8') . '%';
        }

        $fechaPago = trim((string)($filtros['fecha_pago'] ?? ''));
        if ($fechaPago !== '') {
            $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $fechaPago, new DateTimeZone(self::TZ));
            $errores = DateTimeImmutable::getLastErrors();
            $fechaValida = $fecha instanceof DateTimeImmutable
                && ($errores === false || (((int)($errores['warning_count'] ?? 0)) === 0 && ((int)($errores['error_count'] ?? 0)) === 0))
                && $fecha->format('Y-m-d') === $fechaPago;
            if ($fechaValida) {
                $where[] = 'r.fecha_pago_programada = :fecha_pago';
                $params[':fecha_pago'] = $fechaPago;
            }
        }

        $sql = "SELECT r.*, u.nombre AS usuario_nombre, u.documento AS usuario_documento,
                       c.banco, c.tipo_cuenta, c.numero_cuenta, c.cci,
                       c.titular_nombre, c.titular_documento, c.estado_validacion AS cuenta_estado
                FROM retiro_solicitud r
                INNER JOIN usuario u ON u.codigo_usuario = r.codigo_usuario
                INNER JOIN usuario_cuenta_bancaria c ON c.codigo_cuenta_bancaria = r.codigo_cuenta_bancaria
                WHERE " . implode(' AND ', $where) . "
                ORDER BY r.fecha_pago_programada DESC, r.codigo_retiro DESC
                LIMIT 300";
        $st = $this->dblink->prepare($sql);
        foreach ($params as $k => $v) $st->bindValue($k, $v, PDO::PARAM_STR);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $items = [];
        foreach ($rows as $r) {
            $item = $this->formatearRetiro($r);
            $item['usuario'] = [
                'codigo_usuario' => (int)$r['codigo_usuario'],
                'nombre' => (string)$r['usuario_nombre'],
                'documento' => $esSoporte ? $this->enmascarar((string)$r['usuario_documento'], 2) : (string)$r['usuario_documento'],
            ];
            if ($esSoporte) {
                // El comprobante puede contener datos bancarios adicionales; Soporte
                // consulta el estado, pero el archivo queda restringido a Admin/propietario.
                $item['comprobante_path'] = '';
            }
            $item['cuenta'] = [
                'banco' => (string)$r['banco'],
                'tipo_cuenta' => (string)$r['tipo_cuenta'],
                'numero_cuenta' => $esSoporte ? $this->enmascarar((string)$r['numero_cuenta']) : (string)$r['numero_cuenta'],
                'cci' => $esSoporte ? $this->enmascarar((string)$r['cci']) : (string)$r['cci'],
                'titular_nombre' => (string)$r['titular_nombre'],
                'titular_documento' => $esSoporte ? $this->enmascarar((string)$r['titular_documento'], 2) : (string)$r['titular_documento'],
                'estado' => (string)$r['cuenta_estado'],
            ];
            $items[] = $item;
        }

        $res = $this->dblink->query(
            "SELECT
                SUM(estado = 'solicitado') AS solicitados,
                SUM(estado = 'programado') AS programados,
                SUM(estado = 'observado') AS observados,
                SUM(estado = 'pagado') AS pagados,
                COALESCE(SUM(CASE WHEN estado IN ('programado','observado') THEN monto_final ELSE 0 END),0) AS monto_pendiente
             FROM retiro_solicitud"
        )->fetch(PDO::FETCH_ASSOC) ?: [];

        return ['items' => $items, 'resumen' => [
            'solicitados' => (int)($res['solicitados'] ?? 0),
            'programados' => (int)($res['programados'] ?? 0),
            'observados' => (int)($res['observados'] ?? 0),
            'pagados' => (int)($res['pagados'] ?? 0),
            'monto_pendiente' => round((float)($res['monto_pendiente'] ?? 0), 2),
        ]];
    }

    public function listarCuentasPendientes(): array
    {
        $st = $this->dblink->query(
            "SELECT c.*, u.nombre AS usuario_nombre, u.documento AS usuario_documento
             FROM usuario_cuenta_bancaria c
             INNER JOIN usuario u ON u.codigo_usuario = c.codigo_usuario
             WHERE c.estado_registro = 1
             ORDER BY FIELD(c.estado_validacion,'pendiente','observada','validada'), c.updated_at DESC
             LIMIT 300"
        );
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return array_map(function(array $r): array {
            return [
                'codigo_cuenta_bancaria' => (int)$r['codigo_cuenta_bancaria'],
                'codigo_usuario' => (int)$r['codigo_usuario'],
                'usuario_nombre' => (string)$r['usuario_nombre'],
                'usuario_documento' => (string)$r['usuario_documento'],
                'cuenta' => $this->cuentaPublica($r, true),
                'updated_at' => $r['updated_at'] ?? null,
            ];
        }, $rows);
    }

    public function validarCuentaAdmin(int $codigoCuenta, int $codigoAdmin, string $accion, string $observacion = ''): array
    {
        $accion = strtolower(trim($accion));
        if (!in_array($accion, ['validar','observar'], true)) {
            return ['ok' => false, 'mensaje' => 'Acción inválida.'];
        }
        if ($accion === 'observar' && trim($observacion) === '') {
            return ['ok' => false, 'mensaje' => 'Indica el motivo de la observación.'];
        }

        try {
            $this->dblink->beginTransaction();
            $st = $this->dblink->prepare("SELECT * FROM usuario_cuenta_bancaria WHERE codigo_cuenta_bancaria = :id AND estado_registro = 1 FOR UPDATE");
            $st->bindValue(':id', $codigoCuenta, PDO::PARAM_INT);
            $st->execute();
            $cuenta = $st->fetch(PDO::FETCH_ASSOC);
            if (!$cuenta) {
                $this->dblink->rollBack();
                return ['ok' => false, 'mensaje' => 'No se encontró la cuenta bancaria.'];
            }

            // Protección para cuentas registradas antes del endurecimiento de validaciones.
            // El Administrador no puede validar un formato que el flujo actual rechazaría.
            if ($accion === 'validar') {
                $formato = $this->validarDatosCuentaBancaria(
                    trim((string)$cuenta['banco']),
                    strtolower(trim((string)$cuenta['tipo_cuenta'])),
                    trim((string)$cuenta['numero_cuenta']),
                    trim((string)$cuenta['cci'])
                );
                if (!($formato['ok'] ?? false)) {
                    $this->dblink->rollBack();
                    return [
                        'ok' => false,
                        'error' => 'CUENTA_FORMATO_INVALIDO',
                        'mensaje' => 'La cuenta no puede validarse: ' . (string)($formato['mensaje'] ?? 'revisa sus datos bancarios.') . ' Solicita al vecino que la corrija.',
                    ];
                }
            }

            $estado = $accion === 'validar' ? 'validada' : 'observada';
            $up = $this->dblink->prepare(
                "UPDATE usuario_cuenta_bancaria
                 SET estado_validacion = :estado,
                     observacion_admin = :obs,
                     validado_por = :admin,
                     fecha_validacion = NOW(),
                     updated_at = NOW()
                 WHERE codigo_cuenta_bancaria = :id"
            );
            $up->bindValue(':estado', $estado, PDO::PARAM_STR);
            $up->bindValue(':obs', trim($observacion) !== '' ? mb_substr(trim($observacion), 0, 500, 'UTF-8') : null, trim($observacion) !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $up->bindValue(':admin', $codigoAdmin, PDO::PARAM_INT);
            $up->bindValue(':id', $codigoCuenta, PDO::PARAM_INT);
            $up->execute();

            $notif = new Notificacion($this->dblink);
            $notif->crear([
                'codigo_usuario' => (int)$cuenta['codigo_usuario'],
                'categoria' => Notificacion::CAT_BILLETERA,
                'subcategoria' => $estado === 'validada' ? 'cuenta_bancaria_validada' : 'cuenta_bancaria_observada',
                'referencia_id' => $codigoCuenta,
                'titulo' => $estado === 'validada' ? 'Cuenta bancaria validada' : 'Revisa tu cuenta bancaria',
                'mensaje' => $estado === 'validada'
                    ? 'Tu cuenta bancaria fue validada y ya puedes utilizarla para solicitar retiros.'
                    : 'Tu cuenta bancaria fue observada. Revisa la observación y corrige los datos antes de solicitar un retiro.',
                'payload' => ['ruta' => '/billetera/retirar', 'estado' => $estado],
            ]);

            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => $estado === 'validada' ? 'Cuenta bancaria validada.' : 'Cuenta bancaria observada.'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][Retiro][validarCuentaAdmin] ' . $e->getMessage());
            return ['ok' => false, 'mensaje' => 'No se pudo actualizar la cuenta bancaria.'];
        }
    }

    public function configuraciones(): array
    {
        $st = $this->dblink->query(
            "SELECT codigo_retiro_configuracion, nombre_jornada, dia_pago,
                    dia_inicio_corte, TIME_FORMAT(hora_inicio_corte,'%H:%i') AS hora_inicio_corte,
                    dia_fin_corte, TIME_FORMAT(hora_fin_corte,'%H:%i') AS hora_fin_corte,
                    saldo_minimo, activo, updated_at
             FROM retiro_configuracion
             ORDER BY codigo_retiro_configuracion"
        );
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function segundosSemana(int $diaIso, string $hora): int
    {
        [$h, $m, $s] = array_pad(array_map('intval', explode(':', $hora)), 3, 0);
        return (($diaIso - 1) * 86400) + ($h * 3600) + ($m * 60) + $s;
    }

    private function ventanasSemanalesSeSuperponen(
        int $diaInicioA, string $horaInicioA, int $diaFinA, string $horaFinA,
        int $diaInicioB, string $horaInicioB, int $diaFinB, string $horaFinB
    ): bool {
        $semana = 7 * 86400;
        $aInicio = $this->segundosSemana($diaInicioA, $horaInicioA);
        $aFin = $this->segundosSemana($diaFinA, $horaFinA);
        if ($aFin < $aInicio) $aFin += $semana;

        $bInicioBase = $this->segundosSemana($diaInicioB, $horaInicioB);
        $bFinBase = $this->segundosSemana($diaFinB, $horaFinB);
        if ($bFinBase < $bInicioBase) $bFinBase += $semana;

        foreach ([-$semana, 0, $semana] as $offset) {
            $bInicio = $bInicioBase + $offset;
            $bFin = $bFinBase + $offset;
            if ($aInicio <= $bFin && $bInicio <= $aFin) {
                return true;
            }
        }
        return false;
    }

    public function guardarConfiguracion(int $codigo, array $data, int $codigoAdmin): array
    {
        $nombre = trim((string)($data['nombre_jornada'] ?? ''));
        $diaInicio = (int)($data['dia_inicio_corte'] ?? 0);
        $diaFin = (int)($data['dia_fin_corte'] ?? 0);
        $horaInicio = trim((string)($data['hora_inicio_corte'] ?? ''));
        $horaFin = trim((string)($data['hora_fin_corte'] ?? ''));
        $activo = filter_var($data['activo'] ?? true, FILTER_VALIDATE_BOOL) ? 1 : 0;

        if ($codigo <= 0 || $nombre === '' || !in_array($diaInicio, range(1,7), true) || !in_array($diaFin, range(1,7), true)) {
            return ['ok' => false, 'mensaje' => 'Revisa los días configurados para la jornada.'];
        }
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $horaInicio) || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $horaFin)) {
            return ['ok' => false, 'mensaje' => 'Revisa las horas de inicio y fin del corte.'];
        }

        // No se modifica una ventana que ya tiene solicitudes abiertas: cada solicitud
        // conserva el corte exacto con el que fue registrada y evitamos dividir una jornada.
        $abiertas = $this->dblink->prepare(
            "SELECT COUNT(*)
             FROM retiro_solicitud
             WHERE codigo_retiro_configuracion = :id
               AND estado = 'solicitado'
               AND corte_fin >= NOW()"
        );
        $abiertas->bindValue(':id', $codigo, PDO::PARAM_INT);
        $abiertas->execute();
        if ((int)$abiertas->fetchColumn() > 0) {
            return ['ok' => false, 'mensaje' => 'Esta jornada tiene solicitudes abiertas. Espera al cierre antes de cambiar su ventana.'];
        }

        // Martes/viernes y S/20 son reglas del piloto, no parámetros operativos editables.
        $actualSt = $this->dblink->prepare(
            "SELECT dia_pago FROM retiro_configuracion WHERE codigo_retiro_configuracion = :id LIMIT 1"
        );
        $actualSt->bindValue(':id', $codigo, PDO::PARAM_INT);
        $actualSt->execute();
        $diaPago = (int)$actualSt->fetchColumn();
        if (!in_array($diaPago, [2, 5], true)) {
            return ['ok' => false, 'mensaje' => 'La jornada no corresponde a un día de pago habilitado para el piloto.'];
        }
        $minimo = self::SALDO_MINIMO_FALLBACK;

        if ($activo === 1) {
            $otros = $this->dblink->prepare(
                "SELECT codigo_retiro_configuracion, dia_inicio_corte, hora_inicio_corte, dia_fin_corte, hora_fin_corte
                 FROM retiro_configuracion
                 WHERE activo = 1 AND codigo_retiro_configuracion <> :id"
            );
            $otros->bindValue(':id', $codigo, PDO::PARAM_INT);
            $otros->execute();
            foreach ($otros->fetchAll(PDO::FETCH_ASSOC) ?: [] as $otra) {
                if ($this->ventanasSemanalesSeSuperponen(
                    $diaInicio, $horaInicio . ':00', $diaFin, $horaFin . ':00',
                    (int)$otra['dia_inicio_corte'], (string)$otra['hora_inicio_corte'],
                    (int)$otra['dia_fin_corte'], (string)$otra['hora_fin_corte']
                )) {
                    return ['ok' => false, 'mensaje' => 'El corte se superpone con otra jornada activa. Ajusta el inicio o fin antes de guardar.'];
                }
            }
        }

        $st = $this->dblink->prepare(
            "UPDATE retiro_configuracion
             SET nombre_jornada = :nombre,
                 dia_inicio_corte = :dia_inicio,
                 hora_inicio_corte = :hora_inicio,
                 dia_fin_corte = :dia_fin,
                 hora_fin_corte = :hora_fin,
                 saldo_minimo = :minimo,
                 activo = :activo,
                 actualizado_por = :admin,
                 updated_at = NOW()
             WHERE codigo_retiro_configuracion = :id"
        );
        $st->execute([
            ':nombre' => mb_substr($nombre, 0, 80, 'UTF-8'),
            ':dia_inicio' => $diaInicio, ':hora_inicio' => $horaInicio . ':00',
            ':dia_fin' => $diaFin, ':hora_fin' => $horaFin . ':00', ':minimo' => $minimo,
            ':activo' => $activo, ':admin' => $codigoAdmin, ':id' => $codigo,
        ]);
        return ['ok' => true, 'mensaje' => 'Configuración de corte actualizada.'];
    }

    public function marcarObservado(int $codigoRetiro, int $codigoAdmin, string $observacion): array
    {
        $observacion = trim($observacion);
        if ($observacion === '') return ['ok' => false, 'mensaje' => 'Indica el motivo de la observación.'];
        $st = $this->dblink->prepare(
            "UPDATE retiro_solicitud
             SET estado = 'observado', observacion_admin = :obs, procesado_por = :admin, updated_at = NOW()
             WHERE codigo_retiro = :id AND estado = 'programado'"
        );
        $st->execute([':obs' => mb_substr($observacion, 0, 500, 'UTF-8'), ':admin' => $codigoAdmin, ':id' => $codigoRetiro]);
        return $st->rowCount() === 1
            ? ['ok' => true, 'mensaje' => 'Retiro observado. El importe continúa reservado hasta resolver la incidencia.']
            : ['ok' => false, 'mensaje' => 'El retiro ya no se encuentra disponible para observar.'];
    }

    public function marcarPagado(int $codigoRetiro, int $codigoAdmin, string $numeroOperacion, string $comprobantePath): array
    {
        $numeroOperacion = trim($numeroOperacion);
        if ($numeroOperacion === '' || $comprobantePath === '') {
            return ['ok' => false, 'mensaje' => 'Registra el número de operación y el comprobante del pago.'];
        }

        try {
            $this->dblink->beginTransaction();
            $st = $this->dblink->prepare("SELECT * FROM retiro_solicitud WHERE codigo_retiro = :id FOR UPDATE");
            $st->bindValue(':id', $codigoRetiro, PDO::PARAM_INT);
            $st->execute();
            $r = $st->fetch(PDO::FETCH_ASSOC);
            if (!$r || !in_array((string)$r['estado'], ['programado','observado'], true) || (float)$r['monto_final'] <= 0) {
                $this->dblink->rollBack();
                return ['ok' => false, 'mensaje' => 'El retiro no está disponible para registrar el pago.'];
            }
            if (!$this->existeMovimientoRetiro($codigoRetiro, 'RETIRO_RESERVA')) {
                $this->dblink->rollBack();
                return ['ok' => false, 'mensaje' => 'El retiro requiere revisión porque su saldo aún no se encuentra reservado.'];
            }

            $up = $this->dblink->prepare(
                "UPDATE retiro_solicitud
                 SET estado = 'pagado', fecha_pago = NOW(), numero_operacion = :op,
                     comprobante_path = :comp, procesado_por = :admin,
                     observacion_admin = NULL, updated_at = NOW()
                 WHERE codigo_retiro = :id"
            );
            $up->execute([':op' => mb_substr($numeroOperacion, 0, 100, 'UTF-8'), ':comp' => $comprobantePath, ':admin' => $codigoAdmin, ':id' => $codigoRetiro]);

            (new Notificacion($this->dblink))->crear([
                'codigo_usuario' => (int)$r['codigo_usuario'],
                'categoria' => Notificacion::CAT_BILLETERA,
                'subcategoria' => 'retiro_pagado',
                'referencia_id' => $codigoRetiro,
                'titulo' => 'Retiro pagado',
                'mensaje' => 'EV registró el pago de tu retiro por S/ ' . number_format((float)$r['monto_final'], 2) . '.',
                'payload' => ['ruta' => '/billetera/retirar', 'codigo_retiro' => $codigoRetiro],
            ]);

            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'Pago registrado correctamente.'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][Retiro][marcarPagado] ' . $e->getMessage());
            return ['ok' => false, 'mensaje' => 'No se pudo registrar el pago.'];
        }
    }

    public function cancelarYReintegrar(int $codigoRetiro, int $codigoAdmin, string $motivo): array
    {
        $motivo = trim($motivo);
        if ($motivo === '') return ['ok' => false, 'mensaje' => 'Indica el motivo de la cancelación.'];
        try {
            $this->dblink->beginTransaction();
            $st = $this->dblink->prepare("SELECT * FROM retiro_solicitud WHERE codigo_retiro = :id FOR UPDATE");
            $st->bindValue(':id', $codigoRetiro, PDO::PARAM_INT);
            $st->execute();
            $r = $st->fetch(PDO::FETCH_ASSOC);
            if (!$r || !in_array((string)$r['estado'], ['programado','observado'], true)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'mensaje' => 'El retiro ya no puede cancelarse.'];
            }
            $monto = round((float)$r['monto_final'], 2);
            $saldoFueReservado = $this->existeMovimientoRetiro($codigoRetiro, 'RETIRO_RESERVA');
            if ($monto > 0 && $saldoFueReservado) {
                $b = $this->obtenerBilleteraBloqueada((int)$r['codigo_usuario']);
                $antes = round((float)$b['saldo_actual'], 2);
                $despues = round($antes + $monto, 2);
                $this->registrarMovimiento((int)$b['codigo_billetera'], 'C', $monto, $antes, $despues,
                    'RETIRO_REINTEGRO', 'Reintegro por cancelación de retiro #' . $codigoRetiro, $codigoRetiro);
                $this->actualizarSaldo((int)$b['codigo_billetera'], $despues);
            }
            $up = $this->dblink->prepare(
                "UPDATE retiro_solicitud
                 SET estado='cancelado', observacion_admin=:motivo, procesado_por=:admin, updated_at=NOW()
                 WHERE codigo_retiro=:id"
            );
            $up->execute([':motivo' => mb_substr($motivo,0,500,'UTF-8'), ':admin'=>$codigoAdmin, ':id'=>$codigoRetiro]);
            $this->dblink->commit();
            return [
                'ok' => true,
                'mensaje' => $saldoFueReservado
                    ? 'Retiro cancelado y saldo reintegrado a la billetera.'
                    : 'Retiro cancelado. No existía una reserva de saldo que reintegrar.',
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][Retiro][cancelarYReintegrar] '.$e->getMessage());
            return ['ok'=>false,'mensaje'=>'No se pudo cancelar el retiro.'];
        }
    }
}
