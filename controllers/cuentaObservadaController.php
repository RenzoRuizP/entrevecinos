<?php
// controllers/cuentaObservadaController.php

declare(strict_types=1);

require_once __DIR__ . '/../models/CuentaObservada.php';

final class cuentaObservadaController
{
    public function index(): void
    {
        // ============================
        // Sesión validada por index.php
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
        // Defaults para la vista
        // ============================
        $nombre             = '';
        $email              = '';
        $nombreComunidad    = '';
        $mensajeObservacion = '';
        $fechaObservacion   = null;
        $modoVista          = 'revision_inicial';

        try {
            $model = new CuentaObservada();

            $usuario = $model->obtenerUsuarioPorCodigo($codigoUsuario);

            if (!$usuario) {
                header('Location: ' . $loginUrl, true, 302);
                exit;
            }

            $nombre        = (string)($usuario['nombre'] ?? '');
            $email         = (string)($usuario['email'] ?? '');
            $estadoUsuario = (int)($usuario['estado'] ?? 0);

            /*
             * Si más adelante decides bloquear esta vista para usuarios que
             * ya no estén en revisión, puedes reactivar esta validación.
             */
            /*
            if ($estadoUsuario !== 1) {
                header('Location: ' . $baseUrl . '/MenuPrincipal', true, 302);
                exit;
            }
            */

            $nombreComunidad = $model->obtenerNombreComunidad($codigoUsuario);

            /*
             * Fallback defensivo desde el token, por si el usuario todavía
             * no tiene usuario_residencia correctamente registrado.
             */
            if ($nombreComunidad === '') {
                $nombreComunidad = trim((string)(
                    $auth['condominio_nombre']
                    ?? $auth['urbanizacion_nombre']
                    ?? $auth['nombre_comunidad']
                    ?? ''
                ));
            }

            $revision = $model->obtenerRevisionUsuario($codigoUsuario);

            if ($revision && (int)($revision['estado_revision'] ?? 0) === 3) {
                $modoVista = 'observado';
                $mensajeObservacion = (string)($revision['mensaje_observacion'] ?? '');
                $fechaObservacion   = $revision['fecha_observacion'] ?? null;
            } else {
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
            'nombreComunidad'    => $nombreComunidad,
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