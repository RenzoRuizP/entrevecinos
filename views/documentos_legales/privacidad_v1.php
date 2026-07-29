<?php
// views/documentos_legales/privacidad_v1.php
/** @var array $legalConfig */
$r = $legalConfig['responsable'] ?? [];
$o = $legalConfig['operacion'] ?? [];
$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$bancoCodigo = trim((string)($r['banco_datos_codigo'] ?? ''));
$bancoEstado = trim((string)($r['estado_banco_datos'] ?? ''));
?>

<div class="ev-legal-intro">
  <p>
    Esta Política explica de forma clara qué datos personales utiliza
    <strong><?= $e($r['nombre_comercial'] ?? 'Entre Vecinos (EV)') ?></strong>, para qué los necesita, con quién puede
    comunicarlos, dónde se alojan, cuánto tiempo se conservan y cómo puede el usuario ejercer sus derechos.
  </p>
</div>

<div class="ev-legal-summary" aria-label="Resumen de privacidad">
  <h2><i class="bi bi-shield-check" aria-hidden="true"></i> Resumen de privacidad</h2>
  <ul>
    <li>EV usa datos de identidad, contacto y residencia para crear y validar cuentas de vecinos.</li>
    <li>Los datos de operaciones, conversaciones y evidencias se utilizan para prestar el servicio, mantener trazabilidad y atender incidencias.</li>
    <li>El alojamiento principal contratado se encuentra en São Paulo, Brasil, por lo que existe un flujo internacional de datos.</li>
    <li>EV no vende bases de datos ni utiliza los datos para publicidad sin una autorización adicional cuando sea exigible.</li>
    <li>El usuario puede ejercer sus derechos mediante privacidad@entrevecinos.pe.</li>
  </ul>
</div>

<section id="responsable">
  <h2>1. Responsable del tratamiento</h2>
  <p>
    El titular que decide las finalidades y medios del tratamiento de los datos personales utilizados por EV es:
  </p>
  <div class="ev-legal-data-grid">
    <div><span>Nombre comercial</span><strong><?= $e($r['nombre_comercial'] ?? '') ?></strong></div>
    <div><span>Responsable</span><strong><?= $e($r['nombre_legal'] ?? '') ?></strong></div>
    <div><span>Condición</span><strong><?= $e($r['tipo_titular'] ?? 'Persona natural') ?></strong></div>
    <div><span>Identificación tributaria</span><strong><?= $e($r['documento_tributario'] ?? '') ?></strong></div>
    <div><span>Domicilio</span><strong><?= $e($r['domicilio'] ?? '') ?></strong></div>
    <div><span>Correo de privacidad</span><strong><?= $e($r['correo_privacidad'] ?? '') ?></strong></div>
    <div><span>Sitio web</span><strong><?= $e($r['sitio_web'] ?? '') ?></strong></div>
    <div><span>Teléfono</span><strong><?= $e($r['telefono_soporte'] ?? '') ?></strong></div>
  </div>
  <p>
    Las solicitudes relacionadas con datos personales se reciben mediante
    <strong><?= $e($r['correo_privacidad'] ?? '') ?></strong> o en el domicilio indicado.
  </p>
</section>

<section id="marco">
  <h2>2. Marco aplicable y alcance</h2>
  <p>
    El tratamiento se realiza conforme a la Constitución Política del Perú, la Ley N.° 29733, Ley de Protección de
    Datos Personales, su Reglamento aprobado por el Decreto Supremo N.° 016-2024-JUS y las demás normas aplicables.
  </p>
  <p>
    Esta Política se aplica al sitio web público, formularios de registro, validación de residencia, marketplace,
    productos, servicios, pedidos, conversaciones, comunidad, soporte, notificaciones, Libro de Reclamaciones y demás
    funciones de EV que impliquen tratamiento de datos personales.
  </p>
</section>

<section id="banco-datos">
  <h2>3. Banco de datos personales</h2>
  <div class="ev-legal-callout <?= $bancoCodigo !== '' ? 'ev-legal-callout--success' : 'ev-legal-callout--warning' ?>">
    <strong>Banco de datos: <?= $e($r['banco_datos_nombre'] ?? 'Usuarios de Entre Vecinos') ?></strong>
    <?php if ($bancoCodigo !== ''): ?>
      <p>Código o constancia de inscripción: <?= $e($bancoCodigo) ?>.</p>
    <?php else: ?>
      <p>Estado: <?= $e($bancoEstado !== '' ? $bancoEstado : 'Pendiente de inscripción') ?>. El registro de usuarios reales no debe habilitarse hasta completar esta obligación y consignar el código correspondiente.</p>
    <?php endif; ?>
  </div>
  <p>
    El titular mantendrá actualizada la información registral y los flujos de datos que correspondan. El estado de
    inscripción se mostrará de forma transparente mientras el documento se encuentre en fase prepiloto.
  </p>
</section>

<section id="datos-recopilados">
  <h2>4. Datos personales que trata EV</h2>
  <h3>4.1 Identificación y contacto</h3>
  <ul>
    <li>nombres y apellidos;</li>
    <li>tipo y número de documento de identidad;</li>
    <li>correo electrónico y número telefónico;</li>
    <li>fotografía de perfil, cuando el usuario decida registrarla;</li>
    <li>fecha de nacimiento o confirmación de mayoría de edad, cuando sea requerida.</li>
  </ul>

  <h3>4.2 Residencia y pertenencia a la comunidad</h3>
  <ul>
    <li>departamento, provincia y distrito;</li>
    <li>condominio, urbanización o comunidad residencial;</li>
    <li>dirección, torre, bloque, departamento, vivienda o referencia necesaria;</li>
    <li>comprobante de domicilio u otra evidencia solicitada para validar residencia;</li>
    <li>resultado, observaciones e historial de la validación.</li>
  </ul>

  <h3>4.3 Cuenta, autenticación y seguridad</h3>
  <ul>
    <li>identificador de usuario, rol, estado de cuenta y permisos;</li>
    <li>contraseña almacenada mediante mecanismos de hash; EV no debe conservarla en texto legible;</li>
    <li>fecha y hora de accesos, cierres de sesión y actividad relevante;</li>
    <li>dirección IP, navegador, sistema operativo, dispositivo, identificadores de sesión y registros técnicos;</li>
    <li>eventos de seguridad, auditoría y prevención de fraude.</li>
  </ul>

  <h3>4.4 Datos generados al utilizar EV</h3>
  <ul>
    <li>publicaciones de productos y servicios, precios, fotografías, descripciones y disponibilidad;</li>
    <li>pedidos, solicitudes, estados, aceptaciones, rechazos, cancelaciones y confirmaciones;</li>
    <li>mensajes, preguntas, respuestas, imágenes de referencia y archivos;</li>
    <li>cotizaciones, importes, alcance, condiciones, fechas, horarios y ubicaciones de atención;</li>
    <li>reprogramaciones, incidencias, respuestas, soluciones, evidencias y solicitudes de soporte;</li>
    <li>calificaciones, etiquetas, comentarios y reportes;</li>
    <li>notificaciones e interacciones de comunidad;</li>
    <li>movimientos de billetera, recargas, comprobantes y datos operativos únicamente cuando esos módulos se encuentren habilitados y se informe su uso.</li>
  </ul>

  <h3>4.5 Libro de Reclamaciones y atención</h3>
  <ul>
    <li>identificación y datos de contacto del consumidor;</li>
    <li>información sobre el producto o servicio, reclamo o queja, detalle y pedido concreto;</li>
    <li>número de hoja, fecha, estado, respuesta y constancia de atención;</li>
    <li>información de representante cuando el consumidor sea menor de edad o actúe mediante un tercero.</li>
  </ul>

  <h3>4.6 Datos de terceros incluidos por un usuario</h3>
  <p>
    El usuario debe evitar compartir información personal de terceros que no sea necesaria. Cuando adjunte una fotografía,
    comprobante, conversación o evidencia con datos de otra persona, debe contar con una base válida y aplicar medidas de
    minimización, como ocultar números, direcciones o información irrelevante.
  </p>
</section>

<section id="datos-sensibles">
  <h2>5. Datos sensibles y minimización</h2>
  <p>
    EV no solicita, como regla general, datos sensibles para operar el piloto. El usuario no debe adjuntar información sobre
    salud, biometría, orientación sexual, creencias, afiliaciones, antecedentes u otra información especialmente protegida si
    no es estrictamente necesaria para un caso concreto y no cuenta con una base legal válida.
  </p>
  <p>
    Los comprobantes de residencia deben mostrar solo la información necesaria para validar pertenencia. EV podrá solicitar
    que se oculten datos no pertinentes y aplicará controles de acceso restringido.
  </p>
</section>

<section id="fuentes">
  <h2>6. De dónde obtiene EV los datos</h2>
  <p>EV obtiene información:</p>
  <ul>
    <li>directamente del usuario durante el registro, actualización de perfil, publicación o uso de una función;</li>
    <li>de las acciones realizadas por el usuario dentro de la Plataforma;</li>
    <li>de la contraparte de una operación cuando registra información necesaria para coordinarla o reportar un problema;</li>
    <li>de administradores de comunidad o personal autorizado, exclusivamente dentro de sus funciones y con acceso limitado;</li>
    <li>de proveedores tecnológicos que generan registros necesarios para alojamiento, seguridad, correo, disponibilidad o soporte;</li>
    <li>de autoridades o fuentes legítimas cuando sea necesario cumplir una obligación o verificar un hecho relevante.</li>
  </ul>
</section>

<section id="finalidades-necesarias">
  <h2>7. Finalidades necesarias para prestar EV</h2>
  <p>EV trata los datos para:</p>
  <ol>
    <li>recibir, evaluar, crear, autenticar, administrar y proteger la cuenta;</li>
    <li>verificar identidad, mayoría de edad, residencia y pertenencia a una comunidad;</li>
    <li>gestionar observaciones, subsanaciones, cambios de residencia, habilitaciones y cierres de cuenta;</li>
    <li>permitir publicar, buscar, solicitar, coordinar y dar seguimiento a productos y servicios;</li>
    <li>gestionar pedidos, conversaciones, cotizaciones, reprogramaciones, cancelaciones, incidencias, soluciones y calificaciones;</li>
    <li>mostrar a la contraparte la información estrictamente necesaria para evaluar y ejecutar una operación;</li>
    <li>enviar notificaciones operativas, alertas de seguridad y comunicaciones indispensables para el servicio;</li>
    <li>prestar soporte, resolver problemas, gestionar reclamos y conservar la trazabilidad de decisiones;</li>
    <li>prevenir fraude, abuso, suplantación, accesos no autorizados, publicaciones prohibidas y riesgos para la comunidad;</li>
    <li>realizar respaldos, mantenimiento, auditorías, corrección de errores, control de calidad y mejora de la Plataforma;</li>
    <li>generar estadísticas agregadas o anonimizadas para comprender el uso y planificar mejoras;</li>
    <li>cumplir obligaciones legales y atender requerimientos válidos de autoridades;</li>
    <li>ejercer, sustentar o defender derechos en procedimientos, reclamos o controversias.</li>
  </ol>
</section>

<section id="publicidad">
  <h2>8. Promociones, publicidad y consentimiento opcional</h2>
  <p>
    Las finalidades necesarias descritas anteriormente no incluyen autorización automática para publicidad. EV no utilizará
    los datos para campañas comerciales, prospección, llamadas, mensajes promocionales o publicidad personalizada cuando la
    normativa exija consentimiento sin solicitar una autorización adicional, separada, libre, específica y opcional.
  </p>
  <p>
    Negarse a recibir publicidad no impide registrarse ni utilizar las funciones esenciales. El usuario podrá retirar esa
    autorización mediante el mecanismo informado en cada canal o escribiendo a <?= $e($r['correo_privacidad'] ?? '') ?>.
  </p>
</section>

<section id="base-legal">
  <h2>9. Bases que permiten el tratamiento</h2>
  <p>
    Según la finalidad y el dato involucrado, el tratamiento puede sustentarse en el consentimiento previo, libre, expreso,
    inequívoco e informado del usuario; en las medidas necesarias para atender su solicitud y operar la relación de uso; en
    el cumplimiento de obligaciones legales; en la protección de la seguridad; o en otras bases reconocidas por la normativa peruana.
  </p>
  <p>
    Durante el registro, el usuario debe abrir la posibilidad de leer esta Política y marcar un checkbox independiente. EV no
    permite enviar la solicitud mientras no se hayan aceptado tanto los Términos y Condiciones como esta Política. La aceptación
    queda asociada a la versión, fecha, hora, dirección IP, navegador o dispositivo, origen y huella digital del contenido.
  </p>
  <p>
    La aceptación de esta Política no sustituye la información detallada ni autoriza finalidades distintas a las comunicadas.
    En el caso del Libro de Reclamaciones, el tratamiento se realiza principalmente para cumplir obligaciones legales y atender
    la relación de consumo; la presentación de una hoja no se condiciona a otorgar autorización para publicidad u otras finalidades no necesarias.
  </p>
</section>

<section id="obligatorios">
  <h2>10. Datos obligatorios, opcionales y consecuencias</h2>
  <p>
    Los campos marcados como obligatorios son necesarios para crear, validar y operar la cuenta o atender una solicitud concreta.
    Si el usuario no proporciona datos suficientes para verificar identidad o residencia, EV no podrá aprobar la cuenta o limitará
    las funciones que dependan de esa verificación.
  </p>
  <p>
    Los datos opcionales se identificarán como tales. No proporcionarlos no impedirá el uso de las funciones esenciales, salvo
    cuando sean necesarios para una operación elegida por el propio usuario, por ejemplo adjuntar evidencia a una incidencia.
  </p>
</section>

<section id="visibilidad">
  <h2>11. Datos visibles para otros usuarios</h2>
  <p>
    Para facilitar la confianza y la coordinación, EV puede mostrar a otros usuarios información limitada, como nombre, fotografía
    de perfil, comunidad, publicaciones, reputación, estado operativo y datos necesarios para una operación. La visibilidad exacta
    depende de la función y del momento del flujo.
  </p>
  <p>
    La dirección exacta, teléfono u otros datos de coordinación solo deben comunicarse cuando sean necesarios para la entrega o
    prestación y conforme a las acciones del usuario. Los comprobantes de residencia, documentos de identidad, datos internos de
    revisión y credenciales no son públicos.
  </p>
</section>

<section id="soporte-acceso">
  <h2>12. Conversaciones, evidencias y acceso por soporte</h2>
  <p>
    Las conversaciones y archivos de una operación están destinados a sus participantes. El personal autorizado de EV puede
    acceder de forma limitada cuando sea necesario para atender una incidencia o reclamo, investigar un reporte, proteger la
    seguridad, moderar contenido, cumplir una obligación legal o ejercer la defensa de derechos.
  </p>
  <p>
    El acceso debe obedecer al principio de necesidad, quedar sujeto a permisos y trazabilidad, y no habilita la revisión indiscriminada
    de conversaciones. El personal debe mantener confidencialidad y utilizar la información solo para la finalidad que justificó el acceso.
  </p>
</section>

<section id="destinatarios">
  <h2>13. Destinatarios y proveedores que pueden recibir datos</h2>
  <p>EV puede comunicar o permitir acceso limitado a datos a:</p>
  <ul>
    <li><strong>la contraparte de una operación</strong>, en la medida necesaria para evaluar, coordinar o ejecutar la solicitud;</li>
    <li><strong>personal autorizado de soporte y administración</strong>, bajo perfiles y funciones definidas;</li>
    <li><strong>Hostinger</strong>, como proveedor de alojamiento, infraestructura, respaldo y servicios tecnológicos contratados;</li>
    <li><strong>proveedores de correo electrónico</strong>, para entregar comunicaciones operativas o respuestas solicitadas;</li>
    <li><strong>WhatsApp/Meta</strong>, únicamente cuando el usuario utiliza voluntariamente el enlace o canal de WhatsApp; ese servicio aplica sus propias políticas;</li>
    <li><strong>redes sociales oficiales</strong>, cuando el usuario decide visitar, seguir o interactuar con dichos perfiles;</li>
    <li><strong>Google Fonts (Google)</strong>, cuando se cargan tipografías desde sus servidores; puede recibir datos técnicos como dirección IP, navegador y fecha de la solicitud;</li>
    <li><strong>jsDelivr</strong>, utilizado para distribuir bibliotecas y componentes de interfaz; puede recibir datos técnicos necesarios para entregar el recurso solicitado;</li>
    <li><strong>YouTube (Google)</strong>, únicamente cuando el usuario decide abrir o reproducir un video incrustado en el sitio público; desde ese momento se aplican también las condiciones y políticas de dicho proveedor;</li>
    <li><strong>asesores profesionales</strong> sujetos a deberes de confidencialidad, cuando sea necesario para cumplimiento o defensa legal;</li>
    <li><strong>autoridades administrativas, policiales, fiscales o judiciales</strong>, ante una obligación legal o requerimiento válido.</li>
  </ul>
  <p>
    EV no vende, alquila ni entrega bases de datos a anunciantes para usos comerciales no autorizados.
  </p>
</section>

<section id="transferencias">
  <h2>14. Alojamiento en Brasil y flujo internacional de datos</h2>
  <div class="ev-legal-callout ev-legal-callout--info">
    <strong>Ubicación principal informada</strong>
    <p>El VPS de EV es provisto por <?= $e($o['proveedor_alojamiento'] ?? 'Hostinger') ?> y la ubicación seleccionada es <?= $e($o['ubicacion_alojamiento'] ?? 'São Paulo, Brasil') ?>.</p>
  </div>
  <p>
    Por ello, los datos personales alojados o procesados en esa infraestructura pueden ser objeto de un flujo transfronterizo
    desde Perú hacia Brasil. Antes de habilitar el registro de usuarios reales, EV debe completar la comunicación o registro
    aplicable de este flujo, verificar las condiciones contractuales del proveedor y adoptar las garantías y medidas de seguridad correspondientes.
  </p>
  <p>
    Algunos proveedores de correo, recursos web, redes sociales o comunicaciones pueden operar desde otros países. EV mantendrá
    un inventario razonable de proveedores y actualizará esta Política cuando exista un cambio relevante que afecte la información
    que debe conocer el usuario.
  </p>
</section>

<section id="conservacion">
  <h2>15. Plazos y criterios de conservación</h2>
  <p>EV aplica los siguientes criterios, sujetos a obligaciones legales o a la necesidad de atender una controversia:</p>
  <div class="ev-legal-table-wrap">
    <table class="ev-legal-table">
      <thead><tr><th>Información</th><th>Criterio de conservación</th></tr></thead>
      <tbody>
        <tr><td>Cuenta, perfil y validación aprobada</td><td>Mientras la cuenta esté activa y hasta cinco (5) años después de su cierre, salvo que un plazo menor resulte suficiente o exista una obligación o controversia que justifique conservarla.</td></tr>
        <tr><td>Solicitudes de registro rechazadas o desistidas</td><td>Hasta seis (6) meses después de la decisión final para atender aclaraciones, seguridad y prevención de registros abusivos; luego se elimina o anonimiza lo que ya no sea necesario.</td></tr>
        <tr><td>Comprobantes de residencia</td><td>Solo durante el tiempo necesario para validar o revalidar la residencia y atender observaciones. Cuando ya no sean necesarios, se eliminan o se restringe su acceso, salvo incidencia, fraude o obligación legal.</td></tr>
        <tr><td>Pedidos, servicios, conversaciones, cotizaciones, incidencias y calificaciones</td><td>Durante la relación y hasta cinco (5) años después del cierre de la operación o cuenta, para trazabilidad, atención de reclamos y defensa de derechos, salvo ajuste debidamente justificado.</td></tr>
        <tr><td>Registros de seguridad, accesos y auditoría</td><td>Como mínimo dos (2) años cuando corresponda a los controles exigidos y por el tiempo adicional razonable necesario para investigar incidentes.</td></tr>
        <tr><td>Aceptaciones y versiones de documentos legales</td><td>Durante la vigencia de la relación y por el plazo necesario para acreditar el consentimiento, cumplir obligaciones y defender derechos.</td></tr>
        <tr><td>Libro de Reclamaciones</td><td>Por un periodo no menor de dos (2) años, sin perjuicio de un plazo mayor exigido por una controversia o norma aplicable.</td></tr>
      </tbody>
    </table>
  </div>
  <p>
    Cumplidas las finalidades y los plazos, la información será eliminada, anonimizada o bloqueada según corresponda. EV no
    conservará datos indefinidamente sin una finalidad válida y documentada.
  </p>
</section>

<section id="seguridad">
  <h2>16. Seguridad de la información</h2>
  <p>
    EV aplica medidas técnicas, organizativas y legales razonables, incluyendo control de acceso por roles, autenticación,
    hash de contraseñas, sesiones seguras, registro de eventos, respaldos, actualización de componentes, segregación de permisos,
    revisión de incidentes y capacitación o compromisos de confidencialidad para personal autorizado.
  </p>
  <p>
    Ningún sistema garantiza riesgo cero. Si ocurre un incidente de seguridad que pueda afectar significativamente datos personales,
    EV evaluará el impacto, contendrá el evento, documentará las medidas y realizará las notificaciones a la autoridad o a los
    afectados dentro de los plazos exigibles cuando corresponda.
  </p>
</section>

<section id="derechos">
  <h2>17. Derechos del titular</h2>
  <p>
    El usuario puede ejercer los derechos reconocidos por la normativa peruana, incluyendo información, acceso, actualización,
    inclusión, rectificación, cancelación o supresión, oposición y tratamiento objetivo, así como revocar el consentimiento cuando
    corresponda y conocer las condiciones del tratamiento.
  </p>
  <p>
    El ejercicio es gratuito y puede solicitarse escribiendo a <strong><?= $e($r['correo_privacidad'] ?? '') ?></strong> o
    presentando una solicitud en el domicilio del responsable.
  </p>
</section>

<section id="procedimiento-derechos">
  <h2>18. Cómo ejercer los derechos y plazos de respuesta</h2>
  <p>La solicitud debe incluir:</p>
  <ul>
    <li>nombre completo y número de documento de identidad;</li>
    <li>derecho que desea ejercer y descripción clara de su pedido;</li>
    <li>correo, teléfono o dirección para recibir respuesta;</li>
    <li>documentos que acrediten representación, cuando actúe un tercero;</li>
    <li>información estrictamente necesaria para verificar identidad y evitar accesos indebidos.</li>
  </ul>
  <p>
    EV responderá las solicitudes de información dentro de ocho (8) días hábiles; las de acceso dentro de veinte (20) días hábiles;
    y las de rectificación, actualización, inclusión, cancelación, supresión u oposición dentro de diez (10) días hábiles, conforme
    a la normativa aplicable. Cuando la ley permita una ampliación, esta será comunicada y motivada dentro del plazo inicial.
  </p>
  <p>
    Si la solicitud está incompleta, EV podrá requerir una subsanación. La verificación de identidad se utilizará únicamente para
    proteger los datos y no debe exigir información desproporcionada.
  </p>
</section>

<section id="revocacion">
  <h2>19. Revocación del consentimiento y consecuencias</h2>
  <p>
    El usuario puede revocar el consentimiento cuando este sea la base del tratamiento. La revocación no afecta la licitud de lo
    realizado previamente ni obliga a eliminar datos que deban conservarse por otra base legal válida, seguridad, reclamos o defensa de derechos.
  </p>
  <p>
    Cuando los datos sean indispensables para validar y operar la cuenta, la revocación puede hacer imposible mantener el acceso a EV.
    Antes de aplicar esa consecuencia, se informará al usuario de manera clara.
  </p>
</section>

<section id="actualizacion-cierre">
  <h2>20. Actualización de datos y cierre de cuenta</h2>
  <p>
    El usuario debe mantener actualizados sus datos. Puede modificar la información habilitada en el perfil o solicitar una corrección
    mediante soporte. Los cambios de residencia pueden requerir una nueva validación.
  </p>
  <p>
    Al cerrar la cuenta se bloquea el acceso, pero cierta información puede mantenerse con acceso restringido durante los plazos indicados.
    La eliminación se realizará cuando hayan cesado las finalidades y obligaciones aplicables.
  </p>
</section>

<section id="menores">
  <h2>21. Menores de edad</h2>
  <p>
    EV no permite el registro de personas menores de dieciocho (18) años durante el piloto. Si se detecta una cuenta que incumple esta
    regla, podrá suspenderse mientras se verifica la situación y se adoptan medidas de protección y eliminación o conservación legalmente procedentes.
  </p>
  <p>
    El Libro de Reclamaciones puede ser utilizado por un representante en nombre de un consumidor menor de edad, conforme a las reglas aplicables.
  </p>
</section>

<section id="cookies">
  <h2>22. Cookies, tokens y almacenamiento local</h2>
  <p>
    EV utiliza cookies, tokens de autenticación, sesiones y almacenamiento local estrictamente necesarios para iniciar sesión, mantener
    la seguridad, recordar preferencias operativas y hacer funcionar la Plataforma. Estas tecnologías no se usan por sí mismas para publicidad.
  </p>
  <p>
    Si se incorporan analítica no esencial, publicidad, seguimiento entre sitios u otras tecnologías que requieran autorización, EV mostrará
    información específica y solicitará consentimiento separado antes de activarlas cuando corresponda.
  </p>
</section>

<section id="recursos-terceros">
  <h2>23. Recursos técnicos y servicios de terceros</h2>
  <p>
    La app y el sitio público utilizan actualmente recursos técnicos de Google Fonts y jsDelivr. La página pública también ofrece videos de YouTube
    que se cargan cuando el usuario decide abrirlos o reproducirlos. Al solicitar un recurso externo, el proveedor puede recibir información técnica,
    como dirección IP, navegador, fecha, URL solicitada y datos necesarios para entregar el contenido.
  </p>
  <p>
    EV revisará periódicamente estas dependencias, procurará reducir las que no sean necesarias y evaluará alojar localmente los recursos cuando
    resulte razonable. Los servicios, videos, redes sociales y enlaces externos se rigen también por las condiciones y políticas de sus respectivos
    proveedores desde el momento en que el usuario interactúa con ellos.
  </p>
</section>

<section id="automatizadas">
  <h2>24. Validaciones y decisiones automatizadas</h2>
  <p>
    EV puede aplicar validaciones automáticas de formato, duplicidad, seguridad, estado o consistencia. Durante el piloto no se prevé adoptar
    decisiones con efectos jurídicos significativos basadas exclusivamente en perfiles o tratamientos automatizados sin intervención humana.
  </p>
  <p>
    Las observaciones, suspensiones o medidas relevantes podrán ser revisadas por personal autorizado a solicitud del usuario cuando corresponda.
  </p>
</section>

<section id="libro-reclamos">
  <h2>25. Datos del Libro de Reclamaciones</h2>
  <p>
    Los datos ingresados en el Libro de Reclamaciones se utilizan para registrar, acreditar, analizar y responder el reclamo o queja, cumplir
    obligaciones de protección al consumidor y atender requerimientos de autoridades. Este tratamiento se sustenta en el cumplimiento de
    obligaciones legales y en la atención de la relación de consumo; no constituye autorización para publicidad. El registro genera una
    constancia y un número de seguimiento.
  </p>
  <p>
    La información puede comunicarse a Indecopi u otra autoridad competente cuando exista una obligación o requerimiento válido. El usuario debe
    evitar incluir datos sensibles o información de terceros que no sea necesaria para explicar su caso.
  </p>
</section>

<section id="cambios">
  <h2>26. Cambios en esta Política</h2>
  <p>
    EV puede actualizar esta Política por cambios legales, técnicos, operativos, de proveedores o de finalidades. Cada versión indicará fecha de
    publicación y vigencia. Los cambios materiales serán comunicados y se solicitará una nueva aceptación cuando la normativa o la naturaleza del
    cambio lo exijan.
  </p>
  <p>
    Las versiones anteriores y la evidencia de aceptación se conservarán durante el tiempo necesario para trazabilidad y cumplimiento.
  </p>
</section>

<section id="autoridad">
  <h2>27. Consultas y autoridad competente</h2>
  <p>
    Para consultas o solicitudes de privacidad, escribe a <strong><?= $e($r['correo_privacidad'] ?? '') ?></strong>. Si consideras que tu solicitud
    no fue atendida adecuadamente, conservas el derecho de acudir a la Autoridad Nacional de Protección de Datos Personales del Ministerio de Justicia
    y Derechos Humanos o a la autoridad que resulte competente.
  </p>
  <div class="ev-legal-contact-card">
    <p><strong>Responsable:</strong> <?= $e($r['nombre_legal'] ?? '') ?> — <?= $e($r['documento_tributario'] ?? '') ?></p>
    <p><strong>Correo de privacidad:</strong> <?= $e($r['correo_privacidad'] ?? '') ?></p>
    <p><strong>Domicilio:</strong> <?= $e($r['domicilio_notificaciones'] ?? $r['domicilio'] ?? '') ?></p>
  </div>
</section>

<div class="ev-legal-final-note">
  <strong>Versión 1.0.</strong> Publicada el 12 de julio de 2026. Vigente desde el 10 de agosto de 2026.
</div>
