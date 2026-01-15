<?php
// controllers/notificacionesResidenciaController.php
declare(strict_types=1);

final class notificacionesResidenciaController
{
    public function index(): void
    {
        require __DIR__ . '/../views/notificacionesResidenciaView.php';
    }
}
