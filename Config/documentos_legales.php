<?php
// Config/documentos_legales.php
//
// Configuración central de documentos legales de Entre Vecinos (EV).
//
// IMPORTANTE PARA PRODUCCIÓN:
// 1. Mantener "modo_borrador" en true hasta completar la inscripción del
//    banco de datos personales y la revisión jurídica final previa al piloto.
// 2. Completar "banco_datos_codigo" y cambiar "estado_banco_datos" a
//    "Inscrito" antes de habilitar el registro de usuarios reales.
// 3. Cuando se modifique sustancialmente un documento publicado, crear una
//    nueva versión y solicitar nuevamente la aceptación cuando corresponda.

declare(strict_types=1);

require_once __DIR__ . '/config.php';

// La app y la página pública son proyectos separados.
//
// En desarrollo local, los enlaces deben apuntar a:
//   http://localhost/entrevecinos_web
//
// En producción, deben apuntar a:
//   https://www.entrevecinos.pe
//
// La detección no se basa únicamente en HTTP_HOST. También reconoce la ruta
// típica de XAMPP, porque la app puede abrirse mediante un dominio local,
// un VirtualHost o una entrada personalizada en el archivo hosts.
$evHostActual = strtolower(trim((string)($_SERVER['HTTP_HOST'] ?? '')));
$evHostSinPuerto = preg_replace('/:\d+$/', '', $evHostActual) ?: $evHostActual;
$evRutaProyecto = strtolower(str_replace('\\', '/', dirname(__DIR__)));

$evEsHostLocal = in_array($evHostSinPuerto, [
    'localhost',
    '127.0.0.1',
    '::1',
], true)
    || str_ends_with($evHostSinPuerto, '.local')
    || str_ends_with($evHostSinPuerto, '.test');

$evEsRutaXampp = str_contains($evRutaProyecto, '/xampp/htdocs/')
    || str_contains($evRutaProyecto, '/wamp64/www/')
    || str_contains($evRutaProyecto, '/laragon/www/');

// Permite forzar el entorno sin editar este archivo:
//   EV_ENTORNO=local
//   EV_ENTORNO=produccion
$evEntornoForzado = strtolower(trim((string)ev_env('EV_ENTORNO', 'auto')));

if ($evEntornoForzado === 'local') {
    $evEsEntornoLocal = true;
} elseif (in_array($evEntornoForzado, ['produccion', 'production'], true)) {
    $evEsEntornoLocal = false;
} else {
    $evEsEntornoLocal = $evEsHostLocal || $evEsRutaXampp;
}

$evWebPublicaLocal = 'http://localhost/entrevecinos_web';
$evWebPublicaProduccion = 'https://www.entrevecinos.pe';
$evWebPublicaActual = $evEsEntornoLocal ? $evWebPublicaLocal : $evWebPublicaProduccion;

return [
    'modo_borrador' => true,

    'publicacion' => [
        'fecha_publicacion' => '2026-07-12 00:00:00',
        'fecha_vigencia'    => '2026-08-10 00:00:00',
        'inicio_piloto'     => '2026-08-10',
    ],

    'enlaces' => [
        'web_publica_oficial'       => $evWebPublicaProduccion,
        'web_publica_local'         => $evWebPublicaLocal,
        'web_publica_actual'          => $evWebPublicaActual,
        'terminos_condiciones'       => $evWebPublicaActual . '/legal/terminos-y-condiciones.php',
        'politica_privacidad'        => $evWebPublicaActual . '/legal/politica-de-privacidad.php',
        'libro_reclamaciones'        => $evWebPublicaActual . '/libro-de-reclamaciones/',
        'terminos_condiciones_oficial' => $evWebPublicaProduccion . '/legal/terminos-y-condiciones.php',
        'politica_privacidad_oficial'  => $evWebPublicaProduccion . '/legal/politica-de-privacidad.php',
        'libro_reclamaciones_oficial'  => $evWebPublicaProduccion . '/libro-de-reclamaciones/',
    ],

    'responsable' => [
        'tipo_titular'             => 'Persona natural',
        'nombre_comercial'         => 'Entre Vecinos (EV)',
        'nombre_legal'             => 'Marco Renzo Francesco Ruiz Pastor',
        'documento_tributario'     => 'RUC 10459774489',
        'domicilio'                => 'Av. Guardia Civil 953, Chorrillos, Lima, Perú',
        'domicilio_notificaciones' => 'Av. Guardia Civil 953, Chorrillos, Lima, Perú',
        'sitio_web'                => 'https://www.entrevecinos.pe',
        'correo_soporte'           => 'soporte@entrevecinos.pe',
        'correo_privacidad'        => 'privacidad@entrevecinos.pe',
        'correo_reclamos'          => 'reclamos@entrevecinos.pe',
        'telefono_soporte'         => '+51 956 969 182',
        'whatsapp_soporte'         => '+51 956 969 182',
        'banco_datos_nombre'       => 'Usuarios de Entre Vecinos',
        'banco_datos_codigo'       => '',
        'estado_banco_datos'       => 'Pendiente de inscripción ante la Autoridad Nacional de Protección de Datos Personales',
    ],

    'operacion' => [
        'comunidad_inicial'         => 'Urbanización cerrada Villa Flores, Villa El Salvador, Lima',
        'piloto_gratuito'           => true,
        'ev_custodia_dinero'        => false,
        'pagos_directos_usuarios'   => true,
        'edad_minima'               => 18,
        'proveedor_alojamiento'     => 'Hostinger',
        'ubicacion_alojamiento'     => 'São Paulo, Brasil',
        'canales_externos'          => ['WhatsApp', 'redes sociales oficiales de EV'],
        'uso_whatsapp'              => 'Soporte y comunicaciones informativas u operativas iniciadas o solicitadas por el usuario. La publicidad requerirá consentimiento adicional cuando corresponda.',
    ],

    'documentos' => [
        'terminos_condiciones' => [
            'tipo'                 => 'terminos_condiciones',
            'slug'                 => 'terminos-y-condiciones',
            'titulo'               => 'Términos y Condiciones de Uso de Entre Vecinos',
            'version'              => '1.0',
            'archivo_contenido'    => 'terminos_v1.php',
            'fecha_publicacion'    => '2026-07-12 00:00:00',
            'fecha_vigencia'       => '2026-08-10 00:00:00',
            'requiere_aceptacion'  => true,
            'texto_consentimiento' => 'Declaro que he leído, comprendido y acepto los Términos y Condiciones de Uso de Entre Vecinos – Versión 1.0.',
        ],

        'politica_privacidad' => [
            'tipo'                 => 'politica_privacidad',
            'slug'                 => 'politica-de-privacidad',
            'titulo'               => 'Política de Privacidad y Tratamiento de Datos Personales',
            'version'              => '1.0',
            'archivo_contenido'    => 'privacidad_v1.php',
            'fecha_publicacion'    => '2026-07-12 00:00:00',
            'fecha_vigencia'       => '2026-08-10 00:00:00',
            'requiere_aceptacion'  => true,
            'texto_consentimiento' => 'Declaro que he leído la Política de Privacidad y otorgo mi consentimiento libre, previo, expreso e informado para el tratamiento de mis datos personales necesario para el registro, la validación de residencia y el uso de Entre Vecinos, conforme a las finalidades informadas – Versión 1.0.',
        ],
    ],
];
