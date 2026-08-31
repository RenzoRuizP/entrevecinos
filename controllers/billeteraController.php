<?php
// controllers/billeteraController.php

declare(strict_types=1);

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/ConfiguracionPlataforma.php';

class billeteraController
{
    public function index(): void
    {
        $this->renderizarSeccion('resumen');
    }

    public function recargar(): void
    {
        $this->renderizarSeccion('recargar');
    }

    public function retirar(): void
    {
        $this->renderizarSeccion('retirar');
    }

    private function renderizarSeccion(string $seccion): void
    {
        try {
            $seccionesPermitidas = ['resumen', 'recargar', 'retirar'];
            if (!in_array($seccion, $seccionesPermitidas, true)) {
                $seccion = 'resumen';
            }

            // 1) Validación de token (refuerzo, además del router).
            $token = $_COOKIE['auth_token'] ?? null;
            $datosToken = $token ? SesionJWT::verificarToken((string)$token) : null;

            $codigoUsuario = (int)($datosToken['codigo_usuario'] ?? 0);
            $email = trim((string)($datosToken['email'] ?? ''));

            if ($codigoUsuario <= 0 || $email === '') {
                $this->resolverNoAutorizado('token_invalido');
                return;
            }

            /*
             * La disponibilidad se valida también en servidor. Ocultar el menú
             * nunca debe permitir reactivar la billetera mediante URL directa.
             */
            $estadoBilletera = (new ConfiguracionPlataforma())->obtenerEstadoBilleteraUsuario($codigoUsuario);
            $evBilleteraDisponible = (bool)($estadoBilletera['billetera_disponible'] ?? false);
            $evRecargasDisponibles = (bool)($estadoBilletera['recargas_disponibles'] ?? false);

            if (!$evBilleteraDisponible && !ConfiguracionPlataforma::esAdmin($datosToken)) {
                $this->resolverNoDisponible();
                return;
            }

            if ($seccion === 'recargar' && !$evRecargasDisponibles && !ConfiguracionPlataforma::esAdmin($datosToken)) {
                $this->resolverRecargasNoDisponibles();
                return;
            }

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (empty($_SESSION['ev_wallet_csrf']) || !is_string($_SESSION['ev_wallet_csrf'])) {
                $_SESSION['ev_wallet_csrf'] = bin2hex(random_bytes(32));
            }
            $evWalletCsrf = $_SESSION['ev_wallet_csrf'];

            $rolActual = strtolower(trim((string)($datosToken['rol'] ?? $datosToken['nombre_rol'] ?? '')));
            $codigoRolActual = (int)($datosToken['codigo_rol'] ?? 0);
            $evEsVecino = $rolActual === 'vecino' || $codigoRolActual === 2;

            // 2) Enriquecer con User.php, sin bloquear la vista si falla.
            $objUsuario = new User();
            $datosUsuario = $objUsuario->DatosUsuario($email);

            if (!$datosUsuario || !is_array($datosUsuario)) {
                $datosUsuario = [
                    'codigo_usuario' => $codigoUsuario,
                    'nombre'         => $datosToken['nombre'] ?? 'Usuario',
                    'email'          => $email,
                    'rol'            => $datosToken['rol'] ?? null,
                    '_fallback'      => 1,
                ];
            } else {
                if (empty($datosUsuario['codigo_usuario'])) {
                    $datosUsuario['codigo_usuario'] = $codigoUsuario;
                }
                if (empty($datosUsuario['email'])) {
                    $datosUsuario['email'] = $email;
                }
            }

            // 3) Enriquecer con comunidad activa, usando el mismo criterio que Marketplace.
            try {
                $prod = new Producto();
                $conjunto = $prod->obtenerNombreConjuntoActivoUsuario($codigoUsuario);

                $datosUsuario['conjunto_tipo']   = $conjunto['tipo_conjunto'] ?? null;
                $datosUsuario['conjunto_nombre'] = $conjunto['nombre'] ?? null;
                $datosUsuario['condominio']      = $datosUsuario['conjunto_nombre'] ?? ($datosUsuario['condominio'] ?? null);
            } catch (Throwable $e) {
                // La comunidad es contexto visual; no debe romper el módulo financiero.
            }

            // 4) Contexto único para las tres vistas del módulo.
            $evWalletSection = $seccion;

            header('X-Partial-Ok: 1');
            require __DIR__ . '/../views/billeteraView.php';
        } catch (Throwable $e) {
            error_log('[EV][billeteraController::renderizarSeccion][' . $seccion . '] ' . $e->getMessage());

            if ($this->esPeticionParcial()) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'ok'      => false,
                    'error'   => 'SERVER_ERROR',
                    'mensaje' => 'Error del servidor al cargar Billetera.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            header('Location: ' . rtrim(BASE_URL, '/') . '/login');
        }
    }

    private function resolverNoDisponible(): void
    {
        $redirect = rtrim(BASE_URL, '/') . '/MenuPrincipal';

        if ($this->esPeticionParcial()) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok' => false,
                'error' => 'FUNCIONALIDAD_NO_DISPONIBLE',
                'mensaje' => 'La billetera no está disponible para tu comunidad en este momento.',
                'redirect' => $redirect,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        header('Location: ' . $redirect, true, 302);
    }

    private function resolverRecargasNoDisponibles(): void
    {
        $redirect = rtrim(BASE_URL, '/') . '/MenuPrincipal?ev_goto=' . rawurlencode('/billetera');

        if ($this->esPeticionParcial()) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok' => false,
                'error' => 'RECARGAS_NO_DISPONIBLES',
                'mensaje' => 'Las recargas no están disponibles para tu comunidad en este momento.',
                'redirect' => $redirect,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        header('Location: ' . $redirect, true, 302);
    }

    private function resolverNoAutorizado(string $motivo = 'no_autorizado'): void
    {
        if ($this->esPeticionParcial()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok'     => false,
                'error'  => 'UNAUTHORIZED',
                'motivo' => $motivo
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/login');
    }

    private function esPeticionParcial(): bool
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }

        if (isset($_SERVER['HTTP_X_PARTIAL']) && $_SERVER['HTTP_X_PARTIAL'] === '1') {
            return true;
        }

        return isset($_GET['partial']) && $_GET['partial'] === '1';
    }
}
