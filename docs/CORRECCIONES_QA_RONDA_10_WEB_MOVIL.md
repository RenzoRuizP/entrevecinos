# Entre Vecinos — Correcciones QA web y móvil

Base utilizada: `Paquete_actualizacion_EV_UXUI_Mi_Billetera.zip`.

## Alcance implementado

1. **Ayuda EV**
   - Se reemplazó el mensaje provisional por orientación de uso.
   - Se agregó soporte exclusivo por WhatsApp: **996 524 992**.
   - Se agregó acceso directo a WhatsApp.
   - El botón principal ahora muestra **Aceptar**.
   - Se adaptó el modal a escritorio y móvil.

2. **Hora del chat de servicios**
   - La aplicación usa `America/Lima`.
   - Cada conexión MySQL usa la sesión `-05:00` para campos `TIMESTAMP`.
   - El chat interpreta y presenta la fecha en la zona horaria de Perú.

3. **Modal Rechazar solicitud**
   - El campo del motivo ocupa el ancho interno correcto.
   - Se corrigieron margen, `box-sizing`, foco y acciones apiladas en móvil.

4. **Revisión de imágenes de publicaciones**
   - Las imágenes del modal de Soporte son seleccionables.
   - Se agregó visor ampliado, cierre, navegación anterior/siguiente, teclado y gesto lateral móvil.

5. **Tabla Atender publicaciones**
   - Se restauró y dimensionó la columna **Publicación**.
   - Se corrigió el ajuste de línea y legibilidad del título.
   - En móvil la tabla conserva sus columnas mediante desplazamiento horizontal, sin comprimir ni superponer contenido.

6. **Campana de Soporte**
   - El badge suma cuentas pendientes, publicaciones por revisar, recargas por validar e incidencias de servicios.
   - El desplegable muestra accesos operativos directos por categoría.
   - El sondeo utiliza contadores mínimos y no carga la bandeja completa de Soporte.

7. **Comunidad**
   - El nombre del condominio o urbanización se adapta sin cortes agresivos.
   - La cabecera se reorganiza verticalmente en móvil.
   - Se mejoró la alineación del texto descriptivo y la tarjeta de comunidad.

8. **Menú lateral móvil**
   - Toda la barra es desplazable verticalmente.
   - Ayuda, Cerrar sesión, comunidad actual y Cambiar comunidad permanecen accesibles.
   - Se añadió espacio seguro inferior para barras del navegador y dispositivos con `safe-area`.

## Archivos modificados

- `.env.example`
- `Config/config.php`
- `database/Conexion.php`
- `controllers/api/apiNotificacionesController.php`
- `models/SoporteDashboard.php`
- `views/AtenderPublicacionView.php`
- `views/estilos/atenderPublicacionEstilo.php`
- `views/estilos/comunidadVecinoEstilo.php`
- `views/estilos/menuIzquierdaEstilo.php`
- `views/js/atenderPublicacion.js`
- `views/js/menuIzquierda.js`
- `views/js/menuPrincipalContenido.js`
- `views/js/misPedidosVendedor.js`
- `views/js/notificacionesGlobales.js`
- `views/js/servicioConversacion.js`

## Instalación

1. Respaldar el proyecto vigente.
2. Sustituirlo por el proyecto incluido en el ZIP.
3. No ejecutar SQL: no existen cambios de estructura ni datos.
4. Abrir EV y utilizar `Ctrl + F5` en escritorio; en móvil cerrar la pestaña y abrirla nuevamente si el navegador conserva recursos antiguos.

## Casos de prueba

- Abrir Ayuda en web y móvil; validar texto, número, enlace y botón Aceptar.
- Enviar un mensaje de servicio y comparar su hora con la hora de Perú.
- Abrir Rechazar solicitud en un ancho móvil y confirmar que el campo no se desplaza.
- Revisar una publicación con una o varias imágenes; ampliar, navegar y cerrar.
- Abrir Atender publicaciones en escritorio y móvil; validar encabezados, título y scroll horizontal.
- Ingresar como Soporte con pendientes; comparar KPI del panel con el badge de campana.
- Abrir Comunidad con un nombre largo en móvil y escritorio.
- Abrir el menú móvil, desplazarse hasta el final y usar Cambiar comunidad.

## Validación técnica realizada

- Sintaxis PHP completa.
- Sintaxis JavaScript del proyecto.
- Pruebas estáticas responsivas en Chromium para menú móvil, Comunidad, textarea de rechazo y tabla de publicaciones.
- Verificación del formateo de hora de Perú con entradas MySQL y UTC.

La validación funcional definitiva debe ejecutarse en QA con su base de datos y cuentas reales, especialmente para la campana de Soporte y el registro de nuevos mensajes del chat.
