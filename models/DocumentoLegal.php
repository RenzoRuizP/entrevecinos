<?php
// models/DocumentoLegal.php

declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class DocumentoLegal extends Conexion
{
    private const TIPO_TERMINOS = 'terminos_condiciones';
    private const TIPO_PRIVACIDAD = 'politica_privacidad';

    private static ?array $configCache = null;
    private static bool $syncHecho = false;

    public static function configuracion(): array
    {
        if (self::$configCache !== null) {
            return self::$configCache;
        }

        $file = __DIR__ . '/../Config/documentos_legales.php';
        if (!is_file($file)) {
            throw new RuntimeException('No se encontró Config/documentos_legales.php');
        }

        $config = require $file;
        if (!is_array($config) || empty($config['documentos']) || !is_array($config['documentos'])) {
            throw new RuntimeException('La configuración de documentos legales es inválida.');
        }

        self::$configCache = $config;
        return self::$configCache;
    }

    public static function tiposObligatorios(): array
    {
        return [self::TIPO_TERMINOS, self::TIPO_PRIVACIDAD];
    }

    public static function textoConsentimientoPorTipo(string $tipo): string
    {
        $config = self::configuracion();
        return (string)($config['documentos'][$tipo]['texto_consentimiento'] ?? '');
    }

    public static function versionPorTipo(string $tipo): string
    {
        $config = self::configuracion();
        return (string)($config['documentos'][$tipo]['version'] ?? '');
    }

    /**
     * Crea o actualiza la metadata de las versiones configuradas.
     * El contenido legal permanece versionado en archivos PHP del proyecto.
     */
    public static function sincronizarConfiguracionEnPdo(PDO $pdo, bool $forzar = false): void
    {
        if (self::$syncHecho && !$forzar) {
            return;
        }

        $config = self::configuracion();
        $documentos = $config['documentos'] ?? [];

        foreach ($documentos as $tipo => $doc) {
            $tipo = trim((string)($doc['tipo'] ?? $tipo));
            $slug = trim((string)($doc['slug'] ?? ''));
            $titulo = trim((string)($doc['titulo'] ?? ''));
            $version = trim((string)($doc['version'] ?? ''));
            $archivo = basename(trim((string)($doc['archivo_contenido'] ?? '')));
            $texto = trim((string)($doc['texto_consentimiento'] ?? ''));
            $fechaPublicacion = (string)($doc['fecha_publicacion'] ?? date('Y-m-d H:i:s'));
            $fechaVigencia = (string)($doc['fecha_vigencia'] ?? $fechaPublicacion);
            $requiere = !empty($doc['requiere_aceptacion']) ? 1 : 0;

            if ($tipo === '' || $slug === '' || $titulo === '' || $version === '' || $archivo === '' || $texto === '') {
                throw new RuntimeException('La configuración de un documento legal está incompleta.');
            }

            $pseudoRow = [
                'tipo' => $tipo,
                'archivo_contenido' => $archivo,
            ];
            $contenido = self::renderizarContenidoDesdeFila($pseudoRow);
            $hash = hash('sha256', self::normalizarContenidoParaHash($contenido));

            $sql = "
                INSERT INTO documento_legal (
                    tipo,
                    slug,
                    titulo,
                    version,
                    archivo_contenido,
                    texto_consentimiento,
                    hash_documento,
                    estado,
                    requiere_aceptacion,
                    fecha_publicacion,
                    fecha_vigencia
                ) VALUES (
                    :tipo,
                    :slug,
                    :titulo,
                    :version,
                    :archivo,
                    :texto,
                    :hash_documento,
                    'vigente',
                    :requiere,
                    :fecha_publicacion,
                    :fecha_vigencia
                )
                ON DUPLICATE KEY UPDATE
                    slug = VALUES(slug),
                    titulo = VALUES(titulo),
                    archivo_contenido = VALUES(archivo_contenido),
                    texto_consentimiento = VALUES(texto_consentimiento),
                    hash_documento = VALUES(hash_documento),
                    estado = 'vigente',
                    requiere_aceptacion = VALUES(requiere_aceptacion),
                    fecha_publicacion = VALUES(fecha_publicacion),
                    fecha_vigencia = VALUES(fecha_vigencia),
                    updated_at = CURRENT_TIMESTAMP
            ";

            $st = $pdo->prepare($sql);
            $st->execute([
                ':tipo' => $tipo,
                ':slug' => $slug,
                ':titulo' => $titulo,
                ':version' => $version,
                ':archivo' => $archivo,
                ':texto' => $texto,
                ':hash_documento' => $hash,
                ':requiere' => $requiere,
                ':fecha_publicacion' => $fechaPublicacion,
                ':fecha_vigencia' => $fechaVigencia,
            ]);

            $stInactivos = $pdo->prepare(
                "UPDATE documento_legal
                 SET estado = 'inactivo', updated_at = CURRENT_TIMESTAMP
                 WHERE tipo = :tipo
                   AND version <> :version
                   AND estado = 'vigente'"
            );
            $stInactivos->execute([
                ':tipo' => $tipo,
                ':version' => $version,
            ]);
        }

        self::$syncHecho = true;
    }

    public function sincronizarConfiguracion(bool $forzar = false): void
    {
        self::sincronizarConfiguracionEnPdo($this->dblink, $forzar);
    }

    public function obtenerVigentePorTipo(string $tipo): ?array
    {
        $this->sincronizarConfiguracion();

        $st = $this->dblink->prepare(
            "SELECT *
             FROM documento_legal
             WHERE tipo = :tipo
               AND estado = 'vigente'
             ORDER BY fecha_vigencia DESC, codigo_documento_legal DESC
             LIMIT 1"
        );
        $st->execute([':tipo' => trim($tipo)]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return self::completarDocumento($row);
    }

    public function obtenerVigentePorSlug(string $slug): ?array
    {
        $this->sincronizarConfiguracion();

        $st = $this->dblink->prepare(
            "SELECT *
             FROM documento_legal
             WHERE slug = :slug
               AND estado = 'vigente'
             ORDER BY fecha_vigencia DESC, codigo_documento_legal DESC
             LIMIT 1"
        );
        $st->execute([':slug' => trim($slug)]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return self::completarDocumento($row);
    }

    public function obtenerVigentesObligatorios(): array
    {
        $this->sincronizarConfiguracion();

        $tipos = self::tiposObligatorios();
        $placeholders = implode(',', array_fill(0, count($tipos), '?'));

        $st = $this->dblink->prepare(
            "SELECT *
             FROM documento_legal
             WHERE estado = 'vigente'
               AND requiere_aceptacion = 1
               AND tipo IN ({$placeholders})
             ORDER BY FIELD(tipo, 'terminos_condiciones', 'politica_privacidad')"
        );
        $st->execute($tipos);

        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return array_map([self::class, 'completarDocumento'], $rows);
    }

    public function obtenerPendientesUsuario(int $codigoUsuario): array
    {
        if ($codigoUsuario <= 0) {
            return [];
        }

        $docs = $this->obtenerVigentesObligatorios();
        $pendientes = [];

        $sql = "
            SELECT codigo_aceptacion
            FROM usuario_documento_legal_aceptacion
            WHERE codigo_usuario = :usuario
              AND codigo_documento_legal = :documento
              AND hash_documento = :hash_documento
              AND aceptado = 1
            LIMIT 1
        ";
        $st = $this->dblink->prepare($sql);

        foreach ($docs as $doc) {
            $st->execute([
                ':usuario' => $codigoUsuario,
                ':documento' => (int)$doc['codigo_documento_legal'],
                ':hash_documento' => (string)$doc['hash_documento_calculado'],
            ]);

            if (!$st->fetchColumn()) {
                $pendientes[] = $doc;
            }
        }

        return $pendientes;
    }

    public function tienePendientesUsuario(int $codigoUsuario): bool
    {
        return count($this->obtenerPendientesUsuario($codigoUsuario)) > 0;
    }

    public function usuarioTieneAlgunaAceptacion(int $codigoUsuario): bool
    {
        if ($codigoUsuario <= 0) {
            return false;
        }

        $st = $this->dblink->prepare(
            "SELECT 1
             FROM usuario_documento_legal_aceptacion
             WHERE codigo_usuario = :usuario
               AND aceptado = 1
             LIMIT 1"
        );
        $st->execute([':usuario' => $codigoUsuario]);
        return (bool)$st->fetchColumn();
    }

    public function registrarAceptacionesVigentes(
        int $codigoUsuario,
        string $origen,
        ?string $ip,
        ?string $userAgent
    ): array {
        $iniciaTransaccion = !$this->dblink->inTransaction();

        if ($iniciaTransaccion) {
            $this->dblink->beginTransaction();
        }

        try {
            $resultado = self::registrarAceptacionesVigentesEnPdo(
                $this->dblink,
                $codigoUsuario,
                $origen,
                $ip,
                $userAgent
            );

            if ($iniciaTransaccion) {
                $this->dblink->commit();
            }

            return $resultado;
        } catch (Throwable $e) {
            if ($iniciaTransaccion && $this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            throw $e;
        }
    }

    public static function registrarAceptacionesVigentesEnPdo(
        PDO $pdo,
        int $codigoUsuario,
        string $origen,
        ?string $ip,
        ?string $userAgent
    ): array {
        if ($codigoUsuario <= 0) {
            throw new InvalidArgumentException('Usuario inválido para registrar consentimientos.');
        }

        self::sincronizarConfiguracionEnPdo($pdo, true);

        $tipos = self::tiposObligatorios();
        $placeholders = implode(',', array_fill(0, count($tipos), '?'));
        $stDocs = $pdo->prepare(
            "SELECT *
             FROM documento_legal
             WHERE estado = 'vigente'
               AND requiere_aceptacion = 1
               AND tipo IN ({$placeholders})
             ORDER BY FIELD(tipo, 'terminos_condiciones', 'politica_privacidad')"
        );
        $stDocs->execute($tipos);
        $docs = $stDocs->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $encontrados = array_values(array_unique(array_map(
            static fn(array $row): string => (string)($row['tipo'] ?? ''),
            $docs
        )));

        foreach ($tipos as $tipoRequerido) {
            if (!in_array($tipoRequerido, $encontrados, true)) {
                throw new RuntimeException('No existe una versión vigente del documento requerido: ' . $tipoRequerido);
            }
        }

        $origen = in_array($origen, ['registro', 'primer_ingreso', 'nueva_version'], true)
            ? $origen
            : 'primer_ingreso';

        $ip = self::limitarTexto($ip, 45);
        $userAgent = self::limitarTexto($userAgent, 500);
        $registradas = [];

        $sql = "
            INSERT IGNORE INTO usuario_documento_legal_aceptacion (
                codigo_usuario,
                codigo_documento_legal,
                tipo_documento,
                version_documento,
                hash_documento,
                texto_consentimiento,
                hash_consentimiento,
                aceptado,
                origen,
                ip_aceptacion,
                user_agent,
                fecha_aceptacion
            ) VALUES (
                :usuario,
                :documento,
                :tipo,
                :version,
                :hash_documento,
                :texto_consentimiento,
                :hash_consentimiento,
                1,
                :origen,
                :ip,
                :user_agent,
                NOW()
            )
        ";
        $stInsert = $pdo->prepare($sql);

        foreach ($docs as $row) {
            $doc = self::completarDocumento($row);
            $texto = (string)$doc['texto_consentimiento'];
            $hashConsentimiento = hash('sha256', self::normalizarContenidoParaHash($texto));

            $stInsert->execute([
                ':usuario' => $codigoUsuario,
                ':documento' => (int)$doc['codigo_documento_legal'],
                ':tipo' => (string)$doc['tipo'],
                ':version' => (string)$doc['version'],
                ':hash_documento' => (string)$doc['hash_documento_calculado'],
                ':texto_consentimiento' => $texto,
                ':hash_consentimiento' => $hashConsentimiento,
                ':origen' => $origen,
                ':ip' => $ip,
                ':user_agent' => $userAgent,
            ]);

            $registradas[] = [
                'tipo' => (string)$doc['tipo'],
                'version' => (string)$doc['version'],
                'codigo_documento_legal' => (int)$doc['codigo_documento_legal'],
            ];
        }

        return $registradas;
    }

    private static function completarDocumento(array $row): array
    {
        $contenido = self::renderizarContenidoDesdeFila($row);
        $hashCalculado = hash('sha256', self::normalizarContenidoParaHash($contenido));

        $row['contenido_html'] = $contenido;
        $row['hash_documento_calculado'] = $hashCalculado;
        $row['hash_documento'] = $hashCalculado;
        return $row;
    }

    private static function renderizarContenidoDesdeFila(array $row): string
    {
        $archivo = basename((string)($row['archivo_contenido'] ?? ''));
        if ($archivo === '') {
            throw new RuntimeException('El documento legal no tiene archivo de contenido.');
        }

        $path = __DIR__ . '/../views/documentos_legales/' . $archivo;
        if (!is_file($path)) {
            throw new RuntimeException('No se encontró el contenido legal: ' . $archivo);
        }

        $legalConfig = self::configuracion();
        ob_start();
        include $path;
        $html = ob_get_clean();

        if (!is_string($html) || trim($html) === '') {
            throw new RuntimeException('El contenido legal está vacío: ' . $archivo);
        }

        return trim($html);
    }

    private static function normalizarContenidoParaHash(string $contenido): string
    {
        $contenido = str_replace(["\r\n", "\r"], "\n", trim($contenido));
        return preg_replace('/[ \t]+\n/', "\n", $contenido) ?? $contenido;
    }

    private static function limitarTexto(?string $valor, int $max): ?string
    {
        if ($valor === null) {
            return null;
        }

        $valor = trim($valor);
        if ($valor === '') {
            return null;
        }

        if (function_exists('mb_substr')) {
            return mb_substr($valor, 0, $max, 'UTF-8');
        }

        return substr($valor, 0, $max);
    }
}
