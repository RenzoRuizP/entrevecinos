<?php
// controllers/notificacionesController.php
declare(strict_types=1);

final class notificacionesController
{
    public function index(): void
    {
        require __DIR__ . '/../views/notificacionesView.php';
    }
}
