<?php
// controllers/cuentaObservadaController.php

declare(strict_types=1);

final class cuentaObservadaController
{
    public function index(): void
    {
        // ============================
        // Sesión (validada por index.php)
        // ============================
        $auth = $GLOBALS['EV_AUTH'] ?? [];

        $codigoUsuario = (int)($auth['codigo_usuario'] ?? 0);
        $codigoRol     = (int)($auth['codigo_rol'] ?? 0);

        $baseUrl  = defined('BASE_URL') ? rtrim((string)BASE_URL, '/') : '';
        $loginUrl = $baseUrl . '/login';

        if ($codigoUsuario <= 0) {
            header('Location: ' . $loginUrl, true, 302);
            exit;
        }

        // ============================
        // Solo VECINO
        // ============================
        $adminId   = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        $soporteId = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;

        $esVecino = ($codigoRol !== $adminId && $codigoRol !== $soporteId);
        if (!$esVecino) {
            header('Location: ' . $baseUrl . '/MenuPrincipal', true, 302);
            exit;
        }

        // ============================
        // Defaults
        // ============================
        $nombre             = '';
        $email              = '';
        $mensajeObservacion = '';
        $fechaObservacion   = null;
        $modoVista          = null;

        try {
            if (!class_exists('Conexion')) {
                throw new Exception('Clase Conexion no disponible.');
            }

            $cn = new Conexion();
            if (!method_exists($cn, 'getDblink')) {
                throw new Exception('Conexion::getDblink() no existe.');
            }

            $db = $cn->getDblink();
            if (!$db) {
                throw new Exception('Sin conexión a BD.');
            }

            // ============================
            // Usuario
            // ============================
            $sqlUser = "
                SELECT nombre, email, estado
                FROM usuario
                WHERE codigo_usuario = :id
                LIMIT 1
            ";
            $stUser = $db->prepare($sqlUser);
            $stUser->execute([':id' => $codigoUsuario]);
            $user = $stUser->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                header('Location: ' . $loginUrl, true, 302);
                exit;
            }

            $nombre        = (string)$user['nombre'];
            $email         = (string)$user['email'];
            $estadoUsuario = (int)$user['estado'];

            // 👉 Si NO está en revisión, no debería ver esta vista
            if ($estadoUsuario !== 1) {
                header('Location: ' . $baseUrl . '/MenuPrincipal', true, 302);
                exit;
            }

            // ============================
            // Revisión / Observación
            // ============================
            $sqlObs = "
                SELECT estado_revision, mensaje_observacion, fecha_observacion
                FROM usuario_revision
                WHERE codigo_usuario = :id
                LIMIT 1
            ";
            $stObs = $db->prepare($sqlObs);
            $stObs->execute([':id' => $codigoUsuario]);
            $obs = $stObs->fetch(PDO::FETCH_ASSOC);

            if ($obs && (int)$obs['estado_revision'] === 3) {
                // OBSERVADO
                $modoVista = 'observado';
                $mensajeObservacion = (string)($obs['mensaje_observacion'] ?? '');
                $fechaObservacion   = $obs['fecha_observacion'] ?? null;
            } else {
                // REVISIÓN INICIAL
                $modoVista = 'revision_inicial';
            }

        } catch (Throwable $e) {
            error_log('[EV][cuentaObservadaController] ' . $e->getMessage());
            header('Location: ' . $baseUrl . '/MenuPrincipal', true, 302);
            exit;
        }

        // ============================
        // Render vista
        // ============================
        $data = [
            'baseUrl'            => $baseUrl,
            'nombre'             => $nombre,
            'email'              => $email,
            'modoVista'          => $modoVista,
            'mensajeObservacion' => $mensajeObservacion,
            'fechaObservacion'   => $fechaObservacion,
        ];

        extract($data, EXTR_SKIP);

        $viewFile = defined('VIEW_PATH')
            ? rtrim((string)VIEW_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'cuentaObservadaView.php'
            : __DIR__ . '/../views/cuentaObservadaView.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo 'No se encontró la vista cuentaObservadaView.php';
            exit;
        }

        require $viewFile;
    }
}
