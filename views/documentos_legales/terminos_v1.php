<?php
// views/documentos_legales/terminos_v1.php
/** @var array $legalConfig */
$r = $legalConfig['responsable'] ?? [];
$o = $legalConfig['operacion'] ?? [];
$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$pilotoGratuito = !empty($o['piloto_gratuito']);
?>

<div class="ev-legal-intro">
  <p>
    Estos Términos y Condiciones explican, en lenguaje claro, las reglas para registrarse y utilizar
    <strong><?= $e($r['nombre_comercial'] ?? 'Entre Vecinos (EV)') ?></strong>. Al aceptar este documento,
    el usuario celebra un acuerdo de uso de la Plataforma con su titular y se compromete a actuar de buena fe,
    respetar a los demás vecinos y cumplir la normativa peruana aplicable.
  </p>
</div>

<div class="ev-legal-summary" aria-label="Resumen importante">
  <h2><i class="bi bi-stars" aria-hidden="true"></i> Lo más importante</h2>
  <ul>
    <li>EV conecta a residentes verificados de una misma comunidad para publicar, solicitar y coordinar productos y servicios.</li>
    <li>Durante el piloto, EV no cobra comisiones ni cargos operativos a compradores, vendedores o proveedores.</li>
    <li>Los pagos se realizan directamente entre los usuarios; EV no recibe, procesa ni custodia el dinero.</li>
    <li>La información, ofertas y compromisos publicados por cada usuario deben ser verdaderos y cumplirse de buena fe.</li>
    <li>Las reglas de protección al consumidor, protección de datos y demás normas obligatorias siguen siendo aplicables.</li>
  </ul>
</div>

<section id="identificacion">
  <h2>1. Identificación del titular y canales oficiales</h2>
  <p>
    Entre Vecinos es operado en el Perú por una persona natural. Para efectos de estos Términos, el titular y
    responsable de la Plataforma es:
  </p>
  <div class="ev-legal-data-grid">
    <div><span>Nombre comercial</span><strong><?= $e($r['nombre_comercial'] ?? '') ?></strong></div>
    <div><span>Titular</span><strong><?= $e($r['nombre_legal'] ?? '') ?></strong></div>
    <div><span>Identificación tributaria</span><strong><?= $e($r['documento_tributario'] ?? '') ?></strong></div>
    <div><span>Domicilio</span><strong><?= $e($r['domicilio'] ?? '') ?></strong></div>
    <div><span>Sitio web</span><strong><?= $e($r['sitio_web'] ?? '') ?></strong></div>
    <div><span>Soporte</span><strong><?= $e($r['correo_soporte'] ?? '') ?> · <?= $e($r['telefono_soporte'] ?? '') ?></strong></div>
  </div>
  <p>
    Los reclamos se atienden mediante el Libro de Reclamaciones Virtual disponible en el sitio web y el correo
    <strong><?= $e($r['correo_reclamos'] ?? '') ?></strong>. Las consultas de privacidad se atienden en
    <strong><?= $e($r['correo_privacidad'] ?? '') ?></strong>.
  </p>
</section>

<section id="definiciones">
  <h2>2. Definiciones</h2>
  <ul>
    <li><strong>EV o Plataforma:</strong> el sistema web Entre Vecinos, el sitio público y los módulos asociados.</li>
    <li><strong>Usuario o vecino:</strong> persona mayor de edad que solicita una cuenta o cuenta con una cuenta habilitada.</li>
    <li><strong>Comprador o solicitante:</strong> usuario que solicita un producto o servicio.</li>
    <li><strong>Vendedor o proveedor:</strong> usuario que publica, ofrece o atiende productos o servicios.</li>
    <li><strong>Comunidad:</strong> condominio, urbanización cerrada u otra comunidad residencial habilitada en EV.</li>
    <li><strong>Operación:</strong> pedido, solicitud, cotización, coordinación o contratación realizada entre usuarios mediante EV.</li>
    <li><strong>Contenido:</strong> textos, fotografías, archivos, mensajes, cotizaciones, valoraciones, evidencias y demás información registrada por un usuario.</li>
    <li><strong>Piloto:</strong> etapa inicial controlada de validación funcional y operativa de EV con una o más comunidades.</li>
  </ul>
</section>

<section id="aceptacion">
  <h2>3. Aceptación, vigencia y versiones</h2>
  <p>
    El registro exige que el usuario pueda abrir, revisar y aceptar expresamente estos Términos y la Política de
    Privacidad. Los checkboxes se muestran desmarcados y la solicitud no puede enviarse mientras falte cualquiera
    de las dos aceptaciones obligatorias.
  </p>
  <p>
    EV registra la versión, fecha, hora y evidencia técnica razonable de la aceptación. Si una modificación futura
    cambia de manera material los derechos, obligaciones, finalidades o condiciones económicas, EV comunicará la
    nueva versión y solicitará una nueva aceptación cuando corresponda.
  </p>
</section>

<section id="objeto">
  <h2>4. Objeto y naturaleza de EV</h2>
  <p>
    EV facilita la interacción comercial y comunitaria entre residentes verificados de una misma comunidad. Permite,
    entre otras funciones, publicar y buscar productos o servicios, gestionar pedidos y solicitudes, conversar,
    cotizar, reprogramar, reportar problemas, recibir notificaciones, solicitar soporte y registrar calificaciones.
  </p>
  <p>
    EV proporciona la infraestructura digital y las herramientas de trazabilidad, pero no fabrica los productos ni
    presta por cuenta propia los servicios publicados por los usuarios. El vendedor o proveedor es responsable de su
    oferta y el comprador o solicitante es responsable de la información y compromisos que asume. La calificación
    jurídica de cada relación y las obligaciones que correspondan se determinarán por los hechos y la normativa aplicable.
  </p>
</section>

<section id="piloto-comunidad">
  <h2>5. Alcance del piloto y comunidad habilitada</h2>
  <p>
    La comunidad inicial prevista es <strong><?= $e($o['comunidad_inicial'] ?? 'Villa Flores') ?></strong>. La habilitación
    de una comunidad no significa que su administración sea propietaria, garante o parte de las operaciones entre usuarios,
    salvo que se informe expresamente lo contrario para una función concreta.
  </p>
  <p>
    Durante el piloto, EV podrá ajustar procesos, límites operativos y funcionalidades para mejorar la experiencia o
    corregir errores. Los cambios relevantes serán comunicados de forma clara y no afectarán derechos irrenunciables.
  </p>
</section>

<section id="registro">
  <h2>6. Requisitos de registro y cuenta</h2>
  <ol>
    <li>El registro está permitido únicamente a personas de dieciocho (18) años o más.</li>
    <li>El usuario debe proporcionar información verdadera, completa, actual y verificable.</li>
    <li>La cuenta es personal y no puede transferirse, venderse, alquilarse ni utilizarse para suplantar a otra persona.</li>
    <li>El usuario debe utilizar datos de contacto propios o respecto de los cuales tenga autorización válida.</li>
    <li>El envío del formulario no implica aprobación automática. La cuenta puede quedar en revisión, ser observada, aprobada, suspendida o rechazada conforme a la validación y las reglas de EV.</li>
    <li>Una persona no debe crear múltiples cuentas para eludir restricciones, manipular calificaciones o realizar actividades simuladas.</li>
  </ol>
</section>

<section id="residencia">
  <h2>7. Validación de identidad, residencia y pertenencia</h2>
  <p>
    EV solicita información y evidencias razonables para verificar que el usuario pertenece a la comunidad declarada.
    Soporte puede aprobar, observar o rechazar la solicitud, pedir una subsanación o realizar una nueva validación cuando
    cambie la residencia o existan inconsistencias.
  </p>
  <p>
    La validación reduce riesgos y ayuda a preservar el carácter comunitario de EV, pero no constituye una garantía
    absoluta sobre la identidad, solvencia, conducta, calidad de los productos o servicios ni cumplimiento futuro de una persona.
  </p>
  <p>
    Los comprobantes utilizados para validar residencia no son públicos. Su tratamiento se rige por la Política de Privacidad.
  </p>
</section>

<section id="seguridad-cuenta">
  <h2>8. Seguridad y uso responsable de la cuenta</h2>
  <ul>
    <li>El usuario debe crear una contraseña segura y mantenerla confidencial.</li>
    <li>No debe compartir credenciales ni dejar sesiones abiertas en dispositivos de terceros.</li>
    <li>Debe comunicar de inmediato a soporte cualquier acceso no autorizado, pérdida de control o actividad sospechosa.</li>
    <li>EV puede cerrar sesiones, limitar accesos o solicitar una verificación adicional cuando exista un riesgo razonable de seguridad.</li>
    <li>Las acciones realizadas desde una sesión autenticada se asociarán a la cuenta, sin impedir que el titular demuestre un acceso no autorizado.</li>
  </ul>
</section>

<section id="convivencia">
  <h2>9. Reglas generales de convivencia y buena fe</h2>
  <p>Todos los usuarios deben:</p>
  <ul>
    <li>mantener un trato respetuoso, claro y no discriminatorio;</li>
    <li>cumplir los acuerdos aceptados o comunicar oportunamente una imposibilidad real;</li>
    <li>evitar hostigamiento, amenazas, insultos, lenguaje ofensivo o presión indebida;</li>
    <li>utilizar los canales de EV para registrar la información comercial que deba conservar trazabilidad;</li>
    <li>respetar la privacidad, seguridad y normas de convivencia de la comunidad;</li>
    <li>actuar de acuerdo con la ley y no utilizar EV para ocultar actividades ilícitas.</li>
  </ul>
</section>

<section id="productos">
  <h2>10. Publicación de productos</h2>
  <p>El vendedor que publica un producto se obliga a:</p>
  <ul>
    <li>describir de forma veraz sus características, cantidad, estado, precio, disponibilidad y restricciones relevantes;</li>
    <li>usar imágenes propias o contar con autorización para utilizarlas;</li>
    <li>informar defectos, condiciones especiales o limitaciones que puedan influir en la decisión de compra;</li>
    <li>mantener actualizada la disponibilidad y retirar publicaciones que ya no pueda atender;</li>
    <li>entregar un producto que corresponda razonablemente a lo anunciado y a las condiciones aceptadas;</li>
    <li>cumplir las obligaciones legales que le correspondan por la naturaleza y habitualidad de su actividad.</li>
  </ul>
</section>

<section id="pedidos">
  <h2>11. Solicitudes, pedidos, entrega y confirmación de productos</h2>
  <p>
    El comprador debe revisar la publicación antes de solicitar un producto y proporcionar información suficiente para
    la coordinación. El vendedor podrá aceptar o rechazar la solicitud según disponibilidad y deberá respetar los estados,
    tiempos y condiciones mostrados por el sistema.
  </p>
  <p>
    Las partes deben coordinar de manera segura la entrega, verificar el producto y registrar las confirmaciones o
    cancelaciones disponibles. Si el producto no corresponde a lo ofrecido, presenta un problema relevante o existe un
    incumplimiento, el usuario podrá acudir a los canales de soporte habilitados, sin perjuicio de los derechos legales aplicables.
  </p>
</section>

<section id="prohibidos">
  <h2>12. Productos, servicios y contenidos prohibidos</h2>
  <p>No se permite publicar, solicitar, promover o coordinar mediante EV:</p>
  <ul>
    <li>bienes o servicios cuya comercialización o prestación sea ilegal;</li>
    <li>armas, municiones, explosivos, sustancias ilícitas, productos robados, falsificados o de procedencia no acreditable;</li>
    <li>medicamentos sujetos a receta, productos regulados o servicios profesionales sin las autorizaciones exigibles;</li>
    <li>contenido sexual explícito, explotación, trata, violencia, discriminación, acoso o actividades que pongan en riesgo a personas;</li>
    <li>servicios destinados a vulnerar sistemas, obtener datos sin autorización, cometer fraude o infringir derechos de terceros;</li>
    <li>cualquier publicación que incumpla la normativa peruana o las reglas de seguridad de la comunidad.</li>
  </ul>
  <p>
    La lista es referencial y puede complementarse mediante reglas de publicación visibles. EV podrá retirar o limitar
    contenido cuando existan indicios razonables de riesgo o incumplimiento.
  </p>
</section>

<section id="servicios">
  <h2>13. Publicación y solicitud de servicios</h2>
  <p>
    El proveedor debe describir de forma real y suficiente el servicio, experiencia relevante, alcance general,
    disponibilidad y restricciones. El solicitante debe brindar información clara y necesaria para que el proveedor
    evalúe el requerimiento.
  </p>
  <p>
    Para preservar la trazabilidad, la negociación comercial debe realizarse dentro de EV. Las preguntas, respuestas,
    imágenes de referencia, ajustes y cotizaciones deben registrarse en la conversación. Los canales externos pueden
    utilizarse para soporte o coordinación complementaria, pero no deben emplearse para ocultar o sustituir acuerdos
    comerciales que deban constar en la Plataforma.
  </p>
</section>

<section id="cotizacion">
  <h2>14. Cotización final, aceptación y cambios comerciales</h2>
  <p>La cotización final debe mostrar, según la naturaleza del servicio:</p>
  <ul>
    <li>alcance, actividades y entregables acordados;</li>
    <li>importe final;</li>
    <li>condición de pago habilitada;</li>
    <li>fecha, horario y duración estimada;</li>
    <li>ubicación o referencia necesaria para la atención;</li>
    <li>condiciones, exclusiones y observaciones relevantes.</li>
  </ul>
  <p>
    La cotización tendrá la vigencia indicada por el sistema, actualmente setenta y dos (72) horas. Una vez aceptada,
    el chat continúa para coordinación operativa. Todo cambio de precio, alcance, forma de pago o ubicación que altere
    materialmente la prestación exige una nueva cotización final. La aceptación debe efectuarse libremente y luego de
    revisar la información disponible.
  </p>
</section>

<section id="ejecucion">
  <h2>15. Ejecución, reprogramación y cancelación de servicios</h2>
  <p>
    El proveedor puede iniciar el servicio y debe marcarlo como realizado cuando corresponda. Si solo cambia la fecha
    o el horario, cualquiera de las partes puede proponer una reprogramación. La contraparte debe aceptarla o rechazarla,
    solo puede existir una propuesta pendiente a la vez y EV conserva la fecha original y el historial de cambios.
  </p>
  <p>
    La cancelación antes de la ejecución debe registrar un motivo. Una operación cancelada sin ejecución no puede ser
    calificada. Cuando el servicio ya empezó y surge un problema, corresponde utilizar el flujo de incidencias y soporte
    en lugar de ocultar lo ocurrido mediante una cancelación informal.
  </p>
</section>

<section id="incidencias">
  <h2>16. Problemas, soluciones y soporte</h2>
  <p>
    El solicitante puede reportar un problema de manera objetiva y adjuntar evidencias pertinentes. El proveedor puede
    responder, explicar lo ocurrido, adjuntar información y proponer una solución. El solicitante puede confirmar la
    solución o indicar que el problema persiste.
  </p>
  <p>
    Cuando sea necesario, las partes pueden solicitar la intervención de soporte. El personal autorizado podrá revisar
    la información de la operación únicamente para atender el caso, proteger la seguridad, investigar un reporte, cumplir
    una obligación legal o ejercer la defensa de derechos. Soporte puede registrar recomendaciones o medidas operativas,
    pero no sustituye a las autoridades ni elimina las vías legales disponibles.
  </p>
</section>

<section id="calificaciones">
  <h2>17. Calificaciones y reputación</h2>
  <ul>
    <li>El comprador puede calificar al proveedor y el proveedor puede calificar al comprador.</li>
    <li>Cada participante registra una sola calificación independiente por operación completada.</li>
    <li>Ninguna parte ve la calificación de la otra antes de enviar la suya cuando el sistema aplica esa regla.</li>
    <li>Las valoraciones deben referirse a una experiencia real, ser honestas y utilizar lenguaje respetuoso.</li>
    <li>No se permite usar la calificación como amenaza, represalia, extorsión, discriminación o mecanismo para exigir ventajas no acordadas.</li>
    <li>No se puede calificar una operación cancelada sin ejecución ni mientras exista una incidencia pendiente de resolución.</li>
  </ul>
  <p>
    EV podrá ocultar o moderar comentarios que contengan datos personales innecesarios, insultos, amenazas, acusaciones
    manifiestamente ajenas a la operación o contenido contrario a la ley, conservando la trazabilidad necesaria.
  </p>
</section>

<section id="pagos">
  <h2>18. Pagos y condiciones económicas del piloto</h2>
  <?php if ($pilotoGratuito): ?>
    <div class="ev-legal-callout ev-legal-callout--success">
      <strong>Piloto gratuito.</strong>
      <p>EV no cobra comisiones por ventas, cargos por publicar productos o servicios, destacados ni otros cargos operativos a compradores, vendedores o proveedores durante el piloto.</p>
    </div>
  <?php endif; ?>
  <p>
    Los pagos se acuerdan y realizan directamente entre los usuarios. EV no recibe, procesa, administra ni custodia
    el dinero de una operación. Para servicios, las condiciones habilitadas durante el piloto son
    <strong>Pago contra entrega</strong> y <strong>Adelanto acordado</strong>.
  </p>
  <p>
    Cada usuario debe verificar a la contraparte, revisar el acuerdo y conservar sus comprobantes. Si EV adopta un
    modelo comercial después del piloto, las tarifas o comisiones deberán informarse de manera previa, clara y visible,
    y se solicitará una nueva aceptación cuando el cambio sea material.
  </p>
</section>

<section id="contenido">
  <h2>19. Contenido de los usuarios y licencias necesarias</h2>
  <p>
    El usuario conserva los derechos que le correspondan sobre su contenido. Al registrarlo en EV, concede al titular
    una autorización no exclusiva, gratuita, limitada al tiempo y finalidades necesarias para alojar, procesar, mostrar,
    respaldar, moderar y comunicar ese contenido dentro de la Plataforma y para atender incidencias o exigencias legales.
  </p>
  <p>
    El usuario declara que cuenta con los derechos o autorizaciones necesarios y que no vulnerará derechos de terceros.
    Debe evitar publicar datos personales ajenos que no sean necesarios y ocultar información irrelevante en comprobantes,
    fotografías o evidencias.
  </p>
</section>

<section id="propiedad-intelectual">
  <h2>20. Propiedad intelectual de EV</h2>
  <p>
    El nombre Entre Vecinos, el logotipo, elementos gráficos, interfaz, código, documentación, bases de datos y contenidos
    propios están protegidos por la normativa aplicable y pertenecen a sus respectivos titulares. El acceso a EV no
    transfiere derechos ni autoriza copiar, alterar, extraer, distribuir, vender o explotar estos elementos fuera de lo
    permitido por ley o por una autorización expresa.
  </p>
</section>

<section id="privacidad">
  <h2>21. Privacidad, visibilidad y datos personales</h2>
  <p>
    El tratamiento de datos se rige por la Política de Privacidad y Tratamiento de Datos Personales, que debe leerse y
    aceptarse por separado. EV muestra a otros usuarios únicamente la información necesaria para la interacción comunitaria
    y la ejecución de operaciones, de acuerdo con la configuración y las reglas informadas.
  </p>
  <p>
    La verificación de residencia no autoriza a otros vecinos a acceder a comprobantes, documentos de identidad ni datos
    internos de revisión. El uso de la información para publicidad requiere una base legal y, cuando corresponda,
    consentimiento adicional, independiente y opcional.
  </p>
</section>

<section id="canales-externos">
  <h2>22. WhatsApp, redes sociales y enlaces externos</h2>
  <p>
    EV puede ofrecer enlaces a WhatsApp y redes sociales oficiales para soporte, información y contacto. Al utilizar un
    servicio externo, el usuario interactúa también con el proveedor de ese servicio y quedan aplicables sus propias
    condiciones y políticas de privacidad.
  </p>
  <p>
    EV no enviará publicidad por WhatsApp, correo u otros medios que requieran consentimiento adicional sin obtener
    previamente una autorización separada. La negativa a recibir promociones no limita las funciones esenciales de la cuenta.
  </p>
</section>

<section id="notificaciones">
  <h2>23. Comunicaciones y notificaciones</h2>
  <p>
    EV puede enviar notificaciones operativas relacionadas con el registro, validación de residencia, seguridad, pedidos,
    solicitudes, cotizaciones, reprogramaciones, incidencias, soporte, calificaciones, comunidad y cambios relevantes del servicio.
    El usuario debe mantener actualizados sus datos de contacto y revisar el Centro de Notificaciones.
  </p>
  <p>
    Las comunicaciones operativas indispensables no son publicidad. Las promociones o campañas comerciales se gestionarán
    de forma separada cuando la normativa exija consentimiento.
  </p>
</section>

<section id="moderacion">
  <h2>24. Moderación, medidas y derecho a aclarar</h2>
  <p>
    EV puede observar, limitar, ocultar, desactivar o retirar contenido; solicitar una subsanación; restringir funciones;
    suspender temporalmente o inhabilitar cuentas cuando existan indicios razonables de incumplimiento, fraude, riesgo de
    seguridad, afectación a terceros o mandato de autoridad competente.
  </p>
  <p>
    La medida debe ser razonable respecto de la gravedad, reiteración, evidencia y riesgo. Cuando sea viable y no comprometa
    una investigación o la seguridad, EV informará el motivo y habilitará un canal para que el usuario presente una aclaración.
    Las operaciones pendientes podrán ser limitadas, cerradas o conservadas únicamente en la medida necesaria para proteger
    a las partes y mantener la trazabilidad.
  </p>
</section>

<section id="disponibilidad">
  <h2>25. Disponibilidad, mantenimiento y seguridad</h2>
  <p>
    EV aplica medidas razonables para mantener la disponibilidad y seguridad, pero ningún sistema puede garantizar continuidad
    absoluta o ausencia total de errores. Puede realizar mantenimiento, actualizaciones, correcciones, respaldos y cambios
    técnicos. Las interrupciones planificadas se comunicarán cuando sea razonablemente posible.
  </p>
  <p>
    El usuario debe mantener actualizado su dispositivo y navegador, evitar programas maliciosos y utilizar conexiones seguras.
    EV no es responsable por fallas atribuibles exclusivamente a servicios de terceros, conectividad del usuario o eventos fuera
    de su control razonable, sin perjuicio de las responsabilidades que la ley no permita excluir.
  </p>
</section>

<section id="responsabilidad">
  <h2>26. Responsabilidades y derechos que no pueden excluirse</h2>
  <p>
    EV responde por las obligaciones que le correspondan como titular y operador de la Plataforma. Cada usuario responde por
    la veracidad de su información, legalidad de su contenido, calidad y cumplimiento de sus ofertas, pagos directos y demás
    compromisos que asume frente a otra persona.
  </p>
  <p>
    Ninguna cláusula pretende excluir derechos irrenunciables, limitar responsabilidades que no puedan limitarse por ley,
    trasladar al usuario obligaciones propias de EV ni impedir que una persona acuda a Indecopi, la Autoridad Nacional de
    Protección de Datos Personales, la Policía, el Ministerio Público, el Poder Judicial u otra autoridad competente.
  </p>
</section>

<section id="reclamos">
  <h2>27. Atención, soporte y Libro de Reclamaciones</h2>
  <p>
    Para consultas operativas, el usuario puede escribir a <strong><?= $e($r['correo_soporte'] ?? '') ?></strong> o comunicarse
    al <strong><?= $e($r['telefono_soporte'] ?? '') ?></strong>. Para presentar un reclamo o una queja, EV pone a disposición
    un <strong>Libro de Reclamaciones Virtual</strong> visible y accesible en <?= $e($r['sitio_web'] ?? '') ?>.
  </p>
  <p>
    El registro en el Libro genera una constancia y un número de seguimiento. Los reclamos y quejas serán atendidos dentro del
    plazo legal aplicable. El Libro corresponde al servicio digital brindado por EV. Cuando un caso involucre un producto o servicio
    ofrecido por otro usuario, EV lo evaluará dentro de su ámbito de actuación, sin sustituir las obligaciones ni los canales de reclamo
    que correspondan al proveedor directo. El uso del Libro no impide acudir a otras vías de solución ni constituye una renuncia a derechos.
  </p>
</section>

<section id="cierre-cuenta">
  <h2>28. Cierre de cuenta y efectos</h2>
  <p>
    El usuario puede solicitar el cierre de su cuenta mediante los canales habilitados. Antes del cierre, EV puede requerir que
    se atiendan operaciones o incidencias pendientes y verificar la identidad del solicitante. El cierre impide el acceso, pero
    no obliga a eliminar de inmediato información que deba conservarse por seguridad, trazabilidad, reclamos, obligaciones legales
    o defensa de derechos, conforme a la Política de Privacidad.
  </p>
</section>

<section id="modificaciones">
  <h2>29. Modificaciones, integridad y legislación aplicable</h2>
  <p>
    EV puede actualizar estos Términos por cambios legales, técnicos, operativos o comerciales. Cada versión mostrará su número,
    fecha de publicación y vigencia. Los cambios materiales serán comunicados y requerirán nueva aceptación cuando corresponda.
  </p>
  <p>
    Si una cláusula es declarada inválida o inaplicable, las demás continuarán vigentes en la medida permitida por ley. Estos
    Términos se interpretan conforme a las leyes de la República del Perú, incluyendo las normas de protección al consumidor,
    protección de datos personales, comercio electrónico y demás disposiciones aplicables.
  </p>
</section>

<section id="contacto-final">
  <h2>30. Contacto</h2>
  <div class="ev-legal-contact-card">
    <p><strong>Soporte:</strong> <?= $e($r['correo_soporte'] ?? '') ?> · <?= $e($r['telefono_soporte'] ?? '') ?></p>
    <p><strong>Reclamos:</strong> <?= $e($r['correo_reclamos'] ?? '') ?></p>
    <p><strong>Privacidad:</strong> <?= $e($r['correo_privacidad'] ?? '') ?></p>
    <p><strong>Domicilio para notificaciones:</strong> <?= $e($r['domicilio_notificaciones'] ?? $r['domicilio'] ?? '') ?></p>
  </div>
</section>

<div class="ev-legal-final-note">
  <strong>Versión 1.0.</strong> Publicada el 12 de julio de 2026. Vigente desde el 10 de agosto de 2026.
</div>
