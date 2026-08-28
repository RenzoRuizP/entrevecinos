# Entre Vecinos — R4 2026-08-27

## Orden de actualización en DEV

1. Realizar backup de la BD DEV.
2. Ejecutar `EV_migracion_publicacion_activo_switch_20260827.sql` en la BD actual.
3. Reemplazar el proyecto DEV por esta versión.
4. Cerrar sesión e iniciar sesión nuevamente.
5. Validar los escenarios indicados abajo.

## Cambios de esta ronda

- Publicaciones aprobadas: switch persistente Activo/Inactivo por publicación.
- Aprobación de Soporte/Administrador deja la publicación Activa por defecto.
- Una publicación aprobada Inactiva no aparece en Marketplace, pero conserva su aprobación.
- Rechazo del vendedor: se evita la superposición del modal global con el modal propio del seguimiento; queda un solo aviso para el comprador.
- Cancelación del vendedor: disponible después de aceptar y hasta antes de `entregado_vendedor`, con motivo obligatorio.
- Si el pedido tuvo débito de billetera, la cancelación del vendedor devuelve el 100 % mediante la lógica idempotente existente.
- En punto de entrega, los motivos atribuibles al comprador solo se habilitan después de vencer la ventana de recepción.

## Pruebas mínimas

1. Aprobar publicación y confirmar que aparece switch Activo.
2. Desactivar publicación y confirmar que desaparece del Marketplace sin perder estado Aprobado.
3. Reactivar publicación y confirmar que vuelve al Marketplace.
4. Rechazar una solicitud y confirmar que el comprador ve un único modal `Solicitud rechazada`.
5. Aceptar un pedido preparado, cancelarlo desde `En preparación` y comprobar devolución en billetera.
6. Aceptar un pedido no preparado, cancelarlo desde `Despachando` y comprobar cierre correcto.
7. Confirmar que ya no existe Cancelar pedido después de `Entregado por vendedor`.
8. Repetir en escritorio, tablet y móvil.
