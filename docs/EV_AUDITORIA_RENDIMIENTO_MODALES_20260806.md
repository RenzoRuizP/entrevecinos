# Auditoría técnica EV — rendimiento y cierres de modales

Fecha: 2026-08-06

## Hallazgos principales

1. Existían múltiples `MutationObserver` globales observando todo `document.body` o `document.documentElement`. Al abrir un modal, Bootstrap agrega el backdrop al DOM y todos esos observadores se ejecutaban simultáneamente. Algunos volvían a escanear vistas completas o reintentaban inicializaciones.
2. La política global de modales también observaba cada nodo agregado, aunque la navegación EV ya emite el evento `ev:content-loaded`. Era trabajo duplicado.
3. El modal de meta acumulaba varias reglas CSS contradictorias para el mismo botón X. El resultado dependía del orden de carga y no coincidía con Nueva publicación.
4. El overlay global utilizaba `backdrop-filter` a pantalla completa, una operación costosa para la composición gráfica durante la navegación.
5. El loader aparecía inmediatamente incluso en navegaciones rápidas, aumentando la percepción de lentitud.

## Correcciones

- Se retiraron observadores globales redundantes de los módulos y se utilizaron los eventos del ciclo AJAX de EV.
- Se mantuvieron inicializaciones idempotentes mediante atributos `data-*` existentes.
- Se simplificó la política de modales y se eliminó el observer global.
- Se unificó el botón X con el mismo HTML y comportamiento de Nueva publicación.
- Se redujo el costo del overlay, se eliminó el blur de pantalla completa y su aparición se difirió 120 ms.
- Se optimizó el barrido de loaders heredados a una sola consulta DOM.
- Se acortó la animación del acordeón lateral para una respuesta más inmediata.

## Archivos modificados

- views/js/evModalPolicy.js
- views/js/menuIzquierda.js
- views/js/soporteDashboard.js
- views/js/atenderPublicacion.js
- views/js/atenderRecargas.js
- views/js/atenderCuentasUsuario.js
- views/js/marketplace.js
- views/js/producto.js
- views/js/billetera.js
- views/js/datosPersonales.js
- views/js/credenciales.js
- views/js/configuracionPlataforma.js
- views/estilos/evLoadingGlobalEstilo.php
- views/estilos/menuIzquierdaEstilo.php
- views/estilos/dashboardGerencialEstilo.php
- views/estilos/login.estilo.php
- views/dashboardGerencialView.php
- views/login.php

No se modificó la base de datos.
