# Plan de pruebas — Control de funcionalidades y monetización

## Preparación

- Ejecutar primero el script SQL.
- Instalar el proyecto actualizado.
- Tener una cuenta `admin`, una cuenta `soporte` y al menos dos vecinos de la comunidad piloto.
- Registrar Villa Flores con sus datos oficiales si todavía no existe.

## A. Acceso y seguridad

1. Ingresar como `admin`.
2. Confirmar que aparece **Administración > Configuración de plataforma**.
3. Ingresar como vecino y como soporte.
4. Confirmar que no aparece el menú administrativo.
5. Intentar abrir `/configuracion-plataforma` como vecino.
6. Resultado esperado: acceso rechazado o redirección al panel.

## B. Aislamiento por comunidad

1. Seleccionar Villa Flores y desactivar Marketplace.
2. Ingresar como vecino de Villa Flores.
3. Confirmar que Marketplace no aparece y la URL directa es bloqueada.
4. Ingresar como vecino de otra comunidad sin esa configuración.
5. Confirmar que conserva la configuración global.

## C. Perfil de piloto — días 1 al 15

1. Seleccionar Villa Flores.
2. Pulsar **Aplicar perfil gratuito del piloto**.
3. Verificar:
   - Publicar productos: activo.
   - Publicar servicios: activo.
   - Marketplace: inactivo.
   - Comprar productos: inactivo.
   - Solicitar servicios: inactivo.
   - Billetera: inactiva.
   - Comisión producto: 0 %.
   - Recargas: desactivadas.
   - Billetera visible: no.
   - Bono de bienvenida: desactivado.
4. Como vendedor, registrar y publicar un producto.
5. Como vendedor, registrar y publicar un servicio.
6. Intentar acceder directamente al marketplace, billetera y recargas.
7. Resultado esperado: las publicaciones funcionan; las funciones restringidas quedan bloqueadas también en backend.

## D. Apertura del día 16

1. Como administrador, activar manualmente para Villa Flores:
   - Marketplace.
   - Comprar productos.
   - Solicitar servicios.
2. Mantener la billetera y recargas desactivadas.
3. Como comprador, entrar al marketplace.
4. Confirmar que puede ver publicaciones.
5. Solicitar un producto normal.
6. Solicitar un producto que requiere preparación.
7. Resultado esperado: el segundo pedido se registra sin consultar ni descontar saldo de billetera.
8. Solicitar un servicio.
9. Resultado esperado: la solicitud se registra correctamente.

## E. Comisión cero y congelamiento

1. Crear un pedido en Villa Flores durante el piloto.
2. Aceptarlo como vendedor.
3. Verificar en `pedido`:
   - `comision_ev_porcentaje = 0`.
   - `comision_ev_monto = 0`.
   - `comision_ev_pendiente = 0`.
   - `modalidad_monetizacion = 'piloto_gratuito'`.
   - `monetizacion_snapshot_json` contiene la regla aplicada.
4. Cambiar posteriormente la comisión de Villa Flores a otro valor.
5. Confirmar que el pedido anterior mantiene comisión cero.
6. Crear un pedido nuevo y confirmar que utiliza la nueva regla.

## F. Billetera, recargas y bono

1. Con el perfil piloto activo, ingresar como vecino.
2. Confirmar que no aparece Billetera.
3. Probar la URL directa y los endpoints de saldo.
4. Resultado esperado: acceso bloqueado.
5. Intentar registrar una recarga.
6. Resultado esperado: operación bloqueada.
7. Aprobar una cuenta nueva de Villa Flores desde soporte.
8. Resultado esperado: la cuenta se aprueba sin acreditar S/15 ni comunicar un bono inexistente.

## G. Programación por fechas

1. Configurar una funcionalidad en modo programado.
2. Definir inicio futuro y fin posterior, usando hora de Lima.
3. Antes del inicio, confirmar que hereda la regla global.
4. Durante la vigencia, confirmar que utiliza la regla programada.
5. Después del fin, confirmar que vuelve a la regla global.
6. Intentar guardar una fecha final anterior a la inicial.
7. Resultado esperado: validación rechazada.

## H. Historial

1. Modificar una funcionalidad.
2. Modificar una regla de monetización.
3. Abrir la pestaña Historial.
4. Confirmar fecha, concepto, motivo y administrador.

## I. Reglas aún no cobrables

1. Intentar configurar un valor mayor que cero para:
   - Publicación de productos.
   - Publicación diaria de servicios.
   - Comisión por servicios.
2. Resultado esperado: EV rechaza el cambio porque el flujo operativo de cobro todavía no está habilitado.

## J. Regresión mínima

- Inicio de sesión.
- Registro de usuario.
- Aprobación de cuenta.
- Creación, edición y aprobación de publicación.
- Marketplace.
- Pedido de producto.
- Solicitud de servicio.
- Notificaciones.
- Calificaciones.
- Dashboard de soporte.
- Uso móvil.

## Validaciones técnicas realizadas en el paquete

- Sintaxis PHP: 183 archivos revisados, sin errores.
- Sintaxis JavaScript: 47 archivos revisados, sin errores.
- Pruebas estáticas de alcance y rol administrador: aprobadas.
- Verificación estática del script SQL: aprobada.

No se ejecutó una prueba integrada contra MariaDB dentro del entorno de preparación porque el contenedor disponible no incluye servidor MariaDB ni el controlador `pdo_mysql`. Por ello, las pruebas funcionales de este documento deben ejecutarse en la instalación local de EV después de aplicar el SQL.
