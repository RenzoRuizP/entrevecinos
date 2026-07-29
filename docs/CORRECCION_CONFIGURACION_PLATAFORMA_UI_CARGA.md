# Corrección del módulo Configuración de plataforma

## Objetivo

Corregir la carga infinita del módulo administrativo de los puntos 14 y 15 y elevar su interfaz al estándar visual premium de Entre Vecinos.

## Causa encontrada

El archivo `views/js/configuracionPlataforma.js` utilizaba un `MutationObserver` sobre todo el documento y ejecutaba `init()` ante cualquier modificación del DOM.

Cuando la propia pantalla dibujaba las cards de funcionalidades y monetización, el observador volvía a ejecutar `load()`. Esto provocaba el siguiente ciclo:

1. Se mostraba “Cargando configuración…”.
2. La API respondía correctamente con estado HTTP 200.
3. JavaScript renderizaba las cards.
4. El render modificaba el DOM.
5. El observador reiniciaba el módulo.
6. Se repetía la consulta y volvía el estado de carga.

## Corrección aplicada

- El observador ahora solo reacciona cuando se inserta una nueva instancia de `#evConfiguracionPlataforma`.
- La misma instancia del módulo no puede inicializarse dos veces.
- Cada consulta tiene un identificador y un `AbortController`.
- Una consulta anterior se cancela cuando el administrador cambia rápidamente de alcance.
- La API se procesa primero como texto y luego como JSON, permitiendo detectar respuestas HTML o PHP inválidas.
- El estado de carga siempre finaliza.
- Si la API falla, se muestra un estado de error dentro de la pantalla con botón `Reintentar`.
- El botón actualizar muestra actividad únicamente mientras existe una consulta vigente.
- Se añadieron cabeceras `no-store` al endpoint administrativo para impedir respuestas antiguas en caché.

## Mejora UX/UI

- Cabecera más compacta y jerarquizada.
- Identificación visible de acceso exclusivo para Administrador EV.
- Selector de alcance con contexto global, condominio o urbanización.
- Resumen visual de alcance, funcionalidades activas y condición comercial.
- Tabs premium para Funcionalidades, Monetización e Historial.
- Cards blancas, sombras sutiles y efecto hover naranja EV.
- Estados `Activo` e `Inactivo` claramente diferenciados.
- Programación, mensajes y trazabilidad dentro de secciones desplegables para reducir carga visual.
- Diseño responsive para escritorio, tablet y móvil.
- Estado de carga tipo skeleton.
- Estado de error recuperable.
- Mejor formato del historial administrativo.

## Permisos

Se mantiene la opción acordada:

- `admin`: visualiza y administra el módulo.
- `soporte`: no ve el menú y no puede acceder por URL directa.

No se modificaron los permisos ni se otorgó acceso al rol soporte.

## Archivos modificados

1. `views/configuracionPlataformaView.php`
2. `views/estilos/configuracionPlataformaEstilo.php`
3. `views/js/configuracionPlataforma.js`
4. `controllers/api/apiConfiguracionPlataformaController.php`

## Base de datos

Esta corrección no requiere cambios de estructura ni de datos. La base recibida ya contiene:

- 7 funcionalidades.
- 10 reglas de monetización.
- Urbanización Villa Flores.
- Tablas de configuración e historial de los puntos 14 y 15.

El script SQL entregado por separado es únicamente de verificación y no modifica información.

## Pruebas técnicas realizadas

- 292 archivos PHP validados sin errores de sintaxis.
- 47 archivos JavaScript validados sin errores de sintaxis.
- Prueba del módulo en navegador Chromium simulado.
- Una sola consulta inicial a la API.
- Segunda consulta únicamente al cambiar de alcance.
- 7 cards de funcionalidades renderizadas.
- 10 cards de monetización renderizadas.
- Estado “Cargando configuración…” retirado correctamente.
- Cambio de tabs verificado.
- Sin errores JavaScript durante la prueba.

## Instalación local

1. Realizar una copia de seguridad del proyecto actual.
2. Reemplazar el proyecto por el ZIP actualizado o copiar únicamente los cuatro archivos modificados.
3. Reiniciar Apache si mantiene archivos en caché.
4. Abrir Entre Vecinos con el usuario Administrador EV.
5. Presionar `Ctrl + F5` en el navegador.
6. Ingresar a `Administración > Configuración de plataforma`.

## Prueba funcional recomendada

1. Confirmar que la carga termine y aparezcan las cards.
2. Abrir DevTools > Red y verificar una sola solicitud inicial a:
   `/api/admin/configuracion-plataforma`.
3. Seleccionar `Urbanización Villa Flores`.
4. Confirmar que se realice una sola solicitud adicional.
5. Revisar Funcionalidades, Monetización e Historial.
6. Abrir una card, modificar una configuración de prueba y guardar.
7. Confirmar que el historial registre el cambio.
8. Iniciar sesión como soporte y confirmar que el módulo no sea visible ni accesible.
