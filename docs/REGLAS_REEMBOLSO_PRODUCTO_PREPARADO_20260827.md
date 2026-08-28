# EV - Reglas de cancelación y devolución para producto preparado

Fecha de consolidación: 2026-08-27

## Regla funcional

1. Al registrar una solicitud de producto preparado, cuando la configuración comercial de EV habilita el débito por billetera, se debita el monto total del producto y se registra el movimiento.
2. De 00:00 a 01:59 el comprador no puede cancelar la solicitud.
3. Desde 02:00, mientras la solicitud siga en `pendiente_vendedor`, el comprador puede cancelarla por demora del vendedor. Si hubo débito de billetera, EV devuelve el 100 % del monto realmente debitado y registra el movimiento.
4. El vendedor mantiene la posibilidad de aceptar o rechazar hasta el límite absoluto de 04:00.
5. Al cumplirse 04:00 sin respuesta, EV cierra la solicitud como `sin_respuesta_vendedor`. Si hubo débito de billetera, devuelve el 100 % y registra el movimiento.
6. Si el vendedor rechaza la solicitud, EV devuelve el 100 % del monto debitado.
7. Si el vendedor cancela un pedido que había sido aceptado, EV devuelve el 100 % del monto debitado, incluso si el producto ya estaba en preparación.
8. Una vez que el vendedor acepta un producto preparado, el comprador ya no puede cancelarlo voluntariamente.
9. La devolución es idempotente: un pedido no puede acreditar dos veces el mismo importe a la billetera.
10. Las operaciones sobre pedido y billetera utilizan bloqueo transaccional para resolver carreras entre aceptar, rechazar, cancelar y timeout.

## Casos mínimos de regresión

- Preparado pagado: intento de cancelación a 01:30 => bloqueado.
- Preparado pagado: cancelación a 02:00 => cancelado + devolución 100 % + movimiento crédito.
- Preparado pagado: vendedor acepta a 03:00 => pedido en preparación; comprador ya no puede cancelar.
- Preparado pagado: vendedor rechaza antes de 04:00 => devolución 100 % + movimiento crédito.
- Preparado pagado: sin respuesta hasta 04:00 => `sin_respuesta_vendedor` + devolución 100 %.
- Preparado pagado: vendedor cancela después de aceptar => devolución 100 %.
- Repetir la misma operación de devolución => no genera un segundo crédito.
- Carrera cancelar comprador vs aceptar vendedor entre 02:00 y 03:59 => solo una transición debe ganar.
- Intento de aceptación/rechazo una vez vencidos los 04:00 => debe rechazarse y prevalecer el timeout.
- Pedidos concurrentes del mismo vendedor => cancelar/reembolsar un pedido no altera los demás.
