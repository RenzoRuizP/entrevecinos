# Manifiesto de cambios — Combobox de alcance

## Archivos modificados

1. `views/configuracionPlataformaView.php`
   - Reemplazo del selector tradicional por un combobox accesible con búsqueda.

2. `views/estilos/configuracionPlataformaEstilo.php`
   - Estilos EV para campo, menú, resultados, selección, estados vacíos y responsive.

3. `views/js/configuracionPlataforma.js`
   - Búsqueda con debounce y cancelación de peticiones.
   - Navegación por teclado.
   - Selección segura y restauración del valor vigente.
   - Carga única de configuración al cambiar de alcance.

4. `models/ConfiguracionPlataforma.php`
   - Búsqueda limitada de condominios y urbanizaciones.
   - Obtención individual del alcance seleccionado.

5. `controllers/api/apiConfiguracionPlataformaController.php`
   - Nuevo endpoint administrativo de búsqueda.
   - La carga principal deja de enviar todo el catálogo de comunidades.

6. `index.php`
   - Registro de la ruta interna de búsqueda.

## Archivos agregados

- `docs/MEJORA_COMBOBOX_ALCANCE_CONFIGURACION_EV.md`
- `docs/MANIFIESTO_COMBOBOX_ALCANCE_CONFIGURACION_EV.md`

## Base de datos

Sin cambios.
