<?php
// controllers/atenderServiciosController.php

declare(strict_types=1);

final class atenderServiciosController
{
    public function index(): void
    {
        require_once __DIR__ . '/../views/atenderServiciosView.php';
    }
}
