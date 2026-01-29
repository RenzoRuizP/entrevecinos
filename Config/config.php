<?php

define('BASE_URL', '/entrevecinos/');
define('VIEW_STYLE_PATH', __DIR__ . '/../views/estilos/');
define('VIEW_PATH', __DIR__ . '/../views/');

define('EV_ADMIN_ROLE_ID', 1);
if (!defined('EV_SOPORTE_ROLE_ID')) {
  define('EV_SOPORTE_ROLE_ID', 3);
}

define('EV_APP_VER', '1.0.0');

// ✅ Uploads (ruta física + URL pública)
define('EV_UPLOADS_DIR', realpath(__DIR__ . '/../resources/uploads') ?: (__DIR__ . '/../resources/uploads'));
define('EV_UPLOADS_URL', rtrim(BASE_URL, '/') . '/resources/uploads');
