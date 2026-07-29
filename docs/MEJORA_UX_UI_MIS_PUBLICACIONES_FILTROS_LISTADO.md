# Mejora UX/UI — Mis Publicaciones

## Objetivo

Reducir el ruido visual de los estados, ordenar el bloque de filtros y corregir la alineación de la tabla de publicaciones en escritorio y móvil.

## Cambios aplicados

### Estados de publicaciones

- Se reemplazaron los siete botones de estado por un selector único.
- El selector muestra la cantidad correspondiente a cada estado.
- Se conserva el filtro por Todos, Aprobados, Pendientes, Observados, Rechazados, Borradores y Anulados.
- El total general permanece visible como indicador independiente.

### Filtros

- Se reorganizó el bloque bajo el título `Filtrar publicaciones`.
- Los filtros principales son búsqueda, publicación, tipo y categoría.
- Precio mínimo, precio máximo y orden se agrupan como filtros secundarios.
- Se mejoraron etiquetas, textos de ayuda, tamaños, espacios y comportamiento responsive.
- Se conserva la actualización automática y el botón Aplicar filtros.

### Tabla y tarjetas móviles

- Se controlaron los anchos y alineaciones de todas las columnas.
- El valor Producto o Servicio queda contenido dentro de la columna Publicación.
- Título y descripción admiten hasta dos líneas en escritorio.
- Precio, estado y acciones mantienen una alineación consistente.
- En móvil, cada fila se convierte en una tarjeta de dos columnas y los contenidos extensos ocupan todo el ancho.
- Los botones de acción se distribuyen de forma uniforme.

## Archivos modificados

- `views/productoView.php`
- `views/estilos/productoEstilo.php`
- `views/js/producto.js`

## Base de datos

No requiere cambios de base de datos.

## Pruebas recomendadas

1. Cambiar entre todos los estados desde el selector.
2. Buscar por título y descripción.
3. Combinar publicación, tipo y categoría.
4. Filtrar por rango de precio.
5. Cambiar el orden de resultados.
6. Limpiar todos los filtros.
7. Verificar productos y servicios con nombres extensos.
8. Verificar publicaciones observadas con mensajes largos de soporte.
9. Probar la tabla en escritorio, tablet y móvil.
10. Probar filas con uno, dos o tres botones de acción.
