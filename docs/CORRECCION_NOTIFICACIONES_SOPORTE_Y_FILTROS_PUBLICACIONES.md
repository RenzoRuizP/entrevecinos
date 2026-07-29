# Corrección de notificaciones de Soporte y filtros de Mis Publicaciones

## Alcance

### Notificaciones del usuario Soporte

- La campana muestra únicamente novedades no leídas.
- Las atenciones operativas pendientes permanecen en el Panel de Soporte y no inflan el contador de la campana.
- Cuando un vecino envía o reenvía una publicación a revisión, se crea una notificación real para cada usuario habilitado con rol Soporte.
- La campana consulta el contador desde cualquier módulo cada 30 segundos y también al cambiar de vista, volver a la pestaña o abrir el desplegable.
- Al abrir una notificación queda marcada como leída y desaparece del desplegable de pendientes.
- Al aprobar, observar o rechazar una publicación, cualquier notificación pendiente asociada queda resuelta automáticamente.

### Mis Publicaciones

- Se eliminó la tarjeta “Total de publicaciones”.
- Se retiró el botón circular de actualización.
- El selector de Estado quedó integrado en la cabecera con sus contadores.
- El bloque de filtros se dividió en filtros principales y “Más filtros”.
- Precio mínimo, precio máximo y orden permanecen cerrados inicialmente.
- Los resultados se aplican únicamente mediante “Aplicar filtros”.
- Se retiró el texto contradictorio de actualización automática.
- La distribución es responsive para web y móvil.

## Archivos modificados

- `models/Notificacion.php`
- `controllers/api/apiNotificacionesController.php`
- `controllers/api/apiProductoController.php`
- `controllers/api/apiSoporteProductosController.php`
- `views/js/notificacionesGlobales.js`
- `views/js/notificaciones.js`
- `views/js/atenderPublicacion.js`
- `views/productoView.php`
- `views/estilos/productoEstilo.php`
- `views/js/producto.js`

## Pruebas sugeridas

1. Iniciar sesión como Soporte y permanecer en un módulo distinto al Panel de Soporte.
2. Desde otra sesión de vecino, enviar una publicación a revisión.
3. Esperar hasta 30 segundos o abrir la campana; confirmar que aparezca la novedad.
4. Abrir la notificación y confirmar que el contador disminuya.
5. Aprobar, observar o rechazar la publicación y comprobar que no vuelva a aparecer como no leída.
6. En Mis Publicaciones, cambiar el Estado y validar la actualización inmediata.
7. Abrir Filtros, completar campos y confirmar que no se aplican hasta pulsar “Aplicar filtros”.
8. Abrir “Más filtros” y validar precio y orden en escritorio y móvil.
