<?php
declare(strict_types=1);

$evRootPath = dirname(__DIR__);
$evAutoload = $evRootPath . '/vendor/autoload.php';

if (is_file($evAutoload)) {
    require_once $evAutoload;
}

if (class_exists(\Dotenv\Dotenv::class) && is_file($evRootPath . '/.env')) {
    \Dotenv\Dotenv::createImmutable($evRootPath)->safeLoad();
}

if (!function_exists('ev_env')) {
    /**
     * Lee una variable de entorno de forma portable en CLI, Apache y PHP-FPM.
     * Conserva cadenas vacías cuando han sido configuradas explícitamente.
     */
    function ev_env(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }

        $value = getenv($key);

        return $value !== false ? $value : $default;
    }
}

if (!function_exists('ev_normalize_base_url')) {
    /**
     * Normaliza la ruta base pública de EV.
     * Resultado esperado: '/' para raíz o '/entrevecinos/' para subcarpeta.
     */
    function ev_normalize_base_url(?string $value): string
    {
        $value = trim((string)$value);

        if ($value === '' || $value === '/') {
            return '/';
        }

        if (preg_match('#^https?://#i', $value)) {
            $path = parse_url($value, PHP_URL_PATH);
            $value = is_string($path) ? $path : '/';
        }

        $value = '/' . trim(str_replace('\\', '/', $value), '/');
        $value = preg_replace('#/+#', '/', $value) ?: '/';

        return $value === '/' ? '/' : rtrim($value, '/') . '/';
    }
}

if (!function_exists('ev_detect_base_url')) {
    /**
     * Prioridad:
     * 1) EV_BASE_URL del entorno.
     * 2) Detección automática desde SCRIPT_NAME.
     */
    function ev_detect_base_url(): string
    {
        $configured = ev_env('EV_BASE_URL', '');

        if (trim((string)$configured) !== '') {
            return ev_normalize_base_url((string)$configured);
        }

        $projectRoot = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
        $documentRootRaw = (string)($_SERVER['DOCUMENT_ROOT'] ?? '');
        $documentRoot = $documentRootRaw !== '' ? (realpath($documentRootRaw) ?: $documentRootRaw) : '';

        $projectNorm = rtrim(str_replace('\\', '/', $projectRoot), '/');
        $documentNorm = rtrim(str_replace('\\', '/', $documentRoot), '/');

        if ($documentNorm !== '') {
            $projectCompare = strtolower($projectNorm);
            $documentCompare = strtolower($documentNorm);

            if ($projectCompare === $documentCompare) {
                return '/';
            }

            if (str_starts_with($projectCompare, $documentCompare . '/')) {
                $relative = substr($projectNorm, strlen($documentNorm));
                return ev_normalize_base_url($relative);
            }
        }

        $scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
        $directory = str_replace('\\', '/', dirname($scriptName));

        if ($directory === '.' || $directory === '/' || $directory === '\\') {
            return '/';
        }

        return ev_normalize_base_url($directory);
    }
}

if (!defined('BASE_URL')) {
    define('BASE_URL', ev_detect_base_url());
}

if (!defined('EV_APP_ENV')) {
    $evAppEnv = trim((string)ev_env('APP_ENV', 'production'));
    define('EV_APP_ENV', strtolower($evAppEnv !== '' ? $evAppEnv : 'production'));
}

if (!defined('VIEW_STYLE_PATH')) {
    define('VIEW_STYLE_PATH', __DIR__ . '/../views/estilos/');
}

if (!defined('VIEW_PATH')) {
    define('VIEW_PATH', __DIR__ . '/../views/');
}

/* Roles oficiales EV */
if (!defined('EV_ADMIN_ROLE_ID')) {
    define('EV_ADMIN_ROLE_ID', 1);
}

if (!defined('EV_SOPORTE_ROLE_ID')) {
    define('EV_SOPORTE_ROLE_ID', 3);
}

if (!defined('EV_ADMIN_COMUNIDAD_ROLE_ID')) {
    define('EV_ADMIN_COMUNIDAD_ROLE_ID', 4);
}

if (!defined('EV_APP_VER')) {
    define('EV_APP_VER', '1.0.11');
}


if (!function_exists('ev_retiro_bank_rules')) {
    /**
     * Bancos habilitados para liquidaciones EV durante el piloto.
     *
     * - El CCI peruano tiene 20 dígitos y sus 3 primeros identifican a la entidad.
     * - Las longitudes de cuenta se validan por entidad; cuando una entidad publica
     *   más de un formato vigente se admiten únicamente esas longitudes.
     * - Esta validación comprueba estructura/formato, no existencia ni titularidad
     *   real de la cuenta. La titularidad continúa sujeta a validación administrativa.
     */
    function ev_retiro_bank_rules(): array
    {
        return [
            'BCP' => [
                'codigo_cci' => '002',
                'cuenta_longitudes' => ['ahorros' => [13, 14], 'corriente' => [13, 14]],
            ],
            'Interbank' => [
                'codigo_cci' => '003',
                'cuenta_longitudes' => ['ahorros' => [13], 'corriente' => [13]],
            ],
            'Scotiabank' => [
                'codigo_cci' => '009',
                'cuenta_longitudes' => ['ahorros' => [10, 14], 'corriente' => [10, 14]],
            ],
            'BBVA' => [
                'codigo_cci' => '011',
                'cuenta_longitudes' => ['ahorros' => [18], 'corriente' => [18]],
            ],
            'Banco de la Nación' => [
                'codigo_cci' => '018',
                'cuenta_longitudes' => ['ahorros' => [11], 'corriente' => [11]],
            ],
            'Banco Pichincha' => [
                'codigo_cci' => '035',
                'cuenta_longitudes' => ['ahorros' => [11, 12], 'corriente' => [11, 12]],
            ],
            'BanBif' => [
                'codigo_cci' => '038',
                'cuenta_longitudes' => ['ahorros' => [10, 12], 'corriente' => [10, 12]],
            ],
            'MiBanco' => [
                'codigo_cci' => '049',
                'cuenta_longitudes' => ['ahorros' => [10], 'corriente' => [10]],
            ],
        ];
    }
}

/* Uploads (ruta física + URL pública) */
if (!defined('EV_UPLOADS_DIR')) {
    define(
        'EV_UPLOADS_DIR',
        realpath(__DIR__ . '/../resources/uploads') ?: (__DIR__ . '/../resources/uploads')
    );
}

if (!defined('EV_UPLOADS_URL')) {
    define(
        'EV_UPLOADS_URL',
        rtrim(BASE_URL, '/') . '/resources/uploads'
    );
}
