# EV - Validación de regresión: pedidos concurrentes

Fecha: 2026-08-26

## Alcance implementado

- El vendedor puede aceptar múltiples pedidos sin finalizar previamente otro pedido activo.
- Cada pedido avanza, se rechaza, cancela y finaliza de forma independiente.
- El modelo activo de `pedido` ya no contiene campos de secuenciamiento entre solicitudes.
- Se eliminó la ruta antigua de confirmación de espera.
- Dashboard, comprador, vendedor y polling usan únicamente `pendiente_vendedor` como estado pendiente.
- La migración normaliza datos previos y elimina los campos/objetos obsoletos del esquema.

## Validaciones automatizadas ejecutadas

- PHP: lint de todos los archivos PHP first-party del proyecto: OK.
- JavaScript: `node --check` de todos los JS first-party no minificados: OK.
- Rutas: 181 handlers declarados en `index.php` contrastados contra métodos existentes: 0 handlers faltantes.
- Auditoría preventiva `tools/ev_predeploy_audit.php`: OK, sin hallazgos bloqueantes.
- Auditoría específica `tools/ev_pedidos_concurrentes_audit.php`: OK.
- Búsqueda global en código/esquemas activos de identificadores legacy del flujo secuencial: 0 coincidencias.
- Dump limpio `database/scriptBd/EV_bk_25082026_limpio_sin_colas.sql`: sin identificadores legacy.

## Limitación del entorno de validación

El entorno de construcción no tiene acceso a la instancia MariaDB de DEV del usuario. Por ello, las pruebas end-to-end que requieren datos reales, sesión autenticada y navegador deben ejecutarse en DEV después de aplicar la migración. La sintaxis, rutas, dependencias estáticas y consistencia código/esquema sí fueron verificadas.

## Matriz E2E obligatoria en DEV

### Pedidos concurrentes

1. Comprador A crea pedido #1 al vendedor V.
2. Comprador B crea pedido #2 al mismo vendedor V.
3. Comprador C crea pedido #3 al mismo vendedor V.
4. V acepta #1 y lo deja en proceso.
5. Sin finalizar #1, V acepta #2.
6. Sin finalizar #1/#2, V acepta #3.
7. Verificar que #1/#2/#3 permanezcan activos simultáneamente.
8. Avanzar #2 y verificar que #1/#3 no cambien.
9. Cancelar o rechazar un pedido permitido y verificar que los demás no cambien.

### Estados y timeout

1. Crear una solicitud y dejar vencer la ventana de respuesta.
2. Confirmar transición a `sin_respuesta_vendedor`.
3. Confirmar devolución/liberación correspondiente cuando aplique billetera o penalidad.
4. Confirmar que el timeout de una solicitud no cambia otros pedidos del mismo vendedor.

### Billetera y monetización

1. Aceptar dos pedidos sucesivos que generen comisión.
2. Confirmar saldo, movimientos y comisión por cada pedido de forma independiente.
3. Probar saldo insuficiente si la configuración comercial aplicable lo permite.
4. Probar devolución ante rechazo/cancelación/sin respuesta de producto preparado cuando corresponda.

### Comprador

1. Mis pedidos: pendientes, en proceso y finalizados.
2. Cancelación dentro/fuera de la regla temporal vigente.
3. Confirmación de entrega.
4. Calificación posterior a entrega.
5. Notificaciones por aceptación, avance, rechazo, cancelación y entrega.

### Vendedor

1. Mis pedidos: múltiples pendientes y múltiples en proceso.
2. Aceptar/rechazar cada solicitud de forma independiente.
3. Avanzar estados de un pedido sin alterar otro.
4. Cancelación por no recojo cuando corresponda.
5. Notificaciones de nuevas solicitudes.

### Módulos no relacionados - smoke regression

1. Login/logout y expiración JWT de 2 horas.
2. Registro/mi cuenta/cambio de residencia.
3. Mis publicaciones: crear, editar, enviar a revisión y aprobar/rechazar desde Soporte.
4. Marketplace: listar y abrir producto.
5. Centro de notificaciones.
6. Billetera/recargas.
7. Servicios: solicitud y seguimiento.
8. Dashboard vecino/soporte/admin según rol.
9. Menú lateral y navegación.
10. Responsive: escritorio, tablet y móvil.

## Criterio de cierre

La versión puede promoverse de DEV a QA cuando la migración termine sin error, sus consultas de verificación devuelvan 0 y la matriz E2E anterior no presente regresiones funcionales.
