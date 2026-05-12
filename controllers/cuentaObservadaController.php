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

        /*
         * Nuevas variables de contexto.
         * La vista actual puede funcionar sin usarlas, pero quedan listas
         * para diferenciar cuenta observada vs cambio de residencia observado.
         */
        $tipoObservacion            = 'cuenta_pendiente';
        $esCambioResidencia         = false;
        $codigoSolicitudResidencia  = null;
        $estadoSolicitudResidencia  = null;

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
             * Contexto unificado:
             *
             * 1) usuario_residencia_solicitud.estado = 'observada'
             * 2) usuario_residencia_solicitud.estado = 'pendiente'
             * 3) usuario_revision.estado_revision = 3
             * 4) revisión inicial normal
             */
            $contexto = $model->obtenerContextoVista($codigoUsuario);

            $modoVista                 = (string)($contexto['modo_vista'] ?? 'revision_inicial');
            $tipoObservacion           = (string)($contexto['tipo_observacion'] ?? 'cuenta_pendiente');
            $mensajeObservacion        = (string)($contexto['mensaje_observacion'] ?? '');
            $fechaObservacion          = $contexto['fecha_observacion'] ?? null;
            $esCambioResidencia        = (bool)($contexto['es_cambio_residencia'] ?? false);
            $codigoSolicitudResidencia = $contexto['codigo_solicitud_residencia'] ?? null;
            $estadoSolicitudResidencia = $contexto['estado_solicitud_residencia'] ?? null;

            /*
             * Comunidad:
             * - Si hay cambio de residencia, se prioriza la comunidad solicitada.
             * - Si no, se usa la residencia vigente.
             * - Finalmente, fallback desde token.
             */
            $nombreComunidad = trim((string)($contexto['nombre_comunidad'] ?? ''));

            if ($nombreComunidad === '') {
                $nombreComunidad = $model->obtenerNombreComunidad($codigoUsuario);
            }

            if ($nombreComunidad === '') {
                $nombreComunidad = trim((string)(
                    $auth['condominio_nombre']
                    ?? $auth['urbanizacion_nombre']
                    ?? $auth['nombre_comunidad']
                    ?? ''
                ));
            }

            /*
             * Blindaje:
             * Si el usuario ya está habilitado y no tiene ninguna observación
             * ni solicitud abierta de cambio de residencia, no debe ver esta pantalla.
             */
            $tieneObservacion = ($modoVista === 'observado');
            $tieneCambioResidenciaPendiente = (
                $esCambioResidencia
                && strtolower((string)$estadoSolicitudResidencia) === 'pendiente'
            );

            if (
                !$tieneObservacion
                && !$tieneCambioResidenciaPendiente
                && $estadoUsuario !== 1
            ) {
                header('Location: ' . $baseUrl . '/MenuPrincipal', true, 302);
                exit;
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
            'baseUrl'                    => $baseUrl,
            'nombre'                     => $nombre,
            'email'                      => $email,
            'nombreComunidad'            => $nombreComunidad,
            'modoVista'                  => $modoVista,
            'tipoObservacion'            => $tipoObservacion,
            'mensajeObservacion'         => $mensajeObservacion,
            'fechaObservacion'           => $fechaObservacion,
            'esCambioResidencia'         => $esCambioResidencia,
            'codigoSolicitudResidencia'  => $codigoSolicitudResidencia,
            'estadoSolicitudResidencia'  => $estadoSolicitudResidencia,
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