<?php
// controllers/soporteDashboardController.php

require_once __DIR__ . '/../Config/config.php';
// Si tienes middleware / validador JWT, inclúyelo aquí
// require_once __DIR__ . '/../middlewares/auth.php';
// require_once __DIR__ . '/../models/SesionJWT.php';

// =====================================================
// 1) Validación de rol (ajusta a tu forma actual)
// =====================================================
// Ejemplo: $usuario = SesionJWT::usuarioActual(); // o como lo tengas
// if (!$usuario) { header('Location: ' . BASE_URL . '/login'); exit; }
// if (($usuario['rol'] ?? '') !== 'soporte') { header('Location: ' . BASE_URL . '/MenuPrincipal'); exit; }

// =====================================================
// 2) Data del dashboard (placeholder)
//    Luego lo conectas a tus modelos reales
// =====================================================
$kpis = [
  'cuentas' => [
    'pendientes'     => 5,
    'aprobadas_hoy'  => 8,
    'rechazadas'     => 1,
  ],
  'publicaciones' => [
    'en_revision' => 6,
    'reportadas'  => 4,
    'suspendidas' => 2,
  ],
  'recargas' => [
    'pend_validacion' => 3,
    'validadas_hoy'   => 10,
    'observadas'      => 1,
  ],
];

$colaAtencion = [
  [
    'fecha' => '19:30 hoy',
    'tipo'  => 'Cuenta pendiente de verificación (15 min)',
    'modulo'=> 'cuentas',
    'prioridad' => 'alta',
    'url'   => rtrim(BASE_URL,'/') . '/soporte/cuentas?tab=pendientes',
  ],
  [
    'fecha' => '18:50 hoy',
    'tipo'  => 'Publicación reportada por vecino',
    'modulo'=> 'publicaciones',
    'prioridad' => 'media',
    'url'   => rtrim(BASE_URL,'/') . '/soporte/publicaciones?tab=reportadas',
  ],
  [
    'fecha' => '18:20 hoy',
    'tipo'  => 'Recarga pendiente con comprobante',
    'modulo'=> 'recargas',
    'prioridad' => 'alta',
    'url'   => rtrim(BASE_URL,'/') . '/soporte/recargas?tab=pendientes',
  ],
];

// Si tu layout necesita usuario
// $usuario = $usuario ?? ['nombre' => 'Soporte', 'rol' => 'soporte'];

// =====================================================
// 3) Render
// =====================================================
require_once __DIR__ . '/../views/soporteDashboardView.php';
