<?php
// controllers/documentosLegalesController.php

declare(strict_types=1);

require_once __DIR__ . '/../models/DocumentoLegal.php';

final class DocumentosLegalesController
{
    public function terminos(): void
    {
        $this->renderDocumento('terminos_condiciones');
    }

    public function privacidad(): void
    {
        $this->renderDocumento('politica_privacidad');
    }

    public function aceptacion(): void
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        $codigoUsuario = (int)($auth['codigo_usuario'] ?? 0);

        if ($codigoUsuario <= 0) {
            $this->redirect('/login');
        }

        $model = new DocumentoLegal();
        $documentos = $model->obtenerVigentesObligatorios();
        $pendientes = $model->obtenerPendientesUsuario($codigoUsuario);

        if (!$pendientes) {
            $this->redirect('/MenuPrincipal');
        }

        $legalConfig = DocumentoLegal::configuracion();
        require __DIR__ . '/../views/aceptacionLegalView.php';
    }

    private function renderDocumento(string $tipo): void
    {
        try {
            $model = new DocumentoLegal();
            $documento = $model->obtenerVigentePorTipo($tipo);

            if (!$documento) {
                http_response_code(404);
                echo 'Documento legal no disponible.';
                return;
            }

            $legalConfig = DocumentoLegal::configuracion();
            require __DIR__ . '/../views/documentoLegalPublicoView.php';
        } catch (Throwable $e) {
            error_log('[EV][DocumentosLegalesController] ' . $e->getMessage());
            http_response_code(500);
            echo 'No se pudo cargar el documento legal. Verifica la instalación del punto 12.';
        }
    }

    private function redirect(string $path): never
    {
        $base = defined('BASE_URL') ? rtrim((string)BASE_URL, '/') : '';
        header('Location: ' . $base . $path, true, 302);
        exit;
    }
}
