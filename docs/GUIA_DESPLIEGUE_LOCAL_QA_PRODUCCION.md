# Entre Vecinos — Guía de despliegue Local, QA y Producción

## Principio de promoción

El mismo código validado en QA debe promoverse a producción. Las diferencias de cada ambiente se mantienen únicamente en `.env`, la base de datos, certificados, credenciales y archivos cargados por usuarios.

## Ruta base

EV detecta automáticamente si está instalado:

- En raíz: `/` — QA y Producción.
- En subcarpeta: `/entrevecinos/` — XAMPP local.

Cuando sea necesario forzarla, usar en `.env`:

```env
EV_BASE_URL=/
```

o:

```env
EV_BASE_URL=/entrevecinos
```

No se deben agregar rutas `/entrevecinos` directamente en PHP o JavaScript.

## Archivos que nunca deben sobrescribirse al promover

- `.env`
- `uploads/`
- `resources/uploads/`
- logs y respaldos del servidor

El ZIP de promoción no contiene esos datos operativos.

## Validación previa

Desde la raíz del proyecto:

```bash
php tools/ev_predeploy_audit.php . --package
```

El resultado esperado es:

```text
OK: auditoría preventiva EV sin hallazgos bloqueantes.
```

## Promoción recomendada

1. Crear respaldo del código activo y de la base de datos.
2. Descomprimir el paquete en una carpeta nueva de preparación.
3. Mantener el `.env` propio del ambiente.
4. Mantener los directorios de uploads existentes.
5. Ejecutar la auditoría preventiva.
6. Validar sintaxis PHP y JavaScript.
7. Promover a QA.
8. Ejecutar pruebas de humo.
9. Promover exactamente el mismo paquete a producción.

Ejemplo de sincronización controlada:

```bash
rsync -a --delete \
  --exclude='.env' \
  --exclude='uploads/' \
  --exclude='resources/uploads/' \
  origen/ destino/
```

## Pruebas de humo mínimas

- Inicio de sesión y cierre de sesión.
- Dashboard.
- Mi perfil y ubigeo.
- Campana y centro de notificaciones.
- Marketplace.
- Mis publicaciones y modal Agregar.
- Mis pedidos de comprador y vendedor.
- Solicitudes de servicio.
- Billetera.
- Comunidad.
- Módulos de soporte.
- Consola del navegador sin errores de rutas, 404 o cargas permanentes.

## Producción

Producción debe tener un `.env` nuevo, con credenciales y secreto JWT exclusivos. No reutilizar los secretos de QA.
