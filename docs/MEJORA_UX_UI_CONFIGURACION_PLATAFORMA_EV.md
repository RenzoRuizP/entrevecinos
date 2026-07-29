# Mejora UX/UI — Configuración de plataforma EV

## Objetivo

Alinear el módulo **Configuración de plataforma** con el estándar visual y de interacción utilizado por los módulos de **Soporte y Administración** de Entre Vecinos, tomando como referencia directa la arquitectura del módulo **Atención de servicios**.

## Alcance aplicado

- Cabecera EV con icono, categoría, título, descripción y acción principal.
- Tres indicadores operativos con la misma anatomía visual de Atención de servicios.
- Un único contenedor central de trabajo.
- Selector de alcance integrado al encabezado operativo.
- Tabs de Funcionalidades, Monetización e Historial integrados al contenedor.
- Funcionalidades y reglas presentadas como filas administrativas compactas.
- Editor desplegable por elemento para programación, mensajes, valores y motivo.
- Estados Activo/Inactivo, Con cobro/Sin cobro y Habilitada/Deshabilitada.
- Identificación de configuración propia o heredada.
- Aviso de cambios sin guardar.
- Botón de perfil piloto con confirmación EV.
- Diseño responsive para escritorio, tablet y móvil.
- Conservación de la carga única y prevención de solicitudes duplicadas.

## Permisos conservados

- El rol `admin` puede visualizar y modificar el módulo.
- El rol `soporte` no ve el acceso ni puede ingresar directamente.

## Lógica conservada

No se modificaron:

- Reglas de monetización.
- Cálculo y congelamiento de comisiones.
- Protección CSRF.
- Endpoints administrativos.
- Bloqueos frontend y backend.
- Perfil gratuito del piloto.
- Estructura de la base de datos.

## Archivos modificados

- `views/configuracionPlataformaView.php`
- `views/estilos/configuracionPlataformaEstilo.php`
- `views/js/configuracionPlataforma.js`

## Instalación

1. Realizar una copia de seguridad del proyecto local.
2. Reemplazar el proyecto por el contenido del paquete actualizado.
3. No importar nuevamente la base de datos.
4. Reiniciar Apache.
5. Abrir Entre Vecinos con el usuario Administrador EV.
6. Presionar `Ctrl + F5` para limpiar recursos almacenados en caché.
7. Ingresar a `Administración → Configuración de plataforma`.

## Pruebas mínimas

1. Confirmar que se realiza una sola petición inicial a la API de configuración.
2. Cambiar el alcance a Villa Flores y verificar una sola petición adicional.
3. Abrir y cerrar la configuración de una funcionalidad.
4. Cambiar un interruptor y verificar el aviso `Cambio sin guardar`.
5. Guardar y comprobar el mensaje de éxito y el historial.
6. Revisar las pestañas Monetización e Historial.
7. Probar en vista móvil y confirmar que no existe desplazamiento horizontal.
8. Ingresar como soporte y confirmar que el módulo no aparece ni acepta URL directa.
