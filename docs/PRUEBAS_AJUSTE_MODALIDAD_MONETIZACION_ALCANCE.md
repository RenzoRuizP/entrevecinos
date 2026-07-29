# Pruebas: modalidad y monetización por alcance

## 1. Configuración global manual

1. Seleccionar **Todo Entre Vecinos**.
2. Abrir una funcionalidad.
3. Elegir **Manual**.
4. Cambiar el estado y guardar.
5. Verificar que las fechas queden vacías y que el historial registre alcance global.

## 2. Configuración global programada

1. Seleccionar **Todo Entre Vecinos**.
2. Abrir una regla de monetización.
3. Elegir **Programada**.
4. Ingresar fecha de inicio, fecha de fin o ambas.
5. Guardar y comprobar el historial.

## 3. Villa Flores como excepción gratuita

1. Buscar y seleccionar **Urbanización Villa Flores**.
2. Aplicar **Configurar piloto gratuito**.
3. Seleccionar modalidad manual o programada en el cuadro de confirmación.
4. Confirmar que Villa Flores tenga sus propias reglas en cero y la billetera oculta.
5. Volver a **Todo Entre Vecinos** y confirmar que sus valores no cambiaron.

## 4. Otra comunidad con configuración propia

1. Seleccionar otro condominio o urbanización.
2. Configurar un valor diferente al global.
3. Guardar.
4. Confirmar que solo esa comunidad muestre **Configuración propia**.

## 5. Validaciones

- La modalidad programada sin ninguna fecha debe ser rechazada.
- La fecha final anterior a la inicial debe ser rechazada.
- Un importe negativo debe ser rechazado.
- Un porcentaje superior a 100 debe ser rechazado.
- Un costo de publicación mayor que cero debe poder guardarse.
- Soporte no debe ver ni acceder al módulo.
