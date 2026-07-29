# Entre Vecinos — Base local consolidada

Esta versión integra el código funcional local con los cambios válidos que estaban activos en QA al 26/07/2026.

## Antes de reemplazar tu proyecto local

1. Realiza una copia completa de tu carpeta actual `C:\xampp\htdocs\entrevecinos`.
2. Conserva fuera del reemplazo estos elementos del entorno actual:
   - `.env`
   - `uploads/`
   - `resources/uploads/`
   - `public/uploads/`
3. Copia el proyecto consolidado.
4. Vuelve a colocar tu `.env` en la raíz. Si faltan claves, utiliza `.env.example` como referencia.
5. Confirma para XAMPP:
   - `APP_ENV=local`
   - `EV_BASE_URL=/entrevecinos`
   - `EV_ENTORNO=local`
6. No copies el `.env` de QA ni credenciales del servidor.

## Promoción posterior a QA

El mismo código debe utilizarse en QA cambiando únicamente el `.env` del servidor. Los directorios de archivos cargados por usuarios deben preservarse y nunca eliminarse durante el despliegue.

## Base de datos

Esta consolidación no modifica el esquema SQL. No corresponde ejecutar ningún script de base de datos.
