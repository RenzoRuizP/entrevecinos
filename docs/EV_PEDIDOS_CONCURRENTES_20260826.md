# EV — Pedidos concurrentes por vendedor (2026-08-26)

## Objetivo funcional

Se elimina la restricción que obligaba al vendedor a terminar un pedido antes de aceptar el siguiente. Desde esta versión, cada solicitud de producto es independiente y el vendedor puede aceptar y gestionar varios pedidos simultáneamente.

## Nuevo flujo

1. El comprador registra una solicitud.
2. La solicitud queda en `fase = solicitud` y `estado_actual = pendiente_vendedor`.
3. Cada solicitud recibe su propia ventana de respuesta de 240 segundos.
4. La existencia de otros pedidos pendientes o en proceso del mismo vendedor no modifica la solicitud nueva.
5. El vendedor puede aceptar cualquier solicitud pendiente aunque ya tenga otros pedidos en `en_preparacion`, `despachando`, `listo_para_entrega`, `en_camino`, `en_punto_entrega` o `entregado_vendedor`.
6. Al aceptar, únicamente ese pedido pasa a `fase = pedido` y a su estado inicial correspondiente (`en_preparacion` o `despachando`).
7. Rechazar, cancelar, vencer, avanzar o finalizar un pedido no altera el estado de los demás.

## Compatibilidad con pedidos antiguos en cola

No se requiere cambiar la estructura de la base de datos.

Los registros antiguos que todavía tengan `cola_pendiente_confirmacion` o `cola_aceptada` se normalizan de forma defensiva a `pendiente_vendedor` al sincronizar pedidos. La normalización:

- coloca `posicion_cola = 0`;
- asigna una nueva ventana de respuesta de 240 segundos;
- registra un evento de historial `migracion_flujo_concurrente`;
- genera una notificación al vendedor indicando que la solicitud está disponible.

La ruta histórica `POST /api/pedidos/{id}/confirmar-cola` se conserva únicamente por compatibilidad con clientes/caché anteriores; ya no forma parte del flujo visible.

## Base de datos y concurrencia

- La tabla `pedido` no posee una restricción UNIQUE que impida varios pedidos activos por vendedor, por lo que no se requiere migración estructural.
- `posicion_cola` se conserva temporalmente por compatibilidad, pero los pedidos nuevos se registran con valor `0`.
- La aceptación bloquea únicamente la fila del pedido correspondiente mediante `FOR UPDATE`, evitando que el mismo pedido se acepte dos veces en una carrera concurrente.
- Las operaciones de billetera del vendedor mantienen el bloqueo `FOR UPDATE` de su billetera, de modo que las comisiones de dos aceptaciones cercanas se serializan sobre el saldo.
- El esquema actual de `producto` no contiene un campo de stock/inventario. Por ello este cambio no introduce una reserva de stock inexistente ni modifica reglas de inventario.

## UI/UX

Se retiró del flujo activo del comprador y vendedor la presentación de:

- “Cola #N”;
- “Posición en cola”;
- “Aceptar cola”;
- mensajes que indicaban que era necesario terminar el pedido anterior.

Los estados legacy se muestran defensivamente como “Pendiente” si aparecieran antes de ser normalizados por backend. Las vistas conservan su comportamiento responsive en escritorio, tablet y móvil.

## Matriz mínima de pruebas DEV/QA

1. Crear pedido A y pedido B para el mismo vendedor antes de aceptar A.
2. Confirmar que A y B aparecen como solicitudes pendientes independientes.
3. Aceptar A y, sin finalizarlo, aceptar B.
4. Confirmar que A y B quedan simultáneamente en proceso.
5. Avanzar A de estado y verificar que B no cambia.
6. Rechazar una solicitud C mientras A y B siguen activos y verificar que A/B no cambian.
7. Dejar vencer una solicitud D y verificar que solo D pase a `sin_respuesta_vendedor`.
8. Cancelar/finalizar A y verificar que B permanece en su estado previo.
9. Probar productos con y sin preparación, incluida la lógica de billetera/comisión vigente.
10. Probar un registro legacy `cola_aceptada` o `cola_pendiente_confirmacion` y verificar su normalización automática.
11. Validar Mis pedidos comprador/vendedor y Marketplace en escritorio, tablet y móvil.
