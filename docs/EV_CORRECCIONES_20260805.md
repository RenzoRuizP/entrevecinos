# Entre Vecinos — Paquete de actualización 2026-08-05

## Alcance

Este paquete consolida las correcciones solicitadas para configuración operativa, billetera/recargas, menú del vecino, registro de usuario, modal gerencial y carga de sesión.

## Cambios funcionales

### Configuración de plataforma

- Las reglas se resuelven con la residencia activa real del usuario y con prioridad de comunidad sobre la configuración global.
- La disponibilidad de Billetera y Recargas se valida tanto en la interfaz como en las rutas y API del servidor.
- Cuando Billetera está deshabilitada o no visible, se ocultan el menú, el saldo del panel principal y los accesos rápidos relacionados.
- Cuando Recargas está deshabilitada, no se muestra la acción para recargar y el servidor rechaza intentos directos de registro o subsanación.
- Se añadieron validaciones de servidor para Marketplace, compras, solicitudes de servicio y creación de publicaciones según su configuración efectiva.
- El bono de bienvenida, su monto, la comisión por venta de producto y el descuento desde billetera para productos preparados utilizan las reglas configuradas para el alcance correspondiente.

### Navegación del vecino

- Se incorporó el menú **Inicio** para el usuario vecino.
- Dentro de Inicio se añadió el acceso **Menú principal**.
- Los módulos sujetos a configuración se ocultan cuando no están disponibles para la comunidad activa.

### Modal “Definir meta de ingresos”

- Se reemplazó el cierre por un botón accesible con icono blanco, área táctil, estados hover/focus/active y adaptación móvil.

### Modal “Inactivar cuenta”

- El botón principal ahora se llama **Aceptar**.
- Los botones Aceptar y Cancelar incluyen sus respectivos iconos y estilos EV.

### Inicio de sesión y loading

- Se sustituyó la barra de progreso del mensaje de bienvenida por el loading compacto **Cargando...**.
- Se evitó la superposición de dos cargadores durante la redirección posterior al login.

### Modal “Crear mi usuario”

- Los pasos Datos personales, Residencia y Datos de la cuenta se consolidaron como cards blancos completos, incluyendo su título dentro del card.
- Se mantuvo el comportamiento responsivo y la jerarquía visual de la etapa Legal.

## Archivos principales actualizados

- `index.php`
- `controllers/MenuPrincipalController.php`
- `controllers/billeteraController.php`
- `controllers/productoController.php`
- `controllers/api/apiBilleteraController.php`
- `controllers/api/apiDashboardController.php`
- `controllers/api/apiProductoController.php`
- `controllers/api/apiRecargaSaldoController.php`
- `controllers/api/apiSoporteUsuariosController.php`
- `models/ConfiguracionPlataforma.php`
- `models/Pedido.php`
- `views/billeteraView.php`
- `views/dashboardGerencialView.php`
- `views/login.php`
- `views/menuPrincipalContenido.php`
- `views/productoView.php`
- `views/estilos/billeteraEstilo.php`
- `views/estilos/dashboardGerencialEstilo.php`
- `views/estilos/login.estilo.php`
- `views/js/atenderCuentasUsuario.js`
- `views/js/billetera.js`
- `views/js/iniciarSesion.js`

## Validaciones realizadas

- Validación de sintaxis PHP sobre el proyecto, excluyendo dependencias de terceros: correcta.
- Validación de sintaxis JavaScript de los archivos modificados: correcta.
- Verificación estática de rutas, controles de menú y bloqueos de API: correcta.

## Base de datos

Este paquete no requiere un script SQL adicional. Trabaja con las tablas y reglas de configuración ya incluidas en la base de datos entregada por el usuario.

## Pruebas recomendadas en XAMPP

1. Deshabilitar Billetera para Villa Flores y comprobar que desaparezca del menú y panel principal del vecino.
2. Mantener Billetera activa, deshabilitar Billetera visible y comprobar el mismo ocultamiento.
3. Deshabilitar Recargas y comprobar que no aparezca “Recargar saldo” y que una petición directa sea rechazada.
4. Volver a habilitar las reglas y confirmar que menú, vista y operación se restauren.
5. Probar Inicio > Menú principal con un usuario vecino en escritorio y móvil.
6. Revisar hover/focus de la X del modal Definir meta de ingresos.
7. Revisar el modal Inactivar cuenta y sus botones Aceptar/Cancelar.
8. Recorrer los cuatro pasos de Crear mi usuario en escritorio, tablet y móvil.
