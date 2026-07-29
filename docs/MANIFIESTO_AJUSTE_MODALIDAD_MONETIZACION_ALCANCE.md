# Manifiesto de cambios

## Archivos modificados

- `models/ConfiguracionPlataforma.php`
- `controllers/api/apiConfiguracionPlataformaController.php`
- `views/configuracionPlataformaView.php`
- `views/estilos/configuracionPlataformaEstilo.php`
- `views/js/configuracionPlataforma.js`

## Archivos agregados

- `docs/AJUSTE_MODALIDAD_Y_MONETIZACION_POR_ALCANCE.md`
- `docs/PRUEBAS_AJUSTE_MODALIDAD_MONETIZACION_ALCANCE.md`
- `docs/MANIFIESTO_AJUSTE_MODALIDAD_MONETIZACION_ALCANCE.md`

## Base de datos

No se modifica la estructura de tablas. El script SQL separado actualiza únicamente tres descripciones del catálogo de monetización para alinearlas con la configuración por alcance.

## Resultado funcional

- El Administrador EV elige modalidad manual o programada en el alcance global y en cualquier condominio o urbanización.
- La configuración manual permanece hasta un nuevo cambio administrativo.
- La configuración programada acepta inicio, fin o ambas fechas.
- Los importes de monetización mayores que cero pueden guardarse por alcance.
- Villa Flores puede mantener reglas gratuitas propias sin cambiar Todo Entre Vecinos ni otras comunidades.
- El perfil piloto permite al Administrador elegir manual o programado antes de aplicarlo.
