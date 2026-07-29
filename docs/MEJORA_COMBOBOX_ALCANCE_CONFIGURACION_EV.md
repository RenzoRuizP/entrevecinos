# EV — Combobox de búsqueda para alcance de configuración

## Objetivo

Reemplazar el selector tradicional de alcance por un combobox controlado y escalable que permita localizar condominios y urbanizaciones por nombre sin cargar el catálogo completo en cada consulta de configuración.

## Comportamiento implementado

- Búsqueda parcial por nombre.
- Resultados limitados a 25 elementos por consulta.
- Opción global `Todo Entre Vecinos` disponible desde la búsqueda.
- Diferenciación visual entre `Condominio`, `Urbanización` y alcance global.
- Solo se aceptan valores existentes devueltos por el servidor.
- Navegación por teclado con flechas, `Enter`, `Escape` y `Tab`.
- Restauración de la selección vigente cuando se abandona una búsqueda sin elegir un resultado.
- Cancelación de búsquedas anteriores para evitar respuestas fuera de orden.
- Retardo de 220 ms antes de consultar mientras el administrador escribe.
- Una sola recarga de configuración al seleccionar un alcance distinto.
- Diseño responsive y consistente con el estándar UX/UI de Soporte y Administración EV.

## Escalabilidad

La carga principal de configuración ya no devuelve todos los condominios y urbanizaciones. El combobox utiliza un endpoint específico de búsqueda y recibe como máximo 25 coincidencias. Esto evita que el tamaño de la respuesta crezca junto con el número de comunidades.

## Seguridad

- Endpoint disponible únicamente para el rol Administrador EV.
- La selección queda representada por `tipo_alcance` y `codigo_alcance` válidos.
- El texto escrito por el administrador nunca se utiliza directamente como alcance.
- Las consultas utilizan sentencias preparadas.

## Archivos modificados

- `views/configuracionPlataformaView.php`
- `views/estilos/configuracionPlataformaEstilo.php`
- `views/js/configuracionPlataforma.js`
- `models/ConfiguracionPlataforma.php`
- `controllers/api/apiConfiguracionPlataformaController.php`
- `index.php`

## Nueva ruta interna

`GET /api/admin/configuracion-plataforma/alcances?q={texto}&limit=25`

## Base de datos

No se requieren cambios de estructura ni datos.

## Pruebas mínimas en localhost

1. Ingresar como Administrador EV.
2. Abrir `Administración → Configuración de plataforma`.
3. Hacer clic en el campo `Alcance de configuración`.
4. Buscar `Villa` y seleccionar `Urbanización Villa Flores`.
5. Confirmar que el resumen y las reglas correspondan a Villa Flores.
6. Buscar otro condominio usando el teclado y seleccionarlo con `Enter`.
7. Escribir un nombre inexistente y confirmar el mensaje sin coincidencias.
8. Presionar `Escape` y confirmar que se restaure el alcance vigente.
9. Verificar en DevTools que cada búsqueda llama una vez al endpoint `/alcances` y que la configuración se recarga una sola vez al seleccionar.
