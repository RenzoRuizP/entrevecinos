# Validación EV — Productos preparados y billetera (R2)

Fecha: 27/08/2026

## Regla funcional aplicada

- Todo producto con `requiere_preparacion = 1` usa billetera obligatoriamente al registrar la solicitud.
- El débito corresponde al 100 % del subtotal real de la solicitud (`precio_unitario x cantidad`).
- El débito y el registro del pedido se ejecutan dentro de la misma transacción.
- Si el saldo no alcanza, la transacción se revierte y no se crea el pedido.
- El movimiento se registra con origen `PEDIDO_SOLICITUD_PREPARADA` y referencia al `codigo_pedido`.
- Si el vendedor rechaza, cancela o no responde al cumplir 4 minutos, se devuelve el 100 % del monto debitado.
- Desde los 2 minutos y antes del timeout, el comprador puede cancelar una solicitud aún pendiente y recibe el 100 % del monto debitado.
- Después de que el vendedor acepta un producto preparado, el comprador no puede cancelarlo voluntariamente.
- La devolución es idempotente mediante bandera de pedido y validación de movimiento existente.

## Saldo insuficiente

- El backend vuelve a validar el saldo bajo bloqueo `FOR UPDATE`.
- El SweetAlert muestra una acción `Ir a Mi billetera`.
- La navegación utiliza `EVNav.loadPage('/billetera')` cuando está disponible y un fallback a `MenuPrincipal?ev_goto=/billetera`.

## Mi billetera

- `PEDIDO_SOLICITUD_PREPARADA` se muestra como `Pago de producto preparado`.
- `PEDIDO_SOLICITUD_DEVOLUCION` y el alias histórico se muestran como `Devolución de pedido`.

## Validaciones técnicas realizadas

- `php -l` sobre PHP first-party: OK.
- `node --check` sobre JavaScript de `views/js`: OK.
- Auditoría `ev_reembolso_preparados_audit.php`: OK.
- Auditoría `ev_pedidos_concurrentes_audit.php`: OK.
- Auditoría preventiva `ev_predeploy_audit.php`: sin hallazgos bloqueantes.

## Pruebas funcionales recomendadas en DEV

1. Saldo S/ 30.00, preparado S/ 15.00 x 1: al enviar, saldo esperado S/ 15.00 y movimiento -S/ 15.00.
2. Saldo S/ 30.00, preparado S/ 15.00 x 4: no se registra pedido; aparece saldo insuficiente y acceso a Mi billetera.
3. Cancelar antes de 02:00: debe estar bloqueado.
4. Cancelar entre 02:00 y 03:59: devuelve el 100 % y registra movimiento.
5. Rechazo vendedor: devuelve el 100 % y registra movimiento.
6. Timeout a 04:00: devuelve el 100 % automáticamente y registra movimiento.
7. Vendedor acepta preparado: comprador ya no puede cancelarlo.
8. Cancelación del vendedor en estado permitido: devuelve el 100 %.
9. Repetir/recargar tras una devolución: no debe producirse una segunda acreditación.

La validación transaccional end-to-end requiere la instancia MariaDB de DEV; no puede certificarse desde el entorno de empaquetado sin acceso a esa base de datos.
