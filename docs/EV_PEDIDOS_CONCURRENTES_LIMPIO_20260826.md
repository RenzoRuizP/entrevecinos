# EV - Pedidos concurrentes (modelo limpio)

## Regla funcional

El vendedor puede aceptar y gestionar múltiples pedidos en paralelo. Cada pedido conserva un ciclo de vida independiente y ningún pedido activo bloquea la aceptación de otro.

## Estados de solicitud

Toda solicitud nueva inicia en `pendiente_vendedor` y dispone de su propia ventana de respuesta. Al aceptar, pasa a la fase `pedido` con el estado que corresponda según preparación.

## Base de datos

El modelo `pedido` no conserva campos de secuenciamiento entre solicitudes. La migración `database/migrations/20260826_eliminar_flujo_secuencial_pedidos.sql` normaliza datos antiguos y elimina las columnas obsoletas si existen.

## Regresión obligatoria

1. Crear tres solicitudes para el mismo vendedor.
2. Aceptar las tres sin finalizar ninguna previamente.
3. Avanzar cada una de forma independiente.
4. Rechazar/cancelar una y verificar que las demás no cambian.
5. Validar timeout, notificaciones, billetera/comisión y cierre.
6. Validar comprador y vendedor en escritorio, tablet y móvil.
