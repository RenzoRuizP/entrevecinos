# Mejora UX/UI — Mi billetera EV

## Objetivo
Alinear la vista **Mi billetera** con el estándar visual vigente de Entre Vecinos utilizado en **Solicitudes de servicio** y otros módulos operativos.

## Archivos modificados
- `views/billeteraView.php`
- `views/estilos/billeteraEstilo.php`
- `views/js/billetera.js`

## Cambios principales
- Cabecera EV unificada con icono, categoría, título, descripción y resumen.
- Indicadores integrados de saldo, movimientos y recargas.
- Panel operativo único para la gestión de billetera.
- Acciones principales consistentes: Recargar saldo, Soporte técnico y Actualizar.
- Secciones de recargas y movimientos con el mismo lenguaje visual de Solicitudes de servicio.
- Estados vacíos unificados.
- Cards de movimientos con hover, jerarquía y acentos EV.
- Tabla de recargas actualizada visualmente.
- Botón Actualizar ahora refresca saldo, movimientos y recargas.
- Responsive para escritorio, tablet y móvil.

## Lógica conservada
- Consulta de saldo.
- Registro y subsanación de recargas.
- Historial de recargas.
- Movimientos de billetera.
- Modales y validaciones.
- Autenticación, permisos y manejo de sesión.

## Instalación
1. Crear una copia de seguridad del proyecto actual.
2. Reemplazar el proyecto por el contenido del ZIP.
3. Abrir la vista Mi billetera.
4. Presionar `Ctrl + F5`.

No se requiere ejecutar SQL ni reiniciar Apache.
