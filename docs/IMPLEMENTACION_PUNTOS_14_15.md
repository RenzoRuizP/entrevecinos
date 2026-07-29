# Entre Vecinos — Implementación de los puntos 14 y 15

Fecha de preparación: 26 de julio de 2026

## Alcance entregado

### Punto 14 — Control de funcionalidades

Se incorporó una configuración administrativa por alcance:

- Global.
- Condominio.
- Urbanización.

Funcionalidades configurables:

- Publicar productos.
- Publicar servicios.
- Marketplace.
- Comprar productos.
- Solicitar servicios.
- Billetera.
- Promociones (interruptor preparado; el módulo funcional de campañas y ofertas no forma parte de este paquete).

Cada funcionalidad permite:

- Activación o desactivación manual.
- Activación programada con fecha de inicio y fin.
- Mensaje para el usuario cuando está desactivada.
- Motivo administrativo.
- Historial de cambios.

La protección se aplica en dos niveles:

1. Interfaz: oculta menús, accesos, cards y botones.
2. Backend: bloquea rutas y endpoints aunque se intente acceder directamente.

### Punto 15 — Configuración de monetización

Reglas incorporadas:

- Comisión por venta de productos.
- Costo de publicación de productos.
- Costo diario de publicación de servicios.
- Comisión por servicios.
- Publicaciones destacadas.
- Descuentos desde billetera para pedidos preparados.
- Recargas.
- Visibilidad de billetera.
- Bono de bienvenida.
- Monto del bono de bienvenida.

Aplicación operativa actual:

- La comisión por producto se calcula con la regla de la comunidad y queda congelada en el pedido.
- La habilitación de descuentos desde billetera se aplica al registrar pedidos preparados.
- La visibilidad de billetera, recargas, destacados y bono de bienvenida se controla desde administración.
- Los costos por publicar productos o servicios y la comisión de servicios deben permanecer en cero. El sistema bloquea valores mayores que cero porque sus flujos de cobro posteriores al piloto todavía no están implementados.

## Perfil gratuito del piloto

El administrador general dispone del botón **Aplicar perfil gratuito del piloto**. Por seguridad:

- Solo puede aplicarse a un condominio o urbanización específicos.
- No puede aplicarse masivamente al alcance global.
- Se ejecuta dentro de una sola transacción: si alguna regla falla, no queda una configuración parcial.

Configuración aplicada:

### Funcionalidades

| Funcionalidad | Estado inicial |
|---|---:|
| Publicar productos | Activa |
| Publicar servicios | Activa |
| Marketplace | Inactiva |
| Comprar productos | Inactiva |
| Solicitar servicios | Inactiva |
| Billetera | Inactiva |
| Promociones | Activa como interruptor de reserva |

### Monetización

| Regla | Valor |
|---|---:|
| Comisión de productos | 0 % |
| Publicación de productos | S/0 |
| Publicación diaria de servicios | S/0 |
| Comisión de servicios | 0 % |
| Publicaciones destacadas | Desactivadas |
| Descuento desde billetera | Desactivado |
| Recargas | Desactivadas |
| Billetera visible | No |
| Bono de bienvenida | Desactivado |
| Monto del bono | S/0 |

## Operación recomendada del piloto

### Días 1 al 15

1. Registrar oficialmente Villa Flores en EV si todavía no existe.
2. Ingresar como administrador general.
3. Abrir **Administración > Configuración de plataforma**.
4. Seleccionar Villa Flores.
5. Aplicar el perfil gratuito del piloto.
6. Confirmar que los vendedores pueden publicar productos y servicios.
7. Confirmar que marketplace, compras, solicitudes, billetera y recargas permanecen bloqueados.

### Día 16

En el mismo módulo, seleccionar Villa Flores y activar manualmente:

- Marketplace.
- Comprar productos.
- Solicitar servicios.

Mantener:

- Billetera inactiva.
- Billetera visible: No.
- Recargas: desactivadas.
- Comisión de productos: 0 %.
- Descuento desde billetera: desactivado.

### Cierre del piloto

No desactivar **Comprar productos** ni **Solicitar servicios** mientras existan operaciones pendientes, porque esas opciones también controlan la visibilidad de los módulos de seguimiento. Primero deben cerrarse los pedidos, servicios, incidencias y calificaciones pendientes.

## Congelamiento comercial del pedido

Cada nuevo pedido almacena:

- Porcentaje de comisión aplicado.
- Identificador de la configuración de comisión.
- Modalidad de monetización.
- Fotografía JSON de las reglas utilizadas.
- Fecha de captura en zona horaria America/Lima.

Esto impide cobrar retroactivamente una comisión futura a pedidos creados durante el piloto.

## Compatibilidad

El script SQL crea valores globales equivalentes al comportamiento anterior:

- Comisión de productos: 10 %.
- Billetera y recargas activas.
- Débito para productos preparados activo.
- Bono de bienvenida activo por S/15.

El comportamiento cambia a gratuito únicamente cuando el administrador aplica el perfil del piloto al condominio o urbanización correspondiente.

## Villa Flores

La base de datos recibida contiene Los Faisanes, El Pilar y Urbanización Los Álamos, pero no contiene Villa Flores. El script no inventa su dirección, distrito ni identificador. Villa Flores debe registrarse mediante los datos oficiales antes de seleccionarla en el módulo administrativo.

## Orden obligatorio de instalación

1. Crear una copia de seguridad de la base de datos y del proyecto actual.
2. Ejecutar `Script_SQL_Puntos_14_15_EV.sql` en la base de datos local.
3. Instalar el proyecto actualizado.
4. Iniciar sesión como administrador.
5. Abrir la configuración y verificar los catálogos.
6. Aplicar el perfil únicamente a la comunidad piloto.

No debe instalarse primero el código y después el SQL, porque el nuevo registro de pedidos utiliza las columnas comerciales agregadas por el script.

## Archivos nuevos

- `controllers/api/apiConfiguracionPlataformaController.php`
- `controllers/configuracionPlataformaController.php`
- `middleware/FuncionalidadGuard.php`
- `models/ConfiguracionPlataforma.php`
- `views/configuracionPlataformaView.php`
- `views/estilos/configuracionPlataformaEstilo.php`
- `views/funcionalidadNoDisponibleView.php`
- `views/js/configuracionPlataforma.js`

## Archivos modificados

- `controllers/MenuPrincipalController.php`
- `controllers/api/apiBilleteraController.php`
- `controllers/api/apiPedidoController.php`
- `controllers/api/apiProductoController.php`
- `controllers/api/apiRecargaSaldoController.php`
- `controllers/api/apiSolicitudServicioController.php`
- `controllers/api/apiSoporteRecargasController.php`
- `controllers/api/apiSoporteUsuariosController.php`
- `controllers/atenderRecargasController.php`
- `controllers/billeteraController.php`
- `controllers/marketplaceController.php`
- `controllers/misPedidosCompradorController.php`
- `controllers/misPedidosVendedorController.php`
- `controllers/misSolicitudesServicioCompradorController.php`
- `controllers/misSolicitudesServicioVendedorController.php`
- `controllers/productoController.php`
- `index.php`
- `models/Pedido.php`
- `views/MenuPrincipalView.php`
- `views/js/marketplace.js`
- `views/menuPrincipalContenido.php`
- `views/productoView.php`
- `views/scripts/menuPrincipalScripts.php`

## Seguridad incorporada

- Solo el rol `admin` puede abrir y modificar la configuración.
- Las operaciones administrativas POST exigen token CSRF de la sesión.
- Se valida que el condominio o urbanización exista y esté activo.
- Un alcance inválido no puede convertirse silenciosamente en configuración global.
- El perfil masivo se guarda de forma atómica.
- Los valores JSON expuestos a JavaScript se codifican contra inyección de etiquetas.
