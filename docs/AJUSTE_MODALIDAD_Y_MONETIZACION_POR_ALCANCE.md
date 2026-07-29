# Ajuste: modalidad y monetización por alcance

## Objetivo

Permitir que el Administrador EV defina de forma independiente una configuración manual o programada para:

- Todo Entre Vecinos.
- Cada condominio.
- Cada urbanización.

## Cambios

- Se eliminó la validación rígida que obligaba a mantener en cero los costos de publicación y otras reglas configurables.
- La modalidad manual elimina fechas residuales al guardar.
- La modalidad programada exige al menos una fecha de inicio o de fin.
- Se valida que la fecha final no sea anterior a la inicial.
- Los mensajes de la interfaz ya no presentan todas las configuraciones como exclusivas del piloto.
- Villa Flores puede conservar una excepción gratuita mientras la configuración global u otras comunidades mantienen reglas diferentes.

## Resolución de alcance

1. Se utiliza la configuración propia vigente de la comunidad.
2. Si no existe o está fuera de vigencia, se utiliza la configuración global vigente.
3. Si tampoco existe, se utiliza el valor predeterminado del catálogo.

## Perfil piloto

El botón de perfil gratuito continúa restringido a un condominio o urbanización específicos. Antes de aplicarlo, el Administrador EV selecciona si el perfil será manual o programado y, en este último caso, define su vigencia. Luego puede ajustar individualmente cualquier funcionalidad o regla desde su editor.
