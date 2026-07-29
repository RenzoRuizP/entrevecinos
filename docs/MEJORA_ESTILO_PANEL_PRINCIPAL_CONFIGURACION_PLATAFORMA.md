# Mejora UX/UI — Configuración de plataforma

## Objetivo

Alinear el módulo administrativo **Configuración de plataforma** con el lenguaje visual e interactivo del **Panel principal del vecino**, sin modificar reglas, permisos, endpoints ni estructura de base de datos.

## Cambios incluidos

- Cabecera con remate superior verde–naranja y profundidad equivalente al dashboard.
- Indicadores superiores convertidos en tarjetas resumen con iconos, hover, elevación y acento inferior.
- Tabs con selección naranja EV y foco accesible.
- Filas administrativas con el efecto de las Acciones rápidas del Panel principal:
  - elevación suave;
  - borde naranja;
  - sombra naranja controlada;
  - fondo cálido discreto;
  - icono naranja al pasar el cursor o seleccionar.
- La fila abierta recibe el estado visual `is-selected`.
- El estado `Cambio sin guardar` mantiene la selección visual hasta guardar.
- Botón `Configurar` con estado seleccionado naranja.
- Botón `Guardar cambios` / `Guardar regla` con CTA naranja EV.
- Retroalimentación visual del guardado:
  - Guardando…
  - Guardado
  - Reintentar si falla.
- Inputs, selects, fechas, combobox e interruptores con hover y foco EV.
- Respeto de `prefers-reduced-motion`.
- Ajustes responsive para escritorio, tablet y móvil.

## Archivos modificados

- `views/configuracionPlataformaView.php`
- `views/estilos/configuracionPlataformaEstilo.php`
- `views/js/configuracionPlataforma.js`

## Base de datos

No requiere cambios en estructura ni datos.

## Instalación

1. Respaldar el proyecto actual.
2. Reemplazarlo con el contenido del paquete.
3. Reiniciar Apache.
4. Presionar `Ctrl + F5` en el navegador.
5. Ingresar como Administrador EV y abrir:
   `Administración → Configuración de plataforma`.

## Pruebas recomendadas

1. Pasar el cursor por los indicadores superiores.
2. Pasar el cursor por una funcionalidad y una regla de monetización.
3. Abrir `Configurar` y confirmar que la fila quede seleccionada en naranja.
4. Cambiar un dato y confirmar el aviso `Cambio sin guardar`.
5. Guardar y comprobar los estados `Guardando…` y `Guardado`.
6. Verificar el registro en Historial.
7. Repetir desde una resolución móvil.
