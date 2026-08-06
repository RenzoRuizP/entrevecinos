# Correcciones EV — loading global, cierres e inactivación de cuenta

Fecha: 05/08/2026

## Ajustes implementados

1. Se unificó el cargador global con el lenguaje visual mostrado durante el inicio de sesión:
   - cápsula blanca;
   - spinner EV multicolor verde/naranja;
   - texto `Cargando...`;
   - comportamiento responsivo.
2. Se reemplazó el loader de navegación dinámica y el loader inicial de módulos.
3. Se homologaron los spinners existentes de los módulos para que utilicen la misma composición cromática EV.
4. Se corrigió el botón de cierre del modal `Definir meta de ingresos`:
   - X blanca en estado normal;
   - efecto visible al pasar el puntero, enfocar o presionar;
   - área táctil consistente en móvil.
5. Se corrigió el botón de cierre del modal `Crear mi usuario` con el mismo patrón UX/UI.
6. Se alinearon correctamente los íconos y textos de los botones `Aceptar` y `Cancelar` del modal `Inactivar cuenta`.

## Archivos modificados

- `views/MenuPrincipalView.php`
- `views/login.php`
- `views/js/menuIzquierda.js`
- `views/js/evSweetAlert.js`
- `views/estilos/dashboardGerencialEstilo.php`
- `views/estilos/login.estilo.php`
- `views/estilos/evLoadingGlobalEstilo.php` (nuevo)

## Pruebas recomendadas

1. Iniciar sesión y verificar el loader del modal de bienvenida.
2. Navegar entre módulos desde el menú lateral y comprobar que aparece el mismo loader EV.
3. Abrir `Dashboard gerencial > Definir meta` y validar la X en estado normal, hover, foco y clic.
4. Abrir `Crear mi usuario` y repetir la validación de la X en escritorio y móvil.
5. Abrir `Revisar registro > Desactivar` y comprobar separación, centrado y alineación de los íconos en `Aceptar` y `Cancelar`.
6. Forzar cargas de tablas o módulos y confirmar que los spinners ya no aparecen únicamente en verde.
